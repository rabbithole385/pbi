<?php
/**
 * Dedicated Standalone Webmail Login Portal
 * Branded with the site's name for direct sharing to colleagues / assistants / clients.
 */
require_once dirname(__DIR__) . '/scripts/mailbox_functions.php';

// If already logged into Webmail, redirect to Webmail inbox
if (!empty($_SESSION['webmail_logged']) && !empty($_SESSION['webmail_account_id'])) {
    header("Location: index.php");
    exit;
}

// If admin is logged in via admin panel, offer 1-click SSO or auto-redirect
$adminLoggedIn = (!empty($_SESSION['userAdmin']) || !empty($_SESSION['loggedAdmin']));

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['login_btn']) || isset($_POST['email']))) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } else {
        $auth = mb_authenticate_login($email, $password);
        if ($auth['success']) {
            header("Location: index.php");
            exit;
        } else {
            $error = $auth['message'] ?? 'Invalid email address or password.';
        }
    }
}

// Retrieve default account info for quick reference
$defaultAcc = mb_get_default_account();
$portalName = !empty($sitename) ? ($sitename . ' Webmail') : 'Webmail Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sign In &mdash; <?php echo htmlspecialchars($portalName); ?></title>
    <link rel="shortcut icon" href="../images/<?php echo htmlspecialchars($favicon ?? 'favicon.png'); ?>">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome & Dashlite Icons -->
    <link rel="stylesheet" href="../assets/css/dashlite.css?ver=2.4.0">
    <link rel="stylesheet" href="../assets/css/libs/fontawesome-icons.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --primary-glow: rgba(2, 132, 199, 0.25);
            --bg-dark: #090e17;
            --card-bg: rgba(15, 23, 42, 0.85);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body.webmail-login-body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: radial-gradient(circle at 15% 15%, rgba(2, 132, 199, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 85% 85%, rgba(59, 130, 246, 0.12) 0%, transparent 45%),
                        linear-gradient(135deg, #060b13 0%, #0d1527 50%, #090e17 100%);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        .login-grid-pattern {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .login-card-container {
            width: 100%;
            max-width: 460px;
            padding: 24px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 38px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .login-brand-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo {
            max-height: 52px;
            width: auto;
            margin-bottom: 16px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));
        }

        .login-portal-title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #ffffff;
            margin: 0 0 6px;
        }

        .login-portal-sub {
            font-size: 13.5px;
            color: var(--text-muted);
            margin: 0;
        }

        .mail-badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(2, 132, 199, 0.15);
            border: 1px solid rgba(2, 132, 199, 0.35);
            color: #38bdf8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 14px;
        }

        .form-floating-custom {
            margin-bottom: 20px;
            position: relative;
        }

        .form-floating-custom label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            color: #64748b;
            font-size: 16px;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .form-control-custom {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 13px 44px 13px 42px;
            font-size: 14.5px;
            color: #f8fafc;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .form-control-custom:focus {
            background: rgba(15, 23, 42, 0.95);
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px var(--primary-glow);
        }

        .form-control-custom:focus + .input-icon-left,
        .input-group-custom:focus-within .input-icon-left {
            color: #38bdf8;
        }

        .pass-toggle-btn {
            position: absolute;
            right: 14px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            font-size: 15px;
            transition: color 0.2s;
        }
        .pass-toggle-btn:hover {
            color: #f8fafc;
        }

        .btn-submit-mail {
            width: 100%;
            padding: 13px 20px;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -0.01em;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(2, 132, 199, 0.45);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-submit-mail:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -5px rgba(2, 132, 199, 0.6);
        }

        .btn-submit-mail:active {
            transform: translateY(1px);
        }

        .security-footer-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            color: #64748b;
            margin-top: 24px;
            text-align: center;
        }

        .security-footer-badge i {
            color: #10b981;
        }

        .sso-banner {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: #34d399;
        }

        .sso-banner a {
            color: #ffffff;
            background: #059669;
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            font-size: 12px;
        }

        .credentials-pill-card {
            background: rgba(2, 132, 199, 0.08);
            border: 1px dashed rgba(2, 132, 199, 0.35);
            border-radius: 12px;
            padding: 12px 16px;
            margin-top: 22px;
            font-size: 12.5px;
            color: #94a3b8;
        }
        .credentials-pill-card strong {
            color: #38bdf8;
        }
        .credentials-pill-card code {
            background: rgba(0,0,0,0.3);
            color: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="webmail-login-body">
    <div class="login-grid-pattern"></div>

    <div class="login-card-container">
        <div class="login-card">
            <div class="login-brand-header">
                <?php if (!empty($logo) && file_exists(dirname(__DIR__) . '/' . $logo)): ?>
                    <img src="../<?php echo htmlspecialchars($logo); ?>" alt="<?php echo htmlspecialchars($sitename); ?>" class="login-logo">
                <?php endif; ?>
                <div>
                    <span class="mail-badge-tag"><em class="icon ni ni-shield-check"></em> Encrypted Webmail</span>
                </div>
                <h1 class="login-portal-title"><?php echo htmlspecialchars($sitename); ?> Mail</h1>
                <p class="login-portal-sub">Sign in to your official organization inbox</p>
            </div>

            <?php if ($adminLoggedIn): ?>
                <div class="sso-banner">
                    <span><em class="icon ni ni-user-check"></em> Admin session detected</span>
                    <a href="index.php">Open Mailbox &rarr;</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-icon d-flex align-items-center mb-4" role="alert" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; border-radius: 10px; font-size: 13.5px; padding: 12px 16px;">
                    <em class="icon ni ni-alert-circle mr-2" style="font-size: 18px;"></em>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="mailLoginForm">
                <div class="form-floating-custom">
                    <label for="email">Email Address</label>
                    <div class="input-group-custom">
                        <em class="icon ni ni-mail input-icon-left"></em>
                        <input type="email" name="email" id="email" class="form-control-custom" placeholder="name@yourdomain.com" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? ($defaultAcc['email'] ?? '')); ?>">
                    </div>
                </div>

                <div class="form-floating-custom">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label for="password" style="margin-bottom: 0;">Password</label>
                    </div>
                    <div class="input-group-custom">
                        <em class="icon ni ni-lock-alt input-icon-left"></em>
                        <input type="password" name="password" id="password" class="form-control-custom" placeholder="Enter your mailbox password" required>
                        <button type="button" class="pass-toggle-btn" id="togglePasswordBtn" title="Toggle password visibility">
                            <em class="icon ni ni-eye" id="eyeIcon"></em>
                        </button>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 13px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #94a3b8;">
                        <input type="checkbox" name="remember" value="1" style="accent-color: #0284c7; width: 15px; height: 15px;"> Remember this device
                    </label>
                    <a href="mailto:<?php echo htmlspecialchars($siteemail ?? 'admin@pbigroups.com'); ?>" style="color: #38bdf8; text-decoration: none;">Need assistance?</a>
                </div>

                <button type="submit" name="login_btn" class="btn-submit-mail" id="submitBtn">
                    <em class="icon ni ni-signin"></em>
                    <span>Sign In to Mailbox</span>
                </button>
            </form>

            <?php if (!empty($defaultAcc)): ?>
            <div class="credentials-pill-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <strong><em class="icon ni ni-info"></em> Mailbox Direct Access:</strong>
                    <span style="font-size: 11px; color: #64748b;">Share with staff/client</span>
                </div>
                <div>Default User: <code><?php echo htmlspecialchars($defaultAcc['email']); ?></code></div>
                <div>Default Pass: <code>Admin@2026</code></div>
            </div>
            <?php endif; ?>

            <div class="security-footer-badge">
                <i class="fa fa-lock"></i>
                <span>TLS / SSL 256-bit Secure Mail Protocol &bull; <?php echo htmlspecialchars($sitename); ?></span>
            </div>
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (toggleBtn && passInput) {
            toggleBtn.addEventListener('click', function() {
                if (passInput.type === 'password') {
                    passInput.type = 'text';
                    eyeIcon.className = 'icon ni ni-eye-off';
                } else {
                    passInput.type = 'password';
                    eyeIcon.className = 'icon ni ni-eye';
                }
            });
        }
    </script>
</body>
</html>
