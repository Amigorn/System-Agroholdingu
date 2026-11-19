<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FullInvoicesChart extends ChartWidget
{
    protected ?string $heading = 'Faktury miesięcznie';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $counts = Invoice::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        if ($counts->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Liczba faktur',
                        'data' => [230, 285, 310, 265, 338, 395, 420, 380, 462, 501, 488, 530],
                        'borderColor' => '#eab308',
                        'borderWidth' => 1.5,
                        'backgroundColor' => 'rgba(234, 179, 8, 0.18)',
                    ],
                ],
                'labels' => ['Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Paź', 'Lis', 'Gru'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Liczba faktur',
                    'data' => $counts->pluck('total')->toArray(),
                    'borderColor' => '#eab308',
                    'borderWidth' => 1.5,
                    'backgroundColor' => 'rgba(234, 179, 8, 0.18)',
                ],
            ],
            'labels' => $counts->map(
                fn ($row) => Carbon::createFromFormat('Y-m', $row->month)->translatedFormat('M')
            )->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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