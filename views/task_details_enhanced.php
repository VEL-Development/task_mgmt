<?php
require_once 'models/TaskEnhanced.php';
require_once 'models/TaskStatus.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$task = new TaskEnhanced($db);
$taskData = $task->getById($_GET['id']);
$attachments = $task->getAttachments($_GET['id']);
$notes = $task->getNotes($_GET['id']);
$auditLog = $task->getAuditLog($_GET['id']);

$statusModel = new TaskStatus($db);
$currentStatus = $statusModel->getStatusById($taskData['status_id']);

if (!$taskData) {
    header('Location: index.php?error=Task not found');
    exit;
}

$page_title = "Task Details";
include 'includes/header.php';
?>

<div class="header-section">
    <div class="page-header-content">
        <div class="page-title-section">
            <h1 class="page-title"><i class="fas fa-tasks"></i> Task Details</h1>
            <div class="task-title-display"><?php echo htmlspecialchars($taskData['title']); ?></div>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button onclick="shareTask()" class="btn-modern btn-outline">
                <i class="fas fa-share-alt"></i> Share
            </button>
            <a href="index.php?action=edit_task&id=<?php echo $taskData['id']; ?>" class="btn-modern btn-primary">
                <i class="fas fa-edit"></i> Edit Task
            </a>
            <a href="index.php?action=tasks_list" class="btn-modern btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="task-overview-cards">
    <div class="overview-card status-card">
        <div class="card-icon status-<?php echo $currentStatus['group_status']; ?>" style="background-color: <?php echo $currentStatus['color']; ?>">
            <i class="fas fa-circle"></i>
        </div>
        <div class="card-content">
            <div class="card-label">Status</div>
            <div class="card-value"><?php echo htmlspecialchars($currentStatus['name']); ?></div>
        </div>
    </div>
    
    <div class="overview-card priority-card">
        <div class="card-icon priority-<?php echo $taskData['priority']; ?>">
            <i class="fas fa-flag"></i>
        </div>
        <div class="card-content">
            <div class="card-label">Priority</div>
            <div class="card-value"><?php echo ucfirst($taskData['priority']); ?></div>
        </div>
    </div>
    
    <div class="overview-card assignee-card">
        <div class="card-icon">
            <i class="fas fa-user"></i>
        </div>
        <div class="card-content">
            <div class="card-label">Assigned To</div>
            <div class="card-value"><?php echo $taskData['assigned_name'] ?: 'Unassigned'; ?></div>
        </div>
    </div>
    
    <div class="overview-card progress-card">
        <div class="card-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="card-content">
            <div class="card-label">Progress</div>
            <div class="card-value">
                <?php 
                $progress = 0;
                switch($currentStatus['group_status']) {
                    case 'pending': $progress = 0; break;
                    case 'in_progress': $progress = 50; break;
                    case 'completed': $progress = 100; break;
                    case 'cancelled': $progress = 0; break;
                }
                echo $progress; ?>%
            </div>
        </div>
    </div>
</div>

