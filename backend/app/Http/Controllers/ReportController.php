<?php

namespace App\Http\Controllers;

use App\Actions\Report\GenerateSalesReport;
use App\Models\Sale;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller implements HasMiddleware
{
    private const GRANULARITIES = ['day', 'month', 'quarter', 'semester', 'year'];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:reports.view'),
        ];
    }

    public function index(Request $request, GenerateSalesReport $action): View
    {
        $granularity = $this->granularity($request);

        return view('reports.index', [
            'rows' => $action->execute($granularity),
            'granularity' => $granularity,
        ]);
    }

    public function export(Request $request, GenerateSalesReport $action): StreamedResponse
    {
        $granularity = $this->granularity($request);
        $rows = $action->execute($granularity);

        $filename = 'relatorio-vendas-'.$granularity.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Período', 'Vendas', 'Total (R$)'], ';');

            foreach ($rows as $row) {
                fputcsv($handle, [$row->label, $row->count, number_format($row->total, 2, ',', '.')], ';');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function detail(Request $request): View
    {
        try {
            $from = Carbon::parse($request->query('from'));
            $to = Carbon::parse($request->query('to'));
        } catch (Exception) {
            abort(400, 'Período inválido.');
        }

        return view('reports.detail', [
            'sales' => Sale::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$from, $to])
                ->with(['customer', 'user'])
                ->orderBy('created_at')
                ->get(),
            'label' => $request->string('label')->value(),
            'granularity' => $this->granularity($request),
        ]);
    }

    private function granularity(Request $request): string
    {
        $granularity = $request->string('granularity')->value();

        return in_array($granularity, self::GRANULARITIES, true) ? $granularity : 'day';
    }
}
