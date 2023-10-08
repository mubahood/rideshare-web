<?php

namespace App\Admin\Controllers;

use App\Models\AdminRole;
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
        $grid->disableBatchActions();
        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('phone_number', __('Phone number'))->sortable();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('user_type', __('User Role'))
            ->label([
                'Admin' => 'success',
                'Driver' => 'warning',
                'Customer' => 'info',
            ], 'danger')
            ->sortable();
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


        /* 
    $form->radioCard('user_type', 'User Role')->options([
            'Admin' => 'Admin',
            'Driver' => 'Driver',
            'Customer' => 'Customer',
        ])->rules('required');
        $form->radioCard('status', 'Status')->options([
            '0' => 'Blocked',
            '2' => 'Pending',
            '1' => 'Active',
        ])->rules('required'); */

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
        $form->text('phone_number_2', 'Phone number 2');

        $form->radioCard('user_type', 'User Role')->options([
            'Admin' => 'Admin',
            'Driver' => 'Driver',
            'Customer' => 'Customer',
        ])->rules('required');
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
