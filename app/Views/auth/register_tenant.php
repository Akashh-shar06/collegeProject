<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution Onboarding Registration - Campus360 Multi-Tenant ERP</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            background: linear-gradient(135deg, #F4F7FA 0%, #E2E8F0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .register-card {
            background: #FFFFFF;
            border-radius: var(--radius-lg);
            padding: 40px;
            width: 100%;
            max-width: 540px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; }
        .form-control { width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 14px; outline: none; }
        .error-alert { background-color: var(--danger-light); color: var(--danger); padding: 12px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; margin-bottom: 16px; border: 1px solid #FCA5A5; }
    </style>
</head>
<body>
<div class="register-card">
    <div style="text-align:center; margin-bottom:24px;">
        <div style="width:50px; height:50px; background:linear-gradient(135deg, #0066FF, #00C8FF); border-radius:var(--radius-md); color:#FFFFFF; font-size:24px; font-weight:800; display:flex; align-items:center; justify-content:center; margin:0 auto 12px auto;">C</div>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-main);">Onboard Your Institution</h2>
        <p style="font-size:13px; color:var(--text-muted);">Register your school or college on Campus360 ERP</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="index.php?page=register_tenant_process" method="POST">
        <?= \App\Core\CSRF::inputField() ?>

        <div class="form-group">
            <label>Institution Full Name</label>
            <input type="text" name="institution_name" class="form-control" placeholder="e.g. St. Mark Academy" required>
        </div>

        <div class="form-group">
            <label>Institution Unique Code (Short Identifier)</label>
            <input type="text" name="institution_code" class="form-control" placeholder="e.g. ST_MARK" required>
        </div>

        <div class="form-group">
            <label>School Administrator Username</label>
            <input type="text" name="admin_username" class="form-control" placeholder="e.g. schooladmin_stmark" required>
        </div>

        <div class="form-group">
            <label>School Administrator Email</label>
            <input type="email" name="admin_email" class="form-control" placeholder="e.g. admin@stmark.edu" required>
        </div>

        <div class="form-group">
            <label>Set Administrator Password</label>
            <input type="password" name="admin_password" class="form-control" placeholder="Minimum 8 characters" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-weight:700; font-size:14px; margin-top:10px;">Complete Onboarding & Provision Tenant</button>
    </form>

    <div style="margin-top:20px; text-align:center; font-size:12px;">
        Already registered? <a href="index.php?page=login" style="color:var(--primary); font-weight:700; text-decoration:none;">Log in to Portal</a>
    </div>
</div>
</body>
</html>
