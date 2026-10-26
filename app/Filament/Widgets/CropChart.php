<?php

namespace App\Filament\Widgets;

use App\Models\Crop;
use Filament\Widgets\ChartWidget;

class CropChart extends ChartWidget
{
    // Pamiętamy o braku słowa "static", aby uniknąć konfliktu z klasą bazową
    protected ?string $heading = 'Struktura upraw w gospodarstwie';
    
    // Ustalamy kolejność wyświetlania na pulpicie (np. zaraz pod pierwszym wykresem)
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Grupujemy uprawy po nazwie i zliczamy ich wystąpienia
        $cropData = Crop::select('name', \DB::raw('count(*) as total'))
            ->groupBy('name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Liczba upraw',
                    'data' => $cropData->pluck('total')->toArray(), // Liczba wystąpień dla danej uprawy
                    'backgroundColor' => [
                        '#3b82f6', 
                        '#10b981', 
                        '#f59e0b', 
                        '#ef4444', 
                        '#8b5cf6'
                    ], // Różne kolory dla słupków
                ],
            ],
            // Unikalne nazwy upraw na osi X
            'labels' => $cropData->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Wykres słupkowy
    }

    protected function getOptions(): ?array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}