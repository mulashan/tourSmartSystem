@php
    $dashboardCalendarMonths = $dashboardCalendarMonths ?? $dashboard_calendar_months ?? [];
    $dashboardCurrentMonth = $dashboardCurrentMonth ?? $dashboard_current_month ?? null;
    $dashboardCalendarYear = $dashboardCalendarYear ?? $dashboard_calendar_year ?? (int) date('Y');
    $dashboardCalendarColors = [
        1 => ['bg' => '#fff3a7', 'border' => '#efd86f'],
        2 => ['bg' => '#f6c7df', 'border' => '#e2a7c4'],
        3 => ['bg' => '#dff6b4', 'border' => '#c3e48d'],
        4 => ['bg' => '#bfe8f5', 'border' => '#98d3e4'],
        5 => ['bg' => '#ddd0f3', 'border' => '#c2afe8'],
        6 => ['bg' => '#ffe8b6', 'border' => '#f0cf84'],
        7 => ['bg' => '#f8c2d5', 'border' => '#e79db7'],
        8 => ['bg' => '#f6e2a6', 'border' => '#e4cb74'],
        9 => ['bg' => '#e9c4de', 'border' => '#d7a6c7'],
        10 => ['bg' => '#dff2bd', 'border' => '#c4de8f'],
        11 => ['bg' => '#cfeef0', 'border' => '#aadce0'],
        12 => ['bg' => '#e4d7f6', 'border' => '#cdb9eb'],
    ];
@endphp

@if($dashboardCurrentMonth)
    @php
        $currentMonthColor = $dashboardCalendarColors[(int) $dashboardCurrentMonth['month_number']] ?? ['bg' => '#f5f7fb', 'border' => '#dbe4f0'];
        $currentMonthEvents = $dashboardCurrentMonth['events'] ?? [];
    @endphp
    <div
        class="dashboard-calendar-card"
        id="dashboardCurrentCalendarCard"
        style="background: {{ $currentMonthColor['bg'] }}; border-color: {{ $currentMonthColor['border'] }};"
        data-toggle="modal"
        data-target="#dashboardCalendarMonthsModal"
    >
        <div class="dashboard-calendar-head">
            <div class="dashboard-calendar-label">School Calendar</div>
            <h3 class="dashboard-calendar-title">{{ strtoupper($dashboardCurrentMonth['month_name']) }}</h3>
            <div class="dashboard-calendar-year">{{ $dashboardCalendarYear }}</div>
        </div>
        <div class="dashboard-calendar-body">
            @if(count($currentMonthEvents) > 0)
                <ul class="dashboard-calendar-events">
                    @foreach($currentMonthEvents as $event)
                        @php
                            $event = (object) $event;
                            $eventLabel = strlen($event->event_description) > 36 ? substr($event->event_description, 0, 33) . '...' : $event->event_description;
                        @endphp
                        <li>
                            <button
                                type="button"
                                class="dashboard-calendar-event-link dashboardEventDetails"
                                data-id="{{ (int) $event->id }}"
                                onclick="event.stopPropagation();"
                            >
                                {{ date('j', strtotime($event->event_date)) }} - {{ $eventLabel }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="dashboard-calendar-empty">No events for this month.</div>
            @endif
        </div>
        <div class="dashboard-calendar-foot">Click to open all months</div>
    </div>
@endif

<div class="modal fade" id="dashboardCalendarMonthsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">School Calendar {{ $dashboardCalendarYear }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="dashboard-month-grid">
                    @foreach($dashboardCalendarMonths as $monthData)
                        @php
                            $monthColor = $dashboardCalendarColors[(int) $monthData['month_number']] ?? ['bg' => '#f5f7fb', 'border' => '#dbe4f0'];
                            $monthEvents = $monthData['events'] ?? [];
                        @endphp
                        <div class="dashboard-month-tile" style="background: {{ $monthColor['bg'] }}; border-color: {{ $monthColor['border'] }};">
                            <div class="dashboard-month-name">{{ strtoupper($monthData['month_name']) }}</div>
                            @if(count($monthEvents) > 0)
                                <ul class="dashboard-month-events">
                                    @foreach($monthEvents as $event)
                                        @php
                                            $event = (object) $event;
                                            $eventLabel = strlen($event->event_description) > 28 ? substr($event->event_description, 0, 25) . '...' : $event->event_description;
                                        @endphp
                                        <li>
                                            <button type="button" class="dashboard-calendar-event-link dashboardEventDetails" data-id="{{ (int) $event->id }}">
                                                {{ date('j', strtotime($event->event_date)) }} - {{ $eventLabel }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="dashboard-month-empty">No events</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
