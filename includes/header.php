<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>TaskFlow Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/modern.css" rel="stylesheet">
    <link href="assets/css/user_management.css" rel="stylesheet">
    <link href="assets/css/user_dashboard.css" rel="stylesheet">
    <link href="assets/css/user_dashboard_page.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php if(isset($_SESSION['user_id'])): ?>
    <nav class="modern-nav">
        <div class="container" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
            <a href="?" class="nav-brand">
                <i class="fas fa-tasks"></i>
                TaskFlow Pro
            </a>
            <div class="nav-links">
                <a href="?" class="nav-link"><i class="fas fa-chart-pie"></i> Dashboard</a>
                <a href="?action=tasks_list" class="nav-link"><i class="fas fa-list"></i> Tasks</a>
                <a href="?action=create_task" class="nav-link"><i class="fas fa-plus"></i> New Task</a>
                <a href="?action=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="?action=user_management" class="nav-link"><i class="fas fa-users"></i> Users</a>
                <?php endif; ?>
                <div class="nav-user" onclick="toggleUserMenu()">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <span><?php echo $_SESSION['full_name']; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>
    </nav>
    <div id="userMenu" class="user-dropdown">
        <a href="?action=profile" class="dropdown-item">
            <i class="fas fa-user"></i> Profile
        </a>
        <a href="?action=settings" class="dropdown-item">
            <i class="fas fa-cog"></i> Settings
        </a>
        <div class="dropdown-divider"></div>
        <a href="#" onclick="confirmLogout()" class="dropdown-item">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('show');
        }
        
        function confirmLogout() {
            Swal.fire({
                title: 'Logout Confirmation',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'controllers/auth.php?action=logout';
                }
            });
        }
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav-user') && !e.target.closest('#userMenu')) {
                document.getElementById('userMenu').classList.remove('show');
            }
        });
        
        // Global SweetAlert configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        
        // Show success/error messages
        <?php if(isset($_GET['success'])): ?>
        Toast.fire({
            icon: 'success',
            title: 'Task <?php echo $_GET['success']; ?> successfully!'
        });
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
        Toast.fire({
            icon: 'error',
            title: '<?php echo $_GET['error']; ?>'
        });
        <?php endif; ?>
    </script>
    <?php endif; ?>
    <main class="main-content">
        <div class="container">