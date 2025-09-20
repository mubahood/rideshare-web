<div class="row">
    <div class="col-md-12">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">⚡ Bulk Operations Dashboard</h3>
                <div class="box-tools pull-right">
                    <a href="{{ admin_url('employees') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
            <div class="box-body">
                
                <!-- Operation Selection -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <strong>Bulk Operations</strong> - Select users and perform actions on multiple records simultaneously.
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">🔍 Filter & Select Users</h3>
                    </div>
                    <div class="box-body">
                        <form id="bulk-filter-form">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>User Type</label>
                                        <select class="form-control" name="user_type" id="filter-user-type">
                                            <option value="">All Types</option>
                                            <option value="Admin">Admin</option>
                                            <option value="Driver">Driver</option>
                                            <option value="Pending Driver">Pending Driver</option>
                                            <option value="Customer">Customer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control" name="status" id="filter-status">
                                            <option value="">All Statuses</option>
                                            <option value="1">Active</option>
                                            <option value="2">Pending</option>
                                            <option value="0">Blocked</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Service Type</label>
                                        <select class="form-control" name="service" id="filter-service">
                                            <option value="">All Services</option>
                                            <option value="car">🚗 Car</option>
                                            <option value="boda">🏍️ Boda</option>
                                            <option value="ambulance">🚑 Ambulance</option>
                                            <option value="police">🚔 Police</option>
                                            <option value="delivery">📦 Delivery</option>
                                            <option value="breakdown">🔧 Breakdown</option>
                                            <option value="firebrugade">🚒 Fire Brigade</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Registration Date</label>
                                        <select class="form-control" name="date_range" id="filter-date">
                                            <option value="">All Time</option>
                                            <option value="today">Today</option>
                                            <option value="week">This Week</option>
                                            <option value="month">This Month</option>
                                            <option value="year">This Year</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-primary" onclick="loadFilteredUsers()">
                                        <i class="fa fa-search"></i> Load Users
                                    </button>
                                    <button type="button" class="btn btn-default" onclick="clearFilters()">
                                        <i class="fa fa-refresh"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users Selection Table -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">👥 Select Users for Bulk Operations</h3>
                        <div class="box-tools pull-right">
                            <span id="selected-count" class="badge bg-green">0 selected</span>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="bulk-users-table">
                                <thead>
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="select-all" onclick="toggleAllSelection()">
                                        </th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Services</th>
                                        <th>Registration</th>
                                    </tr>
                                </thead>
                                <tbody id="users-tbody">
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <p class="text-muted">Use filters above to load users for bulk operations.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions Panel -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">⚡ Available Bulk Actions</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>User Management Actions</h4>
                                <div class="btn-group-vertical btn-block">
                                    <button type="button" class="btn btn-success" onclick="performBulkAction('approve')" id="bulk-approve-btn" disabled>
                                        <i class="fa fa-check"></i> Approve Selected Users
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="performBulkAction('activate')" id="bulk-activate-btn" disabled>
                                        <i class="fa fa-play"></i> Activate Selected Users
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="performBulkAction('block')" id="bulk-block-btn" disabled>
                                        <i class="fa fa-ban"></i> Block Selected Users
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="performBulkAction('reset-password')" id="bulk-reset-btn" disabled>
                                        <i class="fa fa-key"></i> Reset Passwords
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4>Service Management Actions</h4>
                                <div class="form-group">
                                    <label>Select Service to Approve</label>
                                    <select class="form-control" id="bulk-service-select">
                                        <option value="">Select Service</option>
                                        <option value="car">🚗 Car Service</option>
                                        <option value="boda">🏍️ Boda Service</option>
                                        <option value="ambulance">🚑 Ambulance Service</option>
                                        <option value="police">🚔 Police Service</option>
                                        <option value="delivery">📦 Delivery Service</option>
                                        <option value="breakdown">🔧 Breakdown Service</option>
                                        <option value="firebrugade">🚒 Fire Brigade Service</option>
                                    </select>
                                </div>
                                <div class="btn-group-vertical btn-block">
                                    <button type="button" class="btn btn-primary" onclick="performBulkServiceAction('approve')" id="bulk-service-approve-btn" disabled>
                                        <i class="fa fa-check-circle"></i> Approve Selected Service
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="performBulkServiceAction('reject')" id="bulk-service-reject-btn" disabled>
                                        <i class="fa fa-times-circle"></i> Reject Selected Service
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h4>Communication Actions</h4>
                                <div class="form-group">
                                    <label>Notification Message</label>
                                    <textarea class="form-control" id="bulk-message" rows="3" placeholder="Enter message to send to selected users..."></textarea>
                                </div>
                                <button type="button" class="btn btn-info" onclick="sendBulkNotification()" id="bulk-notify-btn" disabled>
                                    <i class="fa fa-envelope"></i> Send Notification to Selected Users
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operation Progress -->
                <div class="box box-info" id="operation-progress" style="display: none;">
                    <div class="box-header with-border">
                        <h3 class="box-title">📊 Operation Progress</h3>
                    </div>
                    <div class="box-body">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped active" id="progress-bar" style="width: 0%"></div>
                        </div>
                        <div id="operation-status"></div>
                        <div id="operation-results"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
