<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden - Access Restricted</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            background: #F4F7FA;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        .error-card {
            background: #FFFFFF;
            border-radius: var(--radius-lg);
            padding: 40px;
            max-width: 480px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }
    </style>
</head>
<body>
<div class="error-card">
    <div style="font-size:48px; margin-bottom:12px;">🛑</div>
    <h2 style="font-size:24px; font-weight:800; color:var(--danger); margin-bottom:8px;">403 Access Forbidden</h2>
    <p style="font-size:14px; color:var(--text-muted); margin-bottom:24px;">Your assigned role does not have permission to access the <strong><?= htmlspecialchars($action ?? 'requested action', ENT_QUOTES, 'UTF-8') ?></strong> module.</p>
    <a href="index.php?page=dashboard" class="btn btn-primary" style="text-decoration:none;">Return to Safe Dashboard</a>
</div>
</body>
</html>
