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
if (!isset($farmers_count)) {
    $farmers_count = 0;
}
if (!isset($ditributors_count)) {
    $ditributors_count = 0;
}
?>
<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-md-4">
                @include('widgets.box-6', [
                    'is_dark' => false,
                    'title' => 'Registered Farmers',
                    'icon' => 'box',
                    'number' => number_format($farmers_count),
                    'link' => 'javascript:;',
                ])
            </div>
            <div class="col-md-4">
                @include('widgets.box-6', [
                    'is_dark' => false,
                    'title' => 'Distribution Records',
                    'icon' => 'list-task',
                    'number' => number_format($ditributors_count),
                    'link' => 'javascript:;',
                ])
            </div>
        </div>
    </div>
</div>
