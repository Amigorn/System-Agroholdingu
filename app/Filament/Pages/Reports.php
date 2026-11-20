<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Models\Crop;
use App\Models\Field;
use App\Models\Machine;
use App\Models\Task;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
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

    protected static ?string $slug = 'reports/operational';

    protected static string|UnitEnum|null $navigationGroup = 'Raporty i Zestawienia';

    protected string $view = 'filament.pages.reports';

    public string $fieldWorkPeriod = '90d';

    public int $completedTasksPage = 1;

    public int $pendingTasksPage = 1;

    public function updatedFieldWorkPeriod(): void
    {
        $this->completedTasksPage = 1;
        $this->pendingTasksPage = 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //
        ];
    }

    public function getReportSections(): array
    {
        return [
            'field_work' => $this->getFieldWorkReport(),
            'fuel_costs' => $this->getFuelAndOperatingCostsReport(),
            'machines' => $this->getMachinesReport(),
            'employees' => $this->getEmployeesReport(),
            'fields_crops' => $this->getFieldsAndCropsReport(),
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

    protected function isCompletedTask(object $task): bool
    {
        $status = strtolower((string) ($task->status ?? ''));

        if (in_array($status, ['completed', 'done', 'finished', 'ukończone', 'wykonane', 'zakończone'], true)) {
            return true;
        }

        return ! empty($task->completed_at);
    }

    protected function extractTaskArea(object $task): float
    {
        foreach (['area', 'hectares', 'worked_area', 'treated_area'] as $col) {
            if (isset($task->{$col}) && is_numeric($task->{$col})) {
                return (float) $task->{$col};
            }
        }

        if (isset($task->field) && is_object($task->field)) {
            foreach (['area', 'hectares'] as $fCol) {
                if (isset($task->field->{$fCol}) && is_numeric($task->field->{$fCol})) {
                    return (float) $task->field->{$fCol};
                }
            }
        }

        return 0.0;
    }

    protected function extractTaskHours(object $task): float
    {
        $total = 0.0;

        // 1. Jeśli zużycia zasobów przechowują bezpośrednio godziny / mth, użyj ich w pierwszej kolejności
        if ($task->relationLoaded('resourceConsumptions') && $task->resourceConsumptions instanceof Collection) {
            foreach ($task->resourceConsumptions as $rc) {
                foreach (['hours', 'engine_hours', 'mth', 'moto_hours', 'work_hours'] as $col) {
                    if (isset($rc->{$col}) && is_numeric($rc->{$col}) && (float) $rc->{$col} > 0) {
                        $total += (float) $rc->{$col};
                        break;
                    }
                }
            }
        }

        if ($total > 0) {
            return round($total, 2);
        }

        // 2. Realny czas pracy: started_at -> completed_at
        if (! empty($task->started_at) && ! empty($task->completed_at)) {
            $start = Carbon::parse($task->started_at);
            $end = Carbon::parse($task->completed_at);

            if ($end->greaterThan($start)) {
                return round($start->diffInMinutes($end) / 60, 1);
            }
        }

        // 3. Fallback dla ukończonych zadań: planned_at -> completed_at / planned_end_at
        if ($this->isCompletedTask($task)) {
            $startRaw = $task->started_at ?? $task->planned_at;
            $endRaw = $task->completed_at ?? $task->planned_end_at;

            if (! empty($startRaw) && ! empty($endRaw)) {
                $start = Carbon::parse($startRaw);
                $end = Carbon::parse($endRaw);

                if ($end->greaterThan($start)) {
                    return round($start->diffInMinutes($end) / 60, 1);
                }
            }
        }

        return 0.0;
    }
    protected function resolveEmployeeNames(Collection $tasks): array
    {
        $map = [];

        // 1. Z załadowanych relacji na zadaniu
        foreach ($tasks as $task) {
            foreach (['employee', 'user', 'worker', 'assignee', 'assignedTo'] as $rel) {
                if (isset($task->{$rel}) && is_object($task->{$rel})) {
                    $obj = $task->{$rel};
                    $full = trim(($obj->first_name ?? '') . ' ' . ($obj->last_name ?? ''));
                    $label = $full !== '' ? $full : ($obj->name ?? $obj->email ?? null);

                    if ($label && isset($obj->id)) {
                        $map['rel:' . $rel . ':' . $obj->id] = $label;
                    }
                }
            }
        }

        // 2. Z tabel pracowników / użytkowników po ID z kluczy obcych
        $fkColumns = ['employee_id', 'user_id', 'worker_id', 'assignee_id', 'assigned_to', 'created_by'];
        $ids = collect();

        foreach ($tasks as $task) {
            foreach ($fkColumns as $fk) {
                if (! empty($task->{$fk}) && is_numeric($task->{$fk})) {
                    $ids->push((int) $task->{$fk});
                }
            }
        }

        $ids = $ids->unique()->values();
        if ($ids->isEmpty()) {
            return $map;
        }

        foreach (['employees', 'workers', 'staff', 'users'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)->whereIn('id', $ids)->get();
            foreach ($rows as $row) {
                $full = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
                $label = $full !== '' ? $full : ($row->name ?? $row->email ?? ('Pracownik #' . $row->id));
                $map['table:' . $table . ':' . $row->id] = $label;
            }
        }

        return $map;
    }

protected function getFieldWorkReport(): array
    {
        $periodOptions = [
            '30d' => 'Ostatnie 30 dni',
            '90d' => 'Ostatnie 90 dni',
            'year' => 'Bieżący rok (' . now()->year . ')',
            'all' => 'Cały okres',
        ];

        $emptyResult = [
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
            'summary_counts' => [
                'completed' => 0,
                'pending' => 0,
                'total' => 0,
            ],
            'completed_tasks' => [],
            'pending_tasks' => [],
            'by_field' => [],
            'by_employee' => [],
            'by_machine' => [],
            'columns' => ['Zabieg / Praca', 'Pole i data', 'Status'],
            'rows' => [],
        ];

        if (! class_exists(Task::class) || ! Schema::hasTable('tasks')) {
            return $emptyResult;
        }

        $start = $this->resolvePeriodStart();
        $canUseTaskMachines = Schema::hasTable('task_machines') && method_exists(Task::class, 'taskMachines');
        $canUseConsumptions = Schema::hasTable('resource_consumptions') && method_exists(Task::class, 'resourceConsumptions');

        $withRelations = collect(['field'])
            ->filter(fn (string $rel) => method_exists(Task::class, $rel))
            ->when($canUseTaskMachines, function ($c) {
                if (class_exists(\App\Models\TaskMachine::class)) {
                    foreach (['user', 'machine', 'tractor'] as $tmRel) {
                        if (method_exists(\App\Models\TaskMachine::class, $tmRel)) {
                            $c->push('taskMachines.' . $tmRel);
                        }
                    }
                }
                $c->push('taskMachines');
            })
            ->when($canUseConsumptions, fn ($c) => $c->push('resourceConsumptions'))
            ->unique()
            ->values()
            ->all();

        $completedStatuses = [
            'completed',
            'done',
            'finished',
            'ukończone',
            'wykonane',
            'zakończone',
        ];

        /** @var Builder $tasksQuery */
        $tasksQuery = Task::query()
            ->when($withRelations !== [], fn (Builder $q) => $q->with($withRelations))
            ->when($start !== null, function (Builder $q) use ($start, $completedStatuses) {
                $q->where(function (Builder $sub) use ($start, $completedStatuses) {
                    // Zadania wykonane
                    $sub->where(function (Builder $completedQ) use ($start, $completedStatuses) {
                        $completedQ->whereIn('status', $completedStatuses);

                        $completedQ->where(function (Builder $dateQ) use ($start) {
                            $hasAnyDateCol = false;

                            if (Schema::hasColumn('tasks', 'completed_at')) {
                                $dateQ->where('completed_at', '>=', $start);
                                $hasAnyDateCol = true;
                            }

                            if (Schema::hasColumn('tasks', 'planned_end_at')) {
                                $method = $hasAnyDateCol ? 'orWhere' : 'where';
                                $dateQ->{$method}(function (Builder $q) use ($start) {
                                    $q->whereNull('completed_at')
                                        ->where('planned_end_at', '>=', $start);
                                });
                                $hasAnyDateCol = true;
                            }

                            if (Schema::hasColumn('tasks', 'planned_at')) {
                                $method = $hasAnyDateCol ? 'orWhere' : 'where';
                                $dateQ->{$method}(function (Builder $q) use ($start) {
                                    $q->whereNull('completed_at')
                                        ->whereNull('planned_end_at')
                                        ->where('planned_at', '>=', $start);
                                });
                                $hasAnyDateCol = true;
                            }

                            if (Schema::hasColumn('tasks', 'created_at')) {
                                $method = $hasAnyDateCol ? 'orWhere' : 'where';
                                $dateQ->{$method}(function (Builder $q) use ($start) {
                                    $q->whereNull('completed_at')
                                        ->whereNull('planned_end_at')
                                        ->whereNull('planned_at')
                                        ->where('created_at', '>=', $start);
                                });
                            }
                        });
                    });

                    // Zadania niewykonane / w realizacji
                    $sub->orWhere(function (Builder $pendingQ) use ($start, $completedStatuses) {
                        $pendingQ->whereNotIn('status', $completedStatuses);

                        $pendingQ->where(function (Builder $dateQ) use ($start) {
                            $hasAnyDateCol = false;

                            if (Schema::hasColumn('tasks', 'planned_at')) {
                                $dateQ->where('planned_at', '>=', $start);
                                $hasAnyDateCol = true;
                            }

                            if (Schema::hasColumn('tasks', 'planned_end_at')) {
                                $method = $hasAnyDateCol ? 'orWhere' : 'where';
                                $dateQ->{$method}('planned_end_at', '>=', $start);
                                $hasAnyDateCol = true;
                            }

                            if (Schema::hasColumn('tasks', 'created_at')) {
                                $method = $hasAnyDateCol ? 'orWhere' : 'where';
                                $dateQ->{$method}(function (Builder $q) use ($start) {
                                    $q->whereNull('planned_at')
                                        ->whereNull('planned_end_at')
                                        ->where('created_at', '>=', $start);
                                });
                            }
                        });
                    });
                });
            });

        /*
        |--------------------------------------------------------------------------
        | Wszystkie zadania w okresie
        |--------------------------------------------------------------------------
        */
        $allTasks = (clone $tasksQuery)
            ->latest('id')
            ->get();

        $completedCollection = $allTasks
            ->filter(fn ($t) => $this->isCompletedTask($t))
            ->values();

        $pendingCollection = $allTasks
            ->reject(fn ($t) => $this->isCompletedTask($t))
            ->values();

        // Niezależna paginacja dwóch list w sekcji prac polowych.
        $tasksPerPage = 5;
        $completedTotal = $completedCollection->count();
        $pendingTotal = $pendingCollection->count();
        $completedLastPage = max(1, (int) ceil($completedTotal / $tasksPerPage));
        $pendingLastPage = max(1, (int) ceil($pendingTotal / $tasksPerPage));
        $this->completedTasksPage = min(max(1, $this->completedTasksPage), $completedLastPage);
        $this->pendingTasksPage = min(max(1, $this->pendingTasksPage), $pendingLastPage);

        $completedPagination = [
            'current_page' => $this->completedTasksPage,
            'last_page' => $completedLastPage,
            'total' => $completedTotal,
            'from' => $completedTotal ? (($this->completedTasksPage - 1) * $tasksPerPage + 1) : null,
            'to' => $completedTotal ? min($this->completedTasksPage * $tasksPerPage, $completedTotal) : null,
        ];
        $pendingPagination = [
            'current_page' => $this->pendingTasksPage,
            'last_page' => $pendingLastPage,
            'total' => $pendingTotal,
            'from' => $pendingTotal ? (($this->pendingTasksPage - 1) * $tasksPerPage + 1) : null,
            'to' => $pendingTotal ? min($this->pendingTasksPage * $tasksPerPage, $pendingTotal) : null,
        ];

        $completedCount = $completedCollection->count();
        $pendingCount = $pendingCollection->count();
        $totalPlanCount = $allTasks->count();

        $workedArea = $completedCollection->sum(
            fn ($t) => $this->extractTaskArea($t)
        );

        $workedHours = $completedCollection->sum(
            fn ($t) => $this->extractTaskHours($t)
        );

        $completionRate = $totalPlanCount > 0
            ? round(($completedCount / $totalPlanCount) * 100, 1)
            : 0.0;

        /*
        |--------------------------------------------------------------------------
        | 1. Prace według pól
        |--------------------------------------------------------------------------
        */
        $byField = $completedCollection
            ->groupBy(function ($t) {
                if ($t->relationLoaded('field') && $t->field) {
                    return $t->field->name ?? ('Pole #' . $t->field->id);
                }

                return ! empty($t->field_id)
                    ? ('Pole #' . $t->field_id)
                    : 'Bez przypisanego pola';
            })
            ->map(function (Collection $group, string $fieldName) {
                $treatments = $group
                    ->map(fn ($t) => $t->type ?: ($t->description ?: 'Zabieg polowy'))
                    ->filter()
                    ->unique()
                    ->take(3)
                    ->implode(', ');

                $area = $group->sum(
                    fn ($t) => $this->extractTaskArea($t)
                );

                return [
                    'name' => $fieldName,
                    'treatments' => $treatments !== ''
                        ? $treatments
                        : 'Zabiegi polowe',
                    'count' => $group->count(),
                    'area' => number_format($area, 2, ',', ' ') . ' ha',
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->take(5)
            ->all();

        /*
        |--------------------------------------------------------------------------
        | 2. Prace według pracowników (user_id siedzi w task_machines)
        |--------------------------------------------------------------------------
        */
        $employeeSource = $completedCollection->isNotEmpty() ? $completedCollection : $allTasks;

        $userIds = $employeeSource
            ->flatMap(function ($t) {
                if (! $t->relationLoaded('taskMachines') || ! $t->taskMachines) {
                    return [];
                }

                return $t->taskMachines->pluck('user_id');
            })
            ->filter(fn ($id) => ! empty($id) && is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $usersMap = [];
        if ($userIds->isNotEmpty() && class_exists(\App\Models\User::class)) {
            $usersMap = \App\Models\User::query()
                ->whereIn('id', $userIds)
                ->get()
                ->mapWithKeys(function ($user) {
                    $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                    $label = $fullName !== ''
                        ? $fullName
                        : ($user->email ?? ('Pracownik #' . $user->id));

                    return [(int) $user->id => $label];
                })
                ->all();
        }

        $employeeStats = collect();

        foreach ($employeeSource as $task) {
            $taskType = $task->type ?: 'Praca polowa';
            $taskHours = $this->extractTaskHours($task);

            $tms = ($task->relationLoaded('taskMachines') && $task->taskMachines)
                ? $task->taskMachines->filter(fn ($tm) => ! empty($tm->user_id))
                : collect();

            if ($tms->isEmpty()) {
                $current = $employeeStats->get('Nieprzypisany', [
                    'name' => 'Nieprzypisany',
                    'kinds' => collect(),
                    'count' => 0,
                    'hours' => 0.0,
                ]);
                $current['kinds']->push($taskType);
                $current['count'] += 1;
                $current['hours'] += $taskHours;
                $employeeStats->put('Nieprzypisany', $current);
                continue;
            }

            $uniqueUsersInTask = $tms->pluck('user_id')->map(fn ($id) => (int) $id)->unique()->values();
            $shareHours = $uniqueUsersInTask->count() > 0 ? ($taskHours / $uniqueUsersInTask->count()) : 0.0;

            foreach ($uniqueUsersInTask as $uid) {
                $name = $usersMap[$uid] ?? ('Pracownik #' . $uid);
                $current = $employeeStats->get($name, [
                    'name' => $name,
                    'kinds' => collect(),
                    'count' => 0,
                    'hours' => 0.0,
                ]);
                $current['kinds']->push($taskType);
                $current['count'] += 1;
                $current['hours'] += $shareHours;
                $employeeStats->put($name, $current);
            }
        }

        $byEmployee = $employeeStats
            ->sortByDesc('count')
            ->take(5)
            ->map(function (array $item) {
                $kinds = $item['kinds']->filter()->unique()->take(2)->implode(', ');

                return [
                    'name' => $item['name'],
                    'kinds' => $kinds !== '' ? $kinds : 'Prace polowe',
                    'count' => $item['count'],
                    'hours' => number_format($item['hours'], 1, ',', ' ') . ' mth',
                ];
            })
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | 3. Prace według maszyn (oraz ciągników w task_machines)
        |--------------------------------------------------------------------------
        */
        $machineStats = collect();

        $machineIdsToFetch = collect();
        foreach ($completedCollection as $task) {
            if ($task->relationLoaded('taskMachines') && $task->taskMachines) {
                foreach ($task->taskMachines as $tm) {
                    if (! empty($tm->machine_id) && is_numeric($tm->machine_id)) {
                        $machineIdsToFetch->push((int) $tm->machine_id);
                    }
                    if (! empty($tm->tractor_id) && is_numeric($tm->tractor_id)) {
                        $machineIdsToFetch->push((int) $tm->tractor_id);
                    }
                }
            }
        }

        $machinesMap = [];
        if ($machineIdsToFetch->isNotEmpty() && class_exists(Machine::class)) {
            $machinesMap = Machine::query()
                ->whereIn('id', $machineIdsToFetch->unique()->values())
                ->get()
                ->mapWithKeys(fn ($m) => [(int) $m->id => ($m->name ?? $m->model ?? ('Maszyna #' . $m->id))])
                ->all();
        }

        foreach ($completedCollection as $task) {
            $taskArea = $this->extractTaskArea($task);
            $taskHours = $this->extractTaskHours($task);

            $tms = ($task->relationLoaded('taskMachines') && $task->taskMachines)
                ? $task->taskMachines
                : collect();

            $unitsInTask = collect();
            foreach ($tms as $tm) {
                if (! empty($tm->machine_id) && is_numeric($tm->machine_id)) {
                    $mid = (int) $tm->machine_id;
                    $mObj = ($tm->relationLoaded('machine') && $tm->machine) ? $tm->machine : null;
                    $mName = $mObj
                        ? ($mObj->name ?? $mObj->model ?? ('Maszyna #' . $mObj->id))
                        : ($machinesMap[$mid] ?? ('Maszyna #' . $mid));
                    $unitsInTask->push($mName);
                }

                if (! empty($tm->tractor_id) && is_numeric($tm->tractor_id)) {
                    $tid = (int) $tm->tractor_id;
                    $tObj = ($tm->relationLoaded('tractor') && $tm->tractor) ? $tm->tractor : null;
                    $tName = $tObj
                        ? ($tObj->name ?? $tObj->model ?? ('Ciągnik #' . $tObj->id))
                        : ($machinesMap[$tid] ?? ('Ciągnik #' . $tid));
                    $unitsInTask->push($tName);
                }
            }

            $unitsInTask = $unitsInTask->filter()->unique()->values();
            if ($unitsInTask->isEmpty()) {
                continue;
            }

            $shareHours = $unitsInTask->count() > 0 ? ($taskHours / $unitsInTask->count()) : 0.0;
            $shareArea = $unitsInTask->count() > 0 ? ($taskArea / $unitsInTask->count()) : 0.0;

            foreach ($unitsInTask as $unitName) {
                $current = $machineStats->get($unitName, [
                    'name' => $unitName,
                    'tasks_count' => 0,
                    'hours' => 0.0,
                    'area' => 0.0,
                ]);

                $current['tasks_count'] += 1;
                $current['hours'] += $shareHours;
                $current['area'] += $shareArea;

                $machineStats->put($unitName, $current);
            }
        }

        $byMachine = $machineStats
            ->sortByDesc('tasks_count')
            ->take(5)
            ->map(fn (array $item) => [
                'name' => $item['name'],
                'tasks_count' => $item['tasks_count'],
                'hours' => number_format($item['hours'], 1, ',', ' ') . ' mth',
                'area' => number_format($item['area'], 2, ',', ' ') . ' ha',
            ])
            ->values()
            ->all();

        $statusLabels = [
            'completed' => 'Wykonano',
            'done' => 'Wykonano',
            'finished' => 'Wykonano',
            'in_progress' => 'W toku',
            'active' => 'W toku',
            'planned' => 'Zaplanowane',
            'pending' => 'Oczekujące',
        ];

        $formatScheduleWindow = function ($task, bool $completed): string {
            if ($completed) {
                $doneAt = $task->completed_at ?? $task->planned_end_at ?? $task->planned_at ?? $task->created_at;

                return $doneAt ? ('Zakończono: ' . Carbon::parse($doneAt)->format('d.m.Y H:i')) : '-';
            }

            $startAt = $task->planned_at ? Carbon::parse($task->planned_at) : ($task->created_at ? Carbon::parse($task->created_at) : null);
            $endAt = $task->planned_end_at ? Carbon::parse($task->planned_end_at) : null;

            if (! $startAt) {
                return '-';
            }

            if (! $endAt) {
                return $startAt->format('d.m.Y H:i');
            }

            if ($startAt->isSameDay($endAt)) {
                return $startAt->format('d.m.Y') . ' (' . $startAt->format('H:i') . '–' . $endAt->format('H:i') . ')';
            }

            return $startAt->format('d.m.Y H:i') . ' – ' . $endAt->format('d.m.Y H:i');
        };

        $formatTaskRow = function ($task, bool $completed) use ($statusLabels, $formatScheduleWindow) {
            $title = $task->type ?: ($task->description ? \Illuminate\Support\Str::limit($task->description, 45) : ('Zabieg #' . $task->id));
            $fieldLabel = ($task->relationLoaded('field') && $task->field)
                ? ($task->field->name ?? ('Pole #' . $task->field->id))
                : 'Ogólne / brak pola';

            $scheduleFormatted = $formatScheduleWindow($task, $completed);

            $rawStatus = strtolower((string) ($task->status ?? 'planned'));
            $statusLabel = $completed
                ? 'Wykonano'
                : ($statusLabels[$rawStatus] ?? ($task->status ?: 'Zaplanowane'));

            return [
                'title' => $title,
                'meta' => $fieldLabel . ' • ' . $scheduleFormatted,
                'value' => $statusLabel,
            ];
        };

        $completedPageItems = $completedCollection
            ->forPage($this->completedTasksPage, $tasksPerPage)
            ->map(fn ($t) => $formatTaskRow($t, true))
            ->values();
        $pendingPageItems = $pendingCollection
            ->forPage($this->pendingTasksPage, $tasksPerPage)
            ->map(fn ($t) => $formatTaskRow($t, false))
            ->values();

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
            'summary_counts' => [
                'completed' => $completedCount,
                'pending' => $pendingCount,
                'total' => $totalPlanCount,
            ],
            'completed_tasks' => $completedPageItems->all(),
            'completed_pagination' => $completedPagination,
            'pending_tasks' => $pendingPageItems->all(),
            'pending_pagination' => $pendingPagination,
            'by_field' => $byField,
            'by_employee' => $byEmployee,
            'by_machine' => $byMachine,
            'columns' => ['Zabieg / Praca', 'Pole i data', 'Status'],
            'rows' => [],
        ];
    }

    protected function getFuelAndOperatingCostsReport(): array
    {
        $fuelLiters = 0;
        $rows = [];

        if (
            Schema::hasTable('resource_consumptions')
            && Schema::hasTable('resources')
            && Schema::hasColumn('resource_consumptions', 'resource_id')
            && Schema::hasColumn('resource_consumptions', 'quantity')
            && Schema::hasColumn('resources', 'type')
        ) {
            // Łączne zużycie paliwa
            $fuelLiters = (float) DB::table('resource_consumptions')
                ->join(
                    'resources',
                    'resources.id',
                    '=',
                    'resource_consumptions.resource_id'
                )
                ->where('resources.type', 'paliwo')
                ->sum('resource_consumptions.quantity');

            $rows[] = [
                'title' => 'Paliwo',
                'meta' => 'Łączne zużycie paliwa',
                'value' => number_format($fuelLiters, 1, ',', ' ') . ' l',
            ];

            // Pozostałe zasoby, pogrupowane według nazwy
            $otherResources = DB::table('resource_consumptions')
                ->join(
                    'resources',
                    'resources.id',
                    '=',
                    'resource_consumptions.resource_id'
                )
                ->where(function ($query) {
                    $query->whereNull('resources.type')
                        ->orWhere('resources.type', '!=', 'paliwo');
                })
                ->select(
                    'resources.id as resource_id',
                    'resources.name',
                    'resources.type',
                    DB::raw('SUM(resource_consumptions.quantity) as total_quantity')
                )
                ->groupBy(
                    'resources.id',
                    'resources.name',
                    'resources.type'
                )
                ->orderBy('resources.name')
                ->get();

            foreach ($otherResources as $item) {
                $resource = \App\Models\Resource::with('unit')
                    ->find($item->resource_id);

                $unit = $resource?->unit?->symbol ?? '';

                $rows[] = [
                    'title' => $item->name,
                    'meta' => $item->type ?: 'Inny zasób',
                    'value' => number_format(
                        (float) $item->total_quantity,
                        2,
                        ',',
                        ' '
                    ) . ($unit !== '' ? ' ' . $unit : ''),
                ];
            }
        }

        return [
            'title' => 'Raporty dotyczące paliwa i kosztów eksploatacji',
            'subtitle' => 'Zużycie paliwa oraz pozostałych zasobów gospodarstwa.',
            'metrics' => [
                [
                    'label' => 'Zużyte paliwo',
                    'value' => number_format($fuelLiters, 1, ',', ' ') . ' l',
                ],
                [
                    'label' => 'Liczba innych zasobów',
                    'value' => (count($rows) > 0 ? count($rows) - 1 : 0) . ' szt.',
                ],
            ],
            'columns' => ['Źródło / Kategoria', 'Rodzaj zasobu', 'Zużyta ilość'],
            'rows' => $rows,
        ];
    }
   protected function getMachinesReport(): array
    {
        $total = class_exists(Machine::class) ? Machine::count() : 0;
        $active = 0;

        if (class_exists(Machine::class)) {
            if (Schema::hasColumn('machines', 'status')) {
                $active = Machine::whereIn('status', [
                    'active',
                    'available',
                    'operational',
                    'sprawna',
                ])->count();
            } else {
                $active = $total;
            }
        }

        return [
            'title' => 'Raporty dotyczące maszyn i pojazdów',
            'subtitle' => 'Stan parku maszynowego, dostępność ciągników, kombajnów i pojazdów transportowych.',
            'metrics' => [
                ['label' => 'Łącznie jednostek', 'value' => $total],
                ['label' => 'Gotowe do pracy', 'value' => $active],
                ['label' => 'Wyłączone / serwis', 'value' => max(0, $total - $active)],
                [
                    'label' => 'Dostępność floty',
                    'value' => $total > 0
                        ? round(($active / $total) * 100, 1) . '%'
                        : '0%',
                ],
            ],
            'columns' => [],
            'rows' => [],
        ];
    }

   protected function getEmployeesReport(): array
    {
        $employeesCount = 0;

        foreach (['employees', 'workers', 'staff'] as $table) {
            if (Schema::hasTable($table)) {
                $employeesCount = DB::table($table)->count();
                break;
            }
        }

        if ($employeesCount === 0 && Schema::hasTable('users')) {
            $employeesCount = DB::table('users')->count();
        }

        return [
            'title' => 'Raporty dotyczące pracowników',
            'subtitle' => 'Zatrudnienie, obsada stanowisk oraz przypisanie kadr do prac i działów.',
            'metrics' => [
                ['label' => 'Liczba osób', 'value' => $employeesCount],
                ['label' => 'Aktywna kadra', 'value' => $employeesCount],
                [
                    'label' => 'Zadania / osoba',
                    'value' => $employeesCount > 0 && class_exists(Task::class)
                        ? round(Task::count() / $employeesCount, 1)
                        : 0,
                ],
                ['label' => 'Obsada', 'value' => $employeesCount > 0 ? '100%' : '0%'],
            ],
            'columns' => [],
            'rows' => [],
        ];
    }

    protected function getFieldsAndCropsReport(): array
    {
        $fieldsCount = class_exists(Field::class) ? Field::count() : 0;
        $cropsCount = class_exists(Crop::class) ? Crop::count() : 0;
        $totalArea = 0;

        if (class_exists(Field::class) && Schema::hasColumn('fields', 'area')) {
            $totalArea = (float) Field::sum('area');
        } elseif (class_exists(Field::class) && Schema::hasColumn('fields', 'hectares')) {
            $totalArea = (float) Field::sum('hectares');
        }

        $rows = class_exists(Crop::class)
            ? Crop::select('name', DB::raw('count(*) as total'))
                ->groupBy('name')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn ($crop) => [
                    'title' => $crop->name ?? 'Uprawa',
                    'meta' => 'Struktura zasiewów',
                    'value' => $crop->total . ' szt.',
                ])->all()
            : [];

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
            'rows' => $rows,
        ];
    }
}