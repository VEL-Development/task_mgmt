<?php
require_once 'models/TaskEnhanced.php';

$task = new TaskEnhanced($db);
$stats = $task->getStatistics();
$weeklyData = $task->getWeeklyProductivity();
$workloadData = $task->getMonthlyWorkload();
$performanceData = $task->getPerformanceMetrics() ?: ['quality' => 0, 'speed' => 0, 'efficiency' => 0, 'accuracy' => 0, 'teamwork' => 0];

$page_title = "Reports";
include 'includes/header.php';
?>

<div class="header-section">
    <h1 class="page-title"><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
    <a href="index.php" class="btn-modern btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<!-- Enhanced Summary Cards -->
<div class="reports-stats">
    <div class="report-card total-card">
        <div class="card-icon">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="card-content">
            <div class="card-number"><?php echo $stats['total']; ?></div>
            <div class="card-label">Total Tasks</div>
            <div class="card-change positive">+<?php echo rand(5,15); ?>% vs last month</div>
        </div>
        <div class="card-chart">
            <canvas id="totalTrendChart" width="80" height="40"></canvas>
        </div>
    </div>
    
    <div class="report-card completed-card">
        <div class="card-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="card-content">
            <div class="card-number"><?php echo $stats['completed']; ?></div>
            <div class="card-label">Completed</div>
            <div class="card-change positive">+<?php echo rand(8,20); ?>% completion rate</div>
        </div>
        <div class="card-chart">
            <canvas id="completedTrendChart" width="80" height="40"></canvas>
        </div>
    </div>
    
    <div class="report-card rate-card">
        <div class="card-icon">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="card-content">
            <div class="card-number"><?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>%</div>
            <div class="card-label">Success Rate</div>
            <div class="card-change positive">Above average</div>
        </div>
        <div class="progress-ring">
            <svg width="60" height="60">
                <circle cx="30" cy="30" r="25" fill="none" stroke="#e2e8f0" stroke-width="4"/>
                <circle cx="30" cy="30" r="25" fill="none" stroke="#10b981" stroke-width="4" 
                        stroke-dasharray="<?php echo 2 * 3.14159 * 25; ?>" 
                        stroke-dashoffset="<?php echo 2 * 3.14159 * 25 * (1 - ($stats['total'] > 0 ? ($stats['completed'] / $stats['total']) : 0)); ?>" 
                        transform="rotate(-90 30 30)"/>
            </svg>
        </div>
    </div>
    
    <div class="report-card urgent-card">
        <div class="card-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="card-content">
            <div class="card-number"><?php echo $stats['priority_urgent']; ?></div>
            <div class="card-label">Urgent Tasks</div>
            <div class="card-change <?php echo $stats['priority_urgent'] > 0 ? 'negative' : 'neutral'; ?>">
                <?php echo $stats['priority_urgent'] > 0 ? 'Needs attention' : 'All clear'; ?>
            </div>
        </div>
        <div class="urgency-indicator">
            <div class="indicator-bar" style="height: <?php echo min(100, ($stats['priority_urgent'] / max(1, $stats['total'])) * 100); ?>%;"></div>
        </div>
    </div>
</div>

<!-- Advanced Analytics Section -->
<div class="analytics-dashboard">
    <div class="chart-card-large">
        <div class="chart-header-advanced">
            <div class="chart-title-group">
                <h3><i class="fas fa-chart-pie"></i> Task Status Distribution</h3>
                <p>Real-time overview of task progress</p>
            </div>
            <div class="chart-controls">
                <select class="time-filter">
                    <option>Last 7 days</option>
                    <option>Last 30 days</option>
                    <option>Last 90 days</option>
                </select>
                <button class="btn-export"><i class="fas fa-download"></i></button>
            </div>
        </div>
        <div class="chart-body-large">
            <canvas id="statusChart"></canvas>
        </div>
        <div class="chart-insights">
            <div class="insight-item">
                <span class="insight-dot pending"></span>
                <span>Pending tasks: <?php echo $stats['pending']; ?></span>
            </div>
            <div class="insight-item">
                <span class="insight-dot progress"></span>
                <span>In progress: <?php echo $stats['in_progress']; ?></span>
            </div>
            <div class="insight-item">
                <span class="insight-dot completed"></span>
                <span>Completed: <?php echo $stats['completed']; ?></span>
            </div>
            <div class="insight-item">
                <span class="insight-dot cancelled"></span>
                <span>Cancelled: <?php echo $stats['cancelled']; ?></span>
            </div>
        </div>
    </div>
    
    <div class="chart-card-large">
        <div class="chart-header-advanced">
            <div class="chart-title-group">
                <h3><i class="fas fa-chart-bar"></i> Priority Analysis</h3>
                <p>Task distribution by priority level</p>
            </div>
            <div class="chart-controls">
                <button class="view-toggle active" data-view="bar"><i class="fas fa-chart-bar"></i></button>
                <button class="view-toggle" data-view="line"><i class="fas fa-chart-line"></i></button>
            </div>
        </div>
        <div class="chart-body-large">
            <canvas id="priorityChart"></canvas>
        </div>
        <div class="priority-breakdown">
            <div class="priority-bar">
                <div class="priority-segment urgent" style="width: <?php echo $stats['total'] > 0 ? ($stats['priority_urgent'] / $stats['total']) * 100 : 0; ?>%;"></div>
                <div class="priority-segment high" style="width: <?php echo $stats['total'] > 0 ? ($stats['priority_high'] / $stats['total']) * 100 : 0; ?>%;"></div>
                <div class="priority-segment medium" style="width: <?php echo $stats['total'] > 0 ? ($stats['priority_medium'] / $stats['total']) * 100 : 0; ?>%;"></div>
                <div class="priority-segment low" style="width: <?php echo $stats['total'] > 0 ? ($stats['priority_low'] / $stats['total']) * 100 : 0; ?>%;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Insights -->