let selectedUsers = [];
let allUsers = [];

function loadFilteredUsers() {
    const formData = $('#bulk-filter-form').serialize();
    
    // Show loading
    $('#users-tbody').html('<tr><td colspan="8" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading users...</td></tr>');
    
    // Simulate AJAX call (in real implementation, you'd make an actual AJAX request)
    setTimeout(function() {
        // Mock data for demonstration
        const mockUsers = [
            {id: 1, name: 'John Doe', email: 'john@example.com', user_type: 'Driver', status: '1', services: 'car, boda', created_at: '2024-01-15'},
            {id: 2, name: 'Jane Smith', email: 'jane@example.com', user_type: 'Customer', status: '1', services: '', created_at: '2024-01-16'},
            {id: 3, name: 'Bob Johnson', email: 'bob@example.com', user_type: 'Pending Driver', status: '2', services: 'ambulance', created_at: '2024-01-17'},
            {id: 4, name: 'Alice Brown', email: 'alice@example.com', user_type: 'Driver', status: '0', services: 'delivery', created_at: '2024-01-18'},
            {id: 5, name: 'Charlie Wilson', email: 'charlie@example.com', user_type: 'Driver', status: '1', services: 'police', created_at: '2024-01-19'}
        ];
        
        allUsers = mockUsers;
        displayUsers(mockUsers);
    }, 1000);
}

function displayUsers(users) {
    let html = '';
    
    if (users.length === 0) {
        html = '<tr><td colspan="8" class="text-center">No users found matching the criteria.</td></tr>';
    } else {
        users.forEach(user => {
            const statusBadge = user.status === '1' ? '<span class="badge bg-green">Active</span>' : 
                               user.status === '2' ? '<span class="badge bg-yellow">Pending</span>' : 
                               '<span class="badge bg-red">Blocked</span>';
            
            html += `
                <tr>
                    <td><input type="checkbox" class="user-checkbox" value="${user.id}" onclick="updateSelection()"></td>
                    <td>${user.id}</td>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.user_type}</td>
                    <td>${statusBadge}</td>
                    <td>${user.services || 'None'}</td>
                    <td>${user.created_at}</td>
                </tr>
            `;
        });
    }
    
    $('#users-tbody').html(html);
}

function toggleAllSelection() {
    const selectAll = $('#select-all').is(':checked');
    $('.user-checkbox').prop('checked', selectAll);
    updateSelection();
}

function updateSelection() {
    selectedUsers = [];
    $('.user-checkbox:checked').each(function() {
        selectedUsers.push(parseInt($(this).val()));
    });
    
    $('#selected-count').text(selectedUsers.length + ' selected');
    
    // Enable/disable action buttons
    const hasSelection = selectedUsers.length > 0;
    $('#bulk-approve-btn, #bulk-activate-btn, #bulk-block-btn, #bulk-reset-btn').prop('disabled', !hasSelection);
    $('#bulk-service-approve-btn, #bulk-service-reject-btn, #bulk-notify-btn').prop('disabled', !hasSelection);
}

function clearFilters() {
    $('#bulk-filter-form')[0].reset();
    $('#users-tbody').html('<tr><td colspan="8" class="text-center"><p class="text-muted">Use filters above to load users for bulk operations.</p></td></tr>');
    selectedUsers = [];
    updateSelection();
    $('#select-all').prop('checked', false);
}

