// SweetAlert2 configurations
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

// Show success message
function showSuccess(message) {
    Toast.fire({
        icon: 'success',
        title: message
    });
}

// Show error message
function showError(message) {
    Toast.fire({
        icon: 'error',
        title: message
    });
}

// Confirm delete action
function confirmDelete(url, message = 'This action cannot be undone!') {
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

// File upload handler
function handleFileUpload(taskId) {
    const input = document.createElement('input');
    input.type = 'file';
    input.multiple = true;
    input.accept = '.pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif';
    
    input.onchange = function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            const formData = new FormData();
            formData.append('task_id', taskId);
            
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            
            fetch('controllers/upload_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Files uploaded successfully');
                    location.reload();
                } else {
                    showError(data.message || 'Upload failed');
                }
            })
            .catch(error => {
                showError('Upload failed');
            });
        }
    };
    
    input.click();
}

// Add note function
function addNote(taskId) {
    Swal.fire({
        title: 'Add Note',
        html: `
            <textarea id="noteText" class="form-control" rows="4" placeholder="Enter your note..."></textarea>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="isPrivate">
                <label class="form-check-label" for="isPrivate">Private note</label>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Note',
        preConfirm: () => {
            const note = document.getElementById('noteText').value;
            const isPrivate = document.getElementById('isPrivate').checked;
            
            if (!note.trim()) {
                Swal.showValidationMessage('Please enter a note');
                return false;
            }
            
            return { note: note, isPrivate: isPrivate };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('task_id', taskId);
            formData.append('note', result.value.note);
            formData.append('is_private', result.value.isPrivate ? 1 : 0);
            
            fetch('controllers/note_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Note added successfully');
                    location.reload();
                } else {
                    showError(data.message || 'Failed to add note');
                }
            });
        }
    });
}

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Initialize dashboard charts
function initDashboardCharts() {
    // Task status chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Completed', 'Cancelled'],
                datasets: [{
                    data: window.chartData?.status || [0, 0, 0, 0],
                    backgroundColor: ['#ffc107', '#0dcaf0', '#198754', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    
    // Priority chart
    const priorityCtx = document.getElementById('priorityChart');
    if (priorityCtx) {
        new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: ['Low', 'Medium', 'High', 'Urgent'],
                datasets: [{
                    label: 'Tasks',
                    data: window.chartData?.priority || [0, 0, 0, 0],
                    backgroundColor: ['#198754', '#ffc107', '#fd7e14', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Counter animation
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const increment = target / 50;
        let current = 0;
        
        const updateCounter = () => {
            if (current < target) {
                current += increment;
                counter.textContent = Math.ceil(current);
                setTimeout(updateCounter, 40);
            } else {
                counter.textContent = target;
            }
        };
        
        updateCounter();
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initDashboardCharts();
    animateCounters();
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
});