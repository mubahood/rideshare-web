{{-- Revenue Chart Component --}}
<div class="box" style="border-radius: 0; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 20px;">
    <div class="box-header with-border" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; border-radius: 0;">
        <h3 class="box-title" style="color: white; font-weight: 600;">
            <i class="fas fa-dollar-sign"></i> Revenue Analytics (Last 30 Days)
        </h3>
    </div>
    <div class="box-body">
        <div style="height: 300px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>

<script>
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($revenue_data, 'date')) !!},
        datasets: [{
            label: 'Daily Revenue (UGX)',
            data: {!! json_encode(array_column($revenue_data, 'revenue')) !!},
            backgroundColor: 'rgba(76, 175, 80, 0.8)',
            borderColor: '#4CAF50',
            borderWidth: 1,
            borderRadius: 4
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
                },
                ticks: {
                    callback: function(value) {
                        return 'UGX ' + value.toLocaleString();
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Revenue: UGX ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>