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
            'day' => $this->truncated('day', 30, fn (Carbon $date) => $date->format('d/m/Y')),
            'month' => $this->truncated('month', 12, fn (Carbon $date) => self::MONTH_NAMES[$date->month].'/'.$date->format('Y')),
            'quarter' => $this->truncated('quarter', 8, fn (Carbon $date) => ceil($date->month / 3).'º Trimestre/'.$date->format('Y')),
            'year' => $this->truncated('year', 5, fn (Carbon $date) => $date->format('Y')),
            'semester' => $this->semesters(6),
        };
    }

    private function truncated(string $unit, int $limit, callable $label): Collection
    {
        return Sale::where('status', '!=', 'cancelled')
            ->selectRaw("date_trunc('{$unit}', created_at) as period, count(*) as count, sum(total) as total")
            ->groupBy('period')
            ->orderByDesc('period')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => (object) [
                'label' => $label(Carbon::parse($row->period)),
                'count' => (int) $row->count,
                'total' => (float) $row->total,
            ]);
    }

    private function semesters(int $limit): Collection
    {
        return Sale::where('status', '!=', 'cancelled')
            ->selectRaw('extract(year from created_at) as year, ceil(extract(month from created_at) / 6.0) as semester, count(*) as count, sum(total) as total')
            ->groupBy('year', 'semester')
            ->orderByDesc('year')
            ->orderByDesc('semester')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => (object) [
                'label' => (int) $row->semester.'º Semestre/'.(int) $row->year,
                'count' => (int) $row->count,
                'total' => (float) $row->total,
            ]);
    }
}
