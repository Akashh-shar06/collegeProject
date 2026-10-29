<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Campus360 Enterprise ERP</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            background: linear-gradient(135deg, #F4F7FA 0%, #E2E8F0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-card {
            background: #FFFFFF;
            border-radius: var(--radius-lg);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }
        .login-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #0066FF, #00C8FF);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 24px;
            font-weight: 800;
            margin: 0 auto 16px auto;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.3);
        }
        .login-title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 6px;
        }
        .login-subtitle {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            outline: none;
            font-size: 14px;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.15);
        }
        .error-alert {
            background-color: var(--danger-light);
            color: var(--danger);
            padding: 12px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #FCA5A5;
        }
        .success-alert {
            background-color: #DEF7EC;
            color: #03543F;
            padding: 12px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #84E1BC;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            border-radius: var(--radius-pill);
            background: var(--primary);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 14px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 102, 255, 0.3);
        }
        .btn-login:hover {
            background: var(--primary-hover);
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">C</div>
    <h2 class="login-title">Campus360 ERP</h2>
    <p class="login-subtitle">Secure Enterprise Portal Login</p>

    <?php if (isset($success)): ?>
        <div class="success-alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="error-alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="index.php?page=login_process" method="POST" autocomplete="off">
        <?= \App\Core\CSRF::inputField() ?>

        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" value="" autocomplete="off" class="form-control" placeholder="Enter username (e.g. admin)" required autofocus>
        </div>

        <div class="form-group">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <label for="password" style="margin-bottom:0;">Password</label>
                <a href="index.php?page=forgot_password" style="font-size:12px; color:var(--primary); font-weight:600; text-decoration:none;">Forgot Password?</a>
            </div>
            <input type="password" id="password" name="password" value="" autocomplete="new-password" class="form-control" placeholder="Enter password (e.g. Admin@123)" required>
        </div>

        <button type="submit" class="btn-login">Sign In to Dashboard</button>
    </form>

    <div style="margin-top:20px; text-align:center; font-size:12px; color:var(--text-muted); background:#F8FAFC; padding:12px; border-radius:8px; border:1px solid #E2E8F0;">
        <div style="font-weight:700; color:var(--text-main); margin-bottom:4px;">🔑 Portal Accounts (Default Password: <code>Admin@123</code>)</div>
        <div><strong>Admin:</strong> <code>admin</code> &nbsp;|&nbsp; <strong>Teacher:</strong> <code>dr_soap</code> &nbsp;|&nbsp; <strong>Student:</strong> <code>student</code> / <code>ankit</code></div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            var u = document.getElementById('username');
            var p = document.getElementById('password');
            if (u) u.value = '';
            if (p) p.value = '';
        }, 30);
    });
</script>
</body>
</html>
