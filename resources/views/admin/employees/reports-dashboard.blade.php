<div class="row">
    <div class="col-md-12">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">📈 Comprehensive Reports Dashboard</h3>
                <div class="box-tools pull-right">
                    <a href="{{ admin_url('employees') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
            <div class="box-body">
                
                <!-- Report Categories -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3>👥</h3>
                                <p>User Reports</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-users"></i>
                            </div>
                            <a href="#user-reports" class="small-box-footer" onclick="showReportSection('user-reports')">
                                Generate Reports <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3>🔧</h3>
                                <p>Service Reports</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-cogs"></i>
                            </div>
                            <a href="#service-reports" class="small-box-footer" onclick="showReportSection('service-reports')">
                                Generate Reports <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3>🚗</h3>
                                <p>Trip Reports</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-road"></i>
                            </div>
                            <a href="#trip-reports" class="small-box-footer" onclick="showReportSection('trip-reports')">
                                Generate Reports <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3>💰</h3>
                                <p>Financial Reports</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-money"></i>
                            </div>
                            <a href="#financial-reports" class="small-box-footer" onclick="showReportSection('financial-reports')">
                                Generate Reports <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Reports Section -->
                <div id="user-reports" class="report-section" style="display: none;">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">👥 User Reports</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Quick Reports</h4>
                                    <div class="list-group">
                                        <a href="#" class="list-group-item" onclick="generateReport('all-users')">
                                            <i class="fa fa-users text-blue"></i> All Users Report
                                            <span class="badge">{{ \Encore\Admin\Auth\Database\Administrator::count() }}</span>
                                        </a>
                                        <a href="#" class="list-group-item" onclick="generateReport('active-drivers')">
                                            <i class="fa fa-car text-green"></i> Active Drivers Report
                                            <span class="badge">{{ \Encore\Admin\Auth\Database\Administrator::where('user_type', 'Driver')->where('status', '1')->count() }}</span>
                                        </a>
                                        <a href="#" class="list-group-item" onclick="generateReport('pending-approvals')">
                                            <i class="fa fa-clock-o text-yellow"></i> Pending Approvals Report
                                            <span class="badge">{{ \Encore\Admin\Auth\Database\Administrator::where('status', '2')->count() }}</span>
                                        </a>
                                        <a href="#" class="list-group-item" onclick="generateReport('blocked-users')">
                                            <i class="fa fa-ban text-red"></i> Blocked Users Report
                                            <span class="badge">{{ \Encore\Admin\Auth\Database\Administrator::where('status', '0')->count() }}</span>
                                        </a>
                                        <a href="#" class="list-group-item" onclick="generateReport('new-registrations')">
                                            <i class="fa fa-user-plus text-purple"></i> New Registrations (This Month)
                                            <span class="badge">{{ \Encore\Admin\Auth\Database\Administrator::whereMonth('created_at', now()->month)->count() }}</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h4>Custom Report Builder</h4>
                                    <form id="custom-user-report-form">
                                        <div class="form-group">
                                            <label>Report Type</label>
                                            <select class="form-control" name="report_type">
                                                <option value="detailed">Detailed User Report</option>
                                                <option value="summary">Summary Report</option>
                                                <option value="activity">Activity Report</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>User Type</label>
                                            <select class="form-control" name="user_type">
                                                <option value="all">All Types</option>
                                                <option value="Admin">Admins</option>
                                                <option value="Driver">Drivers</option>
                                                <option value="Pending Driver">Pending Drivers</option>
                                                <option value="Customer">Customers</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="form-control" name="status">
                                                <option value="all">All Statuses</option>
                                                <option value="1">Active</option>
                                                <option value="2">Pending</option>
                                                <option value="0">Blocked</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Date Range</label>
                                            <input type="date" class="form-control" name="start_date" placeholder="Start Date">
                                            <input type="date" class="form-control" name="end_date" placeholder="End Date" style="margin-top: 5px;">
                                        </div>
                                        <div class="form-group">
                                            <label>Export Format</label>
                                            <select class="form-control" name="format">
                                                <option value="pdf">PDF</option>
                                                <option value="excel">Excel</option>
                                                <option value="csv">CSV</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-primary" onclick="generateCustomReport('user')">
                                            <i class="fa fa-download"></i> Generate Report
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Reports Section -->
                <div id="service-reports" class="report-section" style="display: none;">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">🔧 Service Reports</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Service Analytics</h4>
                                    @php
                                        $services = ['car', 'boda', 'ambulance', 'police', 'delivery', 'breakdown', 'firebrugade'];
                                        $serviceIcons = [
                                            'car' => '🚗', 'boda' => '🏍️', 'ambulance' => '🚑', 
                                            'police' => '🚔', 'delivery' => '📦', 'breakdown' => '🔧', 'firebrugade' => '🚒'
                                        ];
                                    @endphp
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Service</th>
                                                    <th>Requested</th>
                                                    <th>Approved</th>
                                                    <th>Rate</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($services as $service)
                                                    @php
                                                        $requested = \Encore\Admin\Auth\Database\Administrator::where("is_{$service}", 'Yes')->count();
                                                        $approved = \Encore\Admin\Auth\Database\Administrator::where("is_{$service}_approved", 'Yes')->count();
                                                        $rate = $requested > 0 ? round(($approved / $requested) * 100, 1) : 0;
                                                        $icon = $serviceIcons[$service] ?? '🔹';
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $icon }} {{ ucfirst($service) }}</td>
                                                        <td>{{ $requested }}</td>
                                                        <td>{{ $approved }}</td>
                                                        <td>{{ $rate }}%</td>
                                                        <td>
                                                            <a href="#" class="btn btn-xs btn-info" onclick="generateServiceReport('{{ $service }}')">
                                                                <i class="fa fa-download"></i> Report
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h4>Service Performance Chart</h4>
                                    <canvas id="servicePerformanceChart" style="height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trip Reports Section -->
                <div id="trip-reports" class="report-section" style="display: none;">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">🚗 Trip & Booking Reports</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>Trip Analytics</h4>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Metric</th>
                                                    <th>Today</th>
                                                    <th>This Week</th>
                                                    <th>This Month</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><i class="fa fa-road text-blue"></i> Total Trips</td>
                                                    <td>{{ \App\Models\Trip::whereDate('created_at', today())->count() }}</td>
                                                    <td>{{ \App\Models\Trip::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}</td>
                                                    <td>{{ \App\Models\Trip::whereMonth('created_at', now()->month)->count() }}</td>
                                                    <td>{{ \App\Models\Trip::count() }}</td>
                                                    <td><a href="#" class="btn btn-xs btn-primary" onclick="generateTripReport('all')">Report</a></td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fa fa-check text-green"></i> Completed Trips</td>
                                                    <td>{{ \App\Models\Trip::where('status', 'Completed')->whereDate('created_at', today())->count() }}</td>
                                                    <td>{{ \App\Models\Trip::where('status', 'Completed')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}</td>
                                                    <td>{{ \App\Models\Trip::where('status', 'Completed')->whereMonth('created_at', now()->month)->count() }}</td>
                                                    <td>{{ \App\Models\Trip::where('status', 'Completed')->count() }}</td>
                                                    <td><a href="#" class="btn btn-xs btn-success" onclick="generateTripReport('completed')">Report</a></td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fa fa-ticket text-purple"></i> Total Bookings</td>
                                                    <td>{{ \App\Models\TripBooking::whereDate('created_at', today())->count() }}</td>
                                                    <td>{{ \App\Models\TripBooking::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}</td>
                                                    <td>{{ \App\Models\TripBooking::whereMonth('created_at', now()->month)->count() }}</td>
                                                    <td>{{ \App\Models\TripBooking::count() }}</td>
                                                    <td><a href="#" class="btn btn-xs btn-info" onclick="generateBookingReport('all')">Report</a></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Reports Section -->
                <div id="financial-reports" class="report-section" style="display: none;">
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h3 class="box-title">💰 Financial Reports</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Revenue Analytics</h4>
                                    @php
                                        $totalRevenue = \App\Models\TripBooking::where('payment_status', 'paid')->sum('price');
                                        $todayRevenue = \App\Models\TripBooking::where('payment_status', 'paid')->whereDate('created_at', today())->sum('price');
                                        $monthRevenue = \App\Models\TripBooking::where('payment_status', 'paid')->whereMonth('created_at', now()->month)->sum('price');
                                        $pendingPayments = \App\Models\TripBooking::where('payment_status', 'pending')->sum('price');
                                    @endphp
                                    <div class="info-box bg-green">
                                        <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Revenue</span>
                                            <span class="info-box-number">UGX {{ number_format($totalRevenue) }}</span>
                                        </div>
                                    </div>
                                    <div class="info-box bg-blue">
                                        <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">This Month</span>
                                            <span class="info-box-number">UGX {{ number_format($monthRevenue) }}</span>
                                        </div>
                                    </div>
                                    <div class="info-box bg-yellow">
                                        <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Pending Payments</span>
                                            <span class="info-box-number">UGX {{ number_format($pendingPayments) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h4>Generate Financial Reports</h4>
                                    <div class="list-group">
                                        <a href="#" class="list-group-item" onclick="generateFinancialReport('revenue')">
                                            <i class="fa fa-line-chart text-green"></i> Revenue Report
                                        </a>
                                        <a href="#" class="list-group-item" onclick="generateFinancialReport('payments')">
                                            <i class="fa fa-credit-card text-blue"></i> Payment Status Report
                                        </a>
                                        <a href="#" class="list-group-item" onclick="generateFinancialReport('earnings')">
                                            <i class="fa fa-money text-yellow"></i> Driver Earnings Report
                                        </a>
                                        <a href="#" class="list-group-item" onclick="generateFinancialReport('transactions')">
                                            <i class="fa fa-exchange text-purple"></i> Transaction History
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function showReportSection(sectionId) {
    // Hide all sections
    $('.report-section').hide();
    // Show selected section
    $('#' + sectionId).show();
    
    // Scroll to section
    $('html, body').animate({
        scrollTop: $('#' + sectionId).offset().top - 100
    }, 500);
}

function generateReport(type) {
    // This would trigger report generation
    alert('Generating ' + type + ' report...');
    // In a real implementation, you would make an AJAX call to generate and download the report
}

function generateCustomReport(category) {
    var formData = $('#custom-' + category + '-report-form').serialize();
    alert('Generating custom ' + category + ' report with parameters: ' + formData);
    // In a real implementation, you would submit this to a report generation endpoint
}

function generateServiceReport(service) {
    alert('Generating report for ' + service + ' service...');
}

function generateTripReport(type) {
    alert('Generating trip report for: ' + type);
}

function generateBookingReport(type) {
    alert('Generating booking report for: ' + type);
}

function generateFinancialReport(type) {
    alert('Generating financial report for: ' + type);
}

// Initialize charts
$(document).ready(function() {
    // Service Performance Chart
    @php
        $services = ['car', 'boda', 'ambulance', 'police', 'delivery', 'breakdown', 'firebrugade'];
        $chartData = [];
        $chartLabels = [];
        foreach($services as $service) {
            $requested = \Encore\Admin\Auth\Database\Administrator::where("is_{$service}", 'Yes')->count();
            $approved = \Encore\Admin\Auth\Database\Administrator::where("is_{$service}_approved", 'Yes')->count();
            $chartLabels[] = ucfirst($service);
            $chartData[] = $requested > 0 ? round(($approved / $requested) * 100, 1) : 0;
        }
    @endphp
    
    var ctx = document.getElementById('servicePerformanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Approval Rate (%)',
                data: {!! json_encode($chartData) !!},
                backgroundColor: [
                    '#3498db', '#f39c12', '#e74c3c', '#9b59b6', 
                    '#27ae60', '#95a5a6', '#e67e22'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
});
</script>