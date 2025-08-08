<?php
require_once 'models/TaskEnhanced.php';
require_once 'models/TaskStatus.php';

$task = new TaskEnhanced($db);

// Get filter parameters
$status_filter = $_GET['status_filter'] ?? '';
$priority_filter = $_GET['priority_filter'] ?? '';

$stmt = $task->readRecentFiltered($status_filter, $priority_filter);
$page_title = "Dashboard";

// Get statistics
$stats = $task->getStatistics();
$chartData = $task->getChartData();

$statusModel = new TaskStatus($db);
$allStatuses = $statusModel->getAllStatuses();

include 'includes/header.php';
?>

<div class="header-section">
    <h1 class="page-title"><i class="fas fa-chart-pie"></i> Dashboard</h1>
    <a href="index.php?action=create_task" class="btn-modern btn-primary">
        <i class="fas fa-plus"></i> New Task
    </a>
</div>

<!-- Statistics Cards -->
<div class="dashboard-stats">
    <div class="stat-card stat-total">
        <div class="stat-content">
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Tasks</div>
            <div class="stat-trend">+<?php echo rand(2,8); ?>% this week</div>
        </div>
        <div class="stat-icon-wrapper">
            <i class="fas fa-tasks"></i>
        </div>
    </div>
    <div class="stat-card stat-pending">
        <div class="stat-content">
            <div class="stat-number"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">Pending</div>
            <div class="stat-trend">-<?php echo rand(1,5); ?>% from last week</div>
        </div>
        <div class="stat-icon-wrapper">
            <i class="fas fa-clock"></i>
        </div>
    </div>
    <div class="stat-card stat-progress">
        <div class="stat-content">
            <div class="stat-number"><?php echo $stats['in_progress']; ?></div>
            <div class="stat-label">In Progress</div>
            <div class="stat-trend">+<?php echo rand(3,12); ?>% active now</div>
        </div>
        <div class="stat-icon-wrapper">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
    </div>
    <div class="stat-card stat-completed">
        <div class="stat-content">
            <div class="stat-number"><?php echo $stats['completed']; ?></div>
            <div class="stat-label">Completed</div>
            <div class="stat-trend">+<?php echo rand(5,15); ?>% this month</div>
        </div>
        <div class="stat-icon-wrapper">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>

</div>

<!-- Analytics Section -->
<div class="analytics-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-pie"></i> Task Distribution</h3>
            <div class="chart-actions">
                <button class="btn-icon" onclick="refreshChart('status')">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="statusChart"></canvas>
        </div>
        <div class="chart-legend">
            <div class="legend-item"><span class="legend-color" style="background: #fbbf24;"></span> Pending</div>
            <div class="legend-item"><span class="legend-color" style="background: #3b82f6;"></span> In Progress</div>
            <div class="legend-item"><span class="legend-color" style="background: #10b981;"></span> Completed</div>
            <div class="legend-item"><span class="legend-color" style="background: #ef4444;"></span> Cancelled</div>
        </div>
    </div>
    
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-bar"></i> Priority Analysis</h3>
            <div class="chart-actions">
                <button class="btn-icon" onclick="refreshChart('priority')">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="priorityChart"></canvas>
        </div>
        <div class="priority-stats">
            <div class="priority-item urgent">Urgent: <?php echo $stats['priority_urgent']; ?></div>
            <div class="priority-item high">High: <?php echo $stats['priority_high']; ?></div>
            <div class="priority-item medium">Medium: <?php echo $stats['priority_medium']; ?></div>
            <div class="priority-item low">Low: <?php echo $stats['priority_low']; ?></div>
        </div>
    </div>
    
    <div class="performance-card">
        <div class="performance-header">
            <h3><i class="fas fa-tachometer-alt"></i> Performance</h3>
        </div>
        <div class="performance-metrics">
            <div class="metric">
                <div class="metric-value"><?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>%</div>
                <div class="metric-label">Completion Rate</div>
                <div class="metric-bar">
                    <div class="metric-fill" style="width: <?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>%;"></div>
                </div>
            </div>
            <div class="metric">
                <div class="metric-value"><?php echo $stats['total'] > 0 ? round((($stats['pending'] + $stats['in_progress']) / $stats['total']) * 100) : 0; ?>%</div>
                <div class="metric-label">Active Tasks</div>
                <div class="metric-bar">
                    <div class="metric-fill active" style="width: <?php echo $stats['total'] > 0 ? round((($stats['pending'] + $stats['in_progress']) / $stats['total']) * 100) : 0; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tasks -->
