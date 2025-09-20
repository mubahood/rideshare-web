<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {


    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('companies', CompanyController::class);
    $router->resource('clients', ClientController::class);
    $router->resource('employees', EmployeesController::class);
    $router->resource('users', EmployeesController::class);
    $router->resource('admin-roles', AdminRoleController::class);
    $router->resource('tasks', TaskController::class);
    $router->resource('events', EventController::class);
    $router->get('/calendar', 'HomeController@calendar')->name('calendar');
    $router->resource('patients', PatientController::class);
    $router->resource('patient-records', PatientRecordController::class);
    $router->resource('treatment-records', TreatmentRecordController::class);
    $router->resource('route-stages', RouteStageController::class); 
    $router->resource('negotiations', NegotiationController::class);

    $router->resource('gens', GenController::class);
    $router->resource('trips', TripController::class);
    
    // Enhanced Employee Management Routes
    $router->get('{id}/approve', 'EmployeesController@approve')->name('employees.approve');
    $router->get('{id}/block', 'EmployeesController@block')->name('employees.block');
    $router->get('{id}/activate', 'EmployeesController@activate')->name('employees.activate');
    $router->get('{id}/approve-service/{service}', 'EmployeesController@approveService')->name('employees.approve-service');
    $router->get('analytics', 'EmployeesController@analytics')->name('employees.analytics');
    $router->get('reports', 'EmployeesController@reports')->name('employees.reports');
    $router->get('bulk-operations', 'EmployeesController@bulkOperations')->name('employees.bulk-operations');
});
