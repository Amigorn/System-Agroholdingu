<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Widgets\ChartWidget;

class TaskStatusChart extends ChartWidget
{
    // USUŃ słowo "static" (zmień z "protected static" na "protected")
    protected ?string $heading = 'Status zadań w gospodarstwie';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $completed = Task::where('status', 'completed')->count();
        $pending = Task::where('status', '!=', 'completed')->orWhereNull('status')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Liczba zadań',
                    'data' => [$completed, $pending],
                    'backgroundColor' => ['#10b981', '#f59e0b'],
                ],
            ],
            'labels' => ['Ukończone', 'W toku / Zaplanowane'],
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Zgodnie z tym, co wybrałeś w terminalu
    }
}