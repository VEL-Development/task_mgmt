<?php
require_once 'models/TaskEnhanced.php';
$task = new TaskEnhanced($db);
$user = new User($db);

$task->id = $_GET['id'];
if (!$task->readOne()) {
    header("Location: ?");
    exit();
}

$users_stmt = $user->getAllUsers();
$page_title = "Edit Task";

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-edit me-2"></i>Edit Task</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="controllers/task_controller.php">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="task_id" value="<?php echo $task->id; ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($task->title); ?>" placeholder="Enter task title" required>
                                <label for="title">Task Title *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-floating">
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Provide detailed information about the task..." style="height: 120px;"><?php echo htmlspecialchars($task->description); ?></textarea>
                            <label for="description">Description</label>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?php echo $task->status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $task->status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $task->status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $task->status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low" <?php echo $task->priority == 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo $task->priority == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo $task->priority == 'high' ? 'selected' : ''; ?>>High</option>
                                <option value="urgent" <?php echo $task->priority == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $task->start_date; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo $task->due_date; ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="assigned_to" class="form-label">Assign To</label>
                            <select class="form-select" id="assigned_to" name="assigned_to">
                                <option value="">Select team member...</option>
                                <?php while ($user_row = $users_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $user_row['id']; ?>" <?php echo $task->assigned_to == $user_row['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user_row['full_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="estimated_hours" class="form-label">Estimated Hours</label>
                            <input type="number" class="form-control" id="estimated_hours" name="estimated_hours" 
                                   step="0.5" min="0" value="<?php echo $task->estimated_hours; ?>">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>