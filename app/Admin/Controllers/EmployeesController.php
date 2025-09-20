<?php

namespace App\Admin\Controllers;

use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\Negotiation;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Tab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Comprehensive Users & Drivers Management - 360° Control';

    /**
     * 360-Degree User Management Dashboard
     */
    public function index(Content $content)
    {
        return $content
            ->title('Comprehensive User Management')
            ->description('360° Control of Users, Drivers & Platform Analytics')
            ->row($this->analyticsRow())
            ->body($this->grid());
    }

    /**
     * Analytics Dashboard Row
     */
    protected function analyticsRow()
    {
        $totalUsers = Administrator::count();
        $activeDrivers = Administrator::where('user_type', 'Driver')->where('status', '1')->count();
        $pendingApprovals = Administrator::where('status', '2')->count();
        $totalTrips = Trip::count();
        $todayTrips = Trip::whereDate('created_at', today())->count();
        $totalRevenue = TripBooking::where('payment_status', 'paid')->sum('price');
        $onlineDrivers = Administrator::where('ready_for_trip', 'Yes')->count();

        $html = '<div class="row" style="margin-bottom: 20px;">';

        // Total Users Card
        $html .= '<div class="col-md-2"><div class="info-box bg-blue"><span class="info-box-icon"><i class="fa fa-users"></i></span>';
        $html .= '<div class="info-box-content"><span class="info-box-text">Total Users</span>';
        $html .= '<span class="info-box-number">' . number_format($totalUsers) . '</span></div></div></div>';

        // Active Drivers Card
        $html .= '<div class="col-md-2"><div class="info-box bg-green"><span class="info-box-icon"><i class="fa fa-car"></i></span>';
        $html .= '<div class="info-box-content"><span class="info-box-text">Active Drivers</span>';
        $html .= '<span class="info-box-number">' . number_format($activeDrivers) . '</span></div></div></div>';

        // Online Drivers Card
        $html .= '<div class="col-md-2"><div class="info-box bg-yellow"><span class="info-box-icon"><i class="fa fa-circle"></i></span>';
        $html .= '<div class="info-box-content"><span class="info-box-text">Online Now</span>';
        $html .= '<span class="info-box-number">' . number_format($onlineDrivers) . '</span></div></div></div>';

        // Pending Approvals Card
        $html .= '<div class="col-md-2"><div class="info-box bg-orange"><span class="info-box-icon"><i class="fa fa-clock-o"></i></span>';
        $html .= '<div class="info-box-content"><span class="info-box-text">Pending</span>';
        $html .= '<span class="info-box-number">' . number_format($pendingApprovals) . '</span></div></div></div>';

        // Today's Trips Card
        $html .= '<div class="col-md-2"><div class="info-box bg-purple"><span class="info-box-icon"><i class="fa fa-road"></i></span>';
        $html .= '<div class="info-box-content"><span class="info-box-text">Today Trips</span>';
        $html .= '<span class="info-box-number">' . number_format($todayTrips) . '</span></div></div></div>';

        // Revenue Card
        $html .= '<div class="col-md-2"><div class="info-box bg-red"><span class="info-box-icon"><i class="fa fa-money"></i></span>';
        $html .= '<div class="info-box-content"><span class="info-box-text">Revenue</span>';
        $html .= '<span class="info-box-number">UGX ' . number_format($totalRevenue) . '</span></div></div></div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Enhanced Grid with Comprehensive Data
     */
    protected function grid()
    {
        $grid = new Grid(new Administrator());

        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

        $grid->model()->orderBy('id', 'desc');

        // Enhanced columns with comprehensive information
        $grid->column('id', __('ID'))->sortable()->width(60);
        $grid->column('avatar', __('Photo'))->image('', 50, 50)->width(80);

        $grid->column('user_info', __('User Information'))->display(function () {
            $html = "<div style='line-height: 1.4;'>";
            $html .= "<strong style='color: #2c3e50; font-size: 14px;'>{$this->name}</strong><br>";
            $html .= "<span style='color: #7f8c8d; font-size: 12px;'>{$this->email}</span><br>";
            $html .= "<span style='color: #34495e; font-size: 12px;'>{$this->phone_number}</span>";
            if ($this->phone_number_2) {
                $html .= "<br><span style='color: #34495e; font-size: 11px;'>Alt: {$this->phone_number_2}</span>";
            }
            if ($this->nin) {
                $html .= "<br><span style='color: #95a5a6; font-size: 10px;'>NIN: {$this->nin}</span>";
            }
            $html .= "</div>";
            return $html;
        })->width(200);

        $grid->column('user_type_status', __('Type & Status'))->display(function () {
            $typeColors = [
                'Admin' => '#e74c3c',
                'Driver' => '#3498db',
                'Pending Driver' => '#f39c12',
                'Customer' => '#95a5a6'
            ];
            $statusColors = ['1' => '#27ae60', '2' => '#f39c12', '0' => '#e74c3c'];
            $statusLabels = ['1' => 'Active', '2' => 'Pending', '0' => 'Blocked'];

            $typeColor = $typeColors[$this->user_type] ?? '#95a5a6';
            $statusColor = $statusColors[$this->status] ?? '#95a5a6';
            $statusLabel = $statusLabels[$this->status] ?? 'Unknown';

            $availability = '';
            if ($this->ready_for_trip == 'Yes') {
                $availability = "<br><span style='background: #27ae60; color: white; padding: 1px 4px; border-radius: 8px; font-size: 9px;'>🟢 Online</span>";
            }

            return "<span style='background: {$typeColor}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-right: 3px;'>{$this->user_type}</span>" .
                "<span style='background: {$statusColor}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px;'>{$statusLabel}</span>" .
                $availability;
        })->width(150);

        $grid->column('services', __('Services & Approvals'))->display(function () {
            $services = [];
            $serviceTypes = ['car', 'boda', 'ambulance', 'police', 'delivery', 'breakdown', 'firebrugade'];
            $serviceIcons = [
                'car' => '🚗',
                'boda' => '🏍️',
                'ambulance' => '🚑',
                'police' => '🚔',
                'delivery' => '📦',
                'breakdown' => '🔧',
                'firebrugade' => '🚒'
            ];

            foreach ($serviceTypes as $service) {
                $requested = $this->{"is_{$service}"} == 'Yes';
                $approved = $this->{"is_{$service}_approved"} == 'Yes';

                if ($requested) {
                    $color = $approved ? '#27ae60' : '#f39c12';
                    $status = $approved ? '✓' : '⏳';
                    $icon = $serviceIcons[$service] ?? '🔹';
                    $services[] = "<span style='background: {$color}; color: white; padding: 1px 5px; border-radius: 8px; font-size: 10px; margin: 1px;'>" .
                        "{$icon}" . ucfirst($service) . " {$status}</span>";
                }
            }

            return empty($services) ? '<span style="color: #95a5a6;">No Services</span>' : implode('<br>', array_slice($services, 0, 3));
        })->width(180);

        $grid->column('trip_stats', __('Trip Statistics'))->display(function () {
            $driverTrips = Trip::where('driver_id', $this->id)->count();
            $customerBookings = TripBooking::where('customer_id', $this->id)->count();
            $activeTrips = Trip::where('driver_id', $this->id)->whereIn('status', ['Pending', 'Ongoing'])->count();
            $completedTrips = Trip::where('driver_id', $this->id)->where('status', 'Completed')->count();

            $html = "<div style='font-size: 11px; line-height: 1.3;'>";
            if ($driverTrips > 0) {
                $html .= "<span style='color: #3498db;'>🚗 {$driverTrips} trips</span><br>";
                $html .= "<span style='color: #27ae60;'>✅ {$completedTrips} done</span><br>";
            }
            if ($customerBookings > 0) {
                $html .= "<span style='color: #9b59b6;'>🎫 {$customerBookings} bookings</span><br>";
            }
            if ($activeTrips > 0) {
                $html .= "<span style='color: #e74c3c; font-weight: bold;'>⚡ {$activeTrips} active</span>";
            }
            $html .= "</div>";

            return $html ?: '<span style="color: #95a5a6;">No Activity</span>';
        })->width(120);

        $grid->column('financial_info', __('Financial Summary'))->display(function () {
            $earnings = TripBooking::where('driver_id', $this->id)->where('payment_status', 'paid')->sum('price');
            $spent = TripBooking::where('customer_id', $this->id)->where('payment_status', 'paid')->sum('price');
            $pendingPayments = TripBooking::where('driver_id', $this->id)->where('payment_status', 'pending')->sum('price');

            $html = "<div style='font-size: 11px; line-height: 1.3;'>";
            if ($earnings > 0) {
                $html .= "<span style='color: #27ae60; font-weight: bold;'>💰 UGX " . number_format($earnings) . "</span><br>";
            }
            if ($pendingPayments > 0) {
                $html .= "<span style='color: #f39c12;'>⏳ UGX " . number_format($pendingPayments) . "</span><br>";
            }
            if ($spent > 0) {
                $html .= "<span style='color: #e67e22;'>💳 UGX " . number_format($spent) . "</span>";
            }
            $html .= "</div>";

            return $html ?: '<span style="color: #95a5a6;">No Transactions</span>';
        })->width(130);

        $grid->column('location_activity', __('Location & Activity'))->display(function () {
            $html = "<div style='font-size: 11px; line-height: 1.3;'>";

            if ($this->current_address && str_contains($this->current_address, ',')) {
                $coords = explode(',', $this->current_address);
                if (count($coords) == 2) {
                    $lat = round(floatval($coords[0]), 3);
                    $lng = round(floatval($coords[1]), 3);
                    $html .= "<span style='color: #3498db;'>📍 {$lat}, {$lng}</span><br>";
                }
            }

            $lastActive = $this->updated_at ? Carbon::parse($this->updated_at)->diffForHumans() : 'Never';
            $html .= "<span style='color: #95a5a6;'>🕒 {$lastActive}</span>";

            if ($this->automobile) {
                $html .= "<br><span style='color: #34495e;'>🚙 {$this->automobile}</span>";
            }
            $html .= "</div>";
            return $html;
        })->width(150);
        $grid->column('current_address', __('Current Address'))
            ->editable()->width(200)->sortable();
        $grid->column('ready_for_trip', __('Ready for Trip'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])->width(120)->sortable()
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No'
            ]);
        $serviceColumns = [
            'is_car' => 'Car Requested',
            'is_car_approved' => 'Car Approved',
            'is_boda' => 'Boda Requested',
            'is_boda_approved' => 'Boda Approved',
            'is_ambulance' => 'Ambulance Requested',
            'is_ambulance_approved' => 'Ambulance Approved',
            'is_police' => 'Police Requested',
            'is_police_approved' => 'Police Approved',
            'is_delivery' => 'Delivery Requested',
            'is_delivery_approved' => 'Delivery Approved',
            'is_breakdown' => 'Breakdown Requested',
            'is_breakdown_approved' => 'Breakdown Approved',
            'is_firebrugade' => 'Fire Brigade Requested',
            'is_firebrugade_approved' => 'Fire Brigade Approved',
        ];

        $grid->column('is_car', __('Car Role'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_car_approved', __('Car Approved'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_boda', __('Boda Role'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_boda_approved', __('Boda Approved'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_ambulance', __('Ambulance Role'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_ambulance_approved', __('Ambulance Approved'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_police', __('Police Role'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_police_approved', __('Police Approved'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_delivery', __('Delivery Role'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_delivery_approved', __('Delivery Approved'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_breakdown', __('Breakdown Role'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_breakdown_approved', __('Breakdown Approved'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_breakdown_approved', __('Breakdown Approved'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_firebrugade', __('Fire Brigade Role'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('is_firebrugade_approved', __('Fire Brigade Approved'))
            ->editable('select', [
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();

        // Enhanced Filtering System
        $grid->filter(function ($filter) {
            $filter->column(1 / 4, function ($filter) {
                $filter->like('name', 'Name');
                $filter->like('email', 'Email');
                $filter->like('phone_number', 'Phone');
                $filter->like('nin', 'National ID');
                $filter->like('driving_license_number', 'License Number');
            });

            $filter->column(1 / 4, function ($filter) {
                $filter->equal('user_type', 'User Type')->select([
                    'Admin' => 'Admin',
                    'Driver' => 'Driver',
                    'Pending Driver' => 'Pending Driver',
                    'Customer' => 'Customer',
                ]);
                $filter->equal('status', 'Status')->select([
                    '1' => 'Active',
                    '2' => 'Pending',
                    '0' => 'Blocked',
                ]);
                $filter->equal('sex', 'Gender')->select([
                    'Male' => 'Male',
                    'Female' => 'Female',
                ]);
                $filter->between('created_at', 'Registration Date')->date();
            });
        });

        $grid->quickSearch('name', 'phone_number', 'email', 'nin', 'driving_license_number')
            ->placeholder('Search by name, phone, email, NIN, or license number');

        // Tools
        $grid->tools(function ($tools) {
            $tools->append('<div class="btn-group">');
            $tools->append('<a href="' . admin_url('analytics') . '" class="btn btn-info btn-sm">📊 Analytics</a>');
            $tools->append('<a href="' . admin_url('reports') . '" class="btn btn-success btn-sm">📈 Reports</a>');
            $tools->append('<a href="' . admin_url('bulk-operations') . '" class="btn btn-warning btn-sm">⚡ Bulk Ops</a>');
            $tools->append('</div>');
        });

        // Pagination
        $grid->paginate(20);

        return $grid;
    }

    /**
     * Comprehensive User Profile View
     */
    protected function detail($id)
    {
        $user = Administrator::findOrFail($id);

        $tab = new Tab();

        // Enhanced bio tab with comprehensive information
        $tab->add('👤 Profile & Bio', $this->createProfileView($user));

        // Service management tab with detailed breakdown
        $tab->add('🔧 Service Management', $this->createServiceManagementView($user));

        // Trip history and analytics
        $tab->add('🚗 Trip History & Analytics', $this->createTripAnalyticsView($user));

        // Financial records and earnings
        $tab->add('💰 Financial Records', $this->createFinancialView($user));

        // Location tracking and activity
        $tab->add('📍 Location & Activity', $this->createLocationView($user));

        // Documents and verification
        $tab->add('📄 Documents & Verification', $this->createDocumentsView($user));

        return $tab;
    }

    /**
     * Enhanced Form with All User Fields
     */
    protected function form()
    {
        $form = new Form(new Administrator());

        // Personal Information Tab
        $form->tab('👤 Personal Information', function ($form) {
            $form->text('first_name', __('First Name'))->rules('required|max:255');
            $form->text('last_name', __('Last Name'))->rules('required|max:255');
            $form->email('email', __('Email'))->rules('required|email|max:255');
            $form->text('phone_number', __('Primary Phone'))->rules('required');
            $form->text('phone_number_2', __('Secondary Phone'));
            $form->image('avatar', __('Profile Photo'))->uniqueName();

            $form->date('date_of_birth', __('Date of Birth'));
            $form->text('place_of_birth', __('Place of Birth'));
            $form->radio('sex', __('Gender'))->options([
                'Male' => 'Male',
                'Female' => 'Female',
            ]);

            $form->textarea('home_address', __('Home Address'));
            $form->textarea('current_address', __('Current Address/GPS'));
        });

        // Account Settings Tab
        $form->tab('⚙️ Account Settings', function ($form) {
            $form->text('username', __('Username'))->rules('max:255');
            $form->password('password', __('Password'))->rules('min:4');

            $form->radioCard('user_type', __('User Type'))->options([
                'Admin' => 'Administrator',
                'Driver' => 'Approved Driver',
                'Pending Driver' => 'Pending Driver Approval',
                'Customer' => 'Customer/Passenger',
            ])->rules('required')->default('Customer');

            $form->radioCard('status', __('Account Status'))->options([
                '1' => 'Active - Full Access',
                '2' => 'Pending - Awaiting Approval',
                '0' => 'Blocked - Account Suspended',
            ])->rules('required')->default('1');

            $form->radio('ready_for_trip', __('Availability Status'))->options([
                'Yes' => 'Online/Available for Trips',
                'No' => 'Offline/Not Available',
            ])->default('No');
        });

        // Driver Documentation Tab
        $form->tab('📄 Driver Documentation', function ($form) {
            $form->text('nin', __('National ID Number'));
            $form->text('driving_license_number', __('Driving License Number'));
            $form->date('driving_license_issue_date', __('License Issue Date'));
            $form->date('driving_license_validity', __('License Validity Date'));
            $form->text('driving_license_issue_authority', __('Issuing Authority'));
            $form->image('driving_license_photo', __('License Photo'))->uniqueName();

            $form->select('automobile', __('Primary Vehicle Type'))->options([
                'car' => 'Car',
                'motorcycle' => 'Motorcycle',
                'truck' => 'Truck',
                'van' => 'Van',
                'other' => 'Other',
            ])->default('car');
        });

        // Service Capabilities Tab
        $form->tab('🔧 Service Management', function ($form) {
            $services = [
                'car' => ['🚗', 'Car/Taxi Service', 'Regular passenger transport'],
                'boda' => ['🏍️', 'Bodaboda Service', 'Motorcycle rides for quick transport'],
                'ambulance' => ['🚑', 'Ambulance Service', 'Emergency medical transport'],
                'police' => ['🚔', 'Police Service', 'Law enforcement support'],
                'delivery' => ['📦', 'Delivery Service', 'Package and goods delivery'],
                'breakdown' => ['🔧', 'Breakdown Service', 'Vehicle breakdown assistance'],
                'firebrugade' => ['🚒', 'Fire Brigade Service', 'Emergency fire response'],
            ];

            $form->html('<h4>Service Requests</h4>');
            foreach ($services as $key => [$icon, $label, $description]) {
                $form->radio("is_{$key}", "{$icon} {$label}")
                    ->options(['Yes' => 'Requested', 'No' => 'Not Requested'])
                    ->default('No')
                    ->help($description);
            }

            $form->html('<hr><h4>Service Approvals</h4>');
            foreach ($services as $key => [$icon, $label, $description]) {
                $form->radio("is_{$key}_approved", "{$icon} Approve {$label}")
                    ->options(['Yes' => 'Approved', 'No' => 'Not Approved'])
                    ->default('No');
            }
        });

        // Advanced Settings Tab  
        $form->tab('⚡ Advanced Settings', function ($form) {
            $form->text('enterprise_id', __('Enterprise ID'));
            $form->text('remember_token', __('Remember Token'));
            $form->text('otp', __('OTP Code'));
            $form->number('max_passengers', __('Maximum Passengers'));
            $form->decimal('rating', __('User Rating'));
            $form->textarea('admin_notes', __('Admin Notes'))
                ->help('Internal notes visible only to administrators');
        });

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }

            // Combine first and last name
            if ($form->first_name && $form->last_name) {
                $form->name = $form->first_name . ' ' . $form->last_name;
            }

            // Set default username if not provided
            if (!$form->username && $form->email) {
                $form->username = explode('@', $form->email)[0];
            }
        });

        return $form;
    }

    /**
     * Helper Methods for Creating Enhanced Views
     */
    private function createProfileView($user)
    {
        $driverTrips = Trip::where('driver_id', $user->id)->count();
        $customerBookings = TripBooking::where('customer_id', $user->id)->count();
        $totalEarnings = TripBooking::where('driver_id', $user->id)->where('payment_status', 'paid')->sum('price');

        return view('admin.employees.profile-view', compact('user', 'driverTrips', 'customerBookings', 'totalEarnings'));
    }

    private function createServiceManagementView($user)
    {
        $services = [
            'car' => ['🚗', 'Car/Taxi Service'],
            'boda' => ['🏍️', 'Bodaboda Service'],
            'ambulance' => ['🚑', 'Ambulance Service'],
            'police' => ['🚔', 'Police Service'],
            'delivery' => ['📦', 'Delivery Service'],
            'breakdown' => ['🔧', 'Breakdown Service'],
            'firebrugade' => ['🚒', 'Fire Brigade Service'],
        ];

        return view('admin.employees.service-management', compact('user', 'services'));
    }

    private function createTripAnalyticsView($user)
    {
        $driverTrips = Trip::where('driver_id', $user->id)->with('bookings')->get();
        $customerBookings = TripBooking::where('customer_id', $user->id)->with('trip')->get();
        $monthlyStats = Trip::where('driver_id', $user->id)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();

        return view('admin.employees.trip-analytics', compact('user', 'driverTrips', 'customerBookings', 'monthlyStats'));
    }

    private function createFinancialView($user)
    {
        $earnings = TripBooking::where('driver_id', $user->id)->get();
        $expenses = TripBooking::where('customer_id', $user->id)->get();
        $monthlyEarnings = TripBooking::where('driver_id', $user->id)
            ->where('payment_status', 'paid')
            ->selectRaw('MONTH(created_at) as month, SUM(price) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();

        return view('admin.employees.financial-view', compact('user', 'earnings', 'expenses', 'monthlyEarnings'));
    }

    private function createLocationView($user)
    {
        $recentTrips = Trip::where('driver_id', $user->id)
            ->orWhereHas('bookings', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.employees.location-view', compact('user', 'recentTrips'));
    }

    private function createDocumentsView($user)
    {
        $documents = [
            'avatar' => 'Profile Photo',
            'driving_license_photo' => 'Driving License',
        ];

        return view('admin.employees.documents-view', compact('user', 'documents'));
    }

    /**
     * Custom Action Routes
     */
    public function approve($id)
    {
        $user = Administrator::findOrFail($id);
        $user->status = '1';
        $user->save();

        admin_toastr('User approved successfully', 'success');
        return redirect()->back();
    }

    public function block($id)
    {
        $user = Administrator::findOrFail($id);
        $user->status = '0';
        $user->save();

        admin_toastr('User blocked successfully', 'warning');
        return redirect()->back();
    }

    public function activate($id)
    {
        $user = Administrator::findOrFail($id);
        $user->status = '1';
        $user->save();

        admin_toastr('User activated successfully', 'success');
        return redirect()->back();
    }

    public function approveService($id, $service)
    {
        $user = Administrator::findOrFail($id);
        $field = "is_{$service}_approved";

        if (property_exists($user, $field)) {
            $user->$field = 'Yes';
            $user->save();

            admin_toastr(ucfirst($service) . ' service approved successfully', 'success');
        } else {
            admin_toastr('Invalid service type', 'error');
        }

        return redirect()->back();
    }

    /**
     * Analytics Dashboard
     */
    public function analytics(Content $content)
    {
        $userStats = [
            'total_users' => Administrator::count(),
            'active_drivers' => Administrator::where('user_type', 'Driver')->where('status', '1')->count(),
            'pending_drivers' => Administrator::where('user_type', 'Pending Driver')->count(),
            'customers' => Administrator::where('user_type', 'Customer')->count(),
            'online_drivers' => Administrator::where('ready_for_trip', 'Yes')->count(),
        ];

        $serviceStats = [];
        $services = ['car', 'boda', 'ambulance', 'police', 'delivery', 'breakdown', 'firebrugade'];
        foreach ($services as $service) {
            $serviceStats[$service] = [
                'requested' => Administrator::where("is_{$service}", 'Yes')->count(),
                'approved' => Administrator::where("is_{$service}_approved", 'Yes')->count(),
            ];
        }

        return $content
            ->title('User Analytics Dashboard')
            ->description('Comprehensive Platform Analytics & Insights')
            ->body(view('admin.employees.analytics-dashboard', compact('userStats', 'serviceStats')));
    }

    /**
     * Comprehensive Reports
     */
    public function reports(Content $content)
    {
        return $content
            ->title('User & Service Reports')
            ->description('Detailed Reporting and Data Export')
            ->body(view('admin.employees.reports-dashboard'));
    }

    /**
     * Bulk Operations Interface
     */
    public function bulkOperations(Content $content)
    {
        return $content
            ->title('Bulk Operations & Automation')
            ->description('Batch Processing and Mass Updates')
            ->body(view('admin.employees.bulk-operations'));
    }
}
