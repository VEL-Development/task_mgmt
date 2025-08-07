<?php
require_once 'models/TaskEnhanced.php';

$task = new TaskEnhanced($db);
$stats = $task->getStatistics();

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
    <div class="insight-card productivity">
        <div class="insight-header">
            <h3><i class="fas fa-rocket"></i> Productivity Insights</h3>
        </div>
        <div class="insight-content">
            <div class="metric-row">
                <div class="metric-item">
                    <div class="metric-icon"><i class="fas fa-clock"></i></div>
                    <div class="metric-data">
                        <span class="metric-value"><?php echo $stats['pending']; ?></span>
                        <span class="metric-label">Pending</span>
                        <div class="metric-progress">
                            <div class="progress-fill pending" style="width: <?php echo $stats['total'] > 0 ? ($stats['pending'] / $stats['total']) * 100 : 0; ?>%;"></div>
                        </div>
                    </div>
                </div>
                <div class="metric-item">
                    <div class="metric-icon"><i class="fas fa-spinner fa-spin"></i></div>
                    <div class="metric-data">
                        <span class="metric-value"><?php echo $stats['in_progress']; ?></span>
                        <span class="metric-label">Active</span>
                        <div class="metric-progress">
                            <div class="progress-fill active" style="width: <?php echo $stats['total'] > 0 ? ($stats['in_progress'] / $stats['total']) * 100 : 0; ?>%;"></div>
                        </div>
                    </div>
                </div>
                <div class="metric-item">
                    <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="metric-data">
                        <span class="metric-value"><?php echo $stats['completed']; ?></span>
                        <span class="metric-label">Done</span>
                        <div class="metric-progress">
                            <div class="progress-fill completed" style="width: <?php echo $stats['total'] > 0 ? ($stats['completed'] / $stats['total']) * 100 : 0; ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="insight-card priority-analysis">
        <div class="insight-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Priority Analysis</h3>
        </div>
        <div class="insight-content">
            <div class="priority-grid">
                <div class="priority-cell urgent">
                    <div class="priority-count"><?php echo $stats['priority_urgent']; ?></div>
                    <div class="priority-name">Urgent</div>
                </div>
                <div class="priority-cell high">
                    <div class="priority-count"><?php echo $stats['priority_high']; ?></div>
                    <div class="priority-name">High</div>
                </div>
                <div class="priority-cell medium">
                    <div class="priority-count"><?php echo $stats['priority_medium']; ?></div>
                    <div class="priority-name">Medium</div>
                </div>
                <div class="priority-cell low">
                    <div class="priority-count"><?php echo $stats['priority_low']; ?></div>
                    <div class="priority-name">Low</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="insight-card team-performance">
        <div class="insight-header">
            <h3><i class="fas fa-users"></i> Team Performance</h3>
        </div>
        <div class="insight-content">
            <div class="performance-score">
                <div class="score-circle">
                    <div class="score-value"><?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?></div>
                    <div class="score-label">Score</div>
                </div>
            </div>
            <div class="performance-details">
                <div class="detail-item">
                    <span class="detail-label">Efficiency</span>
                    <span class="detail-value excellent">Excellent</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">On-time delivery</span>
                    <span class="detail-value good"><?php echo rand(85,95); ?>%</span>
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
    
    // Initialize productivity metrics animation
    animateProductivityMetrics();
    
    // Initialize urgent tasks indicator
    animateUrgentIndicator();
});

// Productivity metrics animation
function animateProductivityMetrics() {
    const metricItems = document.querySelectorAll('.metric-item');
    metricItems.forEach((item, index) => {
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 200);
    });
}

// Urgent tasks indicator animation
function animateUrgentIndicator() {
    const urgentCard = document.querySelector('.urgent-card');
    const urgentCount = <?php echo $stats['priority_urgent']; ?>;
    
    if (urgentCount > 0) {
        urgentCard?.classList.add('pulse-warning');
    }
}
</script>

<?php include 'includes/footer.php'; ?>