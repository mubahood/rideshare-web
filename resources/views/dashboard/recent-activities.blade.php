{{-- Recent Activities Component --}}
<div class="box" style="border-radius: 0; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 20px;">
    <div class="box-header with-border" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white; border-radius: 0;">
        <h3 class="box-title" style="color: white; font-weight: 600;">
            <i class="fas fa-clock"></i> Recent Activities
        </h3>
    </div>
    <div class="box-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
        
        @if($activities && count($activities) > 0)
            @foreach($activities as $activity)
            <div class="activity-item" style="padding: 15px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center;">
                <div class="activity-icon" style="margin-right: 15px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 50%;">
                    <i class="{{ $activity['icon'] }}"></i>
                </div>
                <div class="activity-content" style="flex: 1;">
                    <div class="activity-message" style="font-weight: 500; color: #333; margin-bottom: 3px;">
                        {{ $activity['message'] }}
                    </div>
                    <div class="activity-time" style="font-size: 0.85rem; color: #666;">
                        {{ $activity['time'] }}
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div style="padding: 30px; text-align: center; color: #666;">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>No recent activities</p>
            </div>
        @endif
        
    </div>
</div>

<style>
.activity-item:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}
.activity-item:last-child {
    border-bottom: none;
}
</style>