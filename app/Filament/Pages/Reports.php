<?php

namespace App\Filament\Pages;

use App\Models\Crop;
use App\Models\Field;
use App\Models\Machine;
use App\Models\Task;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class Reports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?string $navigationLabel = 'Raporty Operacyjne';
    protected static ?string $title = 'Raporty';
    protected static ?string $slug = 'reports/operational';
    protected static string|UnitEnum|null $navigationGroup = 'Raporty i Zestawienia';
    protected string $view = 'filament.pages.reports';

    public string $fieldWorkPeriod = '90d';

    public int $completedTasksPage = 1;
    public int $pendingTasksPage = 1;
    public int $byFieldPage = 1;
    public int $byEmployeePage = 1;
    public int $byMachinePage = 1;
    public int $fuelRowsPage = 1;
    public int $cropsRowsPage = 1;

    public function updatedFieldWorkPeriod(): void
    {
        $this->completedTasksPage = 1;
        $this->pendingTasksPage = 1;
        $this->byFieldPage = 1;
        $this->byEmployeePage = 1;
        $this->byMachinePage = 1;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getReportSections(): array
    {
        return [
            'field_work' => $this->getFieldWorkReport(),
            'fuel_costs' => $this->getFuelAndOperatingCostsReport(),
            'fields_crops' => $this->getFieldsAndCropsReport(),
            'employees' => $this->getEmployeesReport(),
            'machines' => $this->getMachinesReport(),
        ];
    }

    protected function resolvePeriodStart(): ?Carbon
    {
        return match ($this->fieldWorkPeriod) {
            '30d' => now()->subDays(30)->startOfDay(),
            '90d' => now()->subDays(90)->startOfDay(),
            'year' => now()->startOfYear(),
            'all' => null,
            default => now()->subDays(90)->startOfDay(),
        };
    }

    protected function paginateReportItems(Collection|array $items, int $page, int $perPage = 5): array
    {
        $items = collect($items)->values();
        $total = $items->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);

        return [
            'items' => $items->forPage($page, $perPage)->values()->all(),
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'total' => $total,
                'from' => $total > 0 ? (($page - 1) * $perPage + 1) : null,
                'to' => $total > 0 ? min($page * $perPage, $total) : null,
            ],
        ];
    }

    protected function isCompletedTask(object $task): bool
    {
        $status = strtolower((string) ($task->status ?? ''));

        return in_array($status, [
            'completed', 'done', 'finished', 'ukończone', 'wykonane', 'zakończone',
        ], true) || ! empty($task->completed_at);
    }

    protected function extractTaskArea(object $task): float
    {
        foreach (['area', 'hectares', 'worked_area', 'treated_area'] as $column) {
            if (isset($task->{$column}) && is_numeric($task->{$column})) {
                return (float) $task->{$column};
            }
        }

        if ($task->relationLoaded('field') && $task->field) {
            foreach (['area', 'hectares'] as $column) {
                if (isset($task->field->{$column}) && is_numeric($task->field->{$column})) {
                    return (float) $task->field->{$column};
                }
            }
        }

        return 0.0;
    }

    protected function extractTaskHours(object $task): float
    {
        if ($task->relationLoaded('resourceConsumptions') && $task->resourceConsumptions instanceof Collection) {
            $total = 0.0;

            foreach ($task->resourceConsumptions as $consumption) {
                foreach (['hours', 'engine_hours', 'mth', 'moto_hours', 'work_hours'] as $column) {
                    if (isset($consumption->{$column}) && is_numeric($consumption->{$column}) && $consumption->{$column} > 0) {
                        $total += (float) $consumption->{$column};
                        break;
                    }
                }
            }

            if ($total > 0) {
                return round($total, 2);
            }
        }

        if (! empty($task->started_at) && ! empty($task->completed_at)) {
            $start = Carbon::parse($task->started_at);
            $end = Carbon::parse($task->completed_at);

            if ($end->greaterThan($start)) {
                return round($start->diffInMinutes($end) / 60, 1);
            }
        }

        if ($this->isCompletedTask($task)) {
            $startRaw = $task->started_at ?? $task->planned_at;
            $endRaw = $task->completed_at ?? $task->planned_end_at;

            if ($startRaw && $endRaw) {
                $start = Carbon::parse($startRaw);
                $end = Carbon::parse($endRaw);

                if ($end->greaterThan($start)) {
                    return round($start->diffInMinutes($end) / 60, 1);
                }
            }
        }

        return 0.0;
    }

    protected function getFieldWorkReport(): array
    {
        $periodOptions = [
            '30d' => 'Ostatnie 30 dni',
            '90d' => 'Ostatnie 90 dni',
            'year' => 'Bieżący rok (' . now()->year . ')',
            'all' => 'Cały okres',
        ];

        $emptyPagination = [
            'current_page' => 1, 'last_page' => 1, 'total' => 0, 'from' => null, 'to' => null,
        ];

        $empty = [
            'title' => 'Raporty dotyczące prac polowych',
            'subtitle' => 'Wykonane i zaplanowane zabiegi, powierzchnia uprawiona, motogodziny oraz podział na pola, pracowników i maszyny.',
            'type' => 'field_work',
            'period_options' => $periodOptions,
            'metrics' => [
                ['label' => 'Liczba wykonanych zabiegów', 'value' => 0],
                ['label' => 'Łączna powierzchnia uprawiona', 'value' => '0,00 ha'],
                ['label' => 'Liczba przepracowanych motogodzin', 'value' => '0,0 mth'],
                ['label' => 'Stopień realizacji planu prac polowych', 'value' => '0,0%'],
            ],
            'summary_counts' => ['completed' => 0, 'pending' => 0, 'total' => 0],
            'completed_tasks' => [], 'completed_pagination' => $emptyPagination,
            'pending_tasks' => [], 'pending_pagination' => $emptyPagination,
            'by_field' => [], 'by_field_pagination' => $emptyPagination,
            'by_employee' => [], 'by_employee_pagination' => $emptyPagination,
            'by_machine' => [], 'by_machine_pagination' => $emptyPagination,
            'columns' => [], 'rows' => [],
        ];

        if (! class_exists(Task::class) || ! Schema::hasTable('tasks')) {
            return $empty;
        }

        $start = $this->resolvePeriodStart();
        $relations = [];

        if (method_exists(Task::class, 'field')) {
            $relations[] = 'field';
        }
        if (Schema::hasTable('task_machines') && method_exists(Task::class, 'taskMachines')) {
            $relations[] = 'taskMachines';
            if (class_exists(\App\Models\TaskMachine::class)) {
                foreach (['machine', 'tractor', 'user'] as $relation) {
                    if (method_exists(\App\Models\TaskMachine::class, $relation)) {
                        $relations[] = 'taskMachines.' . $relation;
                    }
                }
            }
        }
        if (Schema::hasTable('resource_consumptions') && method_exists(Task::class, 'resourceConsumptions')) {
            $relations[] = 'resourceConsumptions';
        }

        $completedStatuses = ['completed', 'done', 'finished', 'ukończone', 'wykonane', 'zakończone'];

        $query = Task::query()->when($relations, fn (Builder $q) => $q->with(array_unique($relations)));

        if ($start) {
            $query->where(function (Builder $outer) use ($start, $completedStatuses) {
                // Zadania wykonane: data zakończenia, a w starszych rekordach daty planowane.
                $outer->where(function (Builder $completedQuery) use ($start, $completedStatuses) {
                    $completedQuery->whereIn('status', $completedStatuses);
                    $completedQuery->where(function (Builder $dates) use ($start) {
                        $hasDate = false;

                        if (Schema::hasColumn('tasks', 'completed_at')) {
                            $dates->where('completed_at', '>=', $start);
                            $hasDate = true;
                        }
                        if (Schema::hasColumn('tasks', 'planned_end_at')) {
                            $method = $hasDate ? 'orWhere' : 'where';
                            $dates->{$method}(function (Builder $q) use ($start) {
                                $q->whereNull('completed_at')->where('planned_end_at', '>=', $start);
                            });
                            $hasDate = true;
                        }
                        if (Schema::hasColumn('tasks', 'planned_at')) {
                            $method = $hasDate ? 'orWhere' : 'where';
                            $dates->{$method}(function (Builder $q) use ($start) {
                                $q->whereNull('completed_at')->whereNull('planned_end_at')->where('planned_at', '>=', $start);
                            });
                            $hasDate = true;
                        }
                        if (Schema::hasColumn('tasks', 'created_at')) {
                            $method = $hasDate ? 'orWhere' : 'where';
                            $dates->{$method}(function (Builder $q) use ($start) {
                                $q->whereNull('completed_at')->whereNull('planned_end_at')->whereNull('planned_at')->where('created_at', '>=', $start);
                            });
                        }
                    });
                });

                // Zadania oczekujące / w realizacji.
                $outer->orWhere(function (Builder $pendingQuery) use ($start, $completedStatuses) {
                    $pendingQuery->whereNotIn('status', $completedStatuses);
                    $pendingQuery->where(function (Builder $dates) use ($start) {
                        $hasDate = false;
                        if (Schema::hasColumn('tasks', 'planned_at')) {
                            $dates->where('planned_at', '>=', $start);
                            $hasDate = true;
                        }
                        if (Schema::hasColumn('tasks', 'planned_end_at')) {
                            $method = $hasDate ? 'orWhere' : 'where';
                            $dates->{$method}('planned_end_at', '>=', $start);
                            $hasDate = true;
                        }
                        if (Schema::hasColumn('tasks', 'created_at')) {
                            $method = $hasDate ? 'orWhere' : 'where';
                            $dates->{$method}(function (Builder $q) use ($start) {
                                $q->whereNull('planned_at')->whereNull('planned_end_at')->where('created_at', '>=', $start);
                            });
                        }
                    });
                });
            });
        }

        $allTasks = $query->latest('id')->get();
        $completed = $allTasks->filter(fn ($task) => $this->isCompletedTask($task))->values();
        $pending = $allTasks->reject(fn ($task) => $this->isCompletedTask($task))->values();

        $completedResult = $this->paginateReportItems($completed, $this->completedTasksPage);
        $pendingResult = $this->paginateReportItems($pending, $this->pendingTasksPage);
        $this->completedTasksPage = $completedResult['pagination']['current_page'];
        $this->pendingTasksPage = $pendingResult['pagination']['current_page'];

        $workedArea = $completed->sum(fn ($task) => $this->extractTaskArea($task));
        $workedHours = $completed->sum(fn ($task) => $this->extractTaskHours($task));
        $completedCount = $completed->count();
        $pendingCount = $pending->count();
        $totalCount = $allTasks->count();
        $completionRate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 1) : 0;

        $byFieldAll = $completed->groupBy(function ($task) {
            return ($task->relationLoaded('field') && $task->field)
                ? ($task->field->name ?? ('Pole #' . $task->field->id))
                : (! empty($task->field_id) ? 'Pole #' . $task->field_id : 'Bez przypisanego pola');
        })->map(function (Collection $group, string $name) {
            $treatments = $group->map(fn ($task) => $task->type ?: ($task->description ?: 'Zabieg polowy'))
                ->filter()->unique()->take(3)->implode(', ');

            return [
                'name' => $name,
                'treatments' => $treatments ?: 'Zabiegi polowe',
                'count' => $group->count(),
                'area' => number_format($group->sum(fn ($task) => $this->extractTaskArea($task)), 2, ',', ' ') . ' ha',
            ];
        })->sortByDesc('count')->values();

        $employeeSource = $completed->isNotEmpty() ? $completed : $allTasks;
        $userIds = $employeeSource->flatMap(function ($task) {
            return ($task->relationLoaded('taskMachines') && $task->taskMachines)
                ? $task->taskMachines->pluck('user_id') : [];
        })->filter()->map(fn ($id) => (int) $id)->unique()->values();

        $usersMap = [];
        if ($userIds->isNotEmpty() && class_exists(\App\Models\User::class)) {
            $usersMap = \App\Models\User::query()->whereIn('id', $userIds)->get()->mapWithKeys(function ($user) {
                $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                return [$user->id => ($name ?: ($user->name ?? $user->email ?? ('Pracownik #' . $user->id)))];
            })->all();
        }

        $employeeStats = collect();
        foreach ($employeeSource as $task) {
            $taskMachines = ($task->relationLoaded('taskMachines') && $task->taskMachines)
                ? $task->taskMachines->filter(fn ($tm) => ! empty($tm->user_id)) : collect();
            $taskType = $task->type ?: 'Praca polowa';
            $hours = $this->extractTaskHours($task);
            $ids = $taskMachines->pluck('user_id')->map(fn ($id) => (int) $id)->unique()->values();

            if ($ids->isEmpty()) {
                $ids = collect([0]);
            }

            foreach ($ids as $id) {
                $name = $id ? ($usersMap[$id] ?? ('Pracownik #' . $id)) : 'Nieprzypisany';
                $entry = $employeeStats->get($name, [
                    'name' => $name, 'kinds' => collect(), 'count' => 0, 'hours' => 0.0,
                ]);
                $entry['kinds']->push($taskType);
                $entry['count']++;
                $entry['hours'] += $hours / max(1, $ids->count());
                $employeeStats->put($name, $entry);
            }
        }

        $byEmployeeAll = $employeeStats->sortByDesc('count')->map(function (array $item) {
            $kinds = $item['kinds']->filter()->unique()->take(2)->implode(', ');
            return [
                'name' => $item['name'], 'kinds' => $kinds ?: 'Prace polowe',
                'count' => $item['count'], 'hours' => number_format($item['hours'], 1, ',', ' ') . ' mth',
            ];
        })->values();

        $machineStats = collect();
        $machineIds = $completed->flatMap(function ($task) {
            if (! $task->relationLoaded('taskMachines') || ! $task->taskMachines) {
                return [];
            }
            return $task->taskMachines->flatMap(fn ($tm) => array_filter([$tm->machine_id ?? null, $tm->tractor_id ?? null]));
        })->map(fn ($id) => (int) $id)->unique()->values();

        $machineMap = [];
        if ($machineIds->isNotEmpty() && class_exists(Machine::class)) {
            $machineMap = Machine::query()->whereIn('id', $machineIds)->get()
                ->mapWithKeys(fn ($machine) => [$machine->id => ($machine->name ?? $machine->model ?? ('Maszyna #' . $machine->id))])
                ->all();
        }

        foreach ($completed as $task) {
            if (! $task->relationLoaded('taskMachines') || ! $task->taskMachines) {
                continue;
            }

            $names = collect();
            foreach ($task->taskMachines as $tm) {
                foreach (['machine_id' => 'Maszyna', 'tractor_id' => 'Ciągnik'] as $column => $prefix) {
                    $id = $tm->{$column} ?? null;
                    if (! $id) {
                        continue;
                    }
                    $relation = $column === 'machine_id' ? 'machine' : 'tractor';
                    $object = method_exists($tm, 'relationLoaded') && $tm->relationLoaded($relation) ? $tm->{$relation} : null;
                    $names->push($object?->name ?? $object?->model ?? ($machineMap[$id] ?? ($prefix . ' #' . $id)));
                }
            }

            $names = $names->filter()->unique()->values();
            if ($names->isEmpty()) {
                continue;
            }

            $hoursShare = $this->extractTaskHours($task) / $names->count();
            $areaShare = $this->extractTaskArea($task) / $names->count();

            foreach ($names as $name) {
                $entry = $machineStats->get($name, [
                    'name' => $name, 'tasks_count' => 0, 'hours' => 0.0, 'area' => 0.0,
                ]);
                $entry['tasks_count']++;
                $entry['hours'] += $hoursShare;
                $entry['area'] += $areaShare;
                $machineStats->put($name, $entry);
            }
        }

        $byMachineAll = $machineStats->sortByDesc('tasks_count')->map(fn (array $item) => [
            'name' => $item['name'], 'tasks_count' => $item['tasks_count'],
            'hours' => number_format($item['hours'], 1, ',', ' ') . ' mth',
            'area' => number_format($item['area'], 2, ',', ' ') . ' ha',
        ])->values();

        $byFieldResult = $this->paginateReportItems($byFieldAll, $this->byFieldPage);
        $byEmployeeResult = $this->paginateReportItems($byEmployeeAll, $this->byEmployeePage);
        $byMachineResult = $this->paginateReportItems($byMachineAll, $this->byMachinePage);

        $this->byFieldPage = $byFieldResult['pagination']['current_page'];
        $this->byEmployeePage = $byEmployeeResult['pagination']['current_page'];
        $this->byMachinePage = $byMachineResult['pagination']['current_page'];

        $formatTask = function ($task, bool $isCompleted) {
            $title = $task->type ?: (\Illuminate\Support\Str::limit($task->description ?? '', 45) ?: ('Zabieg #' . $task->id));
            $field = ($task->relationLoaded('field') && $task->field)
                ? ($task->field->name ?? ('Pole #' . $task->field->id)) : 'Ogólne / brak pola';

            if ($isCompleted) {
                $date = $task->completed_at ?? $task->planned_end_at ?? $task->planned_at ?? $task->created_at;
                $schedule = $date ? 'Zakończono: ' . Carbon::parse($date)->format('d.m.Y H:i') : '-';
                $status = 'Wykonano';
            } else {
                $date = $task->planned_at ?? $task->created_at;
                $schedule = $date ? Carbon::parse($date)->format('d.m.Y H:i') : '-';
                $status = $task->status ?: 'Zaplanowane';
            }

            return [
                'title' => $title,
                'meta' => $field . ' • ' . $schedule,
                'value' => $status,
            ];
        };

        return [
            'title' => 'Raporty dotyczące prac polowych',
            'subtitle' => 'Wykonane i zaplanowane zabiegi w wybranym okresie, powierzchnia uprawiona, motogodziny oraz podział na pola, pracowników i maszyny.',
            'type' => 'field_work',
            'period_options' => $periodOptions,
            'metrics' => [
                ['label' => 'Liczba wykonanych zabiegów', 'value' => $completedCount],
                ['label' => 'Łączna powierzchnia uprawiona', 'value' => number_format($workedArea, 2, ',', ' ') . ' ha'],
                ['label' => 'Liczba przepracowanych motogodzin', 'value' => number_format($workedHours, 1, ',', ' ') . ' mth'],
                ['label' => 'Stopień realizacji planu prac polowych', 'value' => number_format($completionRate, 1, ',', ' ') . '%'],
            ],
            'summary_counts' => ['completed' => $completedCount, 'pending' => $pendingCount, 'total' => $totalCount],
            'completed_tasks' => collect($completedResult['items'])->map(fn ($task) => $formatTask($task, true))->all(),
            'completed_pagination' => $completedResult['pagination'],
            'pending_tasks' => collect($pendingResult['items'])->map(fn ($task) => $formatTask($task, false))->all(),
            'pending_pagination' => $pendingResult['pagination'],
            'by_field' => $byFieldResult['items'],
            'by_field_pagination' => $byFieldResult['pagination'],
            'by_employee' => $byEmployeeResult['items'],
            'by_employee_pagination' => $byEmployeeResult['pagination'],
            'by_machine' => $byMachineResult['items'],
            'by_machine_pagination' => $byMachineResult['pagination'],
            'columns' => [], 'rows' => [],
        ];
    }

    protected function getFuelAndOperatingCostsReport(): array
    {
        $fuelLiters = 0.0;
        $rows = [];

        if (
            Schema::hasTable('resource_consumptions')
            && Schema::hasTable('resources')
            && Schema::hasColumn('resource_consumptions', 'resource_id')
            && Schema::hasColumn('resource_consumptions', 'quantity')
            && Schema::hasColumn('resources', 'type')
        ) {
            $fuelLiters = (float) DB::table('resource_consumptions')
                ->join('resources', 'resources.id', '=', 'resource_consumptions.resource_id')
                ->where('resources.type', 'paliwo')
                ->sum('resource_consumptions.quantity');

            $rows[] = [
                'title' => 'Paliwo',
                'meta' => 'Łączne zużycie paliwa',
                'value' => number_format($fuelLiters, 1, ',', ' ') . ' l',
            ];

            $otherResources = DB::table('resource_consumptions')
                ->join('resources', 'resources.id', '=', 'resource_consumptions.resource_id')
                ->where(function ($query) {
                    $query->whereNull('resources.type')->orWhere('resources.type', '!=', 'paliwo');
                })
                ->select(
                    'resources.id as resource_id',
                    'resources.name',
                    'resources.type',
                    'resources.unit_id',
                    DB::raw('SUM(resource_consumptions.quantity) as total_quantity')
                )
                ->groupBy('resources.id', 'resources.name', 'resources.type', 'resources.unit_id')
                ->orderBy('resources.name')
                ->get();

            foreach ($otherResources as $item) {
                $resource = class_exists(\App\Models\Resource::class)
                    ? \App\Models\Resource::with('unit')->find($item->resource_id)
                    : null;
                $unit = $resource?->unit?->symbol ?? '';
                $rows[] = [
                    'title' => $item->name,
                    'meta' => $item->type ?: 'Inny zasób',
                    'value' => number_format((float) $item->total_quantity, 2, ',', ' ') . ($unit !== '' ? ' ' . $unit : ''),
                ];
            }
        }

        $otherResourcesCount = max(0, count($rows) - ($fuelLiters > 0 || count($rows) > 0 ? 1 : 0));
        $rowsResult = $this->paginateReportItems($rows, $this->fuelRowsPage);
        $this->fuelRowsPage = $rowsResult['pagination']['current_page'];

        return [
            'title' => 'Raporty dotyczące paliwa i kosztów eksploatacji',
            'subtitle' => 'Zużycie paliwa oraz pozostałych zasobów gospodarstwa.',
            'metrics' => [
                ['label' => 'Zużyte paliwo', 'value' => number_format($fuelLiters, 1, ',', ' ') . ' l'],
                ['label' => 'Liczba innych zasobów', 'value' => $otherResourcesCount . ' szt.'],
            ],
            'columns' => ['Źródło / Kategoria', 'Rodzaj zasobu', 'Zużyta ilość'],
            'rows' => $rowsResult['items'],
            'rows_pagination' => $rowsResult['pagination'],
        ];
    }

    protected function getMachinesReport(): array
    {
        $total = class_exists(Machine::class) ? Machine::count() : 0;
        $active = 0;

        if (class_exists(Machine::class)) {
            $active = Schema::hasColumn('machines', 'status')
                ? Machine::whereIn('status', ['active', 'available', 'operational', 'sprawna'])->count()
                : $total;
        }

        return [
            'title' => 'Raporty dotyczące maszyn i pojazdów',
            'subtitle' => 'Stan parku maszynowego, dostępność ciągników, kombajnów i pojazdów transportowych.',
            'metrics' => [
                ['label' => 'Łącznie jednostek', 'value' => $total],
                ['label' => 'Gotowe do pracy', 'value' => $active],
                ['label' => 'Wyłączone / serwis', 'value' => max(0, $total - $active)],
                ['label' => 'Dostępność floty', 'value' => $total > 0 ? round(($active / $total) * 100, 1) . '%' : '0%'],
            ],
            'columns' => [], 'rows' => [],
        ];
    }

    protected function getEmployeesReport(): array
    {
        $count = 0;

        foreach (['employees', 'workers', 'staff'] as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                break;
            }
        }

        if ($count === 0 && Schema::hasTable('users')) {
            $count = DB::table('users')->count();
        }

        return [
            'title' => 'Raporty dotyczące pracowników',
            'subtitle' => 'Zatrudnienie, obsada stanowisk oraz przypisanie kadr do prac i działów.',
            'metrics' => [
                ['label' => 'Liczba osób', 'value' => $count],
                ['label' => 'Aktywna kadra', 'value' => $count],
                ['label' => 'Zadania / osoba', 'value' => $count > 0 && class_exists(Task::class) ? round(Task::count() / $count, 1) : 0],
                ['label' => 'Obsada', 'value' => $count > 0 ? '100%' : '0%'],
            ],
            'columns' => [], 'rows' => [],
        ];
    }

    protected function getFieldsAndCropsReport(): array
    {
        $fieldsCount = class_exists(Field::class) ? Field::count() : 0;
        $cropsCount = class_exists(Crop::class) ? Crop::count() : 0;
        $totalArea = 0.0;

        if (class_exists(Field::class) && Schema::hasColumn('fields', 'area')) {
            $totalArea = (float) Field::sum('area');
        } elseif (class_exists(Field::class) && Schema::hasColumn('fields', 'hectares')) {
            $totalArea = (float) Field::sum('hectares');
        }

        $allRows = class_exists(Crop::class)
            ? Crop::query()
                ->select('name', DB::raw('count(*) as total'))
                ->groupBy('name')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($crop) => [
                    'title' => $crop->name ?? 'Uprawa',
                    'meta' => 'Struktura zasiewów',
                    'value' => $crop->total . ' szt.',
                ])->values()
            : collect();

        $rowsResult = $this->paginateReportItems($allRows, $this->cropsRowsPage);
        $this->cropsRowsPage = $rowsResult['pagination']['current_page'];

        return [
            'title' => 'Raporty dotyczące pól i upraw',
            'subtitle' => 'Areał gospodarstwa, liczba działek ewidencyjnych oraz struktura upraw.',
            'metrics' => [
                ['label' => 'Pola uprawne', 'value' => $fieldsCount],
                ['label' => 'Aktywne uprawy', 'value' => $cropsCount],
                ['label' => 'Łączny areał', 'value' => number_format($totalArea, 2, ',', ' ') . ' ha'],
                ['label' => 'Śr. pow. pola', 'value' => $fieldsCount > 0 && $totalArea > 0 ? number_format($totalArea / $fieldsCount, 2, ',', ' ') . ' ha' : '0,00 ha'],
            ],
            'columns' => ['Uprawa', 'Kategoria', 'Liczebność'],
            'rows' => $rowsResult['items'],
            'rows_pagination' => $rowsResult['pagination'],
        ];
    }
}
