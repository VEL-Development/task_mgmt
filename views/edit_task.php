<?php
$task = new Task($db);
$user = new User($db);

$task->id = $_GET['id'];
if (!$task->readOne()) {
    header("Location: index.php");
    exit();
}

$users_stmt = $user->getAllUsers();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Task Manager</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Task</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="controllers/task_controller.php">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="task_id" value="<?php echo $task->id; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($task->title); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($task->description); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="pending" <?php echo $task->status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_progress" <?php echo $task->status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $task->status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $task->status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Priority</label>
                                        <select class="form-select" name="priority">
                                            <option value="low" <?php echo $task->priority == 'low' ? 'selected' : ''; ?>>Low</option>
                                            <option value="medium" <?php echo $task->priority == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                            <option value="high" <?php echo $task->priority == 'high' ? 'selected' : ''; ?>>High</option>
                                            <option value="urgent" <?php echo $task->priority == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Assign To</label>
                                        <select class="form-select" name="assigned_to">
                                            <option value="">Unassigned</option>
                                            <?php while ($user_row = $users_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                                <option value="<?php echo $user_row['id']; ?>" <?php echo $task->assigned_to == $user_row['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($user_row['full_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Due Date</label>
                                        <input type="date" class="form-control" name="due_date" value="<?php echo $task->due_date; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Task</button>
                                <a href="index.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>