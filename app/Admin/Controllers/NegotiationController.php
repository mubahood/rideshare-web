<?php

namespace App\Admin\Controllers;

use App\Models\Negotiation;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class NegotiationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Negotiations';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Negotiation());
        $grid->disableBatchActions();
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Created'))->display(function ($created_at) {
            return date('d M Y h:i a', strtotime($created_at));
        })->sortable();

        $grid->disableCreateButton();

        $grid->column('customer_id', __('Customer'))
            ->display(function () {
                return $this->customer_name;
            })->sortable();
        $grid->column('driver_id', __('Driver'))
            ->display(function () {
                return $this->driver_name;
            })->sortable();
        $grid->column('status', __('Status'))
            ->label([
                'Pending' => 'default',
                'Active' => 'warning',
                'Canceled' => 'danger',
                'Cancelled' => 'danger',
                'Completed' => 'success',
            ])->sortable()
            ->filter([
                'Pending' => 'Pending',
                'Active' => 'Active',
                'Canceled' => 'Canceled',
                'Cancelled' => 'Cancelled',
                'Completed' => 'Completed',
            ])->sortable();
        $grid->column('is_active', __('Is Active'))
            ->label([
                'Yes' => 'success',
                'No' => 'danger',
            ])->sortable()
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->sortable();

        $grid->column('pickup_address', __('Pickup Address'))
            ->display(function () {
                return substr($this->pickup_address, 0, 50) . '...';
            })->sortable()->limit(25);
        $grid->column('dropoff_address', __('Dropoff Address'))
            ->display(function () {
                return substr($this->dropoff_address, 0, 50) . '...';
            })->sortable()
            ->limit(25);
        $grid->column('records', __('Records'))->hide();
        $grid->column('details', __('Details'))->hide();

        $grid->column('pickup_lat', __('Pickup Lat'))->hide();
        $grid->column('pickup_lng', __('Pickup lng'))->hide();
        $grid->column('dropoff_lat', __('Dropoff lat'))->hide();
        $grid->column('dropoff_lng', __('Dropoff lng'))->hide();
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
        $show = new Show(Negotiation::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('customer_id', __('Customer id'));
        $show->field('customer_name', __('Customer name'));
        $show->field('driver_id', __('Driver id'));
        $show->field('driver_name', __('Driver name'));
        $show->field('status', __('Status'));
        $show->field('customer_accepted', __('Customer accepted'));
        $show->field('customer_driver', __('Customer driver'));
        $show->field('pickup_lat', __('Pickup lat'));
        $show->field('pickup_lng', __('Pickup lng'));
        $show->field('pickup_address', __('Pickup address'));
        $show->field('dropoff_lat', __('Dropoff lat'));
        $show->field('dropoff_lng', __('Dropoff lng'));
        $show->field('dropoff_address', __('Dropoff address'));
        $show->field('records', __('Records'));
        $show->field('details', __('Details'));
        $show->field('is_active', __('Is active'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Negotiation());


        $form->display('customer_name', __('Customer'));
        $form->display('driver_name', __('Driver name'));

        $form->radio('status', __('Status'))
            ->options([
                'Pending' => 'Pending',
                'Active' => 'Active',
                'Canceled' => 'Canceled',
                'Completed' => 'Completed',
            ])->default('Pending');

        $form->radio('is_active', __('Is active'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->default('Yes');
        //divider
        $form->divider();

        $form->text('pickup_lat', __('Pickup lat'));
        $form->text('pickup_lng', __('Pickup lng'));

        $form->text('dropoff_lat', __('Dropoff lat'));
        $form->text('dropoff_lng', __('Dropoff lng'));

        $form->text('pickup_address', __('Pickup address'));
        $form->text('dropoff_address', __('Dropoff address'));


        return $form;
    }
}
