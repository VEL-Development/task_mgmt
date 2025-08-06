<?php
require_once 'models/TaskEnhanced.php';

$task = new TaskEnhanced($db);
$stmt = $task->read();
$page_title = "Dashboard";
include 'includes/header.php';
?>

<?php if(isset($_GET['success'])): ?>
<div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem; border: 1px solid #a7f3d0;">
    Task <?php echo $_GET['success']; ?> successfully!
</div>
<?php endif; ?>

<div class="header-section">
    <h1 class="page-title">Dashboard</h1>
    <a href="index.php?action=create_task" class="btn-modern btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        New Task
    </a>
</div>

<div class="task-grid">
    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <div class="task-card">
        <div class="task-title"><?php echo htmlspecialchars($row['title']); ?></div>
        <?php if($row['description']): ?>
        <div class="task-desc"><?php echo substr(htmlspecialchars($row['description']), 0, 80); ?>...</div>
        <?php endif; ?>
        
        <div class="task-meta">
            <span class="status-badge status-<?php echo $row['status']; ?>">
                <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
            </span>
            <span class="status-badge priority-<?php echo $row['priority']; ?>">
                <?php echo ucfirst($row['priority']); ?>
            </span>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; font-size: 0.9rem; color: var(--text-muted);">
            <div><strong>Assigned:</strong> <?php echo $row['assigned_name'] ?: 'Unassigned'; ?></div>
            <div><strong>By:</strong> <?php echo $row['creator_name'] ?: '-'; ?></div>
            <div><strong>Due:</strong> <?php echo $row['due_date'] ? date('M d', strtotime($row['due_date'])) : '-'; ?></div>
        </div>
        
        <div class="task-actions">
            <a href="index.php?action=edit_task&id=<?php echo $row['id']; ?>" class="btn-modern btn-outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit
            </a>
            <form method="POST" action="index.php?action=tasks" style="display: inline;">
                <input type="hidden" name="task_action" value="delete">
                <input type="hidden" name="task_id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="btn-modern btn-outline" onclick="return confirm('Delete this task?')" style="color: #dc2626;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3,6 5,6 21,6"></polyline>
                        <path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a2,2,0,0,1,2,2h4a2,2,0,0,1,2,2V6"></path>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include 'includes/footer.php'; ?>