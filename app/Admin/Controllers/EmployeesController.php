<?php

namespace App\Admin\Controllers;

use App\Models\AdminRole;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Hash;


class EmployeesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'System Users';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Administrator());
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });
        $grid->model()
            ->orderBy('id', 'Desc');
        $grid->actions(function ($actions) {
            //$actions->disableView();
        });


        $grid->filter(function ($filter) {
            $roleModel = config('admin.database.roles_model');
            $filter->equal('main_role_id', 'Filter by role')
                ->select($roleModel::where('slug', '!=', 'super-admin')
                    ->where('slug', '!=', 'student')
                    ->get()
                    ->pluck('name', 'id'));
        });


        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->column('avatar', __('Photo'))->image('', 50, 50)
            ->sortable();
        $grid->column('id', __('Id'))->sortable()->hide();
        $grid->column('created_at', __('Joined'))
            ->display(function ($created_at) {
                return Utils::my_date_time($created_at);
            })->sortable();
        $grid->column('updated_at', __('Last Seen'))
            ->display(function ($created_at) {
                return Utils::my_date_time($created_at);
            })->sortable();

        $grid->column('name', __('Name'))->sortable();
        $grid->column('phone_number', __('Phone number'))->sortable();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();

        $grid->column('sex', 'Gender')
            ->sortable()
            ->filter([
                'Male' => 'Male',
                'Female' => 'Female',
            ]);

        $grid->column('user_type', __('User Role'))
            ->label([
                'Admin' => 'primary',
                'Driver' => 'success',
                'Pending Driver' => 'danger',
                'Customer' => 'info',
            ], 'danger')
            ->sortable()
            ->filter([
                'Admin' => 'Admin',
                'Driver' => 'Driver',
                'Pending Driver' => 'Pending Driver',
                'Customer' => 'Customer',
            ]);
        $grid->column('status', __('User Status'))
            ->using(
                [
                    '0' => 'Blocked',
                    '2' => 'Pending',
                    '1' => 'Active',
                ],
                'Unknown'
            )
            ->dot([
                '0' => 'danger',
                '2' => 'warning',
                '1' => 'success',
            ], 'danger')->sortable();

        $grid->column('automobile', __('Automobile'))
            ->display(function ($automobile) {
                if ($automobile) {
                    return $automobile;
                }
                return 'N/A';
            })->sortable();

        $grid->column('ready_for_trip', 'Availability')
            ->dot([
                'Yes' => 'success',
                'No' => 'danger',
            ], 'danger')
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->sortable(); 



        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {

        $u = Administrator::findOrFail($id);
        $tab = new Tab();
        $tab->add('Bio', view('admin.dashboard.show-user-profile-bio', [
            'u' => $u
        ]));
        return $tab;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $u = Admin::user();

        $form = new Form(new Administrator());


        $form->divider('BIO DATA');

        $u = Admin::user();
        $form->text('first_name')->rules('required');
        $form->text('last_name')->rules('required');
        $form->text('phone_number', 'Phone number')->rules('required');
        $form->date('date_of_birth', 'Date of birth');
        $form->text('phone_number_2', 'Phone number 2');
        $form->image('avatar', 'Photo')->uniqueName();

        $form->radio('sex', 'Gender')->options([
            'Male' => 'Male',
            'Female' => 'Female',
        ])->rules('required');

        $form->divider();


        $form->radioCard('user_type', 'User Role')->options([
            'Admin' => 'Admin',
            'Driver' => 'Driver',
            'Customer' => 'Customer',
        ])->rules('required')
            ->default('Customer')
            ->when('Driver', function (Form $form) {
                $form->divider();
                $form->radioCard('automobile', 'Automobile')->options([
                    'Special Car' => 'Special Car',
                    'Taxi' => 'Taxi',
                    'Ambulance' => 'Ambulance',
                    'Bodaboda' => 'Bodaboda',
                ]);
                $form->radioCard('ready_for_trip', 'Availability')->options([
                    'Yes' => 'Yes',
                    'No' => 'No',
                ])->rules('required');
                $form->text('current_address', 'Current Address')
                    ->help('GPS Coordinates: <span id="gps"></span>');
                $form->text('nin', 'National ID Number');
                $form->text('driving_license_number', 'Driving License Number');
                $form->date('driving_license_issue_date', 'Driving License Issue Date');
                $form->date('driving_license_validity', 'Driving License Validity');
                $form->text('driving_license_issue_authority', 'Driving License Issue Authority');
                $form->image('driving_license_photo', 'Driving License Photo')->uniqueName();
            })->default('Customer');

        $form->radioCard('status', 'Status')->options([
            '0' => 'Blocked',
            '2' => 'Pending',
            '1' => 'Active',
        ])->rules('required');


        $form->disableReset();
        $form->disableViewCheck();
        return $form;
    }
}
