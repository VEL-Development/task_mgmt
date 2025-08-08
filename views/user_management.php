<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?action=dashboard");
    exit;
}

require_once 'models/User.php';
require_once 'models/TaskEnhanced.php';
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$task = new TaskEnhanced($db);
$users = $user->getAllUsers();
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="header-section">
        <div class="page-title-section">
            <h1 class="page-title"><i class="fas fa-users-cog"></i> User Management</h1>
            <p class="page-subtitle">Manage team members, roles, and permissions</p>
        </div>
        <button class="btn-modern btn-primary btn-add-user" onclick="showAddUserModal()">
            <i class="fas fa-user-plus"></i> Add New User
        </button>
    </div>

    <div class="users-container">
        <div class="users-header">
            <div class="users-stats">
                <?php 
                $totalUsers = count($users);
                $activeUsers = count(array_filter($users, fn($u) => ($u['status'] ?? 'active') === 'active'));
                $adminCount = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
                ?>
                <div class="stat-card-mini">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?= $totalUsers ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
                <div class="stat-card-mini">
                    <div class="stat-icon active"><i class="fas fa-user-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?= $activeUsers ?></div>
                        <div class="stat-label">Active Users</div>
                    </div>
                </div>
                <div class="stat-card-mini">
                    <div class="stat-icon admin"><i class="fas fa-crown"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?= $adminCount ?></div>
                        <div class="stat-label">Administrators</div>
                    </div>
                </div>
            </div>
            
            <div class="users-filters">
                <div class="filter-group">
                    <select id="roleFilter" onchange="filterUsers()">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="team_lead">Team Lead</option>
                        <option value="member">Member</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select id="statusFilter" onchange="filterUsers()">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

            </div>
        </div>
        
        <div class="users-grid" id="usersGrid">
            <?php foreach ($users as $u): 
                $userTasks = $task->getUserTaskStats($u['id']);
                $completionRate = $userTasks['total'] > 0 ? round(($userTasks['completed'] / $userTasks['total']) * 100) : 0;
            ?>
            <div class="user-card <?= ($u['status'] ?? 'active') === 'inactive' ? 'inactive' : '' ?>" 
                 data-role="<?= $u['role'] ?>" data-status="<?= $u['status'] ?? 'active' ?>">
                
                <div class="user-card-header">
                    <div class="user-avatar-container">
                        <div class="user-avatar-large role-<?= $u['role'] ?>">
                            <?= strtoupper(substr($u['full_name'], 0, 2)) ?>
                        </div>
                        <div class="status-dot <?= ($u['status'] ?? 'active') === 'active' ? 'active' : 'inactive' ?>"></div>
                    </div>
                    
                    <div class="user-info">
                        <h3 class="user-name"><?= htmlspecialchars($u['full_name']) ?></h3>
                        <p class="user-username">@<?= htmlspecialchars($u['username']) ?></p>
                        <div class="user-badges">
                            <span class="role-badge role-<?= $u['role'] ?>">
                                <i class="fas fa-<?= $u['role'] === 'admin' ? 'crown' : ($u['role'] === 'team_lead' ? 'star' : 'user') ?>"></i>
                                <?= ucfirst(str_replace('_', ' ', $u['role'])) ?>
                            </span>
                            <span class="status-badge status-<?= ($u['status'] ?? 'active') === 'active' ? 'active' : 'inactive' ?>">
                                <i class="fas fa-<?= ($u['status'] ?? 'active') === 'active' ? 'check-circle' : 'times-circle' ?>"></i>
                                <?= ($u['status'] ?? 'active') === 'active' ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    </div>
                    

                </div>
                
                <div class="user-performance">
                    <div class="performance-header">
                        <h4><i class="fas fa-chart-line"></i> Performance Overview</h4>
                        <div class="completion-rate">
                            <span class="rate-number"><?= $completionRate ?>%</span>
                            <span class="rate-label">Completion</span>
                        </div>
                    </div>
                    
                    <div class="task-stats-grid">
                        <div class="stat-item total">
                            <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                            <div class="stat-data">
                                <div class="stat-number"><?= $userTasks['total'] ?? 0 ?></div>
                                <div class="stat-label">Total</div>
                            </div>
                        </div>
                        <div class="stat-item completed">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-data">
                                <div class="stat-number"><?= $userTasks['completed'] ?? 0 ?></div>
                                <div class="stat-label">Done</div>
                            </div>
                        </div>
                        <div class="stat-item progress">
                            <div class="stat-icon"><i class="fas fa-clock"></i></div>
                            <div class="stat-data">
                                <div class="stat-number"><?= $userTasks['in_progress'] ?? 0 ?></div>
                                <div class="stat-label">Active</div>
                            </div>
                        </div>
                        <div class="stat-item pending">
                            <div class="stat-icon"><i class="fas fa-pause-circle"></i></div>
                            <div class="stat-data">
                                <div class="stat-number"><?= isset($userTasks['pending']) ? $userTasks['pending'] : 0 ?></div>
                                <div class="stat-label">Pending</div>
                            </div>
                        </div>
                        <div class="stat-item cancelled">
                            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                            <div class="stat-data">
                                <div class="stat-number"><?= isset($userTasks['cancelled']) ? $userTasks['cancelled'] : 0 ?></div>
                                <div class="stat-label">Cancelled</div>
                            </div>
                        </div>
                        <div class="stat-item overdue">
                            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="stat-data">
                                <div class="stat-number"><?= isset($userTasks['overdue']) ? $userTasks['overdue'] : 0 ?></div>
                                <div class="stat-label">Overdue</div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($userTasks['total'] > 0): ?>
                    <div class="progress-bar-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $completionRate ?>%"></div>
                        </div>
                        <div class="progress-labels">
                            <span>Progress</span>
                            <span><?= $completionRate ?>% Complete</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="user-meta">
                    <div class="meta-row">
                        <div class="meta-item">
                            <i class="fas fa-envelope"></i>
                            <span><?= htmlspecialchars($u['email']) ?></span>
                        </div>
                    </div>
                    <div class="meta-row">
                        <div class="meta-item">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Joined <?= date('M j, Y', strtotime($u['created_at'])) ?></span>
                        </div>
                        <?php if (($u['status'] ?? 'active') === 'active'): ?>
                        <div class="meta-item">
                            <i class="fas fa-circle online-indicator"></i>
                            <span>Online</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="user-actions">
                    <button class="btn-action-primary" onclick="window.open('index.php?action=user_dashboard&user_id=<?= $u['id'] ?>', '_blank')">
                        <i class="fas fa-chart-pie"></i>
                        <span>View Dashboard</span>
                    </button>
                    <button class="btn-action-secondary" onclick="editUser(<?= $u['id'] ?>)">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </button>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                    <button class="btn-action-<?= ($u['status'] ?? 'active') === 'active' ? 'danger' : 'success' ?>" 
                            onclick="toggleUserStatus(<?= $u['id'] ?>, '<?= ($u['status'] ?? 'active') === 'active' ? 'inactive' : 'active' ?>')">
                        <i class="fas fa-<?= ($u['status'] ?? 'active') === 'active' ? 'user-slash' : 'user-check' ?>"></i>
                        <span><?= ($u['status'] ?? 'active') === 'active' ? 'Deactivate' : 'Activate' ?></span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        

        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content modal-enhanced">
        <div class="modal-header">
            <div class="modal-title">
                <div class="modal-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h3>Add New User</h3>
                    <p>Create a new team member account</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('addUserModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addUserForm" class="modal-form">
            <div class="form-section">
                <h4><i class="fas fa-user"></i> Personal Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Full Name</label>
                        <input type="text" name="full_name" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-at"></i> Username</label>
                        <input type="text" name="username" placeholder="Enter username" required>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" placeholder="Enter email address" required>
                </div>
            </div>
            
            <div class="form-section">
                <h4><i class="fas fa-lock"></i> Security & Access</h4>
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Password</label>
                    <div class="password-input">
                        <input type="password" name="password" placeholder="Enter password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user-tag"></i> Role</label>
                    <div class="role-selector">
                        <input type="radio" name="role" value="member" id="role_member" checked>
                        <label for="role_member" class="role-option">
                            <i class="fas fa-user"></i>
                            <span>Member</span>
                            <small>Basic access to assigned tasks</small>
                        </label>
                        
                        <input type="radio" name="role" value="team_lead" id="role_lead">
                        <label for="role_lead" class="role-option">
                            <i class="fas fa-star"></i>
                            <span>Team Lead</span>
                            <small>Manage team tasks and members</small>
                        </label>
                        
                        <input type="radio" name="role" value="admin" id="role_admin">
                        <label for="role_admin" class="role-option">
                            <i class="fas fa-crown"></i>
                            <span>Admin</span>
                            <small>Full system access and control</small>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('addUserModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Create User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content modal-enhanced">
        <div class="modal-header">
            <div class="modal-title">
                <div class="modal-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div>
                    <h3>Edit User</h3>
                    <p>Update user information and permissions</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('editUserModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editUserForm" class="modal-form">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="form-section">
                <h4><i class="fas fa-user"></i> Personal Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Full Name</label>
                        <input type="text" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-at"></i> Username</label>
                        <input type="text" name="username" id="edit_username" required>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
            </div>
            
            <div class="form-section">
                <h4><i class="fas fa-user-tag"></i> Role & Permissions</h4>
                <div class="form-group">
                    <div class="role-selector" id="edit_role_selector">
                        <!-- Role options will be populated by JavaScript -->
                    </div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('editUserModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Update User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- User Dashboard Modal -->
<div id="userDashboardModal" class="modal modal-large">
    <div class="modal-content modal-enhanced">
        <div class="modal-header">
            <div class="modal-title">
                <div class="modal-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <h3 id="dashboard_user_name">User Dashboard</h3>
                    <p>Task overview and performance metrics</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('userDashboardModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="userDashboardContent">
                <!-- Dashboard content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showAddUserModal() {
    document.getElementById('addUserModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

function togglePassword(button) {
    const input = button.previousElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function editUser(id) {
    fetch(`controllers/user_controller.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(user => {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_full_name').value = user.full_name;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            
            // Set role
            const roleSelector = document.getElementById('edit_role_selector');
            roleSelector.innerHTML = `
                <input type="radio" name="role" value="member" id="edit_role_member" ${user.role === 'member' ? 'checked' : ''}>
                <label for="edit_role_member" class="role-option">
                    <i class="fas fa-user"></i>
                    <span>Member</span>
                    <small>Basic access to assigned tasks</small>
                </label>
                
                <input type="radio" name="role" value="team_lead" id="edit_role_lead" ${user.role === 'team_lead' ? 'checked' : ''}>
                <label for="edit_role_lead" class="role-option">
                    <i class="fas fa-star"></i>
                    <span>Team Lead</span>
                    <small>Manage team tasks and members</small>
                </label>
                
                <input type="radio" name="role" value="admin" id="edit_role_admin" ${user.role === 'admin' ? 'checked' : ''}>
                <label for="edit_role_admin" class="role-option">
                    <i class="fas fa-crown"></i>
                    <span>Admin</span>
                    <small>Full system access and control</small>
                </label>
            `;
            
            document.getElementById('editUserModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
}

function toggleUserStatus(id, status) {
    const action = status === 'inactive' ? 'Deactivate' : 'Activate';
    const icon = status === 'inactive' ? 'user-slash' : 'user-check';
    
    Swal.fire({
        title: `${action} User?`,
        text: `Are you sure you want to ${action.toLowerCase()} this user?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'inactive' ? '#ef4444' : '#10b981',
        confirmButtonText: `<i class="fas fa-${icon}"></i> ${action}`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('controllers/user_controller.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=toggle_status&id=${id}&status=${status}`
            }).then(response => response.text()).then(result => {
                if (result === 'success') {
                    Swal.fire('Success!', `User ${action.toLowerCase()}d successfully`, 'success');
                    location.reload();
                } else {
                    Swal.fire('Error!', `Failed to ${action.toLowerCase()} user`, 'error');
                }
            });
        }
    });
}





function filterUsers() {
    const roleFilter = document.getElementById('roleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const userCards = document.querySelectorAll('.user-card');
    
    userCards.forEach(card => {
        const role = card.dataset.role;
        const status = card.dataset.status;
        
        const roleMatch = !roleFilter || role === roleFilter;
        const statusMatch = !statusFilter || status === statusFilter;
        
        if (roleMatch && statusMatch) {
            card.style.display = 'block';
            card.style.animation = 'fadeIn 0.3s ease';
        } else {
            card.style.display = 'none';
        }
    });
}

function viewUserDashboard(id) {
    // Show loading state
    document.getElementById('userDashboardContent').innerHTML = `
        <div class="loading-state">
            <div class="spinner"></div>
            <p>Loading user dashboard...</p>
        </div>
    `;
    document.getElementById('userDashboardModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    fetch(`controllers/user_controller.php?action=dashboard&id=${id}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('userDashboardContent').innerHTML = html;
            // Initialize dashboard charts if needed
            initializeDashboardCharts();
        })
        .catch(error => {
            document.getElementById('userDashboardContent').innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load dashboard</p>
                </div>
            `;
        });
}

function initializeDashboardCharts() {
    // Initialize any charts in the dashboard modal
    const chartElements = document.querySelectorAll('.chart-container');
    chartElements.forEach(element => {
        // Chart initialization code would go here
    });
}

// Form submissions
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'create');
    
    fetch('controllers/user_controller.php', {
        method: 'POST',
        body: formData
    }).then(response => response.text()).then(result => {
        if (result === 'success') {
            Swal.fire({
                title: 'Success!',
                text: 'User created successfully',
                icon: 'success',
                confirmButtonColor: '#10b981'
            });
            closeModal('addUserModal');
            this.reset();
            location.reload();
        } else {
            Swal.fire('Error!', 'Failed to create user', 'error');
        }
    });
});

document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'update');
    
    fetch('controllers/user_controller.php', {
        method: 'POST',
        body: formData
    }).then(response => response.text()).then(result => {
        if (result === 'success') {
            Swal.fire({
                title: 'Success!',
                text: 'User updated successfully',
                icon: 'success',
                confirmButtonColor: '#10b981'
            });
            closeModal('editUserModal');
            location.reload();
        } else {
            Swal.fire('Error!', 'Failed to update user', 'error');
        }
    });
});

// Close modal on outside click
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    

}

// Add keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close all modals
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = 'auto';
        

    }
});

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth animations to cards one by one
    const cards = document.querySelectorAll('.user-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 200);
    });
});
</script>

<?php include 'includes/footer.php'; ?>