<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use App\Models\Utils;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Auth;
use App\Admin\Extensions\Nav\Shortcut;
use App\Admin\Extensions\Nav\Dropdown;

Utils::system_boot();


Admin::css('/assets/js/calender/main.css');
Admin::js('/assets/js/calender/main.js');

Admin::css('/css/jquery-confirm.min.css');
Admin::js('/assets/js/jquery-confirm.min.js');
Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {

    /*     $u = Auth::user();
    $navbar->left(view('admin.search-bar', [
        'u' => $u
    ]));

    $navbar->left(Shortcut::make([
        'News post' => 'news-posts/create',
        'Products or Services' => 'products/create',
        'Jobs and Opportunities' => 'jobs/create',
        'Event' => 'events/create',
    ], 'fa-plus')->title('ADD NEW'));
    $navbar->left(Shortcut::make([
        'Person with disability' => 'people/create',
        'Association' => 'associations/create',
        'Group' => 'groups/create',
        'Service provider' => 'service-providers/create',
        'Institution' => 'institutions/create',
        'Counselling Centre' => 'counselling-centres/create',
    ], 'fa-wpforms')->title('Register new'));

    $navbar->left(new Dropdown());

    $navbar->right(Shortcut::make([
        'How to update your profile' => '',
        'How to register a new person with disability' => '',
        'How to register as service provider' => '',
        'How to register to post a products & services' => '',
        'How to register to apply for a job' => '',
        'How to register to use mobile App' => '',
        'How to register to contact us' => '',
        'How to register to give a testimonial' => '',
        'How to register to contact counselors' => '',
    ], 'fa-question')->title('HELP')); */
});




Encore\Admin\Form::forget(['map', 'editor']);
Admin::css(url('/assets/css/bootstrap.css'));
Admin::css('/css/styles.css');
Admin::css('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css');
