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

    <div class="users-grid">
        <?php foreach ($users as $u): 
            $userTasks = $task->getUserTaskStats($u['id']);
        ?>
        <div class="user-card <?= $u['status'] === 'inactive' ? 'inactive' : '' ?>">
            <div class="user-header">
                <div class="user-avatar-large">
                    <?= strtoupper(substr($u['full_name'], 0, 2)) ?>
                </div>
                <div class="user-info">
                    <h3 class="user-name"><?= htmlspecialchars($u['full_name']) ?></h3>
                    <p class="user-username">@<?= htmlspecialchars($u['username']) ?></p>
                    <span class="role-badge role-<?= $u['role'] ?>">
                        <i class="fas fa-<?= $u['role'] === 'admin' ? 'crown' : ($u['role'] === 'team_lead' ? 'star' : 'user') ?>"></i>
                        <?= ucfirst(str_replace('_', ' ', $u['role'])) ?>
                    </span>
                </div>
                <div class="user-status">
                    <span class="status-indicator <?= $u['status'] ?? 'active' ?>">
                        <?= ($u['status'] ?? 'active') === 'active' ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
            </div>
            
            <div class="user-stats">
                <div class="stat-item">
                    <div class="stat-number"><?= $userTasks['total'] ?? 0 ?></div>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $userTasks['completed'] ?? 0 ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $userTasks['pending'] ?? 0 ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            
            <div class="user-meta">
                <div class="meta-item">
                    <i class="fas fa-envelope"></i>
                    <?= htmlspecialchars($u['email']) ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    Joined <?= date('M j, Y', strtotime($u['created_at'])) ?>
                </div>
            </div>
            
            <div class="user-actions">
                <button class="btn-action btn-view" onclick="viewUserDashboard(<?= $u['id'] ?>)" title="View Dashboard">
                    <i class="fas fa-chart-pie"></i>
                </button>
                <button class="btn-action btn-edit" onclick="editUser(<?= $u['id'] ?>)" title="Edit User">
                    <i class="fas fa-edit"></i>
                </button>
                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                <button class="btn-action <?= ($u['status'] ?? 'active') === 'active' ? 'btn-deactivate' : 'btn-activate' ?>" 
                        onclick="toggleUserStatus(<?= $u['id'] ?>, '<?= ($u['status'] ?? 'active') === 'active' ? 'inactive' : 'active' ?>')" 
                        title="<?= ($u['status'] ?? 'active') === 'active' ? 'Deactivate' : 'Activate' ?> User">
                    <i class="fas fa-<?= ($u['status'] ?? 'active') === 'active' ? 'user-slash' : 'user-check' ?>"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
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

function viewUserDashboard(id) {
    fetch(`controllers/user_controller.php?action=dashboard&id=${id}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('userDashboardContent').innerHTML = html;
            document.getElementById('userDashboardModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
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
</script>

<?php include 'includes/footer.php'; ?>