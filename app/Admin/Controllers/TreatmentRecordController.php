<?php

namespace App\Admin\Controllers;

use App\Models\Patient;
use App\Models\TreatmentRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TreatmentRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Treatment Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TreatmentRecord());

        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Date'))
            ->display(function ($date) {
                return date('d-m-Y', strtotime($date));
            });
        $grid->column('administrator_id', __('Docttor'))
            ->display(function ($id) {
                return $this->administrator->name;
            })
            ->sortable();
        $grid->column('patient_id', __('Patient'))
            ->display(function ($id) {
                if ($this->patient_user == null) {
                    return '-';
                }
                return $this->patient_user->full_name;
            })
            ->sortable();
        $grid->column('procedure', __('Procedure'))
            ->dot([
                'Extraction' => 'danger',
                'Filling' => 'success',
                'Cleaning' => 'info',
                'Root Canal' => 'warning',
                'Crown' => 'primary',
                'Bridge' => 'default',
                'Implant' => 'danger',
                'Denture' => 'success',
                'Braces' => 'info',
                'Invisalign' => 'warning',
                'Other' => 'primary',
            ])->filter([
                'Extraction' => 'Extraction',
                'Filling' => 'Filling',
                'Cleaning' => 'Cleaning',
                'Root Canal' => 'Root Canal',
                'Crown' => 'Crown',
                'Bridge' => 'Bridge',
                'Implant' => 'Implant',
                'Denture' => 'Denture',
                'Braces' => 'Braces',
                'Invisalign' => 'Invisalign',
                'Other' => 'Other',
            ])->sortable();
        /*  
                $form->decimal('upper_canines', __('Number of Upper Canines Exracted'));
                $form->decimal('upper_premolars', __('Number of Upper Premolars Exracted'));
                $form->decimal('upper_molars', __('Number of Upper Molars Exracted'));
                $form->decimal('lower_incisors', __('Number of Lower Incisors Exracted'));
                $form->decimal('lower_canines', __('Number of Lower Canines Exracted'));
                $form->decimal('lower_premolars', __('Number of Lower Premolars Exracted'));
                $form->decimal('lower_molars', __('Number of Lower Molars Exracted'));
 */

        $grid->column('upper_incisors', __('Upper Incisors'))->sortable();
        $grid->column('upper_canines', __('Upper Canines'))->sortable();
        $grid->column('upper_premolars', __('Upper Premolars'))->sortable();
        $grid->column('upper_molars', __('Upper Molars'))->sortable();
        $grid->column('lower_incisors', __('Lower Incisors'))->sortable();
        $grid->column('lower_canines', __('Lower Canines'))->sortable();
        $grid->column('lower_premolars', __('Lower Premolars'))->sortable();
        $grid->column('lower_molars', __('Lower Molars'))->sortable();

        $grid->column('details', __('Details'))->hide();
        $grid->column('photos', __('Photos'))->gallery(
            ['width' => 60, 'height' => 60, 'zooming' => true, 'class' => 'rounded']
        );

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
        $show = new Show(TreatmentRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('patient_id', __('Patient id'));
        $show->field('procedure', __('Procedure'));
        $show->field('teeth_extracted', __('Teeth extracted'));
        $show->field('details', __('Details'));
        $show->field('photos', __('Photos'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TreatmentRecord());

        $form->hidden('administrator_id', __('Administrator id'))->default(auth('admin')->user()->id);
        $form->select('patient_id', __('Patient'))->options(Patient::toSelectArray())->rules('required');

        $form->radio('procedure', __('Dental Procedure'))
            ->options([
                'Extraction' => 'Extraction',
                'Filling' => 'Filling',
                'Cleaning' => 'Cleaning',
                'Root Canal' => 'Root Canal',
                'Crown' => 'Crown',
                'Bridge' => 'Bridge',
                'Implant' => 'Implant',
                'Denture' => 'Denture',
                'Braces' => 'Braces',
                'Invisalign' => 'Invisalign',
                'Other' => 'Other',
            ])->when('Other', function (Form $form) {
                $form->textarea('procedure_other', __('Other Procedure'));
            })->when('Extraction', function (Form $form) {
                $form->decimal('upper_incisors', __('Number of Upper Incisors Exracted'));
                $form->decimal('upper_canines', __('Number of Upper Canines Exracted'));
                $form->decimal('upper_premolars', __('Number of Upper Premolars Exracted'));
                $form->decimal('upper_molars', __('Number of Upper Molars Exracted'));
                $form->decimal('lower_incisors', __('Number of Lower Incisors Exracted'));
                $form->decimal('lower_canines', __('Number of Lower Canines Exracted'));
                $form->decimal('lower_premolars', __('Number of Lower Premolars Exracted'));
                $form->decimal('lower_molars', __('Number of Lower Molars Exracted'));
            })->rules('required');
        $form->multipleImage('photos', __('Photos'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
