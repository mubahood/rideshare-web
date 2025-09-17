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
    protected $title = 'System Users & Driver Management';

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

        // Enhanced filtering for service management
        $grid->filter(function ($filter) {
            $roleModel = config('admin.database.roles_model');
            $filter->equal('main_role_id', 'Filter by role')
                ->select($roleModel::where('slug', '!=', 'super-admin')
                    ->where('slug', '!=', 'student')
                    ->get()
                    ->pluck('name', 'id'));
            
            // Service-specific filters
            $filter->group('service_filters', 'Service Filters', function ($group) {
                $group->equal('is_car', 'Car Service')->select(['Yes' => 'Requested', 'No' => 'Not Requested']);
                $group->equal('is_boda', 'Boda Service')->select(['Yes' => 'Requested', 'No' => 'Not Requested']);
                $group->equal('is_ambulance', 'Ambulance Service')->select(['Yes' => 'Requested', 'No' => 'Not Requested']);
                $group->equal('is_police', 'Police Service')->select(['Yes' => 'Requested', 'No' => 'Not Requested']);
                $group->equal('is_delivery', 'Delivery Service')->select(['Yes' => 'Requested', 'No' => 'Not Requested']);
                $group->equal('is_breakdown', 'Breakdown Service')->select(['Yes' => 'Requested', 'No' => 'Not Requested']);
                $group->equal('is_firebrugade', 'Fire Brigade Service')->select(['Yes' => 'Requested', 'No' => 'Not Requested']);
            });
            
            // Approval status filters
            $filter->group('approval_filters', 'Approval Status Filters', function ($group) {
                $group->equal('is_car_approved', 'Car Approved')->select(['Yes' => 'Approved', 'No' => 'Not Approved']);
                $group->equal('is_boda_approved', 'Boda Approved')->select(['Yes' => 'Approved', 'No' => 'Not Approved']);
                $group->equal('is_ambulance_approved', 'Ambulance Approved')->select(['Yes' => 'Approved', 'No' => 'Not Approved']);
                $group->equal('is_police_approved', 'Police Approved')->select(['Yes' => 'Approved', 'No' => 'Not Approved']);
                $group->equal('is_delivery_approved', 'Delivery Approved')->select(['Yes' => 'Approved', 'No' => 'Not Approved']);
                $group->equal('is_breakdown_approved', 'Breakdown Approved')->select(['Yes' => 'Approved', 'No' => 'Not Approved']);
                $group->equal('is_firebrugade_approved', 'Fire Brigade Approved')->select(['Yes' => 'Approved', 'No' => 'Not Approved']);
            });
        });

        $grid->quickSearch('name', 'phone_number', 'email', 'nin', 'driving_license_number')->placeholder('Search by name, phone, email, NIN, or license number');
        
        $grid->column('avatar', __('Photo'))->image('', 50, 50)->sortable();
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
        $grid->column('email', __('Email'))->sortable();

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
                'Pending Driver' => 'warning',
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

        // Service status columns
        $grid->column('is_car', 'Car Service')
            ->using(['Yes' => 'Requested', 'No' => 'No'], 'No')
            ->label(['Yes' => 'info', 'No' => 'default'])
            ->sortable();
            
        $grid->column('is_car_approved', 'Car Approved')
            ->using(['Yes' => 'Approved', 'No' => 'Pending'], 'Pending')
            ->label(['Yes' => 'success', 'No' => 'warning'])
            ->sortable();
            
        $grid->column('is_boda', 'Boda Service')
            ->using(['Yes' => 'Requested', 'No' => 'No'], 'No')
            ->label(['Yes' => 'warning', 'No' => 'default'])
            ->sortable()->hide();
            
        $grid->column('is_ambulance', 'Ambulance Service')
            ->using(['Yes' => 'Requested', 'No' => 'No'], 'No')
            ->label(['Yes' => 'danger', 'No' => 'default'])
            ->sortable()->hide();

        $grid->column('automobile', __('Primary Vehicle'))
            ->display(function ($automobile) {
                if ($automobile) {
                    return '<span class="label label-default">' . $automobile . '</span>';
                }
                return '<span class="text-muted">N/A</span>';
            })->sortable();

        $grid->column('ready_for_trip', 'Availability')
            ->dot([
                'Yes' => 'success',
                'No' => 'danger',
            ], 'danger')
            ->filter([
                'Yes' => 'Available',
                'No' => 'Not Available',
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
        
        // Enhanced bio tab with service information
        $tab->add('Bio & Services', view('admin.dashboard.show-user-profile-bio', [
            'u' => $u
        ]));
        
        // Service management tab
        $tab->add('Service Management', view('admin.dashboard.user-service-management', [
            'u' => $u,
            'services' => [
                'car' => ['requested' => $u->is_car, 'approved' => $u->is_car_approved],
                'boda' => ['requested' => $u->is_boda, 'approved' => $u->is_boda_approved],
                'ambulance' => ['requested' => $u->is_ambulance, 'approved' => $u->is_ambulance_approved],
                'police' => ['requested' => $u->is_police, 'approved' => $u->is_police_approved],
                'delivery' => ['requested' => $u->is_delivery, 'approved' => $u->is_delivery_approved],
                'breakdown' => ['requested' => $u->is_breakdown, 'approved' => $u->is_breakdown_approved],
                'firebrugade' => ['requested' => $u->is_firebrugade, 'approved' => $u->is_firebrugade_approved],
            ]
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
        $form = new Form(new Administrator());

        $form->divider('PERSONAL INFORMATION');

        $form->text('first_name', 'First Name')->rules('required');
        $form->text('last_name', 'Last Name')->rules('required');
        $form->email('email', 'Email Address')->rules('email');
        $form->text('phone_number', 'Primary Phone Number')->rules('required');
        $form->text('phone_number_2', 'Secondary Phone Number');
        $form->date('date_of_birth', 'Date of Birth')->rules('required');
        $form->image('avatar', 'Profile Photo')->uniqueName();

        $form->radio('sex', 'Gender')->options([
            'Male' => 'Male',
            'Female' => 'Female',
        ])->rules('required');

        $form->textarea('home_address', 'Home Address');
        $form->textarea('current_address', 'Current Address')
            ->help('GPS Coordinates and detailed address');

        $form->divider('ACCOUNT SETTINGS');

        $form->radioCard('user_type', 'User Role')->options([
            'Admin' => 'Administrator',
            'Driver' => 'Approved Driver',
            'Pending Driver' => 'Pending Driver Approval',
            'Customer' => 'Customer/Passenger',
        ])->rules('required')
            ->default('Customer')
            ->when('Driver', function (Form $form) {
                $form->divider('DRIVER INFORMATION');
                
                // Identity and licensing
                $form->text('nin', 'National ID Number')->rules('required');
                $form->text('driving_license_number', 'Driving License Number')->rules('required');
                $form->date('driving_license_issue_date', 'License Issue Date')->rules('required');
                $form->date('driving_license_validity', 'License Expiry Date')->rules('required');
                $form->text('driving_license_issue_authority', 'License Issuing Authority')->rules('required');
                $form->image('driving_license_photo', 'Driving License Photo')->uniqueName()->rules('required');
                
                // Vehicle type is automatically set to 'car' as discussed
                $form->hidden('automobile')->default('car');
                
                $form->radio('ready_for_trip', 'Current Availability')->options([
                    'Yes' => 'Available for Trips',
                    'No' => 'Not Available',
                ])->default('No');

                $form->divider('SERVICE SELECTION');
                
                // Individual service selection (Yes/No fields)
                $form->radio('is_car', 'Car Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No')
                    ->help('Regular passenger transport service');
                $form->radio('is_boda', 'Boda Boda Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No')
                    ->help('Motorcycle rides for quick transport');
                $form->radio('is_ambulance', 'Ambulance Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No')
                    ->help('Emergency medical transport service');
                $form->radio('is_police', 'Police Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No')
                    ->help('Law enforcement transport support');
                $form->radio('is_delivery', 'Delivery Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No')
                    ->help('Package and goods delivery service');
                $form->radio('is_breakdown', 'Breakdown Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No')
                    ->help('Vehicle breakdown assistance and towing');
                $form->radio('is_firebrugade', 'Fire Brigade Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No')
                    ->help('Emergency fire response support');

                $form->divider('SERVICE APPROVAL STATUS');
                
                // Individual service approval controls (Yes/No fields)
                $form->radio('is_car_approved', 'Approve Car Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_boda_approved', 'Approve Boda Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_ambulance_approved', 'Approve Ambulance Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_police_approved', 'Approve Police Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_delivery_approved', 'Approve Delivery Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_breakdown_approved', 'Approve Breakdown Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_firebrugade_approved', 'Approve Fire Brigade Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
            })
            ->when('Pending Driver', function (Form $form) {
                $form->divider('PENDING DRIVER INFORMATION');
                
                // Identity and licensing
                $form->text('nin', 'National ID Number');
                $form->text('driving_license_number', 'Driving License Number');
                $form->date('driving_license_issue_date', 'License Issue Date');
                $form->date('driving_license_validity', 'License Expiry Date');
                $form->text('driving_license_issue_authority', 'License Issuing Authority');
                $form->image('driving_license_photo', 'Driving License Photo')->uniqueName();
                
                // Vehicle type is automatically set to 'car' as discussed
                $form->hidden('automobile')->default('car');

                $form->divider('REQUESTED SERVICES');
                
                // Show individual service request status as read-only fields
                $form->radio('is_car', 'Car Service Requested')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_boda', 'Boda Service Requested')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_ambulance', 'Ambulance Service Requested')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_police', 'Police Service Requested')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_delivery', 'Delivery Service Requested')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_breakdown', 'Breakdown Service Requested')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_firebrugade', 'Fire Brigade Service Requested')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');

                $form->divider('APPROVE SERVICES');
                
                // Service approval controls (Yes/No fields)
                $form->radio('is_car_approved', 'Approve Car Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_boda_approved', 'Approve Boda Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_ambulance_approved', 'Approve Ambulance Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_police_approved', 'Approve Police Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_delivery_approved', 'Approve Delivery Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_breakdown_approved', 'Approve Breakdown Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
                $form->radio('is_firebrugade_approved', 'Approve Fire Brigade Service')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->default('No');
            });

        $form->radioCard('status', 'Account Status')->options([
            '1' => 'Active - User can access all features',
            '2' => 'Pending - Awaiting verification or approval',
            '0' => 'Blocked - Account suspended',
        ])->rules('required')->default('1');

        // Custom saving logic 
        $form->saving(function (Form $form) {
            // Hash password if provided
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
            
            // Combine first and last name
            if ($form->first_name && $form->last_name) {
                $form->name = $form->first_name . ' ' . $form->last_name;
            }
            
            // Ensure automobile is set to 'car' as discussed
            if (!$form->automobile) {
                $form->automobile = 'car';
            }
            
            // No conversion needed - radio fields already submit 'Yes'/'No' values
        });

        // No custom editing logic needed - radio fields work directly with 'Yes'/'No' values

        $form->disableReset();
        $form->disableViewCheck();
        
        return $form;
    }
}