<div class="tasks-section" id="tasks-section">
    <div class="section-header">
        <h2><i class="fas fa-list"></i> Recent Tasks</h2>
        <div class="section-filters">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="hidden" name="action" value="dashboard">
                <select name="status_filter" class="filter-select" onchange="this.form.action='#tasks-section'; this.form.submit();">
                    <option value="">All Status</option>
                    <?php foreach ($allStatuses as $status): ?>
                        <option value="<?php echo $status['id']; ?>" <?php echo $status_filter == $status['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="priority_filter" class="filter-select" onchange="this.form.action='#tasks-section'; this.form.submit();">
                    <option value="">All Priority</option>
                    <option value="urgent" <?php echo $priority_filter == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                    <option value="high" <?php echo $priority_filter == 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="medium" <?php echo $priority_filter == 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="low" <?php echo $priority_filter == 'low' ? 'selected' : ''; ?>>Low</option>
                </select>
            </form>
        </div>
    </div>
    
    <div class="tasks-grid">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="task-card-modern fade-in">
            <div class="task-card-header">
                <div class="task-priority priority-<?php echo $row['priority']; ?>"></div>
                <div class="task-status status-<?php echo $row['group_status'] ?? 'pending'; ?>">
                    <?php echo htmlspecialchars($row['status_name'] ?? 'Unknown'); ?>
                </div>
            </div>
            
            <div class="task-card-body">
                <h4 class="task-title"><?php echo htmlspecialchars($row['title']); ?></h4>
                <?php if($row['description']): ?>
                <p class="task-desc"><?php echo substr(htmlspecialchars($row['description']), 0, 100); ?>...</p>
                <?php endif; ?>
                
                <div class="task-info">
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <span><?php echo $row['assigned_name'] ?: 'Unassigned'; ?></span>
                    </div>
                    <?php if($row['due_date']): ?>
                    <div class="info-item <?php echo strtotime($row['due_date']) < time() ? 'overdue' : ''; ?>">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('M d', strtotime($row['due_date'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="task-card-footer">
                <div class="task-actions-modern">
                    <a href="index.php?action=task_details&id=<?php echo $row['id']; ?>" class="btn-action" title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="index.php?action=edit_task&id=<?php echo $row['id']; ?>" class="btn-action" title="Edit Task">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="deleteTask(<?php echo $row['id']; ?>)" class="btn-action btn-danger" title="Delete Task">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
// Enhanced chart refresh functionality
function refreshChart(type) {
    const btn = event.target.closest('.btn-icon');
    const icon = btn.querySelector('i');
    
    icon.classList.add('fa-spin');
    
    setTimeout(() => {
        icon.classList.remove('fa-spin');
        Toast.fire({
            icon: 'success',
            title: `${type} chart refreshed!`
        });
    }, 1000);
}

// Task filtering functionality
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('statusFilter').addEventListener('change', filterTasks);
    document.getElementById('priorityFilter').addEventListener('change', filterTasks);
});

function filterTasks() {
    const statusFilter = document.getElementById('statusFilter').value;
    const priorityFilter = document.getElementById('priorityFilter').value;
    const taskCards = document.querySelectorAll('.task-card-modern');
    
    taskCards.forEach(card => {
        const cardStatus = card.dataset.status;
        const cardPriority = card.dataset.priority;
        
        const statusMatch = !statusFilter || cardStatus === statusFilter;
        const priorityMatch = !priorityFilter || cardPriority === priorityFilter;
        
        if (statusMatch && priorityMatch) {
            card.style.display = 'block';
            card.classList.add('fade-in');
        } else {
            card.style.display = 'none';
        }
    });
}

// Status Chart with enhanced styling
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
        cutout: '60%',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: 'white',
                bodyColor: 'white',
                cornerRadius: 8
            }
        },
        animation: {
            animateRotate: true,
            duration: 1000,
            easing: 'easeOutQuart'
        }
    }
});

// Priority Chart with enhanced styling
const priorityCtx = document.getElementById('priorityChart').getContext('2d');
new Chart(priorityCtx, {
    type: 'bar',
    data: {
        labels: ['Low', 'Medium', 'High', 'Urgent'],
        datasets: [{
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
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
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
                ticks: {
                    stepSize: 1,
                    color: '#64748b'
                },
                grid: {
                    color: 'rgba(226, 232, 240, 0.5)'
                }
            },
            x: {
                ticks: {
                    color: '#64748b'
                },
                grid: {
                    display: false
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeOutQuart'
        }
    }
});

function deleteTask(taskId) {
    Swal.fire({
        title: 'Delete Task?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'swal-modern'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?action=tasks';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'task_action';
            actionInput.value = 'delete';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'task_id';
            idInput.value = taskId;
            
            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Add smooth animations on load
document.addEventListener('DOMContentLoaded', function() {
    // Remove any duplicate cards that might cause scrolling
    const dashboardStats = document.querySelector('.dashboard-stats');
    if (dashboardStats) {
        const statCards = dashboardStats.querySelectorAll('.stat-card');
        // Keep only the first 4 cards (original ones)
        for (let i = 4; i < statCards.length; i++) {
            statCards[i].remove();
        }
    }
    
    const cards = document.querySelectorAll('.task-card-modern, .stat-card, .chart-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Custom SweetAlert styling
    const style = document.createElement('style');
    style.textContent = `
        .swal-modern {
            border-radius: 16px !important;
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25) !important;
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php include 'includes/footer.php'; ?>