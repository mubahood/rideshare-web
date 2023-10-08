<?php

namespace App\Admin\Controllers;

use App\Models\RouteStage;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RouteStageController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Route Stages';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RouteStage());
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });
        $grid->model()
            ->orderBy('name', 'asc');
        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->disableBatchActions();

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'))->sortable();
        $grid->column('latitute', __('Latitute'))->sortable();
        $grid->column('longitude', __('Longitude'))->sortable();
        $grid->column('details', __('Details'))->hide();

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
        $show = new Show(RouteStage::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('latitute', __('Latitute'));
        $show->field('longitude', __('Longitude'));
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
        $form = new Form(new RouteStage());

        $form->text('name', __('Name'))->required();
        $form->text('latitute', __('Latitute'));
        $form->text('longitude', __('Longitude'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
