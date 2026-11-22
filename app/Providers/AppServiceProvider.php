<?php

namespace App\Providers;

use App\Models\Machine;
use App\Models\TaskMachine;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Machine::updated(function (Machine $machine): void {
            if (! $machine->wasChanged('status')) {
                return;
            }

            $repairStatuses = ['under_repair', 'w naprawie', 'in_repair', 'repair'];
            $newStatus = mb_strtolower(trim((string) $machine->status));

            if (! in_array($newStatus, $repairStatuses, true)) {
                return;
            }

            $plannedStatuses = ['planned', 'zaplanowane', 'zaplanowany', 'scheduled'];

            $assignments = TaskMachine::query()
                ->with(['task.field'])
                ->where(function ($q) use ($machine): void {
                    $q->where('machine_id', $machine->getKey())
                        ->orWhere('tractor_id', $machine->getKey());
                })
                ->whereHas('task', function ($q) use ($plannedStatuses): void {
                    $q->whereIn('status', $plannedStatuses);
                })
                ->get()
                ->groupBy('task_id');

            if ($assignments->isEmpty()) {
                return;
            }

            $recipients = User::query()->get();
            if ($recipients->isEmpty()) {
                return;
            }

            $machineLabel = $machine->name ?? $machine->model ?? ('Maszyna #' . $machine->getKey());

            foreach ($assignments as $taskAssignments) {
                $task = $taskAssignments->first()?->task;
                if (! $task) {
                    continue;
                }

                try {
                    $taskTitle = $task->type ?: ('Zadanie #' . $task->getKey());
                    $fieldName = $task->field?->name ? " ({$task->field->name})" : '';

                    $notification = Notification::make()
                        ->warning()
                        ->title('Maszyna w naprawie — zmień sprzęt w zadaniu')
                        ->body("Maszyna {$machineLabel} trafiła do naprawy. Wymaga podmiany w zaplanowanym zadaniu: {$taskTitle}{$fieldName}.");

                    if (class_exists(\App\Filament\Resources\Tasks\TaskResource::class)) {
                        $url = \App\Filament\Resources\Tasks\TaskResource::getUrl('edit', ['record' => $task]);
                        $notification->actions([
                            Action::make('open_task')
                                ->label('Otwórz zadanie')
                                ->url($url),
                        ]);
                    }

                    $notification->sendToDatabase($recipients);
                } catch (\Throwable $e) {
                    Log::error('Błąd powiadomienia o maszynie w naprawie', [
                        'task_id' => $task->getKey(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}