function performBulkAction(action) {
    if (selectedUsers.length === 0) {
        alert('Please select users first.');
        return;
    }
    
    const actionNames = {
        'approve': 'approve',
        'activate': 'activate', 
        'block': 'block',
        'reset-password': 'reset passwords for'
    };
    
    if (confirm(`Are you sure you want to ${actionNames[action]} ${selectedUsers.length} selected users?`)) {
        showProgress();
        simulateOperation(action, selectedUsers);
    }
}

function performBulkServiceAction(action) {
    const service = $('#bulk-service-select').val();
    if (!service) {
        alert('Please select a service first.');
        return;
    }
    
    if (selectedUsers.length === 0) {
        alert('Please select users first.');
        return;
    }
    
    if (confirm(`Are you sure you want to ${action} ${service} service for ${selectedUsers.length} selected users?`)) {
        showProgress();
        simulateServiceOperation(action, service, selectedUsers);
    }
}

function sendBulkNotification() {
    const message = $('#bulk-message').val().trim();
    if (!message) {
        alert('Please enter a notification message.');
        return;
    }
    
    if (selectedUsers.length === 0) {
        alert('Please select users first.');
        return;
    }
    
    if (confirm(`Are you sure you want to send this notification to ${selectedUsers.length} selected users?`)) {
        showProgress();
        simulateNotificationOperation(message, selectedUsers);
    }
}

function showProgress() {
    $('#operation-progress').show();
    $('#progress-bar').css('width', '0%');
    $('#operation-status').text('Starting operation...');
    $('#operation-results').html('');
}

function simulateOperation(action, userIds) {
    let progress = 0;
    const total = userIds.length;
    const processed = [];
    
    const interval = setInterval(function() {
        progress += 20;
        $('#progress-bar').css('width', progress + '%');
        
        if (progress <= 100) {
            const currentBatch = Math.floor((progress / 100) * total);
            $('#operation-status').text(`Processing... ${currentBatch}/${total} users`);
        }
        
        if (progress >= 100) {
            clearInterval(interval);
            $('#operation-status').text(`Operation completed successfully!`);
            $('#operation-results').html(`
                <div class="alert alert-success">
                    <i class="fa fa-check"></i> Successfully ${action}ed ${total} users.
                </div>
            `);
            
            // Refresh the user list
            setTimeout(function() {
                loadFilteredUsers();
                selectedUsers = [];
                updateSelection();
                $('#select-all').prop('checked', false);
            }, 2000);
        }
    }, 500);
}

function simulateServiceOperation(action, service, userIds) {
    let progress = 0;
    const total = userIds.length;
    
    const interval = setInterval(function() {
        progress += 25;
        $('#progress-bar').css('width', progress + '%');
        
        if (progress <= 100) {
            const currentBatch = Math.floor((progress / 100) * total);
            $('#operation-status').text(`${action}ing ${service} service... ${currentBatch}/${total} users`);
        }
        
        if (progress >= 100) {
            clearInterval(interval);
            $('#operation-status').text(`Service operation completed!`);
            $('#operation-results').html(`
                <div class="alert alert-success">
                    <i class="fa fa-check"></i> Successfully ${action}ed ${service} service for ${total} users.
                </div>
            `);
        }
    }, 600);
}

function simulateNotificationOperation(message, userIds) {
    let progress = 0;
    const total = userIds.length;
    
    const interval = setInterval(function() {
        progress += 20;
        $('#progress-bar').css('width', progress + '%');
        
        if (progress <= 100) {
            const currentBatch = Math.floor((progress / 100) * total);
            $('#operation-status').text(`Sending notifications... ${currentBatch}/${total} users`);
        }
        
        if (progress >= 100) {
            clearInterval(interval);
            $('#operation-status').text(`Notifications sent successfully!`);
            $('#operation-results').html(`
                <div class="alert alert-success">
                    <i class="fa fa-envelope"></i> Successfully sent notifications to ${total} users.
                    <br><strong>Message:</strong> "${message}"
                </div>
            `);
            $('#bulk-message').val('');
        }
    }, 400);
}

// Initialize
$(document).ready(function() {
    updateSelection();
});
</script>