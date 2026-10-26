<?php

namespace App\Filament\Widgets;

use App\Models\Field;
use App\Models\Task;
use App\Models\Machine;
use App\Models\Crop;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pola uprawne', Field::count())
                ->description('Wszystkie zarejestrowane działki')
                ->descriptionIcon('heroicon-m-map')
                ->color('success'),

            Stat::make('Zadania', Task::count())
                ->description('Łączna liczba zadań')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('warning'),

            Stat::make('Maszyny', Machine::count())
                ->description('Park maszynowy')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            Stat::make('Uprawy', Crop::count())
                ->description('Aktywne uprawy w sezonie')
                ->descriptionIcon('heroicon-m-rectangle-group')
                ->color('primary'),
        ];
    }
}