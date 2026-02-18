(function () {

    function initCharts() {

        const wrapper = document.getElementById('jobman-dashboard');
        if (!wrapper) return;

        const months = JSON.parse(wrapper.dataset.months || '[]');
        const counts = JSON.parse(wrapper.dataset.counts || '[]');
        const statusLabels = JSON.parse(wrapper.dataset.statuslabels || '[]');
        const statusCounts = JSON.parse(wrapper.dataset.statuscounts || '[]'); 

        const applicationsCanvas = document.getElementById('applicationsChart');
        const statusCanvas = document.getElementById('statusChart');

        if (!applicationsCanvas || !statusCanvas) return;

        new Chart(applicationsCanvas, {
            type: 'line',
            data: { 
                labels: months,
                datasets: [{
                    label: 'Bewerbungen',
                    data: counts,
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ["Neu", "In Prüfung", "Interview", "Abgelehnt", "Angenommen"],
                datasets: [{
                    label: "Population (millions)",
                    data: statusCounts
                }]
            },
            options: {
                responsive: true,
            },
            plugins: {
                legend: { display: true },
                tooltip: { enabled: true }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initCharts);

})();
