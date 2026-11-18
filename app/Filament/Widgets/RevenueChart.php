<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Przychody ze sprzedaży płodów rolnych i usług (ostatnie 6 miesięcy)';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => now()->subMonths($i));

        try {
            if (! class_exists(Invoice::class)) {
                throw new \RuntimeException('Brak modelu Invoice');
            }

            $model = new Invoice();
            $table = $model->getTable();
            $amountColumn = Schema::hasColumn($table, 'total')
                ? 'total'
                : (Schema::hasColumn($table, 'amount') ? 'amount' : null);

            if (! $amountColumn) {
                throw new \RuntimeException('Brak kolumny kwoty w tabeli faktur');
            }

            $startDate = now()->subMonths(5)->startOfMonth();
            $endDate = now()->endOfMonth();

            $rows = Invoice::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM($amountColumn) as total_sum")
                ->groupBy('ym')
                ->pluck('total_sum', 'ym');

            $labels = $months->map(fn (Carbon $date) => $date->translatedFormat('M Y'))->toArray();
            $values = $months->map(fn (Carbon $date) => (float) ($rows[$date->format('Y-m')] ?? 0))->toArray();

            return [
                'datasets' => [
                    [
                        'label' => 'Przychód (PLN)',
                        'data' => $values,
                        'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                        'borderColor' => '#059669',
                        'borderWidth' => 2,
                        'fill' => true,
                    ],
                ],
                'labels' => $labels,
            ];
        } catch (\Throwable $e) {
            return [
                'datasets' => [
                    [
                        'label' => 'Przychód (PLN - Symulacja)',
                        'data' => [45000, 59000, 80000, 125000, 160000, 210000],
                        'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                        'borderColor' => '#059669',
                        'borderWidth' => 2,
                        'fill' => true,
                    ],
                ],
                'labels' => $months->map(fn (Carbon $date) => $date->translatedFormat('M Y'))->toArray(),
            ];
        }
    }

    protected function getType(): string
    {
        return 'line';
    }
}