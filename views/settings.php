<?php
$page_title = "Settings";
include 'includes/header.php';

// Get user settings from database
$query = "SELECT * FROM user_settings WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Default values if no settings found
if (!$settings) {
    $settings = [
        'email_notifications' => 1,
        'due_date_reminders' => 1,
        'status_updates' => 1,
        'theme' => 'light',
        'language' => 'en',
        'tasks_per_page' => 10,
        'default_view' => 'grid',
        'show_profile' => 1,
        'activity_tracking' => 1,
        'session_timeout' => 30
    ];
}
?>

<div class="header-section">
    <h1 class="page-title"><i class="fas fa-cog"></i> Settings</h1>
    <a href="index.php" class="btn-modern btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="form-container-modern">
    <div class="form-card-enhanced">
        <div class="form-header">
            <h2><i class="fas fa-cogs"></i> Application Settings</h2>
            <p>Customize your experience</p>
        </div>
        
        <form method="POST" action="index.php?action=settings_update" id="settingsForm">
            <div class="form-content-edit">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-bell"></i> Notifications
                    </div>
                    
                    <div class="form-group-modern">
                        <label class="checkbox-label">
                            <input type="checkbox" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Email notifications for task assignments
                        </label>
                    </div>
                    
                    <div class="form-group-modern">
                        <label class="checkbox-label">
                            <input type="checkbox" name="due_date_reminders" <?php echo $settings['due_date_reminders'] ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Due date reminders
                        </label>
                    </div>
                    
                    <div class="form-group-modern">
                        <label class="checkbox-label">
                            <input type="checkbox" name="status_updates" <?php echo $settings['status_updates'] ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Task status update notifications
                        </label>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-palette"></i> Appearance
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="theme" class="form-label-modern">
                            <i class="fas fa-paint-brush"></i> Theme
                        </label>
                        <select id="theme" name="theme" class="form-select-modern">
                            <option value="light" <?php echo $settings['theme'] == 'light' ? 'selected' : ''; ?>>Light</option>
                            <option value="dark" <?php echo $settings['theme'] == 'dark' ? 'selected' : ''; ?>>Dark</option>
                            <option value="auto" <?php echo $settings['theme'] == 'auto' ? 'selected' : ''; ?>>Auto (System)</option>
                        </select>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="language" class="form-label-modern">
                            <i class="fas fa-language"></i> Language
                        </label>
                        <select id="language" name="language" class="form-select-modern">
                            <option value="en" <?php echo $settings['language'] == 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="es" <?php echo $settings['language'] == 'es' ? 'selected' : ''; ?>>Spanish</option>
                            <option value="fr" <?php echo $settings['language'] == 'fr' ? 'selected' : ''; ?>>French</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-list"></i> Dashboard Preferences
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="tasks_per_page" class="form-label-modern">
                            <i class="fas fa-list-ol"></i> Tasks per page
                        </label>
                        <select id="tasks_per_page" name="tasks_per_page" class="form-select-modern">
                            <option value="10" <?php echo $settings['tasks_per_page'] == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $settings['tasks_per_page'] == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $settings['tasks_per_page'] == 50 ? 'selected' : ''; ?>>50</option>
                        </select>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="default_view" class="form-label-modern">
                            <i class="fas fa-eye"></i> Default view
                        </label>
                        <select id="default_view" name="default_view" class="form-select-modern">
                            <option value="grid" <?php echo $settings['default_view'] == 'grid' ? 'selected' : ''; ?>>Grid View</option>
                            <option value="list" <?php echo $settings['default_view'] == 'list' ? 'selected' : ''; ?>>List View</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-shield-alt"></i> Privacy & Security
                    </div>
                    
                    <div class="form-group-modern">
                        <label class="checkbox-label">
                            <input type="checkbox" name="show_profile" <?php echo $settings['show_profile'] ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Show my profile to other team members
                        </label>
                    </div>
                    
                    <div class="form-group-modern">
                        <label class="checkbox-label">
                            <input type="checkbox" name="activity_tracking" <?php echo $settings['activity_tracking'] ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Enable activity tracking
                        </label>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="session_timeout" class="form-label-modern">
                            <i class="fas fa-clock"></i> Session timeout (minutes)
                        </label>
                        <select id="session_timeout" name="session_timeout" class="form-select-modern">
                            <option value="30" <?php echo $settings['session_timeout'] == 30 ? 'selected' : ''; ?>>30 minutes</option>
                            <option value="60" <?php echo $settings['session_timeout'] == 60 ? 'selected' : ''; ?>>1 hour</option>
                            <option value="120" <?php echo $settings['session_timeout'] == 120 ? 'selected' : ''; ?>>2 hours</option>
                            <option value="480" <?php echo $settings['session_timeout'] == 480 ? 'selected' : ''; ?>>8 hours</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-actions-modern">
                <button type="button" onclick="history.back()" class="btn-action-modern btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button type="button" onclick="resetSettings()" class="btn-action-modern btn-outline">
                    <i class="fas fa-undo"></i> Reset to Default
                </button>
                <button type="submit" class="btn-action-modern btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function resetSettings() {
    Swal.fire({
        title: 'Reset Settings?',
        text: 'This will restore all settings to their default values.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, reset',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('settingsForm').reset();
            Toast.fire({
                icon: 'success',
                title: 'Settings reset to default'
            });
        }
    });
}

document.getElementById('settingsForm').addEventListener('submit', function(e) {
    // Form will submit normally to save to database
});
</script>

<?php include 'includes/footer.php'; ?>