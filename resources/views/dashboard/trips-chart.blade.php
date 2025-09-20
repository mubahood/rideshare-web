{{-- Trips Chart Component --}}
<div class="box" style="border-radius: 0; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <div class="box-header with-border" style="background: linear-gradient(135deg, #040404 0%, #FF9900 100%); color: white; border-radius: 0;">
        <h3 class="box-title" style="color: white; font-weight: 600;">
            <i class="fas fa-chart-line"></i> Trip Trends (Last 7 Days)
        </h3>
    </div>
    <div class="box-body">
        <div style="height: 300px;">
            <canvas id="tripsChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('tripsChart').getContext('2d');
const tripsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($trips_data, 'date')) !!},
        datasets: [{
            label: 'New Trips',
            data: {!! json_encode(array_column($trips_data, 'trips')) !!},
            borderColor: '#FF9900',
            backgroundColor: 'rgba(255, 153, 0, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3
        }, {
            label: 'Bookings',
            data: {!! json_encode(array_column($trips_data, 'bookings')) !!},
            borderColor: '#ECC60F',
            backgroundColor: 'rgba(236, 198, 15, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 20
                }
            }
        }
    }
});
</script>