<div class="task-content-grid">
    <div class="main-content">
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-align-left"></i> Description</h3>
            </div>
            <div class="card-body">
                <?php echo $taskData['description'] ? nl2br(htmlspecialchars($taskData['description'])) : '<em class="text-muted">No description provided</em>'; ?>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line"></i> Progress</h3>
            </div>
            <div class="card-body">
                <?php 
                $progress = 0;
                switch($currentStatus['group_status']) {
                    case 'pending': $progress = 0; break;
                    case 'in_progress': $progress = 50; break;
                    case 'completed': $progress = 100; break;
                    case 'cancelled': $progress = 0; break;
                }
                ?>
                <div class="progress-container">
                    <div class="progress-bar-new">
                        <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                    </div>
                    <span class="progress-text"><?php echo $progress; ?>% Complete</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="sidebar">
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-users"></i> People</h3>
            </div>
            <div class="card-body">
                <div class="person-list">
                    <div class="person">
                        <div class="person-avatar"><?php echo $taskData['assigned_name'] ? strtoupper(substr($taskData['assigned_name'], 0, 1)) : '?'; ?></div>
                        <div class="person-info">
                            <div class="person-name"><?php echo $taskData['assigned_name'] ?: 'Unassigned'; ?></div>
                            <div class="person-role">Assignee</div>
                        </div>
                    </div>
                    <div class="person">
                        <div class="person-avatar creator"><?php echo strtoupper(substr($taskData['creator_name'], 0, 1)); ?></div>
                        <div class="person-info">
                            <div class="person-name"><?php echo $taskData['creator_name']; ?></div>
                            <div class="person-role">Creator</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-calendar"></i> Timeline</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php if($taskData['start_date']): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot start"></div>
                        <div class="timeline-content">
                            <div class="timeline-label">Start Date</div>
                            <div class="timeline-value"><?php echo date('M d, Y', strtotime($taskData['start_date'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if($taskData['due_date']): ?>
                    <div class="timeline-item <?php echo strtotime($taskData['due_date']) < strtotime('today') ? 'overdue' : ''; ?>">
                        <div class="timeline-dot due"></div>
                        <div class="timeline-content">
                            <div class="timeline-label">Due Date</div>
                            <div class="timeline-value">
                                <?php echo date('M d, Y', strtotime($taskData['due_date'])); ?>
                                <?php if(strtotime($taskData['due_date']) < strtotime('today')): ?>
                                    <span class="overdue-text">Overdue</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-paperclip"></i> Attachments <span class="count-badge"><?php echo count($attachments); ?></span></h3>
    </div>
    <div class="card-body">
        <div class="upload-zone">
            <input type="file" name="attachment" id="attachment" multiple class="file-input">
            <label for="attachment" class="upload-label">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Drop files here or click to upload</span>
            </label>
        </div>
        
        <?php if(count($attachments) > 0): ?>
        <div class="attachments-grid">
            <?php foreach($attachments as $attachment): ?>
            <div class="attachment-card">
                <div class="attachment-preview">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="attachment-details">
                    <div class="attachment-name"><?php echo htmlspecialchars($attachment['original_name']); ?></div>
                    <div class="attachment-meta"><?php echo number_format($attachment['file_size'] / 1024, 1); ?> KB</div>
                </div>
                <div class="attachment-actions">
                    <a href="uploads/<?php echo $attachment['filename']; ?>" target="_blank" class="action-btn download">
                        <i class="fas fa-download"></i>
                    </a>
                    <button onclick="deleteAttachment(<?php echo $attachment['id']; ?>)" class="action-btn delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-paperclip"></i>
            <p>No attachments yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-sticky-note"></i> Notes <span class="count-badge"><?php echo count($notes); ?></span></h3>
    </div>
    <div class="card-body">
        <form id="noteForm" class="note-form">
            <textarea name="note" placeholder="Add a note..." class="note-input" rows="3" required></textarea>
            <div class="note-form-footer">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_private">
                    <span class="checkmark"></span>
                    Private note
                </label>
                <button type="submit" class="btn-add-note">
                    <i class="fas fa-plus"></i> Add Note
                </button>
            </div>
            <input type="hidden" name="task_id" value="<?php echo $taskData['id']; ?>">
        </form>
        
        <?php if(count($notes) > 0): ?>
        <div class="notes-timeline">
            <?php foreach($notes as $note): ?>
            <div class="note-bubble <?php echo $note['is_private'] ? 'private' : ''; ?>">
                <div class="note-avatar"><?php echo strtoupper(substr($note['author_name'], 0, 1)); ?></div>
                <div class="note-content-wrapper">
                    <div class="note-header-new">
                        <span class="note-author"><?php echo $note['author_name']; ?></span>
                        <?php if($note['is_private']): ?>
                            <i class="fas fa-lock private-icon"></i>
                        <?php endif; ?>
                        <span class="note-time"><?php echo date('M d, g:i A', strtotime($note['created_at'])); ?></span>
                    </div>
                    <div class="note-text"><?php echo nl2br(htmlspecialchars($note['note'])); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-sticky-note"></i>
            <p>No notes yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Activity History <span class="count-badge"><?php echo count($auditLog); ?></span></h3>
    </div>
    <div class="card-body">
        <?php if(count($auditLog) > 0): ?>
        <div class="activity-timeline">
            <?php foreach($auditLog as $log): ?>
            <div class="activity-item">
                <div class="activity-dot <?php echo $log['action']; ?>">
                    <i class="fas fa-<?php echo $log['action'] == 'created' ? 'plus' : ($log['action'] == 'updated' ? 'edit' : 'info'); ?>"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-text">
                        <strong><?php echo $log['user_name']; ?></strong> <?php echo $log['action']; ?> 
                        <?php if($log['field_name']): ?>
                            <?php 
                            switch($log['field_name']) {
                                case 'status_id': echo 'status'; break;
                                case 'priority': echo 'priority'; break;
                                case 'title': echo 'title'; break;
                                default: echo $log['field_name'];
                            }
                            ?>
                        <?php else: ?>
                            this task
                        <?php endif; ?>
                    </div>
                    <?php if($log['old_value'] && $log['new_value']): ?>
                    <div class="activity-change">
                        <span class="old-value" <?php 
                            if($log['field_name'] == 'status_id' && $log['old_status_color']) {
                                echo 'style="background-color: ' . $log['old_status_color'] . '; color: white;"';
                            }
                        ?>><?php 
                            if($log['field_name'] == 'status_id' && $log['old_status_name']) {
                                echo htmlspecialchars($log['old_status_name']);
                            } else {
                                echo htmlspecialchars($log['old_value']);
                            }
                        ?></span>
                        <i class="fas fa-arrow-right"></i>
                        <span class="new-value" <?php 
                            if($log['field_name'] == 'status_id' && $log['new_status_color']) {
                                echo 'style="background-color: ' . $log['new_status_color'] . '; color: white;"';
                            }
                        ?>><?php 
                            if($log['field_name'] == 'status_id' && $log['new_status_name']) {
                                echo htmlspecialchars($log['new_status_name']);
                            } else {
                                echo htmlspecialchars($log['new_value']);
                            }
                        ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="activity-time"><?php echo date('M d, Y g:i A', strtotime($log['created_at'])); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <p>No activity history</p>
        </div>
        <?php endif; ?>
    </div>
</div>
    </div>
</div>

<script>
function shareTask() {
    const url = window.location.href;
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($taskData['title']); ?>',
            url: url
        });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            Toast.fire({
                icon: 'success',
                title: 'Task link copied to clipboard!'
            });
        });
    }
}


