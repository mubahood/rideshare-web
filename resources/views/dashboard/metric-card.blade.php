{{-- Metric Card Component --}}
<div class="small-box" style="background: {{ $bg }}; color: white; border-radius: 0;">
    <div class="inner">
        <h3>{{ $value }}</h3>
        <p style="font-weight: 500;">{{ $title }}</p>
    </div>
    <div class="icon">
        <i class="{{ $icon }}" style="opacity: 0.3;"></i>
    </div>
    @if($change != 0)
    <div class="small-box-footer" style="background: rgba(255,255,255,0.1); color: white;">
        @if($change > 0)
            <i class="fas fa-arrow-up"></i> +{{ number_format($change, 1) }}% from yesterday
        @else
            <i class="fas fa-arrow-down"></i> {{ number_format($change, 1) }}% from yesterday
        @endif
    </div>
    @endif
</div>

<style>
.small-box {
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: transform 0.2s ease;
}
.small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}
.small-box .inner h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0;
}
.small-box .icon {
    top: 10px;
    right: 15px;
    font-size: 3rem;
}
</style>