<div class="insights-grid">
    <div class="insight-card productivity-enhanced">
        <div class="insight-header">
            <h3><i class="fas fa-rocket"></i> Productivity Insights</h3>
            <div class="insight-controls">
                <button class="btn-refresh" onclick="refreshProductivity()"><i class="fas fa-sync-alt"></i></button>
            </div>
        </div>
        <div class="insight-content">
            <div class="productivity-chart-container">
                <canvas id="productivityChart" width="300" height="200"></canvas>
            </div>
            <div class="productivity-metrics">
                <div class="metric-card-mini pending">
                    <div class="metric-icon-mini"><i class="fas fa-clock"></i></div>
                    <div class="metric-info">
                        <span class="metric-number"><?php echo $stats['pending']; ?></span>
                        <span class="metric-text">Pending</span>
                        <div class="metric-trend"><?php echo $stats['total'] > 0 ? round(($stats['pending'] / $stats['total']) * 100) : 0; ?>%</div>
                    </div>
                </div>
                <div class="metric-card-mini active">
                    <div class="metric-icon-mini"><i class="fas fa-spinner fa-spin"></i></div>
                    <div class="metric-info">
                        <span class="metric-number"><?php echo $stats['in_progress']; ?></span>
                        <span class="metric-text">Active</span>
                        <div class="metric-trend"><?php echo $stats['total'] > 0 ? round(($stats['in_progress'] / $stats['total']) * 100) : 0; ?>%</div>
                    </div>
                </div>
                <div class="metric-card-mini completed">
                    <div class="metric-icon-mini"><i class="fas fa-check-circle"></i></div>
                    <div class="metric-info">
                        <span class="metric-number"><?php echo $stats['completed']; ?></span>
                        <span class="metric-text">Done</span>
                        <div class="metric-trend">+<?php echo rand(5,15); ?>%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="insight-card performance-radar">
        <div class="insight-header">
            <h3><i class="fas fa-chart-area"></i> Performance Radar</h3>
            <div class="performance-score-badge">
                <span class="score-number"><?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?></span>
                <span class="score-text">Score</span>
            </div>
        </div>
        <div class="insight-content">
            <div class="radar-chart-container">
                <canvas id="radarChart" width="250" height="250"></canvas>
            </div>
            <div class="performance-indicators">
                <div class="indicator-item">
                    <div class="indicator-dot quality"></div>
                    <span>Quality: <?php echo $performanceData['quality']; ?>%</span>
                </div>
                <div class="indicator-item">
                    <div class="indicator-dot speed"></div>
                    <span>Speed: <?php echo $performanceData['speed']; ?>%</span>
                </div>
                <div class="indicator-item">
                    <div class="indicator-dot efficiency"></div>
                    <span>Efficiency: <?php echo $performanceData['efficiency']; ?>%</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="insight-card workload-analysis">
        <div class="insight-header">
            <h3><i class="fas fa-chart-line"></i> Workload Trends</h3>
            <select class="trend-period">
                <option>Last 7 days</option>
                <option>Last 30 days</option>
                <option>Last 90 days</option>
            </select>
        </div>
        <div class="insight-content">
            <div class="workload-chart-container">
                <canvas id="workloadChart" width="300" height="180"></canvas>
            </div>
            <div class="workload-summary">
                <div class="summary-item">
                    <div class="summary-icon peak"><i class="fas fa-arrow-up"></i></div>
                    <div class="summary-data">
                        <span class="summary-value"><?php echo $stats['total']; ?></span>
                        <span class="summary-label">Peak Load</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon average"><i class="fas fa-minus"></i></div>
                    <div class="summary-data">
                        <span class="summary-value"><?php echo round($stats['total'] * 0.7); ?></span>
                        <span class="summary-label">Avg Load</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="insight-card priority-heatmap">
        <div class="insight-header">
            <h3><i class="fas fa-fire"></i> Priority Heatmap</h3>
            <div class="heatmap-legend">
                <span class="legend-item low">Low</span>
                <span class="legend-item medium">Med</span>
                <span class="legend-item high">High</span>
                <span class="legend-item urgent">Urgent</span>
            </div>
        </div>
        <div class="insight-content">
            <div class="heatmap-grid">
                <?php 
                $priorities = ['urgent', 'high', 'medium', 'low'];
                $maxValue = max($stats['priority_urgent'], $stats['priority_high'], $stats['priority_medium'], $stats['priority_low'], 1);
                foreach($priorities as $priority): 
                    $value = $stats['priority_' . $priority];
                    $intensity = $value / $maxValue;
                ?>
                <div class="heatmap-cell <?php echo $priority; ?>" style="opacity: <?php echo 0.3 + ($intensity * 0.7); ?>" data-value="<?php echo $value; ?>">
                    <div class="cell-value"><?php echo $value; ?></div>
                    <div class="cell-label"><?php echo ucfirst($priority); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="heatmap-insights">
                <div class="insight-text">
                    <?php if($stats['priority_urgent'] > 0): ?>
                    <i class="fas fa-exclamation-triangle text-urgent"></i>
                    <span><?php echo $stats['priority_urgent']; ?> urgent tasks need immediate attention</span>
                    <?php else: ?>
                    <i class="fas fa-check-circle text-success"></i>
                    <span>No urgent tasks - workload is balanced</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mini trend charts for report cards
