{{-- Live Statistics Component --}}
<div class="box" style="border-radius: 0; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <div class="box-header with-border" style="background: linear-gradient(135deg, #040404 0%, #FF9900 100%); color: white; border-radius: 0;">
        <h3 class="box-title" style="color: white; font-weight: 600;">
            <i class="fas fa-tachometer-alt"></i> Live Statistics
        </h3>
    </div>
    <div class="box-body" style="padding: 20px;">
        
        {{-- Drivers Online --}}
        <div class="info-box" style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); color: white; margin-bottom: 15px; border-radius: 0; border: none;">
            <span class="info-box-icon" style="background: rgba(255,255,255,0.2); color: white;">
                <i class="fas fa-car"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text" style="color: white;">Drivers Online</span>
                <span class="info-box-number" style="color: white; font-size: 1.8rem; font-weight: 700;">{{ number_format($drivers_online) }}</span>
            </div>
        </div>

        {{-- Customers Active --}}
        <div class="info-box" style="background: linear-gradient(135deg, #ECC60F 0%, #d4b00e 100%); color: white; margin-bottom: 15px; border-radius: 0; border: none;">
            <span class="info-box-icon" style="background: rgba(255,255,255,0.2); color: white;">
                <i class="fas fa-users"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text" style="color: white;">Active Customers</span>
                <span class="info-box-number" style="color: white; font-size: 1.8rem; font-weight: 700;">{{ number_format($customers_active) }}</span>
            </div>
        </div>

        {{-- Average Trip Price --}}
        <div class="info-box" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; margin-bottom: 15px; border-radius: 0; border: none;">
            <span class="info-box-icon" style="background: rgba(255,255,255,0.2); color: white;">
                <i class="fas fa-money-bill-wave"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text" style="color: white;">Avg Trip Price</span>
                <span class="info-box-number" style="color: white; font-size: 1.8rem; font-weight: 700;">UGX {{ number_format($avg_trip_price) }}</span>
            </div>
        </div>

        {{-- Completion Rate --}}
        <div class="info-box" style="background: linear-gradient(135deg, #FF5722 0%, #E64A19 100%); color: white; margin-bottom: 0; border-radius: 0; border: none;">
            <span class="info-box-icon" style="background: rgba(255,255,255,0.2); color: white;">
                <i class="fas fa-check-circle"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text" style="color: white;">Completion Rate</span>
                <span class="info-box-number" style="color: white; font-size: 1.8rem; font-weight: 700;">{{ number_format($completion_rate, 1) }}%</span>
            </div>
        </div>

    </div>
</div>

<style>
.info-box {
    display: flex;
    align-items: center;
    padding: 15px;
    transition: transform 0.2s ease;
}
.info-box:hover {
    transform: translateX(5px);
}
.info-box-icon {
    border-radius: 0;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.info-box-content {
    margin-left: 15px;
}
.info-box-text {
    font-size: 0.9rem;
    font-weight: 500;
}
</style>