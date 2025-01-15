document.addEventListener('DOMContentLoaded', function() {
    const chartCanvas = document.getElementById('transactions-chart');
    if (!chartCanvas) return;

    const ctx = chartCanvas.getContext('2d');
    const chartData = JSON.parse(chartCanvas.dataset.transactions);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(chartData),
            datasets: [{
                label: 'Montant des transactions (€)',
                data: Object.values(chartData),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + ' €';
                        }
                    }
                }
            }
        }
    });
}); 