<?php
$page_title = "My Tasks";
include 'includes/header.php';
require_once 'models/TaskEnhanced.php';
require_once 'models/TaskStatus.php';

$taskModel = new TaskEnhanced($db);
$statusModel = new TaskStatus($db);
$allStatuses = $statusModel->getAllStatuses();
$user_id = $_SESSION['user_id'];

// Get user's today tasks (start date <= today && due date >= today)
$today = date('Y-m-d');
$query = "SELECT t.*, u.username as assigned_to_name, ts.name as status_name, ts.color as status_color, ts.group_status 
          FROM tasks t 
          LEFT JOIN users u ON t.assigned_to = u.id 
          LEFT JOIN task_statuses ts ON t.status_id = ts.id
          WHERE t.assigned_to = ? AND t.start_date <= ? AND t.due_date >= ?
          ORDER BY t.due_date ASC, t.priority DESC";
$stmt = $db->prepare($query);
$stmt->execute([$user_id, $today, $today]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="header-section">
    <div class="header-left">
        <h1 class="page-title">
            <i class="fas fa-calendar-day"></i> Today's Tasks Summary
        </h1>
        <p class="page-subtitle"><?php echo date('l, F j, Y'); ?></p>
    </div>
    <div class="header-actions">
        <button onclick="exportTasks()" class="btn-modern btn-secondary">
            <i class="fas fa-download"></i> Export CSV
        </button>
        <button onclick="refreshTasks()" class="btn-modern btn-outline">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
        <a href="index.php" class="btn-modern btn-primary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="dashboard-overview">
    <div class="overview-cards">
        <div class="overview-card total">
            <div class="card-icon"><i class="fas fa-tasks"></i></div>
            <div class="card-content">
                <h3><?php echo count($tasks); ?></h3>
                <p>Today's Tasks</p>
                <div class="card-trend">Active workload</div>
            </div>
        </div>
        <div class="overview-card pending">
            <div class="card-icon"><i class="fas fa-clock"></i></div>
            <div class="card-content">
                <h3><?php echo count(array_filter($tasks, fn($t) => $t['group_status'] == 'pending')); ?></h3>
                <p>Pending</p>
                <div class="card-trend">Need attention</div>
            </div>
        </div>
        <div class="overview-card progress">
            <div class="card-icon"><i class="fas fa-spinner fa-spin"></i></div>
            <div class="card-content">
                <h3><?php echo count(array_filter($tasks, fn($t) => $t['group_status'] == 'in_progress')); ?></h3>
                <p>In Progress</p>
                <div class="card-trend">Active work</div>
            </div>
        </div>
        <div class="overview-card completed">
            <div class="card-icon"><i class="fas fa-check-circle"></i></div>
            <div class="card-content">
                <h3><?php echo count(array_filter($tasks, fn($t) => $t['group_status'] == 'completed')); ?></h3>
                <p>Completed</p>
                <div class="card-trend">Well done!</div>
            </div>
        </div>
    </div>
</div>

<div class="tasks-section">
    <div class="section-header">
        <h2><i class="fas fa-tasks"></i> Active Tasks</h2>
        <div class="task-filters">
            <button class="filter-btn active" onclick="filterTasks('all')">
                <i class="fas fa-list"></i> All
            </button>
            <button class="filter-btn" onclick="filterTasks('pending')">
                <i class="fas fa-clock"></i> Pending
            </button>
            <button class="filter-btn" onclick="filterTasks('in_progress')">
                <i class="fas fa-spinner"></i> In Progress
            </button>
            <button class="filter-btn" onclick="filterTasks('completed')">
                <i class="fas fa-check"></i> Completed
            </button>
        </div>
    </div>
    
    <div class="tasks-container">
        <?php if (empty($tasks)): ?>
            <div class="empty-state-modern">
                <div class="empty-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>All Clear for Today!</h3>
                <p>No tasks scheduled for today. Great job staying on top of your work!</p>
                <a href="index.php?action=create_task" class="btn-create-task">
                    <i class="fas fa-plus"></i> Create New Task
                </a>
            </div>
        <?php else: ?>
            <div class="tasks-grid">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card <?php echo $task['group_status']; ?>" data-priority="<?php echo $task['priority']; ?>">
                        <div class="task-card-header">
                            <div class="task-priority priority-<?php echo $task['priority']; ?>">
                                <?php 
                                $priority_icons = ['low' => 'arrow-down', 'medium' => 'minus', 'high' => 'arrow-up', 'urgent' => 'exclamation'];
                                echo '<i class="fas fa-' . $priority_icons[$task['priority']] . '"></i>';
                                ?>
                            </div>
                            <div class="task-status status-<?php echo $task['group_status']; ?>" style="color: <?php echo $task['status_color']; ?>">
                                <?php echo htmlspecialchars($task['status_name']); ?>
                            </div>
                        </div>
                        
                        <div class="task-card-body">
                            <h3 class="task-card-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p class="task-card-desc"><?php echo substr(htmlspecialchars($task['description']), 0, 100) . '...'; ?></p>
                            
                            <div class="task-timeline">
                                <div class="timeline-item">
                                    <i class="fas fa-play"></i>
                                    <span><?php echo date('M j', strtotime($task['start_date'])); ?></span>
                                </div>
                                <div class="timeline-divider"></div>
                                <div class="timeline-item">
                                    <i class="fas fa-flag-checkered"></i>
                                    <span><?php echo date('M j', strtotime($task['due_date'])); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="task-card-footer">
                            <a href="index.php?action=task_details&id=<?php echo $task['id']; ?>" class="btn-card-action">
                                <i class="fas fa-eye"></i> <span>View</span>
                            </a>
                            <?php if ($task['group_status'] != 'completed'): ?>
                                <a href="index.php?action=edit_task&id=<?php echo $task['id']; ?>" class="btn-card-action">
                                    <i class="fas fa-edit"></i> <span>Edit</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-subtitle {
    margin: 0.5rem 0 0 0;
    color: #64748b;
    font-size: 1rem;
    font-weight: 400;
}

.dashboard-overview {
    margin-bottom: 2rem;
}

.overview-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.overview-card {
    background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 20px;
    padding: 1.75rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.overview-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--card-gradient));
    transition: all 0.3s ease;
}

