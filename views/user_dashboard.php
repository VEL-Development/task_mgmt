<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?action=dashboard");
    exit;
}

$userId = $_GET['user_id'] ?? null;
if (!$userId) {
    header("Location: index.php?action=user_management");
    exit;
}

require_once 'models/User.php';
require_once 'models/TaskEnhanced.php';
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$task = new TaskEnhanced($db);

$userData = $user->getUserById($userId);
$userTasks = $task->getUserTaskStats($userId);
$recentTasks = $task->getUserRecentTasks($userId, 10);
$allUserTasks = $task->getTasksWithPagination(['t.assigned_to = ?'], [$userId], 1, 50);

$completionRate = $userTasks['total'] > 0 ? round(($userTasks['completed'] / $userTasks['total']) * 100) : 0;
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="dashboard-header-section">
        <div class="back-navigation">
            <a href="index.php?action=user_management" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to User Management
            </a>
        </div>
        
        <div class="user-profile-header">
            <div class="profile-avatar">
                <div class="avatar-large role-<?= $userData['role'] ?>">
                    <?= strtoupper(substr($userData['full_name'], 0, 2)) ?>
                </div>
                <div class="status-indicator <?= ($userData['status'] ?? 'active') === 'active' ? 'online' : 'offline' ?>"></div>
            </div>
            
            <div class="profile-info">
                <h1 class="profile-name"><?= htmlspecialchars($userData['full_name']) ?></h1>
                <div class="profile-meta">
                    <span class="username">@<?= htmlspecialchars($userData['username']) ?></span>
                    <span class="separator">•</span>
                    <span class="role-info"><?= ucfirst(str_replace('_', ' ', $userData['role'])) ?></span>
                    <span class="separator">•</span>
                    <span class="completion-rate"><?= $completionRate ?>% Complete</span>
                </div>
                <div class="profile-contact">
                    <i class="fas fa-envelope"></i>
                    <?= htmlspecialchars($userData['email']) ?>
                </div>
            </div>
            
            <div class="profile-actions">
                <button class="btn-action-primary" onclick="editUser(<?= $userId ?>)">
                    <i class="fas fa-edit"></i> Edit User
                </button>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="metrics-overview">
            <div class="metric-card total">
                <div class="metric-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="metric-data">
                    <div class="metric-number"><?= $userTasks['total'] ?? 0 ?></div>
                    <div class="metric-label">Total Tasks</div>
                </div>
                <div class="metric-chart">
                    <div class="progress-ring">
                        <div class="ring-fill" style="--percentage: <?= $completionRate ?>"></div>
                        <span class="ring-text"><?= $completionRate ?>%</span>
                    </div>
                </div>
            </div>
            
            <div class="metric-card completed">
                <div class="metric-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="metric-data">
                    <div class="metric-number"><?= $userTasks['completed'] ?? 0 ?></div>
                    <div class="metric-label">Completed</div>
                </div>
                <div class="metric-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>+12%</span>
                </div>
            </div>
            
            <div class="metric-card progress">
                <div class="metric-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="metric-data">
                    <div class="metric-number"><?= $userTasks['in_progress'] ?? 0 ?></div>
                    <div class="metric-label">In Progress</div>
                </div>
                <div class="metric-trend neutral">
                    <i class="fas fa-minus"></i>
                    <span>0%</span>
                </div>
            </div>
            
            <div class="metric-card pending">
                <div class="metric-icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
                <div class="metric-data">
                    <div class="metric-number"><?= $userTasks['pending'] ?? 0 ?></div>
                    <div class="metric-label">Pending</div>
                </div>
                <div class="metric-trend negative">
                    <i class="fas fa-arrow-down"></i>
                    <span>-5%</span>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="tasks-section">
                <div class="section-header">
                    <h3><i class="fas fa-list-check"></i> All Tasks</h3>
                    <div class="section-actions">
                        <select id="taskFilter" onchange="filterTasks()">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                
                <div class="tasks-list" id="tasksList">
                    <?php if (empty($allUserTasks)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>No Tasks Assigned</h4>
                        <p>This user hasn't been assigned any tasks yet.</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($allUserTasks as $t): ?>
                    <div class="task-item" data-status="<?= $t['status'] ?>">
                        <div class="task-priority priority-<?= $t['priority'] ?>"></div>
                        <div class="task-content">
                            <div class="task-header">
                                <h4 class="task-title">
                                    <a href="index.php?action=task_details&id=<?= $t['id'] ?>" target="_blank">
                                        <?= htmlspecialchars($t['title']) ?>
                                    </a>
                                </h4>
                                <div class="task-badges">
                                    <span class="status-badge status-<?= $t['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $t['status'])) ?>
                                    </span>
                                    <span class="priority-badge priority-<?= $t['priority'] ?>">
                                        <?= ucfirst($t['priority']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <p class="task-description"><?= htmlspecialchars(substr($t['description'], 0, 100)) ?>...</p>
                            
                            <div class="task-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    Created: <?= date('M j, Y', strtotime($t['created_at'])) ?>
                                </div>
                                <?php if ($t['due_date']): ?>
                                <div class="meta-item <?= strtotime($t['due_date']) < time() && $t['status'] !== 'completed' ? 'overdue' : '' ?>">
                                    <i class="fas fa-clock"></i>
                                    Due: <?= date('M j, Y', strtotime($t['due_date'])) ?>
                                </div>
                                <?php endif; ?>
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    By: <?= htmlspecialchars($t['creator_name']) ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="task-actions">
                            <a href="index.php?action=task_details&id=<?= $t['id'] ?>" class="btn-task-action" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="activity-section">
                <div class="section-header">
                    <h3><i class="fas fa-history"></i> Recent Activity</h3>
                </div>
                
                <div class="activity-timeline">
                    <?php if (empty($recentTasks)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <p>No recent activity</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($recentTasks as $t): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker status-<?= $t['status'] ?>">
                            <i class="fas fa-<?= $t['status'] === 'completed' ? 'check' : ($t['status'] === 'in_progress' ? 'clock' : 'pause') ?>"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <h5><?= htmlspecialchars($t['title']) ?></h5>
                                <span class="timeline-date"><?= date('M j', strtotime($t['created_at'])) ?></span>
                            </div>
                            <div class="timeline-badges">
                                <span class="status-badge status-<?= $t['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $t['status'])) ?>
                                </span>
                                <span class="priority-badge priority-<?= $t['priority'] ?>">
                                    <?= ucfirst($t['priority']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterTasks() {
    const filter = document.getElementById('taskFilter').value;
    const tasks = document.querySelectorAll('.task-item');
    
    tasks.forEach(task => {
        const status = task.dataset.status;
        if (!filter || status === filter) {
            task.style.display = 'flex';
            task.style.animation = 'fadeIn 0.3s ease';
        } else {
            task.style.display = 'none';
        }
    });
}

function editUser(id) {
    window.location.href = `index.php?action=user_management#edit-${id}`;
}

// Initialize animations
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.metric-card, .task-item, .timeline-item');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
});
</script>

<?php include 'includes/footer.php'; ?>