<?php require_once 'auth.php'; require_once '../includes/conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charts — Alab E-BulSU Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <style>
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        .chart-card {
            padding: 16px;
            border-radius: 8px;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            animation: slideInUp 0.5s ease-out forwards;
            opacity: 0;
        }
        .chart-card:nth-child(1) { animation-delay: 0.1s; }
        .chart-card:nth-child(2) { animation-delay: 0.2s; }
        .chart-card:nth-child(3) { animation-delay: 0.3s; }
        .chart-card:nth-child(4) { animation-delay: 0.4s; }
        .chart-card:nth-child(5) { animation-delay: 0.5s; }
        .chart-card:hover {
            background: rgba(255,255,255,0.04);
            border-color: rgba(255,255,255,0.12);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 8px;
            animation: fadeIn 0.6s ease-out 0.3s forwards;
            opacity: 0;
        }
        .chart-header h3 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            flex: 1;
            min-width: 180px;
        }
        .chart-filters {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            animation: fadeIn 0.6s ease-out 0.4s forwards;
            opacity: 0;
        }
        .chart-filters select {
            background: rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.8);
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }
        .chart-filters select:hover,
        .chart-filters select:focus {
            border-color: rgba(162,155,254,0.5);
            background: rgba(0,0,0,0.4);
            outline: none;
            box-shadow: 0 0 8px rgba(162,155,254,0.15);
            transform: translateY(-2px);
        }
        .chart-filters select option {
            background: #1a1a2e;
            color: rgba(255,255,255,0.9);
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }
        canvas {
            max-height: 200px !important;
            height: 200px !important;
            animation: scaleIn 0.5s ease-out 0.5s forwards;
            opacity: 0;
        }
        .card h3 {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 15px 0;
        }
    </style>
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>


