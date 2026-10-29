<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Campus360 Enterprise ERP</title>
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
        .forgot-card {
            background: #FFFFFF;
            border-radius: var(--radius-lg, 12px);
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: var(--shadow-lg, 0 10px 25px rgba(0,0,0,0.1));
            border: 1px solid var(--border-color, #E2E8F0);
        }
        .forgot-logo {
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
        .forgot-title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main, #1E293B);
            margin-bottom: 6px;
        }
        .forgot-subtitle {
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
        .success-alert {
            background-color: #DEF7EC;
            color: #03543F;
            padding: 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #84E1BC;
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
        .reset-demo-box {
            background: #F0F9FF;
            border: 1px dashed #0284C7;
            padding: 16px;
            border-radius: 8px;
            margin-top: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="forgot-card">
    <div class="forgot-logo">C</div>
    <h2 class="forgot-title">Reset Password</h2>
    <p class="forgot-subtitle">Enter your registered username or email address and we will generate a password reset link for your account.</p>

    <?php if (isset($error)): ?>
        <div class="error-alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="success-alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (isset($resetUrl)): ?>
        <div class="reset-demo-box">
            <p style="font-size:12px; color:#0369A1; font-weight:700; margin:0 0 10px 0;">🔑 Reset Password Token Link:</p>
            <a href="<?= htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-submit" style="display:inline-block; text-decoration:none; width:auto; padding:8px 20px;">Click Here to Reset Password</a>
        </div>
    <?php else: ?>
        <form action="index.php?page=forgot_password_process" method="POST">
            <?= \App\Core\CSRF::inputField() ?>

            <div class="form-group">
                <label for="identity">Username or Registered Email</label>
                <input type="text" id="identity" name="identity" class="form-control" placeholder="Enter username (e.g. admin) or email" required autofocus>
            </div>

            <button type="submit" class="btn-submit">Generate Reset Link</button>
        </form>
    <?php endif; ?>

    <div style="margin-top:24px; text-align:center; font-size:13px;">
        <a href="index.php?page=login" style="color:var(--primary, #0066FF); font-weight:700; text-decoration:none;">← Back to Sign In</a>
    </div>
</div>
</body>
</html>
