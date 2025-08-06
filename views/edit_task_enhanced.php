<?php
require_once 'models/TaskEnhanced.php';
require_once 'models/User.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$task = new TaskEnhanced($db);
$taskData = $task->getById($_GET['id']);

if (!$taskData) {
    header('Location: index.php?error=Task not found');
    exit;
}

$user = new User($db);
$users = $user->read();
$attachments = $task->getAttachments($_GET['id']);

$page_title = "Edit Task";
include 'includes/header.php';
?>

<div class="header-section">
    <h1 class="page-title"><i class="fas fa-edit"></i> Edit Task</h1>
    <div style="display: flex; gap: 1rem;">
        <a href="index.php?action=task_details&id=<?php echo $taskData['id']; ?>" class="btn-modern btn-secondary">
            <i class="fas fa-eye"></i> View Details
        </a>
        <a href="index.php" class="btn-modern btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="form-container-modern">
    <div class="form-card-enhanced edit-mode">
        <div class="form-header-edit">
            <div class="task-status-indicator status-<?php echo $taskData['status']; ?>">
                <i class="fas fa-<?php echo $taskData['status'] == 'completed' ? 'check-circle' : ($taskData['status'] == 'in_progress' ? 'spinner fa-spin' : 'clock'); ?>"></i>
                <span><?php echo ucfirst(str_replace('_', ' ', $taskData['status'])); ?></span>
            </div>
            <h2><i class="fas fa-edit"></i> Edit Task</h2>
            <p>Update task details and track progress</p>
        </div>
        
        <form method="POST" action="index.php?action=tasks" id="taskForm" enctype="multipart/form-data">
            <input type="hidden" name="task_action" value="update">
            <input type="hidden" name="task_id" value="<?php echo $taskData['id']; ?>">
            
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
                               value="<?php echo htmlspecialchars($taskData['title']); ?>"
                               placeholder="Enter a descriptive task title">
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="description" class="form-label-modern">
                            <i class="fas fa-align-left"></i> Description
                        </label>
                        <textarea id="description" name="description" class="form-textarea-modern" rows="4"
                                  placeholder="Describe the task in detail..."><?php echo htmlspecialchars($taskData['description']); ?></textarea>
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
                                <input type="radio" id="status_pending" name="status" value="pending" <?php echo $taskData['status'] == 'pending' ? 'checked' : ''; ?>>
                                <label for="status_pending" class="status-option pending">
                                    <i class="fas fa-clock"></i> Pending
                                </label>
                                
                                <input type="radio" id="status_progress" name="status" value="in_progress" <?php echo $taskData['status'] == 'in_progress' ? 'checked' : ''; ?>>
                                <label for="status_progress" class="status-option progress">
                                    <i class="fas fa-spinner"></i> In Progress
                                </label>
                                
                                <input type="radio" id="status_completed" name="status" value="completed" <?php echo $taskData['status'] == 'completed' ? 'checked' : ''; ?>>
                                <label for="status_completed" class="status-option completed">
                                    <i class="fas fa-check-circle"></i> Completed
                                </label>
                                
                                <input type="radio" id="status_cancelled" name="status" value="cancelled" <?php echo $taskData['status'] == 'cancelled' ? 'checked' : ''; ?>>
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
                                <input type="radio" id="low" name="priority" value="low" <?php echo $taskData['priority'] == 'low' ? 'checked' : ''; ?>>
                                <label for="low" class="priority-option low">Low</label>
                                
                                <input type="radio" id="medium" name="priority" value="medium" <?php echo $taskData['priority'] == 'medium' ? 'checked' : ''; ?>>
                                <label for="medium" class="priority-option medium">Medium</label>
                                
                                <input type="radio" id="high" name="priority" value="high" <?php echo $taskData['priority'] == 'high' ? 'checked' : ''; ?>>
                                <label for="high" class="priority-option high">High</label>
                                
                                <input type="radio" id="urgent" name="priority" value="urgent" <?php echo $taskData['priority'] == 'urgent' ? 'checked' : ''; ?>>
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
                                   value="<?php echo $taskData['start_date']; ?>">
                        </div>
                        
                        <div class="form-group-modern">
                            <label for="due_date" class="form-label-modern">
                                <i class="fas fa-calendar-alt"></i> Due Date
                            </label>
                            <input type="date" id="due_date" name="due_date" class="form-input-modern"
                                   value="<?php echo $taskData['due_date']; ?>">
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
                                <input type="radio" id="unassigned" name="assigned_to" value="" <?php echo empty($taskData['assigned_to']) ? 'checked' : ''; ?>>
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
                                <input type="radio" id="user_<?php echo $row['id']; ?>" name="assigned_to" value="<?php echo $row['id']; ?>" <?php echo $taskData['assigned_to'] == $row['id'] ? 'checked' : ''; ?>>
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
                    
                    <?php if (!empty($attachments)): ?>
                    <div class="existing-attachments">
                        <h4><i class="fas fa-file"></i> Current Attachments</h4>
                        <div class="attachment-list">
                            <?php foreach ($attachments as $attachment): ?>
                            <div class="attachment-item">
                                <div class="attachment-icon">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="attachment-info">
                                    <div class="attachment-name"><?php echo htmlspecialchars($attachment['original_name']); ?></div>
                                    <div class="attachment-meta">
                                        <?php echo number_format($attachment['file_size'] / 1024, 1); ?> KB • 
                                        Uploaded by <?php echo htmlspecialchars($attachment['uploader_name']); ?> • 
                                        <?php echo date('M j, Y', strtotime($attachment['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="attachment-actions">
                                    <a href="uploads/<?php echo $attachment['filename']; ?>" target="_blank" class="btn-attachment-action">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-cloud-upload-alt"></i> Add New Files
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
                <button type="button" onclick="window.location.href='index.php?action=task_details&id=<?php echo $taskData['id']; ?>'" class="btn-action-modern btn-outline">
                    <i class="fas fa-eye"></i> View Details
                </button>
                <button type="submit" class="btn-action-modern btn-primary">
                    <i class="fas fa-save"></i> Update Task
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
    
    // Form submission
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Selected files are already handled by the file input in FormData
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<div class="spinner"></div> Updating...';
    submitBtn.disabled = true;
    
    fetch('index.php?action=tasks', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            const url = new URL(response.url);
            if (url.searchParams.get('success') === 'updated') {
                Swal.fire({
                    title: 'Success!',
                    text: 'Task updated successfully!',
                    icon: 'success',
                    confirmButtonColor: '#6366f1'
                }).then(() => {
                    window.location.href = 'index.php?action=task_details&id=<?php echo $taskData['id']; ?>';
                });
            } else {
                throw new Error('Failed to update task');
            }
        } else {
            return response.text().then(data => {
                if (data.includes('success=updated')) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Task updated successfully!',
                        icon: 'success',
                        confirmButtonColor: '#6366f1'
                    }).then(() => {
                        window.location.href = 'index.php?action=task_details&id=<?php echo $taskData['id']; ?>';
                    });
                } else {
                    throw new Error('Failed to update task');
                }
            });
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to update task. Please try again.',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
        
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
        });
    }
});

