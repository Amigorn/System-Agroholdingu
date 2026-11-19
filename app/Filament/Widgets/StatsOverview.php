<?php

namespace App\Filament\Widgets;

use App\Models\Crop;
use App\Models\Field;
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
        $revenue = Invoice::sum('total') ?? 0;

        return [
            Stat::make('Przychód ze sprzedaży', number_format($revenue, 0, ',', ' ') . ' zł')
                ->description('12% wzrost w ujęciu m/m')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([12, 15, 13, 16, 18, 21, 26]),

            Stat::make('Aktywne uprawy', Crop::count())
                ->description('Bieżący sezon wegetacyjny')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning')
                ->chart([4, 6, 5, 8, 7, 10, 12]),

            Stat::make('Pola uprawne', Field::count())
                ->description('Zarejestrowane działki rolne')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->chart([8, 8, 9, 10, 10, 11, 12]),
        ];
    }
}