.overview-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    border-color: rgba(255,255,255,0.4);
}

.overview-card:hover::before {
    height: 6px;
}

.overview-card.total { --card-gradient: #6366f1, #8b5cf6; }
.overview-card.pending { --card-gradient: #f59e0b, #f97316; }
.overview-card.progress { --card-gradient: #3b82f6, #1d4ed8; }
.overview-card.completed { --card-gradient: #10b981, #059669; }

.card-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    position: relative;
    overflow: hidden;
}

.card-icon::before {
    content: '';
    position: absolute;
    inset: 0;
    background: inherit;
    opacity: 0.1;
    border-radius: inherit;
}

.total .card-icon { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; }
.pending .card-icon { background: linear-gradient(135deg, #f59e0b, #f97316); color: white; }
.progress .card-icon { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; }
.completed .card-icon { background: linear-gradient(135deg, #10b981, #059669); color: white; }

.card-content h3 {
    margin: 0;
    font-size: 2.75rem;
    font-weight: 800;
    color: #1f2937;
    background: linear-gradient(135deg, #1f2937, #374151);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.card-content p {
    margin: 0.5rem 0 0.25rem 0;
    color: #4b5563;
    font-weight: 600;
    font-size: 1rem;
}

.card-trend {
    font-size: 0.8rem;
    color: #6b7280;
    font-weight: 500;
    opacity: 0.8;
}

.tasks-section {
    background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.section-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-header h2 i {
    color: #6366f1;
}

.task-filters {
    display: flex;
    gap: 0.5rem;
    background: rgba(255,255,255,0.5);
    padding: 0.25rem;
    border-radius: 12px;
    backdrop-filter: blur(10px);
}

.filter-btn {
    padding: 0.5rem 1rem;
    border: none;
    background: transparent;
    color: #64748b;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-btn:hover {
    background: rgba(255,255,255,0.8);
    color: #1f2937;
}

.filter-btn.active {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.tasks-container {
    margin: 0;
    padding: 0;
    position: relative;
}

.tasks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 2rem;
    animation: fadeInUp 0.8s ease-out;
    position: relative;
}

.tasks-grid::before {
    content: '';
    position: absolute;
    inset: -1rem;
    background: linear-gradient(45deg, transparent 30%, rgba(99, 102, 241, 0.05) 50%, transparent 70%);
    border-radius: 20px;
    opacity: 0;
    transition: opacity 0.5s ease;
    pointer-events: none;
}

.tasks-grid:hover::before {
    opacity: 1;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes gridPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.01); }
}

.task-card {
    background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(255,255,255,0.9));
    backdrop-filter: blur(25px);
    border: 1px solid rgba(255,255,255,0.4);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    opacity: 1;
    animation: slideInCard 0.6s ease-out;
    cursor: pointer;
    will-change: transform;
}

@keyframes slideInCard {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.9) rotateX(10deg);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1) rotateX(0deg);
    }
}

.task-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    transition: all 0.4s ease;
    border-radius: 24px 24px 0 0;
}

.task-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 0%, rgba(255,255,255,0.2), transparent 70%);
    opacity: 0;
    transition: all 0.4s ease;
    pointer-events: none;
    border-radius: 24px;
}

