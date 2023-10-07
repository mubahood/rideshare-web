<?php
if (!isset($events_count)) {
    $events_count = 0;
}
if (!isset($project_count)) {
    $project_count = 0;
}
if (!isset($tasks_count)) {
    $tasks_count = 0;
}
?>
<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-md-4">
                @include('widgets.box-6', [
                    'is_dark' => false,
                    'title' => 'Today\'s Appointments',
                    'icon' => 'box',
                    'number' => rand(1, 100),
                    'link' => 'javascript:;',
                ])
            </div>
            <div class="col-md-4">
                @include('widgets.box-6', [
                    'is_dark' => false,
                    'title' => 'This Week\'s Appointments',
                    'icon' => 'list-task',
                    'number' => rand(50, 300),
                    'link' => 'javascript:;',
                ])
            </div>
            <div class="col-md-4">
                @include('widgets.box-6', [
                    'is_dark' => false,
                    'title' => 'Upcoming Appointments',
                    'icon' => 'calendar-event-fill',
                    'number' => rand(2, 500),
                    'link' => 'javascript:;',
                ])
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        @include('dashboard.upcoming-events', [
            'items' => $events,
        ])
    </div>
    <div class="col-md-6">
        @include('dashboard.tasks', [
            'items' => $events,
        ])
    </div>
</div>
