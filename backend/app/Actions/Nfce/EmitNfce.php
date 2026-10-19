<?php

namespace App\Actions\Nfce;

use App\Models\Nfce;
use App\Models\Sale;
use App\Models\Unit;
use App\Services\Nfce\NfceXmlBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use NFePHP\NFe\Tools;
use Throwable;

class EmitNfce
{
    public function __construct(
        private NfceXmlBuilder $xmlBuilder
    ) {}

    public function execute(Sale $sale): Nfce
    {
        $existing = Nfce::where('sale_id', $sale->id)->first();

        if ($existing?->status === 'authorized') {
            return $existing;
        }

        $unit = $sale->unit;
        $environment = config('nfce.environment');

        [$serie, $numero] = DB::transaction(function () use ($unit) {
            $locked = Unit::query()->lockForUpdate()->findOrFail($unit->id);
            $numero = $locked->nfce_next_number;
            $locked->update(['nfce_next_number' => $numero + 1]);

            return [$locked->nfce_serie, $numero];
        });

        $nfce = Nfce::updateOrCreate(
            ['sale_id' => $sale->id],
            [
                'unit_id' => $unit->id,
                'status' => 'pending',
                'environment' => $environment,
                'serie' => $serie,
                'numero' => $numero,
                'chave_acesso' => null,
                'protocolo_autorizacao' => null,
                'cstat' => null,
                'motivo' => null,
                'xml_path' => null,
                'erro' => null,
            ],
        );

        try {
            $xml = $this->xmlBuilder->build($sale, $serie, $numero);

            $tools = $this->makeTools();
            $tools->model(65);

            $signed = $tools->signNFe($xml);
            $response = $tools->sefazEnviaLote([$signed], (string) $numero, 1);

            $standard = (new Standardize($response))->toStd();
            $cstat = (string) ($standard->protNFe->infProt->cStat ?? $standard->cStat ?? '');
            $motivo = (string) ($standard->protNFe->infProt->xMotivo ?? $standard->xMotivo ?? '');

            if (in_array($cstat, ['100', '150'], true)) {
                $authorized = Complements::toAuthorize($signed, $response);
                $chave = (string) $standard->protNFe->infProt->chNFe;
                $protocolo = (string) $standard->protNFe->infProt->nProt;

                $path = "nfce/{$chave}.xml";
                Storage::put($path, $authorized);

                $nfce->update([
                    'status' => 'authorized',
                    'chave_acesso' => $chave,
                    'protocolo_autorizacao' => $protocolo,
                    'cstat' => $cstat,
                    'motivo' => $motivo,
                    'xml_path' => $path,
                ]);
            } else {
                $nfce->update([
                    'status' => 'rejected',
                    'cstat' => $cstat,
                    'motivo' => $motivo ?: 'Rejeitado pela SEFAZ sem motivo informado.',
                ]);
            }
        } catch (Throwable $exception) {
            $nfce->update([
                'status' => 'error',
                'erro' => $exception->getMessage(),
            ]);
        }

        return $nfce->fresh();
    }

    private function makeTools(): Tools
    {
        $certificatePath = base_path(config('nfce.certificate_path'));
        $certificate = Certificate::readPfx(
            file_get_contents($certificatePath),
            config('nfce.certificate_password'),
        );

        $tpAmb = config('nfce.environment') === 'producao' ? 1 : 2;

        $configJson = json_encode([
            'atualizacao' => now()->toDateTimeString(),
            'tpAmb' => $tpAmb,
            'razaosocial' => config('nfce.emitente.razao_social'),
            'cnpj' => config('nfce.emitente.cnpj'),
            'siglaUF' => config('nfce.emitente.endereco.uf'),
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'tokenIBPT' => null,
            'CSC' => config('nfce.csc'),
            'CSCid' => config('nfce.csc_id'),
        ]);

        return new Tools($configJson, $certificate);
    }
}
