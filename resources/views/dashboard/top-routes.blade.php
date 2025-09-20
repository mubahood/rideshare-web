{{-- Top Routes Component --}}
<div class="box" style="border-radius: 0; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <div class="box-header with-border" style="background: linear-gradient(135deg, #FF5722 0%, #E64A19 100%); color: white; border-radius: 0;">
        <h3 class="box-title" style="color: white; font-weight: 600;">
            <i class="fas fa-route"></i> Top Routes
        </h3>
    </div>
    <div class="box-body" style="padding: 0;">
        
        @if($routes && count($routes) > 0)
            <div class="table-responsive">
                <table class="table table-striped" style="margin: 0;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 12px; font-weight: 600;">#</th>
                            <th style="padding: 12px; font-weight: 600;">Route</th>
                            <th style="padding: 12px; font-weight: 600; text-align: center;">Trips</th>
                            <th style="padding: 12px; font-weight: 600; text-align: right;">Avg Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routes as $index => $route)
                        <tr>
                            <td style="padding: 12px; font-weight: 500;">{{ $index + 1 }}</td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 500; color: #333;">{{ $route->start_name }}</div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <i class="fas fa-arrow-down" style="color: #FF9900;"></i> {{ $route->end_name }}
                                </div>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <span class="badge" style="background: #ECC60F; color: white; padding: 5px 10px; border-radius: 20px;">
                                    {{ $route->trip_count }}
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 500;">
                                UGX {{ number_format($route->avg_price) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="padding: 30px; text-align: center; color: #666;">
                <i class="fas fa-map-marker-alt" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>No route data available</p>
            </div>
        @endif
        
    </div>
</div>

<style>
.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,0.02);
}
.table tbody tr:hover {
    background-color: rgba(255, 153, 0, 0.1);
}
</style>