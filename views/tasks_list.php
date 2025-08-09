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
$status_filter = isset($_GET['status']) ? implode(',', (array)$_GET['status']) : '';
$priority_filter = isset($_GET['priority']) ? implode(',', (array)$_GET['priority']) : '';
$assigned_filter = isset($_GET['assigned']) ? implode(',', (array)$_GET['assigned']) : '';
$search = $_GET['search'] ?? '';
$start_date_filter = $_GET['start_date'] ?? '';
$due_date_filter = $_GET['due_date'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;

// Build query with filters
$where_conditions = [];
$params = [];

if ($status_filter) {
    $status_ids = explode(',', $status_filter);
    $placeholders = str_repeat('?,', count($status_ids) - 1) . '?';
    $where_conditions[] = "t.status_id IN ($placeholders)";
    $params = array_merge($params, $status_ids);
}
if ($priority_filter) {
    $priorities = explode(',', $priority_filter);
    $placeholders = str_repeat('?,', count($priorities) - 1) . '?';
    $where_conditions[] = "t.priority IN ($placeholders)";
    $params = array_merge($params, $priorities);
}
if ($assigned_filter !== '') {
    $assigned_ids = explode(',', $assigned_filter);
    $assigned_conditions = [];
    foreach ($assigned_ids as $assigned_id) {
        if ($assigned_id === '0') {
            $assigned_conditions[] = "t.assigned_to IS NULL";
        } else {
            $assigned_conditions[] = "t.assigned_to = ?";
            $params[] = $assigned_id;
        }
    }
    if (!empty($assigned_conditions)) {
        $where_conditions[] = "(" . implode(" OR ", $assigned_conditions) . ")";
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
        <a href="#" onclick="exportWithFilters()" class="btn-modern btn-outline">
            <i class="fas fa-download"></i> Export CSV
        </a>
        <a href="index.php" class="btn-modern btn-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<div class="tasks-container">
    <!-- Advanced Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form" id="filtersForm">
            <input type="hidden" name="action" value="tasks_list">
            
            <div class="filters-row" style="width: 100%;">
                <div class="filter-item">
                    <label>Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search tasks..." class="filter-input">
                </div>
                
                <div class="filter-item">
                    <label>Status</label>
                    <div class="select-wrapper">
                        <div class="select-display" onclick="toggleDropdown('status')">
                            <span id="status-display">All Status</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="select-dropdown" id="status-dropdown">
                            <?php foreach ($allStatuses as $status): ?>
                            <label class="option-item">
                                <input type="checkbox" name="status[]" value="<?php echo $status['id']; ?>" 
                                       <?php echo in_array($status['id'], explode(',', $status_filter)) ? 'checked' : ''; ?>
                                       onchange="updateDisplay('status')">
                                <span class="option-text" style="color: <?php echo $status['color']; ?>">
                                    <i class="fas fa-circle"></i> <?php echo htmlspecialchars($status['name']); ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="filter-item">
                    <label>Priority</label>
                    <div class="select-wrapper">
                        <div class="select-display" onclick="toggleDropdown('priority')">
                            <span id="priority-display">All Priority</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="select-dropdown" id="priority-dropdown">
                            <label class="option-item">
                                <input type="checkbox" name="priority[]" value="low" 
                                       <?php echo in_array('low', explode(',', $priority_filter)) ? 'checked' : ''; ?>
                                       onchange="updateDisplay('priority')">
                                <span class="option-text priority-low">Low</span>
                            </label>
                            <label class="option-item">
                                <input type="checkbox" name="priority[]" value="medium" 
                                       <?php echo in_array('medium', explode(',', $priority_filter)) ? 'checked' : ''; ?>
                                       onchange="updateDisplay('priority')">
                                <span class="option-text priority-medium">Medium</span>
                            </label>
                            <label class="option-item">
                                <input type="checkbox" name="priority[]" value="high" 
                                       <?php echo in_array('high', explode(',', $priority_filter)) ? 'checked' : ''; ?>
                                       onchange="updateDisplay('priority')">
                                <span class="option-text priority-high">High</span>
                            </label>
                            <label class="option-item">
                                <input type="checkbox" name="priority[]" value="urgent" 
                                       <?php echo in_array('urgent', explode(',', $priority_filter)) ? 'checked' : ''; ?>
                                       onchange="updateDisplay('priority')">
                                <span class="option-text priority-urgent">Urgent</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="filters-row" style="width: 100%;">         
                <div class="filter-item">
                    <label>Assigned To</label>
                    <div class="select-wrapper">
                        <div class="select-display" onclick="toggleDropdown('assigned')">
                            <span id="assigned-display">All Users</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="select-dropdown" id="assigned-dropdown">
                            <label class="option-item">
                                <input type="checkbox" name="assigned[]" value="0" 
                                       <?php echo in_array('0', explode(',', $assigned_filter)) ? 'checked' : ''; ?>
                                       onchange="updateDisplay('assigned')">
                                <span class="option-text">Unassigned</span>
                            </label>
                            <?php 
                            $users->execute();
                            while ($u = $users->fetch(PDO::FETCH_ASSOC)): 
                            ?>
                            <label class="option-item">
                                <input type="checkbox" name="assigned[]" value="<?php echo $u['id']; ?>" 
                                       <?php echo in_array($u['id'], explode(',', $assigned_filter)) ? 'checked' : ''; ?>
                                       onchange="updateDisplay('assigned')">
                                <span class="option-text"><?php echo htmlspecialchars($u['full_name']); ?></span>
                            </label>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <div class="filter-item">
                    <label>Start Date From</label>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date_filter); ?>" class="filter-input">
                </div>
                
                <div class="filter-item">
                    <label>Due Date Until</label>
                    <input type="date" name="due_date" value="<?php echo htmlspecialchars($due_date_filter); ?>" class="filter-input">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn-apply">
                        <i class="fas fa-search"></i> Apply
                    </button>
                    <button type="button" class="btn-clear" onclick="clearAllFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
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
                        <?php if (isset($taskItem['start_date']) && $taskItem['start_date']): ?>
                        <span><i class="fas fa-play"></i> Start: <?php echo date('M j, Y', strtotime($taskItem['start_date'])); ?></span>
                        <?php endif; ?>
                        <span><i class="fas fa-calendar"></i> Created: <?php echo date('M j, Y', strtotime($taskItem['created_at'])); ?></span>
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


function toggleDropdown(type) {
    try {
        const dropdown = document.getElementById(type + '-dropdown');
        if (!dropdown) return;
        
        const allDropdowns = document.querySelectorAll('.select-dropdown');
        
        allDropdowns.forEach(d => {
            if (d && d !== dropdown) {
                d.classList.remove('show');
            }
        });
        
        dropdown.classList.toggle('show');
    } catch (error) {
        console.warn('Dropdown toggle error:', error);
    }
}

function updateDisplay(type) {
    try {
        const checkboxes = document.querySelectorAll(`input[name="${type}[]"]`);
        const display = document.getElementById(type + '-display');
        
        if (!display || !checkboxes || !checkboxes.length) return;
        
        const checked = Array.from(checkboxes).filter(cb => cb && cb.checked);
        
        if (checked.length === 0) {
            display.textContent = type === 'status' ? 'All Status' : 
                                 type === 'priority' ? 'All Priority' : 'All Users';
            display.className = 'placeholder';
        } else if (checked.length === 1) {
            const textElement = checked[0].nextElementSibling;
            display.textContent = textElement ? textElement.textContent.trim() : `1 selected`;
            display.className = 'selected';
        } else {
            display.textContent = `${checked.length} selected`;
            display.className = 'selected multiple';
        }
    } catch (error) {
        console.warn('Display update error:', error);
    }
}

function clearAllFilters() {
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[type="text"], input[type="date"]').forEach(input => input.value = '');
    updateDisplay('status');
    updateDisplay('priority');
    updateDisplay('assigned');
    // Auto-submit after clearing
    document.getElementById('filtersForm').submit();
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.select-wrapper')) {
        document.querySelectorAll('.select-dropdown').forEach(d => d.classList.remove('show'));
    }
});

// Initialize displays on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        try {
            const statusDisplay = document.getElementById('status-display');
            const priorityDisplay = document.getElementById('priority-display');
            const assignedDisplay = document.getElementById('assigned-display');
            
            if (statusDisplay) updateDisplay('status');
            if (priorityDisplay) updateDisplay('priority');
            if (assignedDisplay) updateDisplay('assigned');
        } catch (error) {
            // Silently handle errors
        }
    }, 100);
});

function exportWithFilters() {
    const form = document.getElementById('filtersForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (key !== 'action') {
            params.append(key, value);
        }
    }
    
    window.location.href = 'index.php?action=export_csv&' + params.toString();
}

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