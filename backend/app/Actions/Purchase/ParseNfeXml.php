<?php

namespace App\Actions\Purchase;

use InvalidArgumentException;
use SimpleXMLElement;

class ParseNfeXml
{
    private const NAMESPACE = 'http://www.portalfiscal.inf.br/nfe';

    /**
     * @return array{
     *     supplier_document: string,
     *     supplier_name: string,
     *     invoice_number: string,
     *     items: array<int, array{code: string, name: string, quantity: float, unit_cost: float}>,
     * }
     */
    public function execute(string $xmlContent): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);

        if ($xml === false) {
            throw new InvalidArgumentException('O arquivo enviado não é um XML válido.');
        }

        $xml->registerXPathNamespace('nfe', self::NAMESPACE);
        $infNFe = $xml->xpath('//nfe:infNFe');

        if (empty($infNFe)) {
            throw new InvalidArgumentException('Não foi possível localizar os dados da NF-e neste arquivo.');
        }

        $infNFe = $infNFe[0];

        return [
            'supplier_document' => $this->onlyDigits((string) ($infNFe->emit->CNPJ ?? '')),
            'supplier_name' => (string) ($infNFe->emit->xNome ?? ''),
            'invoice_number' => (string) ($infNFe->ide->nNF ?? ''),
            'items' => $this->parseItems($infNFe),
        ];
    }

    /**
     * @return array<int, array{code: string, name: string, quantity: float, unit_cost: float}>
     */
    private function parseItems(SimpleXMLElement $infNFe): array
    {
        $items = [];

        foreach ($infNFe->det as $det) {
            $prod = $det->prod;

            if (! $prod) {
                continue;
            }

            $items[] = [
                'code' => (string) $prod->cProd,
                'name' => (string) $prod->xProd,
                'quantity' => (float) $prod->qCom,
                'unit_cost' => (float) $prod->vUnCom,
            ];
        }

        if (empty($items)) {
            throw new InvalidArgumentException('Esta NF-e não possui itens (tags <det>).');
        }

        return $items;
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }
}
