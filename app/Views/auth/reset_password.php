<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Password - Campus360 Enterprise ERP</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            background: linear-gradient(135deg, #F4F7FA 0%, #E2E8F0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .reset-card {
            background: #FFFFFF;
            border-radius: var(--radius-lg, 12px);
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: var(--shadow-lg, 0 10px 25px rgba(0,0,0,0.1));
            border: 1px solid var(--border-color, #E2E8F0);
        }
        .reset-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #0066FF, #00C8FF);
            border-radius: var(--radius-md, 8px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 24px;
            font-weight: 800;
            margin: 0 auto 16px auto;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.3);
        }
        .reset-title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main, #1E293B);
            margin-bottom: 6px;
        }
        .reset-subtitle {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted, #64748B);
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-main, #1E293B);
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            border-radius: var(--radius-md, 8px);
            border: 1px solid var(--border-color, #CBD5E1);
            outline: none;
            font-size: 14px;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .form-control:focus {
            border-color: var(--primary, #0066FF);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.15);
        }
        .error-alert {
            background-color: #FEE2E2;
            color: #991B1B;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #FCA5A5;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            border-radius: 24px;
            background: var(--primary, #0066FF);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 14px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 102, 255, 0.3);
        }
        .btn-submit:hover {
            background: #0052CC;
        }
    </style>
</head>
<body>
<div class="reset-card">
    <div class="reset-logo">🔒</div>
    <h2 class="reset-title">Create New Password</h2>
    <p class="reset-subtitle">Please enter your new account password below.</p>

    <?php if (isset($error)): ?>
        <div class="error-alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="index.php?page=reset_password_process" method="POST" onsubmit="return validatePasswords();">
        <?= \App\Core\CSRF::inputField() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters" required autofocus minlength="6">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter new password" required minlength="6">
        </div>

        <button type="submit" class="btn-submit">Update Password & Login</button>
    </form>

    <div style="margin-top:24px; text-align:center; font-size:13px;">
        <a href="index.php?page=login" style="color:var(--primary, #0066FF); font-weight:700; text-decoration:none;">← Cancel & Return to Sign In</a>
    </div>
</div>

<script>
function validatePasswords() {
    var p1 = document.getElementById('password').value;
    var p2 = document.getElementById('confirm_password').value;
    if (p1 !== p2) {
        alert('Passwords do not match. Please ensure both fields are identical.');
        return false;
    }
    return true;
}
</script>
</body>
</html>
