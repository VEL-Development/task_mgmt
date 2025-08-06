<?php
$user = new User($db);
$users_stmt = $user->getAllUsers();
$page_title = "Create Task";
include 'includes/header.php';
?>

<style>
.form-container { max-width: 600px; margin: 0 auto; }
.form-card { background: var(--surface); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow); }
.form-group { margin-bottom: 1.5rem; }
.form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text); }
.form-input { width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 1rem; transition: all 0.2s; background: var(--surface); }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
.form-textarea { min-height: 100px; resize: vertical; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; }
.btn-modern { padding: 0.75rem 1.5rem; border-radius: var(--radius); border: none; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: var(--primary-dark); }
.btn-secondary { background: var(--secondary); color: var(--text-muted); }
.btn-secondary:hover { background: #e2e8f0; }
</style>

<div class="form-container">
    <div class="form-card">
        <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 2rem; color: var(--text);">Create New Task</h1>
        
        <form method="POST" action="index.php?action=tasks">
            <input type="hidden" name="task_action" value="create">
            
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" class="form-input" name="title" required placeholder="Enter task title">
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-input form-textarea" name="description" placeholder="Describe the task details"></textarea>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-input" name="status">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select class="form-input" name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Assign To</label>
                    <select class="form-input" name="assigned_to">
                        <option value="">Unassigned</option>
                        <?php while ($user_row = $users_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $user_row['id']; ?>">
                                <?php echo htmlspecialchars($user_row['full_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" class="form-input" name="due_date">
                </div>
            </div>
            
            <div class="form-actions">
                <a href="index.php" class="btn-modern btn-secondary">Cancel</a>
                <button type="submit" class="btn-modern btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20,6 9,17 4,12"></polyline>
                    </svg>
                    Create Task
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>