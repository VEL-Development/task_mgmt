<?php
$page_title = "Task Status Management";
include 'includes/header.php';
require_once 'models/TaskStatus.php';

$statusModel = new TaskStatus($db);
$statuses = $statusModel->getAllStatuses();
$groupStatuses = $statusModel->getGroupStatuses();
?>

<div class="header-section">
    <div class="header-left">
        <h1 class="page-title">
            <i class="fas fa-tags"></i> Task Status Management
        </h1>
        <p class="page-subtitle">Manage task statuses and their groupings</p>
    </div>
    <div class="header-actions">
        <button onclick="openCreateModal()" class="btn-modern btn-primary">
            <i class="fas fa-plus"></i> Add Status
        </button>
        <a href="index.php" class="btn-modern btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="status-container">
    <div class="status-grid">
        <?php foreach ($statuses as $status): ?>
            <div class="status-card" style="border-left-color: <?php echo $status['color']; ?>">
                <div class="status-header">
                    <div class="status-info">
                        <h3><?php echo htmlspecialchars($status['name']); ?></h3>
                        <span class="group-badge group-<?php echo $status['group_status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $status['group_status'])); ?>
                        </span>
                    </div>
                    <div class="status-color" style="background-color: <?php echo $status['color']; ?>"></div>
                </div>
                <div class="status-actions">
                    <button onclick="editStatus(<?php echo $status['id']; ?>)" class="btn-small btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="deleteStatus(<?php echo $status['id']; ?>)" class="btn-small btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Status</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="statusForm">
            <input type="hidden" id="statusId" name="id">
            <div class="form-group">
                <label for="statusName">Status Name</label>
                <input type="text" id="statusName" name="name" required>
            </div>
            <div class="form-group">
                <label for="groupStatus">Group Status</label>
                <select id="groupStatus" name="group_status" required>
                    <?php foreach ($groupStatuses as $group): ?>
                        <option value="<?php echo $group; ?>"><?php echo ucfirst(str_replace('_', ' ', $group)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="statusColor">Color</label>
                <input type="color" id="statusColor" name="color" value="#6366f1">
            </div>
            <div class="form-group">
                <label for="sortOrder">Sort Order</label>
                <input type="number" id="sortOrder" name="sort_order" min="0" value="0">
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closeModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<style>
.status-container {
    margin-top: 2rem;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.status-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border-left: 4px solid;
    transition: all 0.3s ease;
}

.status-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.status-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.status-info h3 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
}

.group-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.group-pending { background: #fef3c7; color: #92400e; }
.group-in_progress { background: #dbeafe; color: #1e40af; }
.group-completed { background: #dcfce7; color: #166534; }
.group-cancelled { background: #fecaca; color: #dc2626; }

.status-color {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.status-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-small.btn-primary {
    background: #6366f1;
    color: white;
}

.btn-small.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-small:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
}

.close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
    transition: color 0.2s ease;
}

.close:hover {
    color: #1f2937;
}

.modal form {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #374151;
    font-weight: 500;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}

.modal-actions button {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #6366f1;
    color: white;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-primary:hover,
.btn-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add Status';
    document.getElementById('statusForm').reset();
    document.getElementById('statusId').value = '';
    document.getElementById('statusModal').style.display = 'block';
}

function editStatus(id) {
    fetch(`index.php?action=get_status&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Status';
            document.getElementById('statusId').value = data.id;
            document.getElementById('statusName').value = data.name;
            document.getElementById('groupStatus').value = data.group_status;
            document.getElementById('statusColor').value = data.color;
            document.getElementById('sortOrder').value = data.sort_order;
            document.getElementById('statusModal').style.display = 'block';
        });
}

function deleteStatus(id) {
    Swal.fire({
        title: 'Delete Status?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?action=delete_status&id=${id}`;
        }
    });
}

function closeModal() {
    document.getElementById('statusModal').style.display = 'none';
}

document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const action = formData.get('id') ? 'update_status' : 'create_status';
    
    fetch(`index.php?action=${action}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Toast.fire({
                icon: 'success',
                title: data.message
            });
            setTimeout(() => window.location.reload(), 1000);
        } else {
            Toast.fire({
                icon: 'error',
                title: data.message
            });
        }
        closeModal();
    });
});

window.onclick = function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>