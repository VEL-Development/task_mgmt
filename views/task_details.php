<?php
session_start();
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../models/Task.php';
require_once '../models/TaskEnhanced.php';

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$task = new TaskEnhanced($db);
$task->id = $_GET['id'];

if (!$task->readOne()) {
    header('Location: ../index.php');
    exit;
}

$notes = $task->getNotes($task->id, $_SESSION['user_id']);
$attachments = $task->getAttachments($task->id);
$audit_log = $task->getAuditLog($task->id);
$page_title = "Task Details";

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><?php echo htmlspecialchars($task->title); ?></h4>
                <div>
                    <span class="badge bg-<?php 
                        echo $task->priority == 'urgent' ? 'danger' : 
                            ($task->priority == 'high' ? 'warning' : 
                            ($task->priority == 'medium' ? 'info' : 'secondary')); 
                    ?> me-2">
                        <?php echo ucfirst($task->priority); ?>
                    </span>
                    <span class="badge status-<?php echo $task->status; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $task->status)); ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Due Date:</strong> 
                        <?php echo $task->due_date ? date('M d, Y', strtotime($task->due_date)) : 'Not set'; ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Start Date:</strong> 
                        <?php echo $task->start_date ? date('M d, Y', strtotime($task->start_date)) : 'Not set'; ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Assigned By:</strong> 
                        <?php echo $task->creator_name ?: 'Unknown'; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Description:</strong>
                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($task->description)); ?></p>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-paperclip me-2"></i>Attachments</h5>
                <button onclick="handleFileUpload(<?php echo $task->id; ?>)" class="btn btn-sm btn-primary">
                    <i class="fas fa-upload me-1"></i>Upload
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($attachments)): ?>
                    <p class="text-muted">No attachments</p>
                <?php else: ?>
                    <?php foreach ($attachments as $attachment): ?>
                        <div class="attachment-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file me-2"></i>
                                <strong><?php echo htmlspecialchars($attachment['original_name']); ?></strong>
                                <small class="text-muted">
                                    (<?php echo number_format($attachment['file_size'] / 1024, 1); ?> KB)
                                    - Uploaded by <?php echo $attachment['uploaded_by_name']; ?>
                                    on <?php echo date('M d, Y', strtotime($attachment['created_at'])); ?>
                                </small>
                            </div>
                            <a href="../uploads/<?php echo $attachment['filename']; ?>" 
                               class="btn btn-sm btn-outline-primary" download>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                <button onclick="addNote(<?php echo $task->id; ?>)" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Note
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($notes)): ?>
                    <p class="text-muted">No notes yet</p>
                <?php else: ?>
                    <?php foreach ($notes as $note): ?>
                        <div class="note-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <strong><?php echo $note['full_name']; ?></strong>
                                <div>
                                    <?php if ($note['is_private']): ?>
                                        <span class="badge bg-warning me-2">Private</span>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y H:i', strtotime($note['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($note['note'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i>Audit Log</h5>
            </div>
            <div class="card-body">
                <?php if (empty($audit_log)): ?>
                    <p class="text-muted">No activity yet</p>
                <?php else: ?>
                    <?php foreach ($audit_log as $log): ?>
                        <div class="audit-log mb-3 pb-2 border-bottom">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo $log['full_name']; ?></strong>
                                <small class="text-muted">
                                    <?php echo date('M d, H:i', strtotime($log['created_at'])); ?>
                                </small>
                            </div>
                            <div class="mt-1">
                                <?php if ($log['action'] == 'created'): ?>
                                    <span class="text-success">Created task</span>
                                <?php elseif ($log['action'] == 'updated'): ?>
                                    <span class="text-info">Updated <?php echo $log['field_name']; ?></span>
                                    <?php if ($log['old_value'] && $log['new_value']): ?>
                                        <br><small>From: <?php echo htmlspecialchars($log['old_value']); ?></small>
                                        <br><small>To: <?php echo htmlspecialchars($log['new_value']); ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body text-center">
                <a href="edit_task.php?id=<?php echo $task->id; ?>" class="btn btn-primary mb-2">
                    <i class="fas fa-edit me-1"></i>Edit Task
                </a>
                <br>
                <button onclick="confirmDelete('../controllers/task_controller.php?action=delete&id=<?php echo $task->id; ?>')" 
                        class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i>Delete Task
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>