<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CropChart;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\TaskStatusChart;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Analytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Analiza Biznesowa';
    
    protected static ?string $slug = 'reports/analytics';

    protected static string|UnitEnum|null $navigationGroup = 'Raporty i Zestawienia';

    protected string $view = 'filament.pages.analytics';

    protected function getHeaderWidgets(): array
    {
        return [
            RevenueChart::class,
            CropChart::class,
            TaskStatusChart::class,
        ];
    }
}