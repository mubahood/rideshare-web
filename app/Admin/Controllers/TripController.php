<?php

namespace App\Admin\Controllers;

use App\Models\Trip;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TripController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Trip';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Trip());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('driver_id', __('Driver id'));
        $grid->column('customer_id', __('Customer id'));
        $grid->column('start_stage_id', __('Start stage id'));
        $grid->column('end_stage_id', __('End stage id'));
        $grid->column('scheduled_start_time', __('Scheduled start time'));
        $grid->column('scheduled_end_time', __('Scheduled end time'));
        $grid->column('start_time', __('Start time'));
        $grid->column('end_time', __('End time'));
        $grid->column('status', __('Status'));
        $grid->column('vehicel_reg_number', __('Vehicel reg number'));
        $grid->column('slots', __('Slots'));
        $grid->column('details', __('Details'));
        $grid->column('car_model', __('Car model'));

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
        $show = new Show(Trip::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('driver_id', __('Driver id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('start_stage_id', __('Start stage id'));
        $show->field('end_stage_id', __('End stage id'));
        $show->field('scheduled_start_time', __('Scheduled start time'));
        $show->field('scheduled_end_time', __('Scheduled end time'));
        $show->field('start_time', __('Start time'));
        $show->field('end_time', __('End time'));
        $show->field('status', __('Status'));
        $show->field('vehicel_reg_number', __('Vehicel reg number'));
        $show->field('slots', __('Slots'));
        $show->field('details', __('Details'));
        $show->field('car_model', __('Car model'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Trip());

        $form->number('driver_id', __('Driver id'));
        $form->number('customer_id', __('Customer id'));
        $form->number('start_stage_id', __('Start stage id'));
        $form->number('end_stage_id', __('End stage id'));
        $form->text('scheduled_start_time', __('Scheduled start time'));
        $form->text('scheduled_end_time', __('Scheduled end time'));
        $form->text('start_time', __('Start time'));
        $form->text('end_time', __('End time'));
        $form->text('status', __('Status'));
        $form->text('vehicel_reg_number', __('Vehicel reg number'));
        $form->number('slots', __('Slots'));
        $form->textarea('details', __('Details'));
        $form->textarea('car_model', __('Car model'));

        return $form;
    }
}
