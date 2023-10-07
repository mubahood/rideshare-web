<?php

namespace App\Admin\Controllers;

use App\Models\Patient;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PatientController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Patients';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Patient());
        $u = auth('admin')->user();

        if (!$u->isRole('admin')) {
            $grid->model()->where('administrator_id', $u->id);
        }

        $grid->model()->orderBy('first_name', 'desc');

        $grid->disableBatchActions();
        $grid->quickSearch('first_name', 'last_name')
            ->placeholder('Search...');
        $grid->column('created_at', __('Created'))->display(function ($created_at) {
            return date('d-m-Y', strtotime($created_at));
        })->sortable();

        $grid->column('first_name', __('First name'))->display(function ($first_name) {
            return  $first_name . ' ' . $this->last_name;
        })->sortable();
        $grid->column('gender', __('Gender'))
            ->filter([
                'Male' => 'Male',
                'Female' => 'Female'
            ])
            ->dot([
                'Male' => 'success',
                'Female' => 'info'
            ])
            ->sortable();
        $grid->column('date_of_birth', __('D.O.B'))->sortable();
        $grid->column('phone_number_1', __('Phone Number'));
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('email', __('Email'))->hide();
        $grid->column('occupation', __('Occupation'));
        $grid->column('address', __('Address'));
        $grid->column('how_you_knew_us', __('How you knew us'))->hide();
        $grid->column('details', __('Details'))->hide();

        $grid->column('administrator_id', __('Rgietered By'))
            ->display(function ($administrator_id) {
                if ($this->administrator == null) {
                    return '-';
                }
                return $this->administrator->name;
            })->sortable();

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
        $show = new Show(Patient::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('first_name', __('First name'));
        $show->field('last_name', __('Last name'));
        $show->field('gender', __('Gender'));
        $show->field('date_of_birth', __('Date of birth'));
        $show->field('phone_number_1', __('Phone number 1'));
        $show->field('phone_number_2', __('Phone number 2'));
        $show->field('email', __('Email'));
        $show->field('occupation', __('Occupation'));
        $show->field('address', __('Address'));
        $show->field('how_you_knew_us', __('How you knew us'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Patient());

        //administrator_id hidden
        $form->hidden('administrator_id', __('Administrator id'))->default(auth('admin')->user()->id);
        $form->text('first_name', __('First Name'))->rules('required');
        $form->text('last_name', __('Last Name'))->rules('required');
        $form->radioCard('gender', __('Gender'))
            ->options([
                'Male' => 'Male',
                'Female' => 'Female'
            ])
            ->rules('required');
        $form->date('date_of_birth', __('Date of birth'));
        $form->text('phone_number_1', __('Phone number'))->rules('required');
        $form->text('phone_number_2', __('Phone number 2'));
        $form->email('email', __('Email'));
        $form->text('occupation', __('Occupation'));
        $form->text('address', __('Address'));
        $form->select('how_you_knew_us', __('How did you come to know about us?'))
            ->options([
                'Facebook' => 'Facebook',
                'Instagram' => 'Instagram',
                'Twitter' => 'Twitter',
                'LinkedIn' => 'LinkedIn',
                'Google' => 'Google',
                'Friend' => 'Friend',
                'Family' => 'Family',
                'Colleague' => 'Colleague',
                'School' => 'School',
                'University' => 'University',
                'Newspaper' => 'Newspaper',
                'Magazine' => 'Magazine',
                'TV' => 'TV',
                'Radio' => 'Radio',
                'Other' => 'Other'
            ])
            ->rules('required');
        $form->textarea('details', __('Details'));

        return $form;
    }
}
