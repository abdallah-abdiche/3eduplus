
const monthlySalesCtx = document.getElementById('monthlySalesChart');
if (monthlySalesCtx) {
    new Chart(monthlySalesCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Sales',
                data: [65, 78, 90, 81, 95, 105, 120, 115, 130, 125, 140, 150],
                backgroundColor: '#3b82f6',
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9'
                    },
                    ticks: {
                        color: '#64748b'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b'
                    }
                }
            }
        }
    });
}

const statisticsCtx = document.getElementById('statisticsChart');
let statisticsChart = null;

if (statisticsCtx) {
    const monthlyData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
            {
                label: 'Dataset 1',
                data: [100, 120, 115, 134, 168, 132],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Dataset 2',
                data: [80, 100, 95, 110, 140, 115],
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    };

    const quarterlyData = {
        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
        datasets: [
            {
                label: 'Dataset 1',
                data: [335, 417, 450, 432],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Dataset 2',
                data: [275, 345, 365, 355],
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    };

    const annuallyData = {
        labels: ['2020', '2021', '2022', '2023', '2024'],
        datasets: [
            {
                label: 'Dataset 1',
                data: [1200, 1500, 1800, 2100, 2400],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Dataset 2',
                data: [1000, 1300, 1600, 1900, 2200],
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    };

    statisticsChart = new Chart(statisticsCtx, {
        type: 'line',
        data: monthlyData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9'
                    },
                    ticks: {
                        color: '#64748b'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b'
                    }
                }
            }
        }
    });

    const chartToggles = document.querySelectorAll('.chart-toggle');
    chartToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {

            chartToggles.forEach(t => t.classList.remove('active'));
          
            this.classList.add('active');


            const period = this.getAttribute('data-period');
            let newData;
            
            if (period === 'monthly') {
                newData = monthlyData;
            } else if (period === 'quarterly') {
                newData = quarterlyData;
            } else {
                newData = annuallyData;
            }

            statisticsChart.data = newData;
            statisticsChart.update();
        });
    });
}

document.querySelectorAll('.nav-item').forEach(item => {
    const link = item.querySelector('.nav-link');
    if (link) {
        link.addEventListener('click', function(e) {
            const hasSubmenu = item.querySelector('.nav-submenu');


            if (hasSubmenu) {
                e.preventDefault();
                item.classList.toggle('active');
                const arrow = this.querySelector('.nav-arrow');
                if (arrow) {
                    if (item.classList.contains('active')) {
                        arrow.classList.remove('fa-chevron-down');
                        arrow.classList.add('fa-chevron-up');
                    } else {
                        arrow.classList.remove('fa-chevron-up');
                        arrow.classList.add('fa-chevron-down');
                    }
                }
            }
        });
    }
});

const darkModeToggle = document.querySelector('.dark-mode-toggle');
if (darkModeToggle) {
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const icon = this.querySelector('i');
        if (document.body.classList.contains('dark-mode')) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    });
}

const sidebar = document.querySelector('.sidebar');
if (window.innerWidth <= 768) {
}