// File upload handling
document.getElementById('attachment').addEventListener('change', function() {
    const formData = new FormData();
    const files = this.files;
    
    for(let i = 0; i < files.length; i++) {
        formData.append('attachments[]', files[i]);
    }
    formData.append('task_id', <?php echo $taskData['id']; ?>);
    
    fetch('controllers/attachment_controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Toast.fire({
                icon: 'success',
                title: 'Files uploaded successfully!'
            });
            setTimeout(() => location.reload(), 1000);
        } else {
            Toast.fire({
                icon: 'error',
                title: data.message || 'Upload failed'
            });
        }
    })
    .catch(error => {
        Toast.fire({
            icon: 'error',
            title: 'Upload failed'
        });
    });
});

// Note form handling
document.getElementById('noteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('controllers/note_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Toast.fire({
                icon: 'success',
                title: 'Note added successfully!'
            });
            this.reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            Toast.fire({
                icon: 'error',
                title: data.message || 'Failed to add note'
            });
        }
    })
    .catch(error => {
        Toast.fire({
            icon: 'error',
            title: 'Failed to add note'
        });
    });
});

function deleteAttachment(attachmentId) {
    Swal.fire({
        title: 'Delete Attachment?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('controllers/attachment_controller.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({id: attachmentId})
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Attachment deleted!'
                    });
                    setTimeout(() => location.reload(), 1000);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to delete attachment'
                    });
                }
            });
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>