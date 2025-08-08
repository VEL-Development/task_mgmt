<?php
require_once 'models/TaskEnhanced.php';
require_once 'models/User.php';
require_once 'models/TaskStatus.php';

$task = new TaskEnhanced($db);
$user = new User($db);
$users = $user->read();

$statusModel = new TaskStatus($db);
$allStatuses = $statusModel->getAllStatuses();

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$assigned_filter = $_GET['assigned'] ?? '';
$search = $_GET['search'] ?? '';
$start_date_filter = $_GET['start_date'] ?? '';
$due_date_filter = $_GET['due_date'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;

// Build query with filters
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "t.status_id = ?";
    $params[] = $status_filter;
}
if ($priority_filter) {
    $where_conditions[] = "t.priority = ?";
    $params[] = $priority_filter;
}
if ($assigned_filter !== '') {
    if ($assigned_filter === '0') {
        $where_conditions[] = "t.assigned_to IS NULL";
    } else {
        $where_conditions[] = "t.assigned_to = ?";
        $params[] = $assigned_filter;
    }
}
if ($search) {
    $where_conditions[] = "(t.title LIKE ? OR t.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($start_date_filter) {
    $where_conditions[] = "DATE(t.start_date) >= ?";
    $params[] = $start_date_filter;
}
if ($due_date_filter) {
    $where_conditions[] = "DATE(t.due_date) <= ?";
    $params[] = $due_date_filter;
}

$tasks = $task->getTasksWithPagination($where_conditions, $params, $page, $per_page);
$total_tasks = $task->getTasksCount($where_conditions, $params);
$total_pages = ceil($total_tasks / $per_page);

$page_title = "Tasks List";
include 'includes/header.php';
?>

<div class="header-section">
    <h1 class="page-title"><i class="fas fa-list"></i> Tasks List</h1>
    <div style="display: flex; gap: 1rem;">
        <a href="index.php?action=create_task" class="btn-modern btn-primary">
            <i class="fas fa-plus"></i> New Task
        </a>
        <a href="index.php?action=export_csv&status=<?php echo $status_filter; ?>&priority=<?php echo $priority_filter; ?>&assigned=<?php echo $assigned_filter; ?>&search=<?php echo urlencode($search); ?>&start_date=<?php echo $start_date_filter; ?>&due_date=<?php echo $due_date_filter; ?>" class="btn-modern btn-outline">
            <i class="fas fa-download"></i> Export CSV
        </a>
        <a href="index.php" class="btn-modern btn-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<div class="tasks-container">
    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="action" value="tasks_list">
            
            <div class="filter-group">
                <label><i class="fas fa-search"></i> Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search tasks...">
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-flag"></i> Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    <?php foreach ($allStatuses as $status): ?>
                        <option value="<?php echo $status['id']; ?>" <?php echo $status_filter == $status['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-exclamation-triangle"></i> Priority</label>
                <select name="priority">
                    <option value="">All Priority</option>
                    <option value="low" <?php echo $priority_filter == 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo $priority_filter == 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo $priority_filter == 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="urgent" <?php echo $priority_filter == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-user"></i> Assigned To</label>
                <select name="assigned">
                    <option value="">All Users</option>
                    <option value="0" <?php echo $assigned_filter === '0' ? 'selected' : ''; ?>>Unassigned</option>
                    <?php 
                    $users->execute();
                    while ($u = $users->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                    <option value="<?php echo $u['id']; ?>" <?php echo $assigned_filter == $u['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['full_name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-calendar-plus"></i> Start Date From</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date_filter); ?>">
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-calendar-times"></i> Due Date Until</label>
                <input type="date" name="due_date" value="<?php echo htmlspecialchars($due_date_filter); ?>">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="index.php?action=tasks_list" class="btn-filter btn-clear">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tasks List -->
    <div class="tasks-list">
        <div class="list-header">
            <h3>Tasks (<?php echo $total_tasks; ?> total, showing <?php echo count($tasks); ?>)</h3>
        </div>
        
        <?php if (count($tasks) > 0): ?>
        <div class="task-items">
            <?php foreach ($tasks as $taskItem): ?>
            <div class="task-item">
                <div class="task-main">
                    <div class="task-title">
                        <a href="index.php?action=task_details&id=<?php echo $taskItem['id']; ?>">
                            <?php echo htmlspecialchars($taskItem['title']); ?>
                        </a>
                    </div>
                    <div class="task-description">
                        <?php echo $taskItem['description'] ? substr(htmlspecialchars($taskItem['description']), 0, 100) . '...' : 'No description'; ?>
                    </div>
                    <div class="task-meta">
                        <span><i class="fas fa-user"></i> <?php echo $taskItem['assigned_name'] ?: 'Unassigned'; ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($taskItem['created_at'])); ?></span>
                        <?php if ($taskItem['due_date']): ?>
                        <span class="<?php echo strtotime($taskItem['due_date']) < strtotime('today') ? 'overdue' : ''; ?>">
                            <i class="fas fa-clock"></i> Due: <?php echo date('M j, Y', strtotime($taskItem['due_date'])); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="task-badges">
                    <span class="status-badge status-<?php echo $taskItem['group_status'] ?? 'pending'; ?>" style="color: <?php echo $taskItem['status_color'] ?? '#6366f1'; ?>">
                        <?php echo htmlspecialchars($taskItem['status_name'] ?? 'Unknown'); ?>
                    </span>
                    <span class="priority-badge priority-<?php echo $taskItem['priority']; ?>">
                        <?php echo ucfirst($taskItem['priority']); ?>
                    </span>
                </div>
                <div class="task-actions">
                    <a href="index.php?action=task_details&id=<?php echo $taskItem['id']; ?>" class="btn-task-action">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="index.php?action=edit_task&id=<?php echo $taskItem['id']; ?>" class="btn-task-action">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="deleteTask(<?php echo $taskItem['id']; ?>)" class="btn-task-action btn-delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?action=tasks_list&page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&priority=<?php echo $priority_filter; ?>&assigned=<?php echo $assigned_filter; ?>&search=<?php echo urlencode($search); ?>&start_date=<?php echo $start_date_filter; ?>&due_date=<?php echo $due_date_filter; ?>" class="page-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
            <a href="?action=tasks_list&page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&priority=<?php echo $priority_filter; ?>&assigned=<?php echo $assigned_filter; ?>&search=<?php echo urlencode($search); ?>&start_date=<?php echo $start_date_filter; ?>&due_date=<?php echo $due_date_filter; ?>" 
               class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
            <a href="?action=tasks_list&page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&priority=<?php echo $priority_filter; ?>&assigned=<?php echo $assigned_filter; ?>&search=<?php echo urlencode($search); ?>&start_date=<?php echo $start_date_filter; ?>&due_date=<?php echo $due_date_filter; ?>" class="page-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="no-tasks">
            <i class="fas fa-tasks"></i>
            <h3>No tasks found</h3>
            <p>No tasks match your current filters.</p>
            <a href="index.php?action=create_task" class="btn-modern btn-primary">
                <i class="fas fa-plus"></i> Create First Task
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteTask(taskId) {
    Swal.fire({
        title: 'Delete Task?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?action=tasks';
            form.innerHTML = `
                <input type="hidden" name="task_action" value="delete">
                <input type="hidden" name="task_id" value="${taskId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>