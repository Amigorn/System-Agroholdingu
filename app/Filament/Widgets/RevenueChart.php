<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Przychody (ostatnie miesiące)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $revenues = Invoice::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total) as aggregate')
            )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Przychód (PLN)',
                    'data' => $revenues->pluck('aggregate')->toArray(),
                    'borderColor' => '#eab308',
                    'pointBackgroundColor' => '#facc15',
                    'backgroundColor' => 'rgba(234, 179, 8, 0.12)',
                    'fill' => true,
                    'tension' => 0.15,
                ],
            ],
            'labels' => $revenues->map(
                fn ($row) => Carbon::createFromFormat('Y-m', $row->month)->translatedFormat('M Y')
            )->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}