<?php require_once 'nav.php'; ?>
<div class="main-content">

    <div class="page-header"><h2> Charts &amp; Analytics</h2></div>

    <?php
    $programs = $conn->query("SELECT program_code, program_name FROM program ORDER BY program_name");
    $colleges = $conn->query("SELECT college_code, college_name FROM college ORDER BY college_name");
    ?>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h3> Gender Distribution</h3>
                <div class="chart-filters">
                    <select class="chart-college-filter" data-chart="chartGender">
                        <option value="all">All Colleges</option>
                        <?php 
                        $colleges_result = $conn->query("SELECT college_code, college_name FROM college ORDER BY college_name");
                        while ($col = $colleges_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $col['college_code']; ?>"><?php echo htmlspecialchars($col['college_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select class="chart-program-filter" data-chart="chartGender">
                        <option value="all">All Programs</option>
                        <?php 
                        $programs_result = $conn->query("SELECT program_code, program_name FROM program ORDER BY program_name");
                        while ($prog = $programs_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $prog['program_code']; ?>"><?php echo htmlspecialchars($prog['program_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <canvas id="chartGender" height="200"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3> Semestral Enrollment Trend</h3>
                <div class="chart-filters">
                    <select class="chart-college-filter" data-chart="chartTrend">
                        <option value="all">All Colleges</option>
                        <?php 
                        $colleges_result = $conn->query("SELECT college_code, college_name FROM college ORDER BY college_name");
                        while ($col = $colleges_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $col['college_code']; ?>"><?php echo htmlspecialchars($col['college_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select class="chart-program-filter" data-chart="chartTrend">
                        <option value="all">All Programs</option>
                        <?php 
                        $programs_result = $conn->query("SELECT program_code, program_name FROM program ORDER BY program_name");
                        while ($prog = $programs_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $prog['program_code']; ?>"><?php echo htmlspecialchars($prog['program_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <canvas id="chartTrend" height="200"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3> Enrollment Status Distribution</h3>
                <div class="chart-filters">
                    <select class="chart-college-filter" data-chart="chartStatus">
                        <option value="all">All Colleges</option>
                        <?php 
                        $colleges_result = $conn->query("SELECT college_code, college_name FROM college ORDER BY college_name");
                        while ($col = $colleges_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $col['college_code']; ?>"><?php echo htmlspecialchars($col['college_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select class="chart-program-filter" data-chart="chartStatus">
                        <option value="all">All Programs</option>
                        <?php 
                        $programs_result = $conn->query("SELECT program_code, program_name FROM program ORDER BY program_name");
                        while ($prog = $programs_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $prog['program_code']; ?>"><?php echo htmlspecialchars($prog['program_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <canvas id="chartStatus" height="200"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3> Enrollments by Program</h3>
                <div class="chart-filters">
                    <select class="chart-college-filter" data-chart="chartProgram">
                        <option value="all">All Colleges</option>
                        <?php 
                        $colleges_result = $conn->query("SELECT college_code, college_name FROM college ORDER BY college_name");
                        while ($col = $colleges_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $col['college_code']; ?>"><?php echo htmlspecialchars($col['college_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <canvas id="chartProgram" height="200"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3> Enrollments by Year Level</h3>
                <div class="chart-filters">
                    <select class="chart-college-filter" data-chart="chartYear">
                        <option value="all">All Colleges</option>
                        <?php 
                        $colleges_result = $conn->query("SELECT college_code, college_name FROM college ORDER BY college_name");
                        while ($col = $colleges_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $col['college_code']; ?>"><?php echo htmlspecialchars($col['college_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select class="chart-program-filter" data-chart="chartYear">
                        <option value="all">All Programs</option>
                        <?php 
                        $programs_result = $conn->query("SELECT program_code, program_name FROM program ORDER BY program_name");
                        while ($prog = $programs_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $prog['program_code']; ?>"><?php echo htmlspecialchars($prog['program_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <canvas id="chartYear" height="200"></canvas>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="card">
        <h3> Summary by Program and Status</h3>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Program</th><th>College</th><th>Total</th><th>Pending</th><th>Approved</th><th>Rejected</th></tr>
                </thead>
                <tbody id="summaryTable"><tr><td colspan="6" class="loading">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<?php require_once __DIR__ . "/../includes/csrf_setup.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var charts = {};

function loadChart(chartId, params) {
    var filterCollege = $('[data-chart="' + chartId + '"].chart-college-filter').val() || 'all';
    var filterProgram = $('[data-chart="' + chartId + '"].chart-program-filter').val() || 'all';
    var canvas = document.getElementById(chartId);
    
    if (!canvas) return;
    
    // Fade out effect
    $(canvas).animate({ opacity: 0.3 }, 150);
    
    var data = $.extend({}, params, {
        college: filterCollege,
        program: filterProgram
    });
    
    $.get('../ajax/chart_data.php', data, function (d) {
        if (!d || !d.labels) {
            console.log('No data received', d);
            return;
        }
        
        if (charts[chartId]) {
            charts[chartId].destroy();
        }
        
        var commonOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: 'bottom', 
                    labels: { 
                        color: 'rgba(255,255,255,0.6)',
                        font: { size: 11, weight: '500' },
                        padding: 10,
                        usePointStyle: true
                    }
                },
                title: { display: false },
                filler: { propagate: true }
            }
        };
        
        switch(chartId) {
            case 'chartGender':
                charts[chartId] = new Chart(canvas, {
                    type: 'pie',
                    data: {
                        labels: d.labels,
                        datasets: [{ data: d.values, backgroundColor: ['rgba(116,185,255,0.75)', 'rgba(250,177,160,0.75)', 'rgba(162,155,254,0.75)'], borderColor: 'rgba(255,255,255,0.1)', borderWidth: 1 }]
                    },
                    options: $.extend({}, commonOptions, { plugins: $.extend({}, commonOptions.plugins, { legend: { labels: { boxWidth: 12 } } }) })
                });
                break;
                
            case 'chartStatus':
                charts[chartId] = new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: d.labels,
                        datasets: [{ data: d.values, backgroundColor: ['rgba(253,203,110,0.8)', 'rgba(85,239,196,0.8)', 'rgba(255,118,117,0.8)'], borderColor: 'rgba(255,255,255,0.1)', borderWidth: 1 }]
                    },
                    options: $.extend({}, commonOptions, { plugins: $.extend({}, commonOptions.plugins, { legend: { labels: { boxWidth: 12 } } }) })
                });
                break;
                
            case 'chartProgram':
                charts[chartId] = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: d.labels,
                        datasets: [{ label: 'Students', data: d.values, backgroundColor: 'rgba(162,155,254,0.75)', borderRadius: 4, borderSkipped: false }]
                    },
                    options: $.extend({}, commonOptions, {
                        plugins: $.extend({}, commonOptions.plugins, { legend: { display: false } }),
                        scales: {
                            y: { beginAtZero: true, ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 }, stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.03)', drawBorder: false } },
                            x: { ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } }, grid: { display: false, drawBorder: false } }
                        }
                    })
                });
                break;
                
            case 'chartYear':
                charts[chartId] = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: d.labels,
                        datasets: [{ label: 'Students', data: d.values, backgroundColor: 'rgba(116,185,255,0.75)', borderRadius: 4, borderSkipped: false }]
                    },
                    options: $.extend({}, commonOptions, {
                        plugins: $.extend({}, commonOptions.plugins, { legend: { display: false } }),
                        scales: {
                            y: { beginAtZero: true, ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 }, stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.03)', drawBorder: false } },
                            x: { ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } }, grid: { display: false, drawBorder: false } }
                        }
                    })
                });
                break;
                
            case 'chartTrend':
                charts[chartId] = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: d.labels,
                        datasets: [{ label: 'Enrollments', data: d.values, borderColor: 'rgba(116,185,255,0.8)', backgroundColor: 'rgba(116,185,255,0.05)', tension: 0.3, fill: true, pointBackgroundColor: 'rgba(116,185,255,0.8)', pointBorderColor: '#fff', pointRadius: 3, pointHoverRadius: 5 }]
                    },
                    options: $.extend({}, commonOptions, {
                        scales: {
                            y: { beginAtZero: true, ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.03)', drawBorder: false } },
                            x: { ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } }, grid: { display: false, drawBorder: false } }
                        }
                    })
                });
                break;
        }
        
        // Fade in effect
        $(canvas).animate({ opacity: 1 }, 200);
    }, 'json').fail(function(error) {
        console.error('Chart load failed for ' + chartId, error);
        $(canvas).animate({ opacity: 1 }, 200);
    });
}

