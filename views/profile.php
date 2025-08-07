<?php
$page_title = "Profile";
include 'includes/header.php';

require_once 'models/User.php';
$user = new User($db);
$userData = $user->getById($_SESSION['user_id']);
?>

<div class="header-section">
    <h1 class="page-title"><i class="fas fa-user"></i> Profile</h1>
    <a href="index.php" class="btn-modern btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="form-container-modern">
    <div class="form-card-enhanced">
        <div class="form-header">
            <h2><i class="fas fa-user-circle"></i> My Profile</h2>
            <p>Manage your personal information</p>
        </div>
        
        <form method="POST" action="controllers/profile_controller.php" id="profileForm">
            <div class="form-content-edit">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i> Personal Information
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="full_name" class="form-label-modern">
                            <i class="fas fa-user"></i> Full Name *
                        </label>
                        <input type="text" id="full_name" name="full_name" class="form-input-modern" 
                               value="<?php echo htmlspecialchars($userData['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="email" class="form-label-modern">
                            <i class="fas fa-envelope"></i> Email Address *
                        </label>
                        <input type="email" id="email" name="email" class="form-input-modern" 
                               value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="username" class="form-label-modern">
                            <i class="fas fa-at"></i> Username *
                        </label>
                        <input type="text" id="username" name="username" class="form-input-modern" 
                               value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-lock"></i> Change Password
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="current_password" class="form-label-modern">
                            <i class="fas fa-key"></i> Current Password
                        </label>
                        <input type="password" id="current_password" name="current_password" class="form-input-modern">
                    </div>
                    
                    <div class="form-row-modern">
                        <div class="form-group-modern">
                            <label for="new_password" class="form-label-modern">
                                <i class="fas fa-lock"></i> New Password
                            </label>
                            <input type="password" id="new_password" name="new_password" class="form-input-modern">
                        </div>
                        
                        <div class="form-group-modern">
                            <label for="confirm_password" class="form-label-modern">
                                <i class="fas fa-lock"></i> Confirm Password
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input-modern">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions-modern">
                <button type="button" onclick="history.back()" class="btn-action-modern btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <div style="flex: 1;"></div>
                <button type="submit" class="btn-action-modern btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword && newPassword !== confirmPassword) {
        e.preventDefault();
        Toast.fire({
            icon: 'error',
            title: 'Passwords do not match'
        });
        return;
    }
    
    if (newPassword && !document.getElementById('current_password').value) {
        e.preventDefault();
        Toast.fire({
            icon: 'error',
            title: 'Current password is required to change password'
        });
        return;
    }
});
</script>

<?php include 'includes/footer.php'; ?>