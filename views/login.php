<!DOCTYPE html>
<html>
<head>
    <title>Task Manager - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Task Manager Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_GET['error'])): ?>
                            <div class="alert alert-danger">Invalid username or password!</div>
                        <?php endif; ?>
                        <?php if(isset($_GET['success'])): ?>
                            <div class="alert alert-success">Registration successful! Please login.</div>
                        <?php endif; ?>
                        
                        <form method="POST" action="index.php?action=auth">
                            <input type="hidden" name="action" value="login">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="index.php?action=register">Don't have an account? Register</a>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">Default: admin/password</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>