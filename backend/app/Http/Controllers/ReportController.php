<?php

namespace App\Http\Controllers;

use App\Actions\Report\GenerateSalesReport;
use App\Models\Sale;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller implements HasMiddleware
{
    private const GRANULARITIES = ['day', 'month', 'quarter', 'semester', 'year'];

    private const PAYMENT_LABELS = [
        'cash' => 'Dinheiro',
        'pix' => 'PIX',
        'debit_card' => 'Cartão de Débito',
        'credit_card' => 'Cartão de Crédito',
        'other' => 'Outro',
    ];

    private const STATUS_LABELS = [
        'awaiting_payment' => 'Aguardando Pagamento',
        'paid' => 'Paga',
        'invoiced' => 'Faturada',
        'cancelled' => 'Cancelada',
    ];

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
            fwrite($handle, "\xEF\xBB\xBF");
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
                ->with(['customer', 'seller', 'user'])
                ->orderBy('created_at')
                ->get(),
            'label' => $request->string('label')->value(),
            'granularity' => $this->granularity($request),
        ]);
    }

    public function salesIndex(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        return view('reports.sales', [
            'sales' => $this->salesBetween($from, $to),
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'paymentLabels' => self::PAYMENT_LABELS,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function salesExport(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);
        $sales = $this->salesBetween($from, $to);

        $filename = 'vendas-'.$from->format('Y-m-d').'-a-'.$to->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Data/Hora', 'Produto(s)', 'Forma de pagamento', 'Vendedor', 'Cliente', 'Status', 'Total (R$)'], ';');

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->created_at->format('d/m/Y H:i'),
                    $sale->items->map(fn ($item) => $item->product->name.' (x'.$item->quantity.')')->implode(', '),
                    self::PAYMENT_LABELS[$sale->payment_method] ?? 'Não informado',
                    $sale->seller?->name ?? '—',
                    $sale->customer?->name ?? 'Não identificado',
                    self::STATUS_LABELS[$sale->status] ?? $sale->status,
                    number_format($sale->total, 2, ',', '.'),
                ], ';');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function dateRange(Request $request): array
    {
        try {
            $from = Carbon::parse($request->query('from', now()->startOfMonth()->format('Y-m-d')))->startOfDay();
            $to = Carbon::parse($request->query('to', now()->format('Y-m-d')))->endOfDay();
        } catch (Exception) {
            abort(400, 'Período inválido.');
        }

        return [$from, $to];
    }

    private function salesBetween(Carbon $from, Carbon $to): Collection
    {
        return Sale::whereBetween('created_at', [$from, $to])
            ->with(['customer', 'seller', 'user', 'items.product'])
            ->orderBy('created_at')
            ->get();
    }

    private function granularity(Request $request): string
    {
        $granularity = $request->string('granularity')->value();

        return in_array($granularity, self::GRANULARITIES, true) ? $granularity : 'day';
    }
}
