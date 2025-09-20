<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">👤 Complete User Profile</h3>
        <div class="box-tools pull-right">
            <a href="{{ admin_url('employees/' . $user->id . '/edit') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-edit"></i> Edit Profile
            </a>
        </div>
    </div>
    <div class="box-body">
        
        <div class="row">
            <!-- Profile Photo & Basic Info -->
            <div class="col-md-3">
                <div class="text-center">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" alt="Profile Photo" class="img-circle" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="img-circle bg-gray" style="width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="fa fa-user fa-3x text-white"></i>
                        </div>
                    @endif
                    
                    <h4 style="margin-top: 15px;">{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->user_type }}</p>
                    
                    @php
                        $statusColors = ['1' => 'success', '2' => 'warning', '0' => 'danger'];
                        $statusLabels = ['1' => 'Active', '2' => 'Pending', '0' => 'Blocked'];
                        $statusColor = $statusColors[$user->status] ?? 'default';
                        $statusLabel = $statusLabels[$user->status] ?? 'Unknown';
                    @endphp
                    
                    <span class="label label-{{ $statusColor }}">{{ $statusLabel }}</span>
                    
                    @if($user->ready_for_trip == 'Yes')
                        <br><span class="label label-success" style="margin-top: 5px;">🟢 Online</span>
                    @else
                        <br><span class="label label-default" style="margin-top: 5px;">⚫ Offline</span>
                    @endif
                </div>
            </div>
            
            <!-- Personal Information -->
            <div class="col-md-4">
                <h4><i class="fa fa-user"></i> Personal Information</h4>
                <table class="table table-bordered table-condensed">
                    <tr>
                        <td><strong>Full Name:</strong></td>
                        <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>
                            {{ $user->phone_number }}
                            @if($user->phone_number_2)
                                <br><small class="text-muted">Alt: {{ $user->phone_number_2 }}</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Gender:</strong></td>
                        <td>{{ $user->sex ?: 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Date of Birth:</strong></td>
                        <td>
                            @if($user->date_of_birth)
                                {{ \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') }}
                                <small class="text-muted">({{ \Carbon\Carbon::parse($user->date_of_birth)->age }} years old)</small>
                            @else
                                Not provided
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Registration:</strong></td>
                        <td>{{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Last Activity:</strong></td>
                        <td>{{ \Carbon\Carbon::parse($user->updated_at)->diffForHumans() }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Statistics & Activity -->
            <div class="col-md-5">
                <h4><i class="fa fa-bar-chart"></i> Activity Statistics</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-blue">
                            <span class="info-box-icon"><i class="fa fa-road"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Driver Trips</span>
                                <span class="info-box-number">{{ $driverTrips }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-purple">
                            <span class="info-box-icon"><i class="fa fa-ticket"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Bookings</span>
                                <span class="info-box-number">{{ $customerBookings }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-money"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Earnings</span>
                                <span class="info-box-number">UGX {{ number_format($totalEarnings) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <h5><i class="fa fa-flash"></i> Quick Actions</h5>
                <div class="btn-group-vertical btn-block">
                    @if($user->status == '2')
                        <a href="{{ admin_url('employees/' . $user->id . '/approve') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-check"></i> Approve User
                        </a>
                    @elseif($user->status == '1')
                        <a href="{{ admin_url('employees/' . $user->id . '/block') }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-ban"></i> Block User
                        </a>
                    @else
                        <a href="{{ admin_url('employees/' . $user->id . '/activate') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-unlock"></i> Activate User
                        </a>
                    @endif
                    
                    <a href="{{ admin_url('employees/' . $user->id . '/edit') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-edit"></i> Edit Profile
                    </a>
                    
                    @if(in_array($user->user_type, ['Driver', 'Pending Driver']))
                        <a href="#" class="btn btn-info btn-sm" onclick="showServiceModal()">
                            <i class="fa fa-cogs"></i> Manage Services
                        </a>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Additional Information -->
        <hr>
        <div class="row">
            <!-- Address Information -->
            <div class="col-md-6">
                <h4><i class="fa fa-map-marker"></i> Address Information</h4>
                <table class="table table-bordered">
                    <tr>
                        <td><strong>Home Address:</strong></td>
                        <td>{{ $user->home_address ?: 'Not provided' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Current Location:</strong></td>
                        <td>
                            @if($user->current_address && str_contains($user->current_address, ','))
                                @php
                                    $coords = explode(',', $user->current_address);
                                    $lat = round(floatval($coords[0]), 6);
                                    $lng = round(floatval($coords[1]), 6);
                                @endphp
                                📍 {{ $lat }}, {{ $lng }}
                                <br><small class="text-muted">
                                    <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank">
                                        <i class="fa fa-external-link"></i> View on Map
                                    </a>
                                </small>
                            @else
                                {{ $user->current_address ?: 'Location not available' }}
                            @endif
                        </td>
                    </tr>
                    @if($user->place_of_birth)
                        <tr>
                            <td><strong>Place of Birth:</strong></td>
                            <td>{{ $user->place_of_birth }}</td>
                        </tr>
                    @endif
                </table>
            </div>
            
            <!-- Driver Information -->
            @if(in_array($user->user_type, ['Driver', 'Pending Driver']))
                <div class="col-md-6">
                    <h4><i class="fa fa-car"></i> Driver Information</h4>
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>National ID:</strong></td>
                            <td>{{ $user->nin ?: 'Not provided' }}</td>
                        </tr>
                        <tr>
                            <td><strong>License Number:</strong></td>
                            <td>{{ $user->driving_license_number ?: 'Not provided' }}</td>
                        </tr>
                        <tr>
                            <td><strong>License Issue Date:</strong></td>
                            <td>
                                @if($user->driving_license_issue_date)
                                    {{ \Carbon\Carbon::parse($user->driving_license_issue_date)->format('M d, Y') }}
                                @else
                                    Not provided
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>License Validity:</strong></td>
                            <td>
                                @if($user->driving_license_validity)
                                    {{ \Carbon\Carbon::parse($user->driving_license_validity)->format('M d, Y') }}
                                    @php
                                        $validity = \Carbon\Carbon::parse($user->driving_license_validity);
                                        $isExpired = $validity->isPast();
                                        $daysToExpiry = $validity->diffInDays(now(), false);
                                    @endphp
                                    @if($isExpired)
                                        <span class="label label-danger">Expired {{ abs($daysToExpiry) }} days ago</span>
                                    @elseif($daysToExpiry <= 30)
                                        <span class="label label-warning">Expires in {{ $daysToExpiry }} days</span>
                                    @else
                                        <span class="label label-success">Valid</span>
                                    @endif
                                @else
                                    Not provided
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Vehicle Type:</strong></td>
                            <td>{{ $user->automobile ? ucfirst($user->automobile) : 'Not specified' }}</td>
                        </tr>
                    </table>
                </div>
            @endif
        </div>
        
    </div>
</div>

<!-- Service Management Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">🔧 Service Management</h4>
            </div>
            <div class="modal-body">
                @php
                    $services = [
                        'car' => ['🚗', 'Car Service'],
                        'boda' => ['🏍️', 'Boda Service'],
                        'ambulance' => ['🚑', 'Ambulance Service'],
                        'police' => ['🚔', 'Police Service'],
                        'delivery' => ['📦', 'Delivery Service'],
                        'breakdown' => ['🔧', 'Breakdown Service'],
                        'firebrugade' => ['🚒', 'Fire Brigade Service'],
                    ];
                @endphp
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Requested</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($services as $key => [$icon, $name])
                                @php
                                    $requested = $user->{"is_{$key}"} == 'Yes';
                                    $approved = $user->{"is_{$key}_approved"} == 'Yes';
                                @endphp
                                <tr>
                                    <td>{{ $icon }} {{ $name }}</td>
                                    <td>
                                        @if($requested)
                                            <span class="label label-info">Yes</span>
                                        @else
                                            <span class="label label-default">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($requested && $approved)
                                            <span class="label label-success">Approved</span>
                                        @elseif($requested && !$approved)
                                            <span class="label label-warning">Pending</span>
                                        @else
                                            <span class="label label-default">Not Requested</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($requested && !$approved)
                                            <a href="{{ admin_url('employees/' . $user->id . '/approve-service/' . $key) }}" 
                                               class="btn btn-success btn-xs">
                                                <i class="fa fa-check"></i> Approve
                                            </a>
                                        @elseif($requested && $approved)
                                            <span class="text-success">✓ Approved</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showServiceModal() {
    $('#serviceModal').modal('show');
}
</script>