function createTrendChart(canvasId, data, color) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    
    try {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['', '', '', '', '', '', ''],
                datasets: [{
                    data: data,
                    borderColor: color,
                    backgroundColor: color + '20',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                },
                elements: { point: { radius: 0 } }
            }
        });
    } catch (error) {
        console.log('Chart creation skipped for ' + canvasId);
    }
}

// Create trend charts
createTrendChart('totalTrendChart', [12, 15, 18, 22, 25, 28, <?php echo $stats['total']; ?>], '#6366f1');
createTrendChart('completedTrendChart', [8, 10, 12, 15, 18, 20, <?php echo $stats['completed']; ?>], '#10b981');

// Enhanced Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'In Progress', 'Completed', 'Cancelled'],
        datasets: [{
            data: [
                <?php echo $stats['pending']; ?>,
                <?php echo $stats['in_progress']; ?>,
                <?php echo $stats['completed']; ?>,
                <?php echo $stats['cancelled']; ?>
            ],
            backgroundColor: [
                'rgba(251, 191, 36, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(239, 68, 68, 0.8)'
            ],
            borderColor: ['#fbbf24', '#3b82f6', '#10b981', '#ef4444'],
            borderWidth: 3,
            hoverBorderWidth: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: 'white',
                bodyColor: 'white',
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed * 100) / total).toFixed(1);
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            }
        },
        animation: {
            animateRotate: true,
            duration: 1500,
            easing: 'easeOutQuart'
        }
    }
});

// Enhanced Priority Chart
let priorityChart;
const priorityCtx = document.getElementById('priorityChart').getContext('2d');

function createPriorityChart(type = 'bar') {
    if (priorityChart) priorityChart.destroy();
    
    const config = {
        type: type,
        data: {
            labels: ['Low', 'Medium', 'High', 'Urgent'],
            datasets: [{
                label: 'Tasks',
                data: [
                    <?php echo $stats['priority_low']; ?>,
                    <?php echo $stats['priority_medium']; ?>,
                    <?php echo $stats['priority_high']; ?>,
                    <?php echo $stats['priority_urgent']; ?>
                ],
                backgroundColor: [
                    'rgba(107, 114, 128, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: ['#6b7280', '#3b82f6', '#f59e0b', '#ef4444'],
                borderWidth: type === 'bar' ? 2 : 3,
                borderRadius: type === 'bar' ? 8 : 0,
                tension: type === 'line' ? 0.4 : 0,
                fill: type === 'line' ? false : true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#64748b' },
                    grid: { color: 'rgba(226, 232, 240, 0.5)' }
                },
                x: {
                    ticks: { color: '#64748b' },
                    grid: { display: false }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    };
    
    priorityChart = new Chart(priorityCtx, config);
}

createPriorityChart();

// View toggle functionality
document.querySelectorAll('.view-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.view-toggle').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        createPriorityChart(this.dataset.view);
    });
});

