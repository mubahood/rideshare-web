<div class="row">
    <div class="col-md-12">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">📊 User Analytics Dashboard</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                
                <!-- User Statistics Cards -->
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Users</span>
                                <span class="info-box-number">{{ number_format($userStats['total_users']) }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    All registered users
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-car"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Active Drivers</span>
                                <span class="info-box-number">{{ number_format($userStats['active_drivers']) }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $userStats['total_users'] > 0 ? round(($userStats['active_drivers'] / $userStats['total_users']) * 100, 1) : 0 }}%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ $userStats['total_users'] > 0 ? round(($userStats['active_drivers'] / $userStats['total_users']) * 100, 1) : 0 }}% of total users
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Online Drivers</span>
                                <span class="info-box-number">{{ number_format($userStats['online_drivers']) }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $userStats['active_drivers'] > 0 ? round(($userStats['online_drivers'] / $userStats['active_drivers']) * 100, 1) : 0 }}%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ $userStats['active_drivers'] > 0 ? round(($userStats['online_drivers'] / $userStats['active_drivers']) * 100, 1) : 0 }}% of active drivers
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pending Drivers</span>
                                <span class="info-box-number">{{ number_format($userStats['pending_drivers']) }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $userStats['total_users'] > 0 ? round(($userStats['pending_drivers'] / $userStats['total_users']) * 100, 1) : 0 }}%"></div>
                                </div>
                                <span class="progress-description">
                                    Awaiting approval
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Statistics -->
                <div class="row" style="margin-top: 20px;">
                    <div class="col-md-12">
                        <h4>🔧 Service Request Analytics</h4>
                        <div class="row">
                            @php
                                $serviceIcons = [
                                    'car' => ['🚗', 'Car Service', 'primary'],
                                    'boda' => ['🏍️', 'Boda Service', 'warning'],
                                    'ambulance' => ['🚑', 'Ambulance', 'danger'],
                                    'police' => ['🚔', 'Police', 'info'],
                                    'delivery' => ['📦', 'Delivery', 'success'],
                                    'breakdown' => ['🔧', 'Breakdown', 'default'],
                                    'firebrugade' => ['🚒', 'Fire Brigade', 'danger']
                                ];
                            @endphp

                            @foreach($serviceStats as $service => $stats)
                                @php
                                    [$icon, $label, $color] = $serviceIcons[$service] ?? ['🔹', ucfirst($service), 'default'];
                                    $approvalRate = $stats['requested'] > 0 ? round(($stats['approved'] / $stats['requested']) * 100, 1) : 0;
                                @endphp
                                <div class="col-md-3 col-sm-6">
                                    <div class="small-box bg-{{ $color }}">
                                        <div class="inner">
                                            <h3>{{ $stats['approved'] }}/{{ $stats['requested'] }}</h3>
                                            <p>{{ $icon }} {{ $label }}</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fa fa-cog"></i>
                                        </div>
                                        <div class="small-box-footer">
                                            {{ $approvalRate }}% Approval Rate
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- User Type Distribution Chart -->
                <div class="row" style="margin-top: 20px;">
                    <div class="col-md-6">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">👥 User Type Distribution</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="userTypeChart" style="height: 300px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">🚀 Service Adoption Rates</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="serviceChart" style="height: 300px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Table -->
                <div class="row" style="margin-top: 20px;">
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">📈 Recent User Activity</h3>
                                <div class="box-tools pull-right">
                                    <a href="{{ admin_url('employees') }}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-users"></i> View All Users
                                    </a>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Metric</th>
                                                <th>Today</th>
                                                <th>This Week</th>
                                                <th>This Month</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><i class="fa fa-user-plus text-green"></i> New Registrations</td>
                                                <td>{{ \Encore\Admin\Auth\Database\Administrator::whereDate('created_at', today())->count() }}</td>
                                                <td>{{ \Encore\Admin\Auth\Database\Administrator::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}</td>
                                                <td>{{ \Encore\Admin\Auth\Database\Administrator::whereMonth('created_at', now()->month)->count() }}</td>
                                                <td>{{ number_format($userStats['total_users']) }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fa fa-check text-green"></i> Service Approvals</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>{{ array_sum(array_column($serviceStats, 'approved')) }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fa fa-clock-o text-yellow"></i> Pending Requests</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>{{ array_sum(array_map(function($s) { return $s['requested'] - $s['approved']; }, $serviceStats)) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
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
$(document).ready(function() {
    // User Type Distribution Chart
    var ctx1 = document.getElementById('userTypeChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Active Drivers', 'Pending Drivers', 'Customers', 'Admins'],
            datasets: [{
                data: [
                    {{ $userStats['active_drivers'] }},
                    {{ $userStats['pending_drivers'] }},
                    {{ $userStats['customers'] }},
                    {{ $userStats['total_users'] - $userStats['active_drivers'] - $userStats['pending_drivers'] - $userStats['customers'] }}
                ],
                backgroundColor: ['#00a65a', '#f39c12', '#3c8dbc', '#dd4b39']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            }
        }
    });

    // Service Adoption Chart
    var ctx2 = document.getElementById('serviceChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: [
                @foreach($serviceStats as $service => $stats)
                    '{{ ucfirst($service) }}',
                @endforeach
            ],
            datasets: [{
                label: 'Requested',
                data: [
                    @foreach($serviceStats as $service => $stats)
                        {{ $stats['requested'] }},
                    @endforeach
                ],
                backgroundColor: '#3c8dbc'
            }, {
                label: 'Approved',
                data: [
                    @foreach($serviceStats as $service => $stats)
                        {{ $stats['approved'] }},
                    @endforeach
                ],
                backgroundColor: '#00a65a'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>