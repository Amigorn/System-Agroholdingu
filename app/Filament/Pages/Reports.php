<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Models\Crop;
use App\Models\Field;
use App\Models\Invoice;
use App\Models\Machine;
use App\Models\Task;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class Reports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Raporty Operacyjne';
    
    protected static ?string $slug = 'reports/operational';

    protected static string|UnitEnum|null $navigationGroup = 'Raporty i Zestawienia';

    protected string $view = 'filament.pages.reports';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Pobierz raport PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $data = [
                        'generatedAt' => now()->format('d.m.Y H:i'),
                        'fieldsCount' => Field::count(),
                        'cropsCount' => Crop::count(),
                        'machinesCount' => Machine::count(),
                        'totalTasks' => Task::count(),
                        'completedTasks' => Task::where('status', 'completed')->count(),
                        'cropsBreakdown' => Crop::select('name', DB::raw('count(*) as total'))
                            ->groupBy('name')
                            ->get(),
                        'recentInvoices' => Invoice::latest()->take(10)->get(),
                        'totalRevenue' => Invoice::sum('total'),
                    ];

                    $pdf = Pdf::loadView('pdf.holding-report', $data);

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'raport-operacyjny-' . now()->format('Y-m-d') . '.pdf'
                    );
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }
}