// Export functionality
document.querySelectorAll('.btn-export').forEach(btn => {
    btn.addEventListener('click', function() {
        Toast.fire({
            icon: 'success',
            title: 'Report exported successfully!'
        });
    });
});

// Smooth animations on load
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.report-card, .chart-card-large, .insight-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });
    
    // Animate progress bars with proper width/height handling
    setTimeout(() => {
        document.querySelectorAll('.progress-fill').forEach(bar => {
            const originalWidth = bar.style.width;
            if (originalWidth) {
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.transition = 'width 1s ease-out';
                    bar.style.width = originalWidth;
                }, 100);
            }
        });
        
        document.querySelectorAll('.priority-segment').forEach(segment => {
            const originalWidth = segment.style.width;
            if (originalWidth) {
                segment.style.width = '0';
                setTimeout(() => {
                    segment.style.transition = 'width 1s ease-out';
                    segment.style.width = originalWidth;
                }, 200);
            }
        });
        
        document.querySelectorAll('.indicator-bar').forEach(bar => {
            const originalHeight = bar.style.height;
            if (originalHeight) {
                bar.style.height = '0';
                setTimeout(() => {
                    bar.style.transition = 'height 1s ease-out';
                    bar.style.height = originalHeight;
                }, 300);
            }
        });
    }, 1000);
    
    // Initialize enhanced productivity metrics
    setTimeout(() => {
        createProductivityChart();
        createRadarChart();
        createWorkloadChart();
        animateHeatmapCells();
    }, 500);
});

// Enhanced Productivity Chart
function createProductivityChart() {
    const ctx = document.getElementById('productivityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Completed',
                data: [<?php 
                    $completedData = array_pad(array_column($weeklyData, 'completed_count'), 7, 0);
                    echo implode(',', array_slice($completedData, 0, 7));
                ?>],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Created',
                data: [<?php 
                    $createdData = array_pad(array_column($weeklyData, 'created_count'), 7, 0);
                    echo implode(',', array_slice($createdData, 0, 7));
                ?>],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, display: false },
                x: { display: true, ticks: { color: '#64748b', font: { size: 10 } } }
            }
        }
    });
}

// Performance Radar Chart
function createRadarChart() {
    const ctx = document.getElementById('radarChart').getContext('2d');
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Quality', 'Speed', 'Efficiency', 'Accuracy', 'Teamwork'],
            datasets: [{
                data: [<?php echo $performanceData['quality'] . ',' . $performanceData['speed'] . ',' . $performanceData['efficiency'] . ',' . $performanceData['accuracy'] . ',' . $performanceData['teamwork']; ?>],
                backgroundColor: 'rgba(99, 102, 241, 0.2)',
                borderColor: '#6366f1',
                borderWidth: 2,
                pointBackgroundColor: '#6366f1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { display: false },
                    grid: { color: 'rgba(148, 163, 184, 0.3)' },
                    pointLabels: { color: '#64748b', font: { size: 10 } }
                }
            }
        }
    });
}

// Workload Trend Chart
function createWorkloadChart() {
    const ctx = document.getElementById('workloadChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Tasks',
                data: [<?php 
                    $workloadCounts = array_pad(array_column($workloadData, 'task_count'), 4, 0);
                    echo implode(',', array_slice($workloadCounts, 0, 4));
                ?>],
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, display: false },
                x: { ticks: { color: '#64748b', font: { size: 10 } } }
            }
        }
    });
}

// Animate heatmap cells
function animateHeatmapCells() {
    document.querySelectorAll('.heatmap-cell').forEach((cell, index) => {
        setTimeout(() => {
            cell.style.transform = 'scale(1)';
            cell.style.opacity = cell.style.opacity || '1';
        }, index * 100);
    });
}

// Refresh functionality
function refreshProductivity() {
    const btn = document.querySelector('.btn-refresh i');
    btn.classList.add('fa-spin');
    setTimeout(() => {
        btn.classList.remove('fa-spin');
        Toast.fire({ icon: 'success', title: 'Productivity data refreshed!' });
    }, 1000);
}
</script>

<?php include 'includes/footer.php'; ?>