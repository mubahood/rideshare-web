{{-- Driver Performance Component --}}
<div class="box" style="border-radius: 0; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <div class="box-header with-border" style="background: linear-gradient(135deg, #3F51B5 0%, #303F9F 100%); color: white; border-radius: 0;">
        <h3 class="box-title" style="color: white; font-weight: 600;">
            <i class="fas fa-trophy"></i> Top Performing Drivers
        </h3>
    </div>
    <div class="box-body" style="padding: 0;">
        
        @if($drivers && count($drivers) > 0)
            <div class="table-responsive">
                <table class="table table-striped" style="margin: 0;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 12px; font-weight: 600;">#</th>
                            <th style="padding: 12px; font-weight: 600;">Driver</th>
                            <th style="padding: 12px; font-weight: 600; text-align: center;">Trips</th>
                            <th style="padding: 12px; font-weight: 600; text-align: center;">Completed</th>
                            <th style="padding: 12px; font-weight: 600; text-align: right;">Avg Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($drivers as $index => $driver)
                        <tr>
                            <td style="padding: 12px;">
                                @if($index < 3)
                                    <span style="font-size: 1.2rem;">
                                        @if($index == 0) 🥇
                                        @elseif($index == 1) 🥈
                                        @else 🥉
                                        @endif
                                    </span>
                                @else
                                    <span style="font-weight: 500;">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 500; color: #333;">{{ $driver->name }}</div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <i class="fas fa-phone" style="color: #FF9900;"></i> {{ $driver->phone_number }}
                                </div>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <span class="badge" style="background: #2196F3; color: white; padding: 5px 10px; border-radius: 20px;">
                                    {{ $driver->total_trips }}
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <span class="badge" style="background: #4CAF50; color: white; padding: 5px 10px; border-radius: 20px;">
                                    {{ $driver->completed_trips }}
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 500;">
                                UGX {{ number_format($driver->avg_price) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="padding: 30px; text-align: center; color: #666;">
                <i class="fas fa-user-tie" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>No driver performance data available</p>
            </div>
        @endif
        
    </div>
</div>

<style>
.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,0.02);
}
.table tbody tr:hover {
    background-color: rgba(63, 81, 181, 0.1);
}
</style>