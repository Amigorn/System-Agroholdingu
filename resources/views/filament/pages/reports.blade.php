<x-filament-panels::page>
    @php
        $sections = $this->getReportSections();
    @endphp

    <style>
        .hr-wrap {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .hr-nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.09);
        }
        .hr-nav-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
            margin-right: 4px;
        }
        .hr-nav-link {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            color: #e5e7eb;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.15s ease;
        }
        .hr-nav-link:hover {
            border-color: rgba(245, 158, 11, 0.5);
            color: #fbbf24;
            background: rgba(245, 158, 11, 0.1);
        }
        .hr-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 16px;
            margin-bottom: 18px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.025);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .hr-toolbar-label {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 500;
        }
        .hr-period-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .hr-period-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.03);
            color: #d1d5db;
            transition: all 0.15s ease;
        }
        .hr-period-btn:hover {
            border-color: rgba(245, 158, 11, 0.45);
            color: #fbbf24;
        }
        .hr-period-btn.is-active {
            background: #f59e0b;
            border-color: #f59e0b;
            color: #111827;
            font-weight: 700;
        }
        .hr-grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .hr-grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .hr-grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }
        .hr-kpi {
            padding: 16px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.09);
        }
        .hr-kpi-label {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 500;
        }
        .hr-kpi-value {
            margin-top: 8px;
            font-size: 22px;
            font-weight: 700;
            color: #f9fafb;
            line-height: 1.2;
        }
        .hr-kpi-bar {
            margin-top: 12px;
            height: 3px;
            width: 38px;
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.7);
        }
        .hr-box {
            padding: 16px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .hr-box-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
        }
        .hr-box-title {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #f3f4f6;
        }
        .hr-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .hr-badge-amber {
            background: rgba(245, 158, 11, 0.14);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.28);
        }
        .hr-badge-green {
            background: rgba(16, 185, 129, 0.14);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.28);
        }
        .hr-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }
        .hr-list-item:first-child {
            border-top: none;
            padding-top: 2px;
        }
        .hr-item-title {
            font-size: 13px;
            font-weight: 600;
            color: #f9fafb;
        }
        .hr-item-meta {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px;
        }
        .hr-subcard {
            padding: 11px 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            margin-bottom: 10px;
        }
        .hr-subcard:last-child {
            margin-bottom: 0;
        }
        .hr-subcard-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #f9fafb;
        }
        .hr-subcard-val {
            color: #fbbf24;
            white-space: nowrap;
        }
        .hr-subcard-sub {
            margin-top: 4px;
            font-size: 11px;
            color: #9ca3af;
        }
        .hr-table-wrap {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .hr-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .hr-table th {
            text-align: left;
            padding: 10px 14px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .hr-table th:last-child,
        .hr-table td:last-child {
            text-align: right;
        }
        .hr-table td {
            padding: 11px 14px;
            color: #e5e7eb;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .hr-table tr:last-child td {
            border-bottom: none;
        }
        .hr-empty {
            font-size: 12px;
            color: #9ca3af;
            padding: 8px 0;
            margin: 0;
        }

        .hr-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            margin-top: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
        }
        .hr-pagination-info {
            color: #9ca3af;
            font-size: 12px;
        }
        .hr-pagination-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .hr-page-btn {
            padding: 6px 11px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.04);
            color: #e5e7eb;
            font-size: 12px;
            cursor: pointer;
        }
        .hr-page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        .hr-page-number {
            color: #d1d5db;
            font-size: 12px;
            white-space: nowrap;
        }
    </style>

    <div class="hr-wrap">
        <div class="hr-nav">
            <span class="hr-nav-label">Sekcje:</span>
            @foreach ($sections as $key => $section)
                <a href="#report-{{ $key }}" class="hr-nav-link">
                    {{ str_replace('Raporty dotyczące ', '', $section['title']) }}
                </a>
            @endforeach
        </div>

        @foreach ($sections as $key => $section)
            <div id="report-{{ $key }}">
                <x-filament::section
                    :heading="$section['title']"
                    :description="$section['subtitle']"
                >
                    @if (($section['type'] ?? null) === 'field_work')
                        <div class="hr-toolbar">
                            <span class="hr-toolbar-label">Okres analizy prac polowych:</span>
                            <div class="hr-period-group">
                                @foreach ($section['period_options'] as $periodKey => $periodLabel)
                                    <button
                                        type="button"
                                        wire:click="$set('fieldWorkPeriod', '{{ $periodKey }}')"
                                        class="hr-period-btn {{ $fieldWorkPeriod === $periodKey ? 'is-active' : '' }}"
                                    >
                                        {{ $periodLabel }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="hr-grid-4">
                            @foreach ($section['metrics'] as $metric)
                                <div class="hr-kpi">
                                    <div class="hr-kpi-label">{{ $metric['label'] }}</div>
                                    <div class="hr-kpi-value">{{ $metric['value'] }}</div>
                                    <div class="hr-kpi-bar"></div>
                                </div>
                            @endforeach
                        </div>

                        <div class="hr-grid-2">
                            <div class="hr-box">
                                <div class="hr-box-head">
                                    <h4 class="hr-box-title">Wykonane prace polowe</h4>
                                    <span class="hr-badge hr-badge-green">
                                        {{ $section['summary_counts']['completed'] ?? 0 }} zrealizowanych
                                    </span>
                                </div>

                                @if (! empty($section['completed_tasks']))
                                    @foreach ($section['completed_tasks'] as $task)
                                        <div class="hr-list-item">
                                            <div>
                                                <div class="hr-item-title">{{ $task['title'] }}</div>
                                                <div class="hr-item-meta">{{ $task['meta'] }}</div>
                                            </div>
                                            <span class="hr-badge hr-badge-green">{{ $task['value'] }}</span>
                                        </div>
                                    @endforeach

                                    @php $completedPagination = $section['completed_pagination'] ?? []; @endphp
                                    @if (($completedPagination['last_page'] ?? 1) > 1)
                                        <div class="hr-pagination">
                                            <div class="hr-pagination-info">
                                                Wyświetlanie <strong>{{ $completedPagination['from'] }}</strong>–<strong>{{ $completedPagination['to'] }}</strong>
                                                z <strong>{{ $completedPagination['total'] }}</strong> prac
                                            </div>
                                            <div class="hr-pagination-buttons">
                                                <button type="button" wire:click="$set('completedTasksPage', {{ max(1, ($completedPagination['current_page'] ?? 1) - 1) }})" @disabled(($completedPagination['current_page'] ?? 1) <= 1) class="hr-page-btn">&laquo; Poprzednia</button>
                                                <span class="hr-page-number">{{ $completedPagination['current_page'] }} / {{ $completedPagination['last_page'] }}</span>
                                                <button type="button" wire:click="$set('completedTasksPage', {{ min($completedPagination['last_page'], $completedPagination['current_page'] + 1) }})" @disabled($completedPagination['current_page'] >= $completedPagination['last_page']) class="hr-page-btn">Następna &raquo;</button>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <p class="hr-empty">Brak wykonanych prac w wybranym okresie.</p>
                                @endif
                            </div>

                            <div class="hr-box">
                                <div class="hr-box-head">
                                    <h4 class="hr-box-title">Niewykonane prace</h4>
                                    <span class="hr-badge hr-badge-amber">
                                        {{ $section['summary_counts']['pending'] ?? 0 }} oczekujących
                                    </span>
                                </div>

                                @if (! empty($section['pending_tasks']))
                                    @foreach ($section['pending_tasks'] as $task)
                                        <div class="hr-list-item">
                                            <div>
                                                <div class="hr-item-title">{{ $task['title'] }}</div>
                                                <div class="hr-item-meta">{{ $task['meta'] }}</div>
                                            </div>
                                            <span class="hr-badge hr-badge-amber">{{ $task['value'] }}</span>
                                        </div>
                                    @endforeach

                                    @php $pendingPagination = $section['pending_pagination'] ?? []; @endphp
                                    @if (($pendingPagination['last_page'] ?? 1) > 1)
                                        <div class="hr-pagination">
                                            <div class="hr-pagination-info">
                                                Wyświetlanie <strong>{{ $pendingPagination['from'] }}</strong>–<strong>{{ $pendingPagination['to'] }}</strong>
                                                z <strong>{{ $pendingPagination['total'] }}</strong> prac
                                            </div>
                                            <div class="hr-pagination-buttons">
                                                <button type="button" wire:click="$set('pendingTasksPage', {{ max(1, ($pendingPagination['current_page'] ?? 1) - 1) }})" @disabled(($pendingPagination['current_page'] ?? 1) <= 1) class="hr-page-btn">&laquo; Poprzednia</button>
                                                <span class="hr-page-number">{{ $pendingPagination['current_page'] }} / {{ $pendingPagination['last_page'] }}</span>
                                                <button type="button" wire:click="$set('pendingTasksPage', {{ min($pendingPagination['last_page'], $pendingPagination['current_page'] + 1) }})" @disabled($pendingPagination['current_page'] >= $pendingPagination['last_page']) class="hr-page-btn">Następna &raquo;</button>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <p class="hr-empty">Brak niewykonanych prac w wybranym okresie.</p>
                                @endif
                            </div>
                        </div>

                        <div class="hr-grid-3">
                            <div class="hr-box">
                                <div class="hr-box-head">
                                    <h4 class="hr-box-title">Prace według pól</h4>
                                </div>
                                @if (! empty($section['by_field']))
                                    @foreach ($section['by_field'] as $item)
                                        <div class="hr-subcard">
                                            <div class="hr-subcard-top">
                                                <span>{{ $item['name'] }}</span>
                                                <span class="hr-subcard-val">{{ $item['count'] }} zab. • {{ $item['area'] }}</span>
                                            </div>
                                            <div class="hr-subcard-sub">{{ $item['treatments'] }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="hr-empty">Brak danych według pól.</p>
                                @endif
                            </div>

                            <div class="hr-box">
                                <div class="hr-box-head">
                                    <h4 class="hr-box-title">Prace według pracowników</h4>
                                </div>
                                @if (! empty($section['by_employee']))
                                    @foreach ($section['by_employee'] as $item)
                                        <div class="hr-subcard">
                                            <div class="hr-subcard-top">
                                                <span>{{ $item['name'] }}</span>
                                                <span class="hr-subcard-val">{{ $item['count'] }} prac • {{ $item['hours'] }}</span>
                                            </div>
                                            <div class="hr-subcard-sub">{{ $item['kinds'] }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="hr-empty">Brak danych według pracowników.</p>
                                @endif
                            </div>

                            <div class="hr-box">
                                <div class="hr-box-head">
                                    <h4 class="hr-box-title">Prace według maszyn</h4>
                                </div>
                                @if (! empty($section['by_machine']))
                                    @foreach ($section['by_machine'] as $item)
                                        <div class="hr-subcard">
                                            <div class="hr-subcard-top">
                                                <span>{{ $item['name'] }}</span>
                                                <span class="hr-subcard-val">{{ $item['hours'] }} • {{ $item['area'] }}</span>
                                            </div>
                                            <div class="hr-subcard-sub">Liczba zabiegów: {{ $item['tasks_count'] }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="hr-empty">Brak danych według maszyn.</p>
                                @endif
                            </div>
                        </div>

                    @else
                        <div class="hr-grid-4">
                            @foreach ($section['metrics'] as $metric)
                                <div class="hr-kpi">
                                    <div class="hr-kpi-label">{{ $metric['label'] }}</div>
                                    <div class="hr-kpi-value">{{ $metric['value'] }}</div>
                                    <div class="hr-kpi-bar"></div>
                                </div>
                            @endforeach
                        </div>

                        @if (! empty($section['rows']))
                            <div class="hr-table-wrap">
                                <table class="hr-table">
                                    <thead>
                                        <tr>
                                            @foreach ($section['columns'] as $col)
                                                <th>{{ $col }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($section['rows'] as $row)
                                            <tr>
                                                <td><strong>{{ $row['title'] }}</strong></td>
                                                <td><span class="hr-badge hr-badge-amber">{{ $row['meta'] }}</span></td>
                                                <td><span class="hr-badge hr-badge-green">{{ $row['value'] }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="hr-empty">Brak zarejestrowanych rekordów dla tej kategorii raportowej.</p>
                        @endif
                    @endif
                </x-filament::section>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>