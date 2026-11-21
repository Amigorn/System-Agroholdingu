<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int | array
    {
        return 3;
    }

    protected function getStats(): array
    {
        $revenue = Invoice::where('type', 'sale')->sum('total') ?? 0;
        $costs = Invoice::where('type', 'purchase')->sum('total') ?? 0;

        return [
            Stat::make('Przychód ze sprzedaży', number_format($revenue, 2, ',', ' ') . ' zł')
                ->description('Faktury sprzedażowe')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([12, 15, 13, 16, 18, 21, 26]),

            Stat::make('Ponoszone koszty', number_format($costs, 2, ',', ' ') . ' zł')
                ->description('Faktury zakupowe')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning')
                ->chart([6, 8, 7, 9, 8, 11, 10]),

            Stat::make('Liczba faktur', Invoice::count())
                ->description('Wszystkie dokumenty kupna i sprzedaży')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->chart([8, 8, 9, 10, 10, 11, 12]),
        ];
    }
}