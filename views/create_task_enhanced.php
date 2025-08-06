<?php
require_once 'models/User.php';

$user = new User($db);
$users = $user->read();

$page_title = "Create New Task";
include 'includes/header.php';
?>

<div class="header-section">
    <h1 class="page-title"><i class="fas fa-plus"></i> Create New Task</h1>
    <a href="index.php" class="btn-modern btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="form-container-modern">
    <div class="form-card-enhanced edit-mode">
        <div class="form-header-edit">
            <div class="task-status-indicator status-pending">
                <i class="fas fa-plus"></i>
                <span>New Task</span>
            </div>
            <h2><i class="fas fa-plus"></i> Create New Task</h2>
            <p>Add a new task to your project</p>
        </div>
        
        <form method="POST" action="index.php?action=tasks" id="taskForm">
            <input type="hidden" name="task_action" value="create">
            
            <div class="form-content-edit">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i> Basic Information
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="title" class="form-label-modern">
                            <i class="fas fa-heading"></i> Task Title *
                        </label>
                        <input type="text" id="title" name="title" class="form-input-modern" required 
                               placeholder="Enter a descriptive task title">
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="description" class="form-label-modern">
                            <i class="fas fa-align-left"></i> Description
                        </label>
                        <textarea id="description" name="description" class="form-textarea-modern" rows="4"
                                  placeholder="Describe the task in detail..."></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-cogs"></i> Status & Priority
                    </div>
                    
                    <div class="form-row-modern">
                        <div class="form-group-modern">
                            <label for="status" class="form-label-modern">
                                <i class="fas fa-flag"></i> Status
                            </label>
                            <div class="status-selector">
                                <input type="radio" id="status_pending" name="status" value="pending" checked>
                                <label for="status_pending" class="status-option pending">
                                    <i class="fas fa-clock"></i> Pending
                                </label>
                                
                                <input type="radio" id="status_progress" name="status" value="in_progress">
                                <label for="status_progress" class="status-option progress">
                                    <i class="fas fa-spinner"></i> In Progress
                                </label>
                                
                                <input type="radio" id="status_completed" name="status" value="completed">
                                <label for="status_completed" class="status-option completed">
                                    <i class="fas fa-check-circle"></i> Completed
                                </label>
                                
                                <input type="radio" id="status_cancelled" name="status" value="cancelled">
                                <label for="status_cancelled" class="status-option cancelled">
                                    <i class="fas fa-times-circle"></i> Cancelled
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group-modern">
                            <label for="priority" class="form-label-modern">
                                <i class="fas fa-exclamation-triangle"></i> Priority
                            </label>
                            <div class="priority-selector">
                                <input type="radio" id="low" name="priority" value="low">
                                <label for="low" class="priority-option low">Low</label>
                                
                                <input type="radio" id="medium" name="priority" value="medium" checked>
                                <label for="medium" class="priority-option medium">Medium</label>
                                
                                <input type="radio" id="high" name="priority" value="high">
                                <label for="high" class="priority-option high">High</label>
                                
                                <input type="radio" id="urgent" name="priority" value="urgent">
                                <label for="urgent" class="priority-option urgent">Urgent</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row-modern">
                        <div class="form-group-modern">
                            <label for="start_date" class="form-label-modern">
                                <i class="fas fa-play"></i> Start Date
                            </label>
                            <input type="date" id="start_date" name="start_date" class="form-input-modern" 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group-modern">
                            <label for="due_date" class="form-label-modern">
                                <i class="fas fa-calendar-alt"></i> Due Date
                            </label>
                            <input type="date" id="due_date" name="due_date" class="form-input-modern">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-users"></i> Task Assignment
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="assigned_to" class="form-label-modern">
                            <i class="fas fa-user"></i> Assign To
                        </label>
                        <div class="user-selector">
                            <div class="user-option">
                                <input type="radio" id="unassigned" name="assigned_to" value="" checked>
                                <label for="unassigned" class="user-card">
                                    <div class="user-avatar unassigned">?</div>
                                    <div class="user-info">
                                        <div class="user-name">Unassigned</div>
                                        <div class="user-role">No assignee</div>
                                    </div>
                                </label>
                            </div>
                            <?php while ($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="user-option">
                                <input type="radio" id="user_<?php echo $row['id']; ?>" name="assigned_to" value="<?php echo $row['id']; ?>">
                                <label for="user_<?php echo $row['id']; ?>" class="user-card">
                                    <div class="user-avatar"><?php echo strtoupper(substr($row['full_name'], 0, 1)); ?></div>
                                    <div class="user-info">
                                        <div class="user-name"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                        <div class="user-role">Team Member</div>
                                    </div>
                                </label>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-paperclip"></i> Attachments
                    </div>
                    
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-cloud-upload-alt"></i> Upload Files
                        </label>
                        <div class="file-upload">
                            <input type="file" name="attachments[]" id="attachments" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">
                            <label for="attachments" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choose files or drag here</span>
                            </label>
                        </div>
                        <div class="input-helper">Supported formats: JPG, PNG, PDF, DOC, TXT, ZIP (Max 10MB each)</div>
                        <div id="file-list" class="file-list"></div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions-modern">
                <button type="button" onclick="history.back()" class="btn-action-modern btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <div style="flex: 1;"></div>
                <button type="submit" class="btn-action-modern btn-primary">
                    <i class="fas fa-save"></i> Create Task
                </button>
            </div>
        </form>
    </div>
</div>

<script>

// File upload handling
document.addEventListener('DOMContentLoaded', function() {
    const attachmentInput = document.getElementById('attachments');
    const fileList = document.getElementById('file-list');
    let selectedFiles = [];
    
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            selectedFiles = [...selectedFiles, ...files];
            displayFiles();
        });
    }
    
    function displayFiles() {
        fileList.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-icon">
                    <i class="fas fa-file"></i>
                </div>
                <div class="file-info">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${(file.size / 1024).toFixed(1)} KB</div>
                </div>
                <button type="button" class="file-remove" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            fileList.appendChild(fileItem);
        });
    }
    
    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        displayFiles();
    };
    
    // Form submission - wait for DOM
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Selected files are already handled by the file input in FormData
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<div class="spinner"></div> Creating Task...';
    submitBtn.disabled = true;
    
    fetch('index.php?action=tasks', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            const url = new URL(response.url);
            if (url.searchParams.get('success') === 'created') {
                Swal.fire({
                    title: 'Success!',
                    text: 'Task created successfully!',
                    icon: 'success',
                    confirmButtonColor: '#6366f1',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = 'index.php';
                });
            } else {
                throw new Error('Failed to create task');
            }
        } else {
            return response.text().then(data => {
                if (data.includes('success=created')) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Task created successfully!',
                        icon: 'success',
                        confirmButtonColor: '#6366f1',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const taskId = formData.get('task_id') || 'latest';
                        window.location.href = 'index.php';
                    });
                } else {
                    throw new Error('Failed to create task');
                }
            });
        }
    })
    .catch(error => {
        console.error('Task creation error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Failed to create task. Please try again.',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
        
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
        });
    }
    
    // Enhanced date validation with visual feedback
    const startDateInput = document.getElementById('start_date');
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
    const startDate = new Date(this.value);
    const dueDateInput = document.getElementById('due_date');
    const dueDate = new Date(dueDateInput.value);
    
    if (dueDateInput.value && startDate > dueDate) {
        this.style.borderColor = '#ef4444';
        Toast.fire({
            icon: 'warning',
            title: 'Start date cannot be after due date'
        });
    } else {
        this.style.borderColor = '';
    }
        });
    }
    
    const dueDateInput = document.getElementById('due_date');
    if (dueDateInput) {
        dueDateInput.addEventListener('change', function() {
    const dueDate = new Date(this.value);
    const startDateInput = document.getElementById('start_date');
    const startDate = new Date(startDateInput.value);
    
    if (startDateInput.value && dueDate < startDate) {
        this.style.borderColor = '#ef4444';
        Toast.fire({
            icon: 'warning',
            title: 'Due date cannot be before start date'
        });
    } else {
        this.style.borderColor = '';
    }
        });
    }
    
    // Real-time form validation feedback
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            if (this.value.trim().length > 0) {
                this.style.borderColor = '#10b981';
            } else {
                this.style.borderColor = '';
            }
        });
    }
    
    // Smooth animations on load
    const formCard = document.querySelector('.form-card-enhanced');
    if (formCard) {
        formCard.style.opacity = '0';
        formCard.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            formCard.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            formCard.style.opacity = '1';
            formCard.style.transform = 'translateY(0)';
        }, 100);
    }
});
</script>

<?php include 'includes/footer.php'; ?>