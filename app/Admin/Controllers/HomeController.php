<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Negotiation;
use App\Models\Project;
use App\Models\RouteStage;
use App\Models\Task;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Faker\Factory as Faker;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use SplFileObject;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        $user = Admin::user();
        
        // Get comprehensive dashboard statistics
        $stats = $this->getDashboardStats();
        
        $content->title('<b>' . Utils::greet() . " " . $user->name . '!</b>')
                ->description('Rideshare Operations Dashboard');

        // Top metrics row
        $content->row(function (Row $row) use ($stats) {
            $row->column(3, function (Column $column) use ($stats) {
                $column->append(view('dashboard.metric-card', [
                    'title' => 'Total Users',
                    'value' => number_format($stats['total_users']),
                    'change' => $stats['users_change'],
                    'icon' => 'fas fa-users',
                    'color' => 'primary',
                    'bg' => 'linear-gradient(135deg, #040404 0%, #FF9900 100%)'
                ]));
            });
            
            $row->column(3, function (Column $column) use ($stats) {
                $column->append(view('dashboard.metric-card', [
                    'title' => 'Active Trips',
                    'value' => number_format($stats['active_trips']),
                    'change' => $stats['trips_change'],
                    'icon' => 'fas fa-car',
                    'color' => 'warning', 
                    'bg' => 'linear-gradient(135deg, #ECC60F 0%, #d4b00e 100%)'
                ]));
            });
            
            $row->column(3, function (Column $column) use ($stats) {
                $column->append(view('dashboard.metric-card', [
                    'title' => 'Total Revenue',
                    'value' => 'UGX ' . number_format($stats['total_revenue']),
                    'change' => $stats['revenue_change'],
                    'icon' => 'fas fa-dollar-sign',
                    'color' => 'success',
                    'bg' => 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)'
                ]));
            });
            
            $row->column(3, function (Column $column) use ($stats) {
                $column->append(view('dashboard.metric-card', [
                    'title' => 'Pending Bookings',
                    'value' => number_format($stats['pending_bookings']),
                    'change' => $stats['bookings_change'],
                    'icon' => 'fas fa-clock',
                    'color' => 'info',
                    'bg' => 'linear-gradient(135deg, #2196F3 0%, #1976D2 100%)'
                ]));
            });
        });

        // Main dashboard content
        $content->row(function (Row $row) use ($stats) {
            // Left column - Charts and analytics
            $row->column(8, function (Column $column) use ($stats) {
                // Trip trends chart
                $column->append(view('dashboard.trips-chart', [
                    'trips_data' => $stats['trips_chart_data']
                ]));
                
                // Revenue analytics
                $column->append(view('dashboard.revenue-chart', [
                    'revenue_data' => $stats['revenue_chart_data']
                ]));
            });
            
            // Right column - Quick stats and actions
            $row->column(4, function (Column $column) use ($stats) {
                // Live statistics
                $column->append(view('dashboard.live-stats', [
                    'drivers_online' => $stats['drivers_online'],
                    'customers_active' => $stats['customers_active'],
                    'avg_trip_price' => $stats['avg_trip_price'],
                    'completion_rate' => $stats['completion_rate']
                ]));
                
                // Recent activities
                $column->append(view('dashboard.recent-activities', [
                    'activities' => $stats['recent_activities']
                ]));
            });
        });

        // Bottom row - Detailed insights
        $content->row(function (Row $row) use ($stats) {
            $row->column(6, function (Column $column) use ($stats) {
                $column->append(view('dashboard.top-routes', [
                    'routes' => $stats['top_routes']
                ]));
            });
            
            $row->column(6, function (Column $column) use ($stats) {
                $column->append(view('dashboard.driver-performance', [
                    'drivers' => $stats['top_drivers']
                ]));
            });
        });

        return $content;
    }

    private function getDashboardStats()
    {
        // Current date ranges
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $lastWeek = Carbon::now()->subWeek();
        $lastMonth = Carbon::now()->subMonth();
        
        // Total users
        $totalUsers = Administrator::count();
        $usersYesterday = Administrator::whereDate('created_at', $yesterday)->count();
        $usersChange = $usersYesterday > 0 ? (($totalUsers - $usersYesterday) / $usersYesterday) * 100 : 0;
        
        // Active trips (Ongoing, Pending)
        $activeTrips = Trip::whereIn('status', ['Ongoing', 'Pending'])->count();
        $tripsYesterday = Trip::whereIn('status', ['Ongoing', 'Pending'])
                             ->whereDate('created_at', $yesterday)->count();
        $tripsChange = $tripsYesterday > 0 ? (($activeTrips - $tripsYesterday) / $tripsYesterday) * 100 : 0;
        
        // Revenue calculations
        $totalRevenue = TripBooking::where('payment_status', 'Paid')->sum('price');
        $revenueYesterday = TripBooking::where('payment_status', 'Paid')
                                     ->whereDate('created_at', $yesterday)->sum('price');
        $revenueChange = $revenueYesterday > 0 ? (($totalRevenue - $revenueYesterday) / $revenueYesterday) * 100 : 0;
        
        // Pending bookings
        $pendingBookings = TripBooking::where('status', 'Pending')->count();
        $bookingsYesterday = TripBooking::where('status', 'Pending')
                                       ->whereDate('created_at', $yesterday)->count();
        $bookingsChange = $bookingsYesterday > 0 ? (($pendingBookings - $bookingsYesterday) / $bookingsYesterday) * 100 : 0;
        
        // Chart data for trips (last 7 days)
        $tripsChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $tripsChartData[] = [
                'date' => $date->format('M d'),
                'trips' => Trip::whereDate('created_at', $date)->count(),
                'bookings' => TripBooking::whereDate('created_at', $date)->count()
            ];
        }
        
        // Revenue chart data (last 30 days)
        $revenueChartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenueChartData[] = [
                'date' => $date->format('M d'),
                'revenue' => TripBooking::where('payment_status', 'Paid')
                                       ->whereDate('created_at', $date)
                                       ->sum('price')
            ];
        }
        
        // Live statistics
        $driversOnline = Administrator::where('user_type', 'Driver')
                                    ->where('status', 'Active')
                                    ->count();
        
        $customersActive = Administrator::where('user_type', 'Customer')
                                       ->where('status', 'Active')
                                       ->count();
        
        $avgTripPrice = Trip::avg('price') ?? 0;
        
        $completedTrips = Trip::where('status', 'Completed')->count();
        $totalTripsCreated = Trip::count();
        $completionRate = $totalTripsCreated > 0 ? ($completedTrips / $totalTripsCreated) * 100 : 0;
        
        // Recent activities
        $recentActivities = collect();
        
        // Recent trips
        $recentTrips = Trip::with(['driver', 'customer'])
                          ->orderBy('created_at', 'desc')
                          ->limit(5)
                          ->get();
        
        foreach ($recentTrips as $trip) {
            $recentActivities->push([
                'type' => 'trip',
                'message' => "New trip created by " . ($trip->driver->name ?? 'Unknown'),
                'time' => $trip->created_at->diffForHumans(),
                'icon' => 'fas fa-car text-primary'
            ]);
        }
        
        // Recent bookings
        $recentBookings = TripBooking::orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();
        
        foreach ($recentBookings as $booking) {
            $recentActivities->push([
                'type' => 'booking',
                'message' => "New booking (UGX " . number_format($booking->price) . ")",
                'time' => $booking->created_at->diffForHumans(),
                'icon' => 'fas fa-ticket-alt text-warning'
            ]);
        }
        
        // Sort activities by time
        $recentActivities = $recentActivities->sortByDesc('time')->take(10)->values();
        
        // Top routes
        $topRoutes = DB::table('trips')
            ->select('start_name', 'end_name', DB::raw('COUNT(*) as trip_count'), DB::raw('AVG(price) as avg_price'))
            ->whereNotNull('start_name')
            ->whereNotNull('end_name')
            ->groupBy('start_name', 'end_name')
            ->orderBy('trip_count', 'desc')
            ->limit(10)
            ->get();
        
        // Top performing drivers
        $topDrivers = DB::table('trips')
            ->join('admin_users', 'trips.driver_id', '=', 'admin_users.id')
            ->select('admin_users.name', 'admin_users.phone_number', 
                    DB::raw('COUNT(trips.id) as total_trips'),
                    DB::raw('AVG(trips.price) as avg_price'),
                    DB::raw('SUM(CASE WHEN trips.status = "Completed" THEN 1 ELSE 0 END) as completed_trips'))
            ->groupBy('admin_users.id', 'admin_users.name', 'admin_users.phone_number')
            ->orderBy('total_trips', 'desc')
            ->limit(10)
            ->get();
        
        return [
            'total_users' => $totalUsers,
            'users_change' => round($usersChange, 1),
            'active_trips' => $activeTrips,
            'trips_change' => round($tripsChange, 1),
            'total_revenue' => $totalRevenue,
            'revenue_change' => round($revenueChange, 1),
            'pending_bookings' => $pendingBookings,
            'bookings_change' => round($bookingsChange, 1),
            'trips_chart_data' => $tripsChartData,
            'revenue_chart_data' => $revenueChartData,
            'drivers_online' => $driversOnline,
            'customers_active' => $customersActive,
            'avg_trip_price' => round($avgTripPrice, 0),
            'completion_rate' => round($completionRate, 1),
            'recent_activities' => $recentActivities,
            'top_routes' => $topRoutes,
            'top_drivers' => $topDrivers
        ];
    }

    public function calendar(Content $content)
    {
        $u = Auth::user();
        $content->title('Company Calendar');
        
        $content->row(function (Row $row) {
            $row->column(8, function (Column $column) {
                $column->append(Dashboard::dashboard_calender());
            });
            $row->column(4, function (Column $column) {
                $u = Admin::user();
                $column->append(view('dashboard.upcoming-events', [
                    'items' => Event::where('company_id', $u->company_id ?? 1)
                        ->where('event_date', '>=', Carbon::now()->format('Y-m-d'))
                        ->orderBy('id', 'desc')->limit(8)->get()
                ]));
            });
        });
        
        return $content;
    }
}