$(document).ready(function () {
    // Load all charts initially
    loadChart('chartGender', { type: 'by_gender' });
    loadChart('chartTrend', { type: 'by_trend' });
    loadChart('chartStatus', { type: 'by_status' });
    loadChart('chartProgram', { type: 'by_program' });
    loadChart('chartYear', { type: 'by_year' });
    
    // Handle filter changes
    $(document).on('change', '.chart-college-filter, .chart-program-filter', function() {
        var chartId = $(this).data('chart');
        
        if (chartId === 'chartGender') {
            loadChart('chartGender', { type: 'by_gender' });
        } else if (chartId === 'chartTrend') {
            loadChart('chartTrend', { type: 'by_trend' });
        } else if (chartId === 'chartStatus') {
            loadChart('chartStatus', { type: 'by_status' });
        } else if (chartId === 'chartProgram') {
            loadChart('chartProgram', { type: 'by_program' });
        } else if (chartId === 'chartYear') {
            loadChart('chartYear', { type: 'by_year' });
        }
    });
    
    // Initial summary table load
    $.get('../ajax/chart_data.php', { type: 'summary' }, function (d) {
        if (!d || !d.length) { $('#summaryTable').html('<tr><td colspan="6" class="empty">No data.</td></tr>'); return; }
        var rows = '';
        $.each(d, function (i, r) {
            rows += '<tr>' +
                '<td>' + r.program_name + ' (' + r.program_code + ')</td>' +
                '<td>' + r.college_name + '</td>' +
                '<td><strong>' + r.total + '</strong></td>' +
                '<td><span class="badge badge-pending">'  + r.pending  + '</span></td>' +
                '<td><span class="badge badge-approved">' + r.approved + '</span></td>' +
                '<td><span class="badge badge-rejected">' + r.rejected + '</span></td>' +
            '</tr>';
        });
        $('#summaryTable').html(rows);
    }, 'json');
});
</script>
</body>
</html>