.task-card:hover {
    transform: translateY(-16px) scale(1.04) rotateX(2deg);
    box-shadow: 0 30px 60px rgba(0,0,0,0.12), 0 8px 20px rgba(0,0,0,0.08);
    border-color: rgba(255,255,255,0.7);
    z-index: 10;
}

.task-card:hover::before {
    height: 8px;
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
}

.task-card:hover::after {
    opacity: 1;
}

.task-card:active {
    transform: translateY(-12px) scale(1.02);
    transition: all 0.1s ease;
}

.task-card.hidden {
    display: none;
}

.task-card:nth-child(odd) {
    animation-delay: 0.1s;
}

.task-card:nth-child(even) {
    animation-delay: 0.2s;
}

.task-card:nth-child(3n) {
    animation-delay: 0.3s;
}

.task-card.pending::before { background: linear-gradient(90deg, #f59e0b, #f97316); }
.task-card.in_progress::before { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
.task-card.completed::before { background: linear-gradient(90deg, #10b981, #059669); }
.task-card.cancelled::before { background: linear-gradient(90deg, #ef4444, #dc2626); }

.task-card-header {
    padding: 2rem 2rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.task-card-header::after {
    content: '';
    position: absolute;
    bottom: -0.5rem;
    left: 2rem;
    right: 2rem;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(0,0,0,0.1), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.task-card:hover .task-card-header::after {
    opacity: 1;
}

.task-priority {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 700;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.task-priority::before {
    content: '';
    position: absolute;
    inset: 0;
    background: inherit;
    opacity: 0.1;
    border-radius: inherit;
    transform: scale(0);
    transition: transform 0.3s ease;
}

.task-card:hover .task-priority::before {
    transform: scale(1.2);
}

.task-priority:hover {
    transform: scale(1.1) rotate(5deg);
}

.task-card-body {
    padding: 1.5rem 2rem;
    position: relative;
}

.task-card-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 1.5rem;
    width: 2px;
    height: 0;
    background: linear-gradient(180deg, #6366f1, #8b5cf6);
    transition: height 0.4s ease;
    border-radius: 1px;
}

.task-card:hover .task-card-body::before {
    height: 100%;
}

.task-card-title {
    margin: 0 0 1rem 0;
    font-size: 1.375rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.3;
    transition: all 0.3s ease;
    position: relative;
}

.task-card:hover .task-card-title {
    color: #6366f1;
    transform: translateX(8px);
}

.task-card-title::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
    transition: width 0.4s ease;
    border-radius: 1px;
}

.task-card:hover .task-card-title::after {
    width: 100%;
}

.task-card-desc {
    margin: 0 0 1.25rem 0;
    color: #6b7280;
    line-height: 1.6;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    position: relative;
}

.task-card:hover .task-card-desc {
    color: #4b5563;
    transform: translateX(4px);
}

.task-timeline {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.task-timeline::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.task-card:hover .task-timeline {
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    transform: scale(1.02);
}

.task-card:hover .task-timeline::before {
    opacity: 1;
}

.timeline-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.9rem;
    color: #64748b;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.timeline-item i {
    transition: all 0.3s ease;
}

.task-card:hover .timeline-item {
    color: #475569;
}

.task-card:hover .timeline-item i {
    color: #6366f1;
    transform: scale(1.1);
}

.timeline-divider {
    flex: 1;
    height: 2px;
    background: linear-gradient(90deg, #e2e8f0, #cbd5e1, #e2e8f0);
    border-radius: 1px;
    position: relative;
    overflow: hidden;
}

.timeline-divider::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, #6366f1, transparent);
    transition: left 0.6s ease;
}

.task-card:hover .timeline-divider::after {
    left: 100%;
}

.task-card-footer {
    padding: 1.25rem 2rem 2rem;
    display: flex;
    gap: 1rem;
    position: relative;
}

.task-card-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 2rem;
    right: 2rem;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(0,0,0,0.1), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.task-card:hover .task-card-footer::before {
    opacity: 1;
}

.btn-card-action {
    flex: 1;
    padding: 0.875rem 1rem;
    text-align: center;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    color: #64748b;
    text-decoration: none;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    border: 1px solid rgba(0,0,0,0.05);
    position: relative;
    overflow: hidden;
}

.btn-card-action::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.btn-card-action span {
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
}

.btn-card-action i {
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
}

.btn-card-action:hover {
    color: white;
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    border-color: transparent;
}

.btn-card-action:hover::before {
    opacity: 1;
}

.btn-card-action:hover i {
    transform: scale(1.1);
}

.btn-card-action:active {
    transform: translateY(0) scale(1);
    transition: all 0.1s ease;
}

.priority, .status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.priority-low { background: #dcfce7; color: #166534; }
.priority-medium { background: #fef3c7; color: #92400e; }
.priority-high { background: #fed7aa; color: #c2410c; }
.priority-urgent { background: #fecaca; color: #dc2626; }

.status-pending { background: #e0e7ff; color: #3730a3; }
.status-in_progress { background: #dbeafe; color: #1e40af; }
.status-completed { background: #dcfce7; color: #166534; }
.status-cancelled { background: #fecaca; color: #dc2626; }

.task-description {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 20px;
}

.task-dates {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.date-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    font-size: 0.875rem;
}

.date-item i {
    color: #6366f1;
}

.task-actions {
    display: flex;
    gap: 10px;
}

.btn-small {
    padding: 8px 16px;
    font-size: 0.875rem;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-small.btn-primary {
    background: #6366f1;
    color: white;
}

.btn-small.btn-secondary {
    background: #64748b;
    color: white;
}

.btn-small:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.empty-state-modern {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
}

.empty-state-modern h3 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 1.5rem;
    font-weight: 600;
}

.empty-state-modern p {
    margin: 0 0 2rem 0;
    color: #6b7280;
    font-size: 1.1rem;
}

.btn-create-task {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-create-task:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
}

@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .header-actions {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    
    .section-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .task-filters {
        width: 100%;
        justify-content: center;
    }
    
    .overview-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .tasks-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .tasks-section {
        padding: 1.5rem;
    }
    
    .task-card-header,
    .task-card-body,
    .task-card-footer {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    
    .task-card:hover {
        transform: translateY(-8px) scale(1.02);
    }
}

@media (max-width: 480px) {
    .overview-cards {
        grid-template-columns: 1fr;
    }
    
    .task-filters {
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .filter-btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    
    .tasks-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    
    .task-card {
        border-radius: 20px;
    }
    
    .task-card-title {
        font-size: 1.25rem;
    }
    
    .btn-card-action {
        padding: 0.75rem;
        font-size: 0.85rem;
    }
}
</style>

<script>
function exportTasks() {
    window.location.href = 'index.php?action=export_my_tasks';
}

function refreshTasks() {
    Toast.fire({
        icon: 'info',
        title: 'Refreshing tasks...'
    });
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

function filterTasks(status) {
    const cards = document.querySelectorAll('.task-card');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update active button
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.closest('.filter-btn').classList.add('active');
    
    // Filter cards with animation
    cards.forEach(card => {
        const cardStatus = card.classList.contains('pending') ? 'pending' : 
                          card.classList.contains('in_progress') ? 'in_progress' : 
                          card.classList.contains('completed') ? 'completed' : 'pending';
        
        if (status === 'all' || cardStatus === status) {
            card.style.display = 'block';
            card.style.animation = 'slideInCard 0.5s ease-out';
        } else {
            card.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => {
                card.style.display = 'none';
            }, 300);
        }
    });
}

// Add fade out animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.95); }
    }
`;
document.head.appendChild(style);

// Initialize filter on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add staggered animation to cards
    const cards = document.querySelectorAll('.task-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>

<?php include 'includes/footer.php'; ?>