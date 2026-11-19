<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Machine;
use App\Models\Task;
use App\Models\TaskMachine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $fields = Field::all();
        $workers = User::where('role', 'worker')->get();
        if ($workers->isEmpty()) {
            $workers = User::all();
        }

        $driveUnits = Machine::whereIn('type', ['Ciągnik', 'Kombajn'])->get();
        $machines = Machine::whereNotIn('type', ['Ciągnik', 'Kombajn'])->get();

        if ($fields->isEmpty() || $driveUnits->isEmpty()) {
            return;
        }

        Machine::query()->update([
            'status' => 'available',
        ]);

        // Przechowuje zajęte przedziały czasowe: ['tractor' => [id => [[start, end], ...]], 'machine' => ..., 'user' => ...]
        $bookings = [
            'tractor' => [],
            'machine' => [],
            'user' => [],
        ];

        $overlaps = function (Carbon $startA, Carbon $endA, Carbon $startB, Carbon $endB): bool {
            return $startA->lt($endB) && $endA->gt($startB);
        };

        $isFree = function (string $bucket, int $id, Carbon $start, Carbon $end) use (&$bookings, $overlaps): bool {
            foreach ($bookings[$bucket][$id] ?? [] as [$bStart, $bEnd]) {
                if ($overlaps($start, $end, $bStart, $bEnd)) {
                    return false;
                }
            }

            return true;
        };

        $book = function (string $bucket, int $id, Carbon $start, Carbon $end) use (&$bookings): void {
            $bookings[$bucket][$id][] = [$start->copy(), $end->copy()];
        };

        for ($i = 0; $i < 10; $i++) {
            $task = Task::factory()->create([
                'field_id' => $fields->random()->id,
            ]);

            $taskStart = Carbon::parse($task->planned_at);
            $taskEnd = $task->planned_end_at
                ? Carbon::parse($task->planned_end_at)
                : ($task->completed_at ? Carbon::parse($task->completed_at) : $taskStart->copy()->addHours(4));

            if ($taskEnd->lte($taskStart)) {
                $taskEnd = $taskStart->copy()->addHours(2);
            }

            $isInProgress = $task->status === 'in_progress';
            $setsCount = rand(1, 2);
            $createdSets = 0;

            for ($j = 0; $j < $setsCount; $j++) {
                $freeUnits = $driveUnits->filter(
                    fn ($u) => $isFree('tractor', (int) $u->id, $taskStart, $taskEnd)
                );

                if ($freeUnits->isEmpty()) {
                    break;
                }

                $unit = $freeUnits->random();
                $isHarvester = $unit->type === 'Kombajn';

                $machine = null;
                if (! $isHarvester) {
                    $freeMachines = $machines->filter(
                        fn ($m) => $isFree('machine', (int) $m->id, $taskStart, $taskEnd)
                    );
                    $machine = $freeMachines->isNotEmpty() ? $freeMachines->random() : null;
                }

                $freeWorkers = $workers->filter(
                    fn ($u) => $isFree('user', (int) $u->id, $taskStart, $taskEnd)
                );
                $worker = $freeWorkers->isNotEmpty() ? $freeWorkers->random() : null;

                TaskMachine::create([
                    'task_id' => $task->id,
                    'tractor_id' => $unit->id,
                    'machine_id' => $machine?->id,
                    'user_id' => $worker?->id,
                    'notes' => fake()->optional()->sentence(4),
                ]);

                $book('tractor', (int) $unit->id, $taskStart, $taskEnd);
                if ($machine !== null) {
                    $book('machine', (int) $machine->id, $taskStart, $taskEnd);
                }
                if ($worker !== null) {
                    $book('user', (int) $worker->id, $taskStart, $taskEnd);
                }

                $createdSets++;
            }

            if ($isInProgress && $createdSets === 0) {
                $task->update([
                    'status' => 'planned',
                    'started_at' => null,
                ]);
            }
        }

        /*
         * SYNCHRONIZACJA STATUSÓW MASZYN
         */
        $inUseMachineIds = DB::table('task_machines')
            ->join('tasks', 'tasks.id', '=', 'task_machines.task_id')
            ->where('tasks.status', 'in_progress')
            ->get([
                'task_machines.tractor_id',
                'task_machines.machine_id',
            ])
            ->flatMap(fn ($row) => [
                $row->tractor_id,
                $row->machine_id,
            ])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        Machine::query()->update([
            'status' => 'available',
        ]);

        if ($inUseMachineIds->isNotEmpty()) {
            Machine::whereIn('id', $inUseMachineIds)->update([
                'status' => 'in_use',
            ]);
        }

        $repairMachineIds = Machine::query()
            ->whereNotIn('id', $inUseMachineIds->isNotEmpty()
                ? $inUseMachineIds
                : [0])
            ->inRandomOrder()
            ->limit(rand(2, 4))
            ->pluck('id');

        if ($repairMachineIds->isNotEmpty()) {
            Machine::whereIn('id', $repairMachineIds)->update([
                'status' => 'under_repair',
            ]);
        }
    }
}