// Status change handler for progress update
document.getElementById('status').addEventListener('change', function() {
    const progressBar = document.querySelector('.form-group:last-of-type .progress-bar');
    const progressText = document.querySelector('.form-group:last-of-type span:last-child');
    
    let progress = 0;
    switch(this.value) {
        case 'pending': progress = 0; break;
        case 'in_progress': progress = 50; break;
        case 'completed': progress = 100; break;
        case 'cancelled': progress = 0; break;
    }
    
    if (progressBar && progressText) {
        progressBar.style.width = progress + '%';
        progressText.textContent = progress + '%';
    }
});

// Date validation
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const dueDateInput = document.getElementById('due_date');
    const dueDate = new Date(dueDateInput.value);
    
    if (dueDateInput.value && startDate > dueDate) {
        Toast.fire({
            icon: 'warning',
            title: 'Start date cannot be after due date'
        });
        dueDateInput.focus();
    }
});

document.getElementById('due_date').addEventListener('change', function() {
    const dueDate = new Date(this.value);
    const startDateInput = document.getElementById('start_date');
    const startDate = new Date(startDateInput.value);
    
    if (startDateInput.value && dueDate < startDate) {
        Toast.fire({
            icon: 'warning',
            title: 'Due date cannot be before start date'
        });
        this.focus();
    }
});
</script>

<?php include 'includes/footer.php'; ?>