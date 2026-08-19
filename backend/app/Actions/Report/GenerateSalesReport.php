<?php

namespace App\Actions\Report;

use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GenerateSalesReport
{
    private const GRANULARITIES = ['day', 'month', 'quarter', 'semester', 'year'];

    private const MONTH_NAMES = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    public function execute(string $granularity): Collection
    {
        if (! in_array($granularity, self::GRANULARITIES, true)) {
            throw new InvalidArgumentException("Granularidade de relatório inválida: {$granularity}.");
        }

        return match ($granularity) {
            'day' => $this->byDay(30),
            'month' => $this->byMonth(12),
            'quarter' => $this->byQuarter(8),
            'semester' => $this->bySemester(6),
            'year' => $this->byYear(5),
        };
    }

    // DATE()/EXTRACT() são padrão ANSI SQL e funcionam igual em Postgres e MySQL —
    // evita depender de date_trunc(), que só existe no Postgres, já que a produção
    // roda em MySQL (hospedagem compartilhada) enquanto o dev local usa Postgres.
    private function byDay(int $limit): Collection
    {
        return Sale::where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as day, count(*) as count, sum(total) as total')
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $start = Carbon::parse($row->day)->startOfDay();

                return $this->row($row, $start->format('d/m/Y'), $start, $start->copy()->endOfDay());
            });
    }

    private function byMonth(int $limit): Collection
    {
        return Sale::where('status', '!=', 'cancelled')
            ->selectRaw('EXTRACT(YEAR FROM created_at) as year, EXTRACT(MONTH FROM created_at) as month, count(*) as count, sum(total) as total')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $year = (int) $row->year;
                $month = (int) $row->month;
                $start = Carbon::create($year, $month, 1)->startOfDay();

                return $this->row($row, self::MONTH_NAMES[$month].'/'.$year, $start, $start->copy()->endOfMonth());
            });
    }

    private function byQuarter(int $limit): Collection
    {
        return Sale::where('status', '!=', 'cancelled')
            ->selectRaw('EXTRACT(YEAR FROM created_at) as year, EXTRACT(QUARTER FROM created_at) as quarter, count(*) as count, sum(total) as total')
            ->groupBy('year', 'quarter')
            ->orderByDesc('year')
            ->orderByDesc('quarter')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $year = (int) $row->year;
                $quarter = (int) $row->quarter;
                $start = Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfDay();

                return $this->row($row, "{$quarter}º Trimestre/{$year}", $start, $start->copy()->endOfQuarter());
            });
    }

    private function bySemester(int $limit): Collection
    {
        return Sale::where('status', '!=', 'cancelled')
            ->selectRaw('EXTRACT(YEAR FROM created_at) as year, CEIL(EXTRACT(MONTH FROM created_at) / 6.0) as semester, count(*) as count, sum(total) as total')
            ->groupBy('year', 'semester')
            ->orderByDesc('year')
            ->orderByDesc('semester')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $year = (int) $row->year;
                $semester = (int) $row->semester;
                $start = Carbon::create($year, $semester === 1 ? 1 : 7, 1)->startOfDay();

                return $this->row($row, "{$semester}º Semestre/{$year}", $start, $start->copy()->addMonths(6)->subSecond());
            });
    }

    private function byYear(int $limit): Collection
    {
        return Sale::where('status', '!=', 'cancelled')
            ->selectRaw('EXTRACT(YEAR FROM created_at) as year, count(*) as count, sum(total) as total')
            ->groupBy('year')
            ->orderByDesc('year')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $year = (int) $row->year;
                $start = Carbon::create($year, 1, 1)->startOfDay();

                return $this->row($row, (string) $year, $start, $start->copy()->endOfYear());
            });
    }

    private function row(object $row, string $label, Carbon $start, Carbon $end): object
    {
        return (object) [
            'label' => $label,
            'count' => (int) $row->count,
            'total' => (float) $row->total,
            'start' => $start,
            'end' => $end,
        ];
    }
}
