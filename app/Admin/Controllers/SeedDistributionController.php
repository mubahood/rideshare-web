<?php

namespace App\Admin\Controllers;

use App\Models\SeedDistribution;
use App\Models\SeedModel;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class SeedDistributionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Seed Distribution';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SeedDistribution());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created'));
        $grid->column('farmer_id', __('Farmer'));
        $grid->column('seed_id', __('Seed id'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('description', __('Description'));
        $grid->column('user_id', __('Distributed By'))->display(function ($user_id) {
            if ($this->user == null) {
                return "-";
            }
            return $this->user->name;
        });

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
        $show = new Show(SeedDistribution::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('farmer_id', __('Farmer id'));
        $show->field('seed_id', __('Seed id'));
        $show->field('quantity', __('Quantity'));
        $show->field('description', __('Description'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SeedDistribution());
        $u = Auth::user();
        $form->hidden('user_id')->default($u->id);

        if(!$form->isCreating()){
            throw new \Exception("Seed Distribution can not be edited.");
        }

        $form->select('farmer_id', __('Farmer'))->options(function ($id) {
            $subcounty = Administrator::find($id);
            if ($subcounty) {
                return [$subcounty->id => $subcounty->name . " - " . $subcounty->phone_number];
            }
        })->ajax(url('/api/users'))->rules('required');
        $form->select('seed_id', __('Seed'))
            ->options(SeedModel::where([])->pluck('name', 'id'));
        $form->decimal('quantity', __('Quantity (in Kgs))'))
            ->rules('required');
        $form->text('description', __('Description'));
        $form->decimal('otp', __('Verification Code (OTP)'))
            ->rules('required');

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
