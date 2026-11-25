@php
    $statusLabels = [
        'in_progress' => 'W realizacji',
        'planned' => 'Zaplanowane',
        'completed' => 'Zakończone',
    ];
@endphp

@if ($assignments->isEmpty())
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Ta maszyna nie jest obecnie przypisana do żadnego zadania.
    </p>
@else
    <div style="display: flex; flex-direction: column; gap: 16px;">
        @foreach ($assignments as $item)
            <div
                style="margin-bottom: 14px;"
                class="rounded-lg border border-gray-200 dark:border-gray-700 p-3.5 space-y-2.5"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="font-semibold text-sm text-gray-900 dark:text-white">
                        {{ $item['task_type'] }} — {{ $item['field_name'] }}
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        <span>Operator: <strong>{{ $item['operator_name'] }}</strong></span>
                    </div>

                    @if (! empty($item['task_url']))
                        <a
                            href="{{ $item['task_url'] }}"
                            style="color: #89b329; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center;"
                        >
                            Przejdź do zadania
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif