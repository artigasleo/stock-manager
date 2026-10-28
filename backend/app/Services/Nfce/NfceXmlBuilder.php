<?php

namespace App\Services\Nfce;

use App\Models\Sale;
use NFePHP\NFe\Make;

class NfceXmlBuilder
{
    public function build(Sale $sale, int $serie, int $numero): string
    {
        $emitente = config('nfce.emitente');
        $defaults = config('nfce.defaults');
        $tpAmb = config('nfce.environment') === 'producao' ? 1 : 2;

        $nfe = new Make;

        $std = new \stdClass;
        $std->versao = '4.00';
        $nfe->taginfNFe($std);

        $ide = new \stdClass;
        $ide->cUF = 35; // São Paulo
        $ide->cNF = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $ide->natOp = 'Venda de mercadoria';
        $ide->mod = 65;
        $ide->serie = $serie;
        $ide->nNF = $numero;
        $ide->dhEmi = now()->format('Y-m-d\TH:i:sP');
        $ide->tpNF = 1;
        $ide->idDest = 1;
        $ide->cMunFG = $emitente['endereco']['codigo_municipio_ibge'];
        $ide->tpImp = 4; // DANFE NFC-e
        $ide->tpEmis = 1;
        $ide->tpAmb = $tpAmb;
        $ide->finNFe = 1;
        $ide->indFinal = 1; // consumidor final
        $ide->indPres = 1; // operação presencial
        $ide->procEmi = 0;
        $ide->verProc = '1.0.0';
        $nfe->tagide($ide);

        $emit = new \stdClass;
        $emit->xNome = $emitente['razao_social'];
        $emit->IE = $emitente['ie'];
        $emit->CRT = $emitente['crt'];
        $emit->CNAE = $emitente['cnae'];
        $emit->CNPJ = $emitente['cnpj'];
        $nfe->tagEmit($emit);

        $enderEmit = new \stdClass;
        $enderEmit->xLgr = $emitente['endereco']['logradouro'];
        $enderEmit->nro = $emitente['endereco']['numero'];
        $enderEmit->xBairro = $emitente['endereco']['bairro'];
        $enderEmit->cMun = $emitente['endereco']['codigo_municipio_ibge'];
        $enderEmit->xMun = $emitente['endereco']['municipio'];
        $enderEmit->UF = $emitente['endereco']['uf'];
        $enderEmit->CEP = $emitente['endereco']['cep'];
        $enderEmit->cPais = '1058';
        $enderEmit->xPais = 'Brasil';
        $nfe->tagenderEmit($enderEmit);

        if ($sale->customer?->document) {
            $this->buildDest($nfe, $sale, $tpAmb);
        }

        foreach ($sale->items as $index => $item) {
            $this->buildItem($nfe, $item, $index + 1, $defaults);
        }

        $transp = new \stdClass;
        $transp->modFrete = 9; // sem transporte
        $nfe->tagtransp($transp);

        $pag = new \stdClass;
        $pag->vTroco = 0;
        $nfe->tagpag($pag);

        foreach ($sale->payments as $payment) {
            $detPag = new \stdClass;
            $detPag->tPag = config('payments.nfce_codes')[$payment->payment_method] ?? '99';
            $detPag->vPag = (float) $payment->amount;

            // SEFAZ rejeita (cStat 963) o grupo "card"/tpIntegra pra dinheiro, mas
            // exige (cStat 391, NT 2024.003 v1.11) pra qualquer pagamento eletrônico.
            if (! in_array($payment->payment_method, ['cash', 'cash_usd'], true)) {
                $detPag->tpIntegra = 2; // pagamento não integrado (sem TEF/POS conectado ao sistema)
            }

            $nfe->tagdetPag($detPag);
        }

        return $nfe->getXML();
    }

    private function buildDest(Make $nfe, Sale $sale, int $tpAmb): void
    {
        $dest = new \stdClass;
        $document = $sale->customer->document;

        if (strlen($document) === 14) {
            $dest->CNPJ = $document;
        } else {
            $dest->CPF = $document;
        }

        $dest->xNome = $tpAmb === 2
            ? 'NFE EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL'
            : $sale->customer->name;

        $dest->indIEDest = 9;

        $nfe->tagdest($dest);
    }

    private function buildItem(Make $nfe, $item, int $itemNumber, array $defaults): void
    {
        $product = $item->product;
        $quantity = (float) $item->quantity;
        $unitPrice = (float) $item->unit_price;
        $grossTotal = round($quantity * $unitPrice, 2);
        $discount = round($grossTotal * ((float) $item->discount_percent / 100), 2);

        $prod = new \stdClass;
        $prod->item = $itemNumber;
        $prod->cProd = $product->code;
        $prod->cEAN = $product->barcode ?: 'SEM GTIN';
        $prod->xProd = $product->name;
        $prod->NCM = $product->ncm;
        $prod->CFOP = $product->cfop ?: $defaults['cfop'];
        $prod->uCom = $product->unidade_comercial ?: $defaults['unidade_comercial'];
        $prod->qCom = $quantity;
        $prod->vUnCom = $unitPrice;
        $prod->vProd = $grossTotal;
        $prod->cEANTrib = $prod->cEAN;
        $prod->uTrib = $prod->uCom;
        $prod->qTrib = $quantity;
        $prod->vUnTrib = $unitPrice;

        // vDesc não aceita "0.00" no schema da NFC-e — só entra quando há desconto de fato.
        if ($discount > 0) {
            $prod->vDesc = $discount;
        }

        $prod->indTot = 1;
        $nfe->tagprod($prod);

        $imposto = new \stdClass;
        $imposto->item = $itemNumber;
        $imposto->vTotTrib = 0;
        $nfe->tagimposto($imposto);

        $icms = new \stdClass;
        $icms->item = $itemNumber;
        $icms->orig = $product->origem ?? $defaults['origem'];
        $icms->CSOSN = $product->csosn ?: $defaults['csosn'];
        $nfe->tagICMSSN($icms);
    }
}
