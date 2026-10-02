<?php
/**
 * Standalone Webmail Client Application Portal
 * Branded with the site's name and fully responsive.
 */
require_once dirname(__DIR__) . '/scripts/mailbox_functions.php';

$account = mb_get_current_session();
if (!$account) {
    header("Location: login.php");
    exit;
}

$isAdmin = (!empty($_SESSION['loggedAdmin']) || !empty($_SESSION['userAdmin']) || $account['role'] === 'admin');
$portalTitle = !empty($sitename) ? ($sitename . ' Mail') : 'Webmail';
$counts = mb_get_counts($account['id']);
$allAccounts = mb_get_all_accounts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Inbox &mdash; <?php echo htmlspecialchars($portalTitle); ?></title>
    <link rel="shortcut icon" href="../images/<?php echo htmlspecialchars($favicon ?? 'favicon.png'); ?>">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome & Dashlite Icons -->
    <link rel="stylesheet" href="../assets/css/dashlite.css?ver=2.4.0">
    <link rel="stylesheet" href="../assets/css/libs/fontawesome-icons.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="webmail-app-body">
    <div class="wm-layout">
        <!-- Top App Bar -->
        <header class="wm-topbar">
            <div class="wm-topbar-left">
                <a href="index.php" class="wm-brand-logo">
                    <?php if (!empty($logo) && file_exists(dirname(__DIR__) . '/' . $logo)): ?>
                        <img src="../<?php echo htmlspecialchars($logo); ?>" alt="logo">
                    <?php endif; ?>
                    <span class="wm-brand-title">
                        <?php echo htmlspecialchars($sitename); ?>
                        <span class="wm-brand-badge">Mail</span>
                    </span>
                </a>
            </div>

            <!-- Global Search -->
            <div class="wm-search-container">
                <em class="icon ni ni-search wm-search-icon"></em>
                <input type="text" id="wmSearchInput" class="wm-search-input" placeholder="Search emails by sender, subject, or message content...">
            </div>

            <!-- Topbar Right Tools -->
            <div class="wm-topbar-right">
                <button type="button" class="btn-wm-sync" id="btnSyncMail" title="Fetch incoming emails from mail server">
                    <em class="icon ni ni-reload"></em>
                    <span>Check Mail</span>
                </button>

                <?php if ($isAdmin): ?>
                    <a href="../admin/account_manager" class="btn-wm-sync" style="text-decoration: none;" title="Return to Banking Admin Dashboard">
                        <em class="icon ni ni-shield-check"></em>
                        <span class="d-none d-md-inline">Admin Panel</span>
                    </a>
                <?php endif; ?>

                <button type="button" class="btn-wm-sync" id="btnOpenSettings" title="Mailbox Settings & Accounts">
                    <em class="icon ni ni-setting"></em>
                </button>

                <!-- User profile pill -->
                <div class="wm-user-pill" id="userMenuDropdownBtn" title="<?php echo htmlspecialchars($account['email']); ?>">
                    <div class="wm-avatar-circle">
                        <?php echo strtoupper(substr($account['display_name'] ?? $account['email'], 0, 1)); ?>
                    </div>
                    <div class="d-none d-lg-block text-left" style="line-height: 1.2;">
                        <div style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($account['display_name']); ?></div>
                        <div style="font-size: 11px; color: #94a3b8;"><?php echo htmlspecialchars($account['email']); ?></div>
                    </div>
                    <a href="logout.php" style="color: #ef4444; margin-left: 6px;" title="Sign Out">
                        <em class="icon ni ni-signout"></em>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Body Content -->
        <div class="wm-main-container">
            <!-- Sidebar -->
            <aside class="wm-sidebar">
                <button type="button" class="btn-wm-compose" id="btnOpenCompose">
                    <em class="icon ni ni-edit"></em>
                    <span>Compose</span>
                </button>

                <div class="wm-nav-group-title">Folders</div>
                <ul class="wm-nav-list">
                    <li>
                        <a class="wm-nav-item active" data-folder="inbox">
                            <span class="wm-nav-item-left">
                                <em class="icon ni ni-inbox"></em>
                                <span class="wm-nav-item-text">Inbox</span>
                            </span>
                            <span class="wm-counter-badge" id="badgeInboxCount"><?php echo $counts['unread']; ?></span>
                        </a>
                    </li>
                    <li>
                        <a class="wm-nav-item" data-folder="starred">
                            <span class="wm-nav-item-left">
                                <em class="icon ni ni-star"></em>
                                <span class="wm-nav-item-text">Starred</span>
                            </span>
                            <span class="wm-counter-badge" id="badgeStarredCount"><?php echo $counts['starred']; ?></span>
                        </a>
                    </li>
                    <li>
                        <a class="wm-nav-item" data-folder="sent">
                            <span class="wm-nav-item-left">
                                <em class="icon ni ni-send"></em>
                                <span class="wm-nav-item-text">Sent</span>
                            </span>
                            <span class="wm-counter-badge" id="badgeSentCount"><?php echo $counts['sent']; ?></span>
                        </a>
                    </li>
                    <li>
                        <a class="wm-nav-item" data-folder="drafts">
                            <span class="wm-nav-item-left">
                                <em class="icon ni ni-file-text"></em>
                                <span class="wm-nav-item-text">Drafts</span>
                            </span>
                            <span class="wm-counter-badge" id="badgeDraftsCount"><?php echo $counts['drafts']; ?></span>
                        </a>
                    </li>
                    <li>
                        <a class="wm-nav-item" data-folder="trash">
                            <span class="wm-nav-item-left">
                                <em class="icon ni ni-trash"></em>
                                <span class="wm-nav-item-text">Trash</span>
                            </span>
                            <span class="wm-counter-badge" id="badgeTrashCount"><?php echo $counts['trash']; ?></span>
                        </a>
                    </li>
                </ul>

                <!-- Mailbox Accounts Switcher -->
                <div class="wm-nav-group-title" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Accounts</span>
                    <?php if ($isAdmin): ?>
                        <a href="javascript:void(0)" id="btnAddAccountQuick" title="Add Mailbox User" style="color: #38bdf8; font-size: 13px;">+ Add</a>
                    <?php endif; ?>
                </div>
                <ul class="wm-nav-list" style="margin-bottom: 16px;">
                    <?php foreach ($allAccounts as $accItem): ?>
                        <li>
                            <a class="wm-nav-item <?php echo $accItem['id'] == $account['id'] ? 'text-primary' : ''; ?>" href="javascript:switchMailAccount(<?php echo $accItem['id']; ?>)">
                                <span class="wm-nav-item-left" style="overflow: hidden;">
                                    <em class="icon ni ni-user-circle"></em>
                                    <span class="wm-nav-item-text" style="font-size: 12px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 150px;">
                                        <?php echo htmlspecialchars($accItem['email']); ?>
                                    </span>
                                </span>
                                <?php if ($accItem['id'] == $account['id']): ?>
                                    <em class="icon ni ni-check-circle-fill text-info" style="font-size: 13px;"></em>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Storage Info -->
                <div class="wm-storage-card">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="font-weight: 600; color: #cbd5e1;">Mail Storage</span>
                        <span>18%</span>
                    </div>
                    <div class="wm-storage-progress">
                        <div class="wm-storage-bar"></div>
                    </div>
                    <div style="font-size: 11px;">1.8 GB of 10 GB quota used</div>
                </div>
            </aside>

            <!-- Center Content: List & Reader Panes -->
            <main class="wm-content-area">
                <!-- Messages List Pane -->
                <section class="wm-list-pane" id="wmListPane">
                    <div class="wm-list-header">
                        <div class="wm-list-header-left">
                            <h2 class="wm-folder-title" id="currentFolderTitle">Inbox</h2>
                        </div>
                        <div class="wm-list-toolbar">
                            <button type="button" class="wm-btn-tool" id="btnRefreshList" title="Refresh folder">
                                <em class="icon ni ni-reload"></em>
                            </button>
                            <button type="button" class="wm-btn-tool" id="btnMarkAllRead" title="Mark selected as read">
                                <em class="icon ni ni-mail-read"></em>
                            </button>
                            <button type="button" class="wm-btn-tool" id="btnDeleteSelected" title="Move selected to Trash">
                                <em class="icon ni ni-trash"></em>
                            </button>
                        </div>
                    </div>

                    <!-- Scrollable list of messages -->
                    <div class="wm-message-scroll" id="wmMessageScroll">
                        <div class="p-4 text-center text-muted" id="wmLoadingPlaceholder">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                            <div style="font-size: 13px;">Loading messages...</div>
                        </div>
                    </div>
                </section>

                <!-- Reading Pane -->
                <section class="wm-reader-pane" id="wmReaderPane">
                    <div class="wm-empty-state" id="wmReaderEmpty">
                        <em class="icon ni ni-inbox wm-empty-icon"></em>
                        <h4 style="color: #334155; margin: 0 0 6px;">No message selected</h4>
                        <p style="margin: 0; font-size: 14px;">Select an email from the left to read its full contents.</p>
                    </div>

                    <div id="wmReaderContent" style="display: none; height: 100%; display: none; flex-direction: column;">
                        <div class="wm-reader-header">
                            <h3 class="wm-reader-subject" id="readerSubject">Subject line here</h3>
                            <div class="wm-reader-meta-row">
                                <div class="wm-reader-sender-info">
                                    <div class="wm-reader-avatar" id="readerAvatar">S</div>
                                    <div>
                                        <div class="wm-reader-sender-name" id="readerSenderName">Sender Name</div>
                                        <div class="wm-reader-sender-email">
                                            From: <span id="readerSenderEmail" style="color: #1e293b; font-weight: 500;">sender@example.com</span> &bull; 
                                            To: <span id="readerRecipientEmail">me</span> &bull; 
                                            <span id="readerDate" style="color: #64748b;">Date</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wm-reader-actions">
                                    <button type="button" class="btn-reader-action" id="btnReplyMessage" title="Reply to sender">
                                        <em class="icon ni ni-reply"></em> Reply
                                    </button>
                                    <button type="button" class="btn-reader-action" id="btnForwardMessage" title="Forward email">
                                        <em class="icon ni ni-forward"></em> Forward
                                    </button>
                                    <button type="button" class="btn-reader-action btn-danger-light" id="btnTrashMessage" title="Move to Trash">
                                        <em class="icon ni ni-trash"></em> Delete
                                    </button>
                                    <button type="button" class="btn-reader-action d-lg-none" id="btnCloseReader" title="Back to list">
                                        <em class="icon ni ni-cross"></em>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Attachments Box (Hidden if none) -->
                        <div class="wm-attachments-box" id="readerAttachmentsBox" style="display: none;">
                            <div class="wm-attachments-title">
                                <em class="icon ni ni-clip"></em> Attachments (<span id="readerAttachmentCount">0</span>)
                            </div>
                            <div class="wm-attachment-chips" id="readerAttachmentList"></div>
                        </div>

                        <!-- Email Body Render -->
                        <div class="wm-email-body-content">
                            <iframe id="readerIframe" sandbox="allow-same-origin" style="width: 100%; border: none; min-height: 480px;"></iframe>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- COMPOSE EMAIL MODAL -->
    <div class="wm-modal-backdrop" id="composeModal">
        <div class="wm-compose-modal">
            <div class="wm-compose-header">
                <h4 class="wm-compose-title">
                    <em class="icon ni ni-mail-fill"></em>
                    <span id="composeModalTitle">New Message</span>
                </h4>
                <button type="button" class="wm-compose-close" id="btnCloseCompose">&times;</button>
            </div>
            <form id="composeForm" enctype="multipart/form-data">
                <div class="wm-compose-body">
                    <div class="wm-form-row">
                        <label class="wm-form-label">From:</label>
                        <div class="wm-form-input" style="font-weight: 600; color: #0284c7;">
                            <?php echo htmlspecialchars($account['display_name']); ?> &lt;<?php echo htmlspecialchars($account['email']); ?>&gt;
                        </div>
                    </div>
                    <div class="wm-form-row">
                        <label class="wm-form-label" for="composeTo">To:</label>
                        <input type="email" name="to" id="composeTo" class="wm-form-input" placeholder="recipient@example.com" required>
                        <span style="font-size: 12px; color: #0284c7; cursor: pointer;" id="toggleCcBccBtn">Cc / Bcc</span>
                    </div>
                    <div class="wm-form-row" id="rowCc" style="display: none;">
                        <label class="wm-form-label" for="composeCc">Cc:</label>
                        <input type="text" name="cc" id="composeCc" class="wm-form-input" placeholder="comma-separated email addresses">
                    </div>
                    <div class="wm-form-row" id="rowBcc" style="display: none;">
                        <label class="wm-form-label" for="composeBcc">Bcc:</label>
                        <input type="text" name="bcc" id="composeBcc" class="wm-form-input" placeholder="comma-separated email addresses">
                    </div>
                    <div class="wm-form-row">
                        <label class="wm-form-label" for="composeSubject">Subject:</label>
                        <input type="text" name="subject" id="composeSubject" class="wm-form-input" placeholder="Enter subject" required>
                    </div>

                    <div style="margin-top: 14px;">
                        <textarea name="body" id="composeBody" class="wm-compose-textarea" placeholder="Type your message here..."></textarea>
                    </div>

                    <!-- Selected Attachments List -->
                    <div id="composeAttachmentPreview" style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;"></div>
                </div>

                <div class="wm-compose-footer">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button type="submit" class="btn-send-mail" id="btnSendMailSubmit">
                            <em class="icon ni ni-send"></em>
                            <span>Send Email</span>
                        </button>
                        <label class="btn-attach-file" style="margin: 0; cursor: pointer;">
                            <em class="icon ni ni-clip"></em> Attach Files
                            <input type="file" name="attachments[]" id="composeFileInput" multiple style="display: none;">
                        </label>
                    </div>
                    <button type="button" class="btn btn-sm btn-light" id="btnDiscardCompose" style="color: #ef4444;">
                        <em class="icon ni ni-trash"></em> Discard
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SETTINGS & ACCOUNT MANAGEMENT MODAL -->
    <div class="wm-modal-backdrop" id="settingsModal">
        <div class="wm-compose-modal" style="max-width: 680px;">
            <div class="wm-compose-header">
                <h4 class="wm-compose-title">
                    <em class="icon ni ni-setting"></em> Mailbox Settings & Credentials
                </h4>
                <button type="button" class="wm-compose-close" id="btnCloseSettings">&times;</button>
            </div>
            <div class="wm-compose-body">
                <ul class="nav nav-tabs nav-tabs-mb-icon nav-tabs-card" style="margin-bottom: 20px;">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tabServerConfig">Server Config</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tabShareCredentials">Shareable Portal</a>
                    </li>
                    <?php if ($isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tabCreateAccount">Add Mailbox Account</a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content">
                    <!-- Tab 1: Server Config -->
                    <div class="tab-pane active" id="tabServerConfig">
                        <form id="settingsForm">
                            <h6 style="color: #0284c7; margin-bottom: 12px;"><em class="icon ni ni-inbox-in"></em> Incoming Mail Server (IMAP / POP3)</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 12px;">IMAP Host</label>
                                    <input type="text" name="incoming_host" class="form-control form-control-sm" value="<?php echo htmlspecialchars($account['incoming_host'] ?? ''); ?>" placeholder="mail.yourdomain.com">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size: 12px;">Port</label>
                                    <input type="number" name="incoming_port" class="form-control form-control-sm" value="<?php echo htmlspecialchars($account['incoming_port'] ?? '993'); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size: 12px;">Security</label>
                                    <select name="incoming_secure" class="form-control form-control-sm">
                                        <option value="ssl" <?php echo ($account['incoming_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="tls" <?php echo ($account['incoming_secure'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="none" <?php echo ($account['incoming_secure'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 12px;">IMAP Username</label>
                                    <input type="text" name="incoming_username" class="form-control form-control-sm" value="<?php echo htmlspecialchars($account['incoming_username'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 12px;">IMAP Password</label>
                                    <input type="password" name="incoming_password" class="form-control form-control-sm" placeholder="Leave blank to keep unchanged">
                                </div>
                            </div>

                            <hr style="margin: 16px 0;">

                            <h6 style="color: #0284c7; margin-bottom: 12px;"><em class="icon ni ni-send"></em> Outgoing Mail Server (SMTP)</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 12px;">SMTP Host</label>
                                    <input type="text" name="smtp_host" class="form-control form-control-sm" value="<?php echo htmlspecialchars($account['smtp_host'] ?? ''); ?>" placeholder="mail.yourdomain.com">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size: 12px;">Port</label>
                                    <input type="number" name="smtp_port" class="form-control form-control-sm" value="<?php echo htmlspecialchars($account['smtp_port'] ?? '587'); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size: 12px;">Security</label>
                                    <select name="smtp_secure" class="form-control form-control-sm">
                                        <option value="tls" <?php echo ($account['smtp_secure'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo ($account['smtp_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="none" <?php echo ($account['smtp_secure'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 12px;">SMTP Username</label>
                                    <input type="text" name="smtp_username" class="form-control form-control-sm" value="<?php echo htmlspecialchars($account['smtp_username'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 12px;">SMTP Password</label>
                                    <input type="password" name="smtp_password" class="form-control form-control-sm" placeholder="Leave blank to keep unchanged">
                                </div>
                            </div>

                            <hr style="margin: 16px 0;">

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 12px;">Sender Display Name</label>
                                    <input type="text" name="display_name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($account['display_name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 12px;">Change Webmail Password</label>
                                    <input type="password" name="new_password" class="form-control form-control-sm" placeholder="Set new login password">
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label" style="font-size: 12px;">Email Signature (HTML supported)</label>
                                    <textarea name="signature" class="form-control form-control-sm" rows="2" placeholder="e.g. Best regards, Support Team"><?php echo htmlspecialchars($account['signature'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <div class="mt-4 text-right">
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveSettings">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 2: Shareable Portal Credentials -->
                    <div class="tab-pane" id="tabShareCredentials">
                        <div class="alert alert-primary alert-pro mb-3" style="font-size: 13.5px;">
                            <strong><em class="icon ni ni-share"></em> Shareable Webmail Portal</strong><br>
                            You can share this direct link and credentials with your client, partner, or assistant so they can send and receive emails directly without accessing your banking admin dashboard.
                        </div>

                        <?php 
                        $actualHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                        $webmailUrl = $protocol . $actualHost . dirname($_SERVER['REQUEST_URI']) . '/login';
                        $webmailUrl = str_replace('/./', '/', $webmailUrl);
                        ?>

                        <div class="form-group mb-3">
                            <label class="form-label" style="font-size: 12.5px; font-weight: 600;">Webmail Login Portal URL</label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm" id="shareUrlInput" value="<?php echo htmlspecialchars($webmailUrl); ?>" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyShareText('shareUrlInput')">Copy Link</button>
                                </div>
                            </div>
                        </div>

                        <div class="card card-bordered p-3 bg-light">
                            <div style="font-size: 13px; line-height: 1.8;">
                                <div><strong>Portal:</strong> <?php echo htmlspecialchars($sitename); ?> Webmail</div>
                                <div><strong>Email:</strong> <code><?php echo htmlspecialchars($account['email']); ?></code></div>
                                <div><strong>Password:</strong> <code>Admin@2026</code> (or your custom password)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Create Account -->
                    <?php if ($isAdmin): ?>
                    <div class="tab-pane" id="tabCreateAccount">
                        <form id="createAccountForm">
                            <div class="form-group mb-2">
                                <label class="form-label" style="font-size: 12px;">Display Name</label>
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Support Desk, Billing Team" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="form-label" style="font-size: 12px;">Email Address</label>
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="support@yourdomain.com" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="form-label" style="font-size: 12px;">Password for this user</label>
                                <input type="password" name="password" class="form-control form-control-sm" placeholder="Enter secure password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label" style="font-size: 12px;">Role</label>
                                <select name="role" class="form-control form-control-sm">
                                    <option value="staff">Staff / Assistant (Webmail Only)</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-success btn-sm" id="btnCreateAccountSubmit">Create Mailbox User</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer"></div>

    <!-- Scripts -->
    <script src="../assets/js/bundle.js?ver=2.4.0"></script>
    <script>
        // Global State
        let currentFolder = 'inbox';
        let currentSearch = '';
        let currentMessageId = null;
        let selectedMessageIds = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadFolder('inbox');
            setupEventListeners();
        });

        function setupEventListeners() {
            // Folder Navigation
            document.querySelectorAll('.wm-nav-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    const folder = this.getAttribute('data-folder');
                    if (folder) {
                        e.preventDefault();
                        document.querySelectorAll('.wm-nav-item').forEach(el => el.classList.remove('active'));
                        this.classList.add('active');
                        loadFolder(folder);
                    }
                });
            });

            // Live Search with Debounce
            let searchTimeout = null;
            document.getElementById('wmSearchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                currentSearch = this.value.trim();
                searchTimeout = setTimeout(() => {
                    loadFolder(currentFolder, currentSearch);
                }, 300);
            });

            // Sync / Check Mail Button
            document.getElementById('btnSyncMail').addEventListener('click', function() {
                const btn = this;
                btn.classList.add('spinning');
                btn.disabled = true;

                fetch('ajax.php?action=sync_mail')
                    .then(res => res.json())
                    .then(data => {
                        btn.classList.remove('spinning');
                        btn.disabled = false;
                        if (data.success) {
                            showToast(data.message || 'Incoming emails synchronized!', 'success');
                            loadFolder(currentFolder);
                            updateCounts();
                        } else {
                            showToast(data.message || 'Unable to sync mail server.', 'error');
                        }
                    })
                    .catch(err => {
                        btn.classList.remove('spinning');
                        btn.disabled = false;
                        showToast('Sync request error: ' + err, 'error');
                    });
            });

            // Refresh List
            document.getElementById('btnRefreshList').addEventListener('click', function() {
                loadFolder(currentFolder, currentSearch);
                updateCounts();
            });

            // Compose Modal
            const composeModal = document.getElementById('composeModal');
            document.getElementById('btnOpenCompose').addEventListener('click', () => {
                document.getElementById('composeForm').reset();
                document.getElementById('composeModalTitle').innerText = 'New Message';
                document.getElementById('composeAttachmentPreview').innerHTML = '';
                composeModal.classList.add('show');
            });
            document.getElementById('btnCloseCompose').addEventListener('click', () => composeModal.classList.remove('show'));
            document.getElementById('btnDiscardCompose').addEventListener('click', () => composeModal.classList.remove('show'));

            // Toggle Cc / Bcc
            document.getElementById('toggleCcBccBtn').addEventListener('click', function() {
                const rowCc = document.getElementById('rowCc');
                const rowBcc = document.getElementById('rowBcc');
                const isHidden = rowCc.style.display === 'none';
                rowCc.style.display = isHidden ? 'flex' : 'none';
                rowBcc.style.display = isHidden ? 'flex' : 'none';
            });

            // File Attachment Selector Preview
            document.getElementById('composeFileInput').addEventListener('change', function() {
                const preview = document.getElementById('composeAttachmentPreview');
                preview.innerHTML = '';
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    const chip = document.createElement('div');
                    chip.className = 'wm-att-chip';
                    chip.innerHTML = `<em class="icon ni ni-clip"></em> ${file.name} (${Math.round(file.size/1024)} KB)`;
                    preview.appendChild(chip);
                }
            });

            // Send Form Submission
            document.getElementById('composeForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const sendBtn = document.getElementById('btnSendMailSubmit');
                sendBtn.disabled = true;
                sendBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> Sending...`;

                const formData = new FormData(this);
                formData.append('action', 'send_message');

                fetch('ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = `<em class="icon ni ni-send"></em> <span>Send Email</span>`;
                    if (data.success) {
                        showToast(data.message, 'success');
                        composeModal.classList.remove('show');
                        if (currentFolder === 'sent') loadFolder('sent');
                        updateCounts();
                    } else {
                        showToast(data.message || 'Error sending email', 'error');
                    }
                })
                .catch(err => {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = `<em class="icon ni ni-send"></em> <span>Send Email</span>`;
                    showToast('Connection error: ' + err, 'error');
                });
            });

            // Settings Modal
            const settingsModal = document.getElementById('settingsModal');
            document.getElementById('btnOpenSettings').addEventListener('click', () => settingsModal.classList.add('show'));
            document.getElementById('btnCloseSettings').addEventListener('click', () => settingsModal.classList.remove('show'));

            const quickAddBtn = document.getElementById('btnAddAccountQuick');
            if (quickAddBtn) {
                quickAddBtn.addEventListener('click', () => {
                    settingsModal.classList.add('show');
                    // activate tab 3
                    $('a[href="#tabCreateAccount"]').tab('show');
                });
            }

            // Save Settings
            document.getElementById('settingsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'save_settings');

                fetch('ajax.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            settingsModal.classList.remove('show');
                        } else {
                            showToast(data.message || 'Error saving settings', 'error');
                        }
                    });
            });

            // Create Account Form
            const createForm = document.getElementById('createAccountForm');
            if (createForm) {
                createForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('action', 'create_account');

                    fetch('ajax.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message, 'success');
                                createForm.reset();
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                showToast(data.message || 'Error creating account', 'error');
                            }
                        });
                });
            }

            // Reader Buttons
            document.getElementById('btnTrashMessage').addEventListener('click', function() {
                if (currentMessageId) {
                    markMessage(currentMessageId, 'trash');
                    document.getElementById('wmReaderContent').style.display = 'none';
                    document.getElementById('wmReaderEmpty').style.display = 'flex';
                }
            });

            document.getElementById('btnReplyMessage').addEventListener('click', function() {
                if (currentMessageId) {
                    const toEmail = document.getElementById('readerSenderEmail').innerText;
                    const subject = document.getElementById('readerSubject').innerText;
                    document.getElementById('composeTo').value = toEmail;
                    document.getElementById('composeSubject').value = subject.startsWith('Re:') ? subject : 'Re: ' + subject;
                    document.getElementById('composeModalTitle').innerText = 'Reply';
                    composeModal.classList.add('show');
                }
            });

            document.getElementById('btnForwardMessage').addEventListener('click', function() {
                if (currentMessageId) {
                    const subject = document.getElementById('readerSubject').innerText;
                    document.getElementById('composeSubject').value = subject.startsWith('Fwd:') ? subject : 'Fwd: ' + subject;
                    document.getElementById('composeModalTitle').innerText = 'Forward';
                    composeModal.classList.add('show');
                }
            });

            document.getElementById('btnCloseReader').addEventListener('click', function() {
                document.getElementById('wmReaderPane').classList.remove('active-mobile');
            });
        }

        // Load Messages for Folder
        function loadFolder(folder, search = '') {
            currentFolder = folder;
            document.getElementById('currentFolderTitle').innerText = folder.charAt(0).toUpperCase() + folder.slice(1);
            const scrollContainer = document.getElementById('wmMessageScroll');
            scrollContainer.innerHTML = `
                <div class="p-4 text-center text-muted">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                    <div style="font-size: 13px;">Loading ${folder}...</div>
                </div>`;

            fetch(`ajax.php?action=get_messages&folder=${encodeURIComponent(folder)}&search=${encodeURIComponent(search)}`)
                .then(res => res.json())
                .then(res => {
                    if (!res.success) {
                        scrollContainer.innerHTML = `<div class="p-4 text-center text-danger">${res.message || 'Error'}</div>`;
                        return;
                    }
                    renderMessageList(res.data.items);
                })
                .catch(err => {
                    scrollContainer.innerHTML = `<div class="p-4 text-center text-danger">Failed to fetch messages.</div>`;
                });
        }

        function renderMessageList(items) {
            const scrollContainer = document.getElementById('wmMessageScroll');
            if (!items || items.length === 0) {
                scrollContainer.innerHTML = `
                    <div class="wm-empty-state">
                        <em class="icon ni ni-inbox-in wm-empty-icon"></em>
                        <h5 style="color: #64748b; margin-bottom: 4px;">No emails in ${currentFolder}</h5>
                        <p style="font-size: 13px;">Messages in this folder will appear here.</p>
                    </div>`;
                return;
            }

            let html = '';
            items.forEach(msg => {
                const isUnread = (msg.is_read == 0 && msg.folder === 'inbox') ? 'unread' : '';
                const isStarred = msg.is_starred == 1 ? 'starred' : '';
                const attIcon = msg.has_attachment == 1 ? '<span class="wm-badge-att"><em class="icon ni ni-clip"></em></span>' : '';
                const senderDisplay = currentFolder === 'sent' ? ('To: ' + (msg.recipient_name || msg.recipient_email)) : (msg.sender_name || msg.sender_email);
                const dateDisplay = formatDate(msg.date_received);

                html += `
                <div class="wm-msg-item ${isUnread}" data-id="${msg.id}" onclick="openMessage(${msg.id})">
                    <div class="wm-msg-left" onclick="event.stopPropagation()">
                        <button class="wm-star-btn ${isStarred}" onclick="toggleStar(${msg.id}, this)">
                            <em class="icon ni ni-star-fill"></em>
                        </button>
                    </div>
                    <div class="wm-msg-body">
                        <div class="wm-msg-row-1">
                            <span class="wm-msg-sender">${escapeHtml(senderDisplay)}</span>
                            <span class="wm-msg-date">${dateDisplay}</span>
                        </div>
                        <div class="wm-msg-subject">
                            ${escapeHtml(msg.subject || '(No Subject)')}
                            ${attIcon}
                        </div>
                        <div class="wm-msg-snippet">${escapeHtml(msg.snippet || '')}</div>
                    </div>
                </div>`;
            });

            scrollContainer.innerHTML = html;
        }

        // Open and Read Message
        function openMessage(msgId) {
            currentMessageId = msgId;

            // Highlight in list
            document.querySelectorAll('.wm-msg-item').forEach(el => el.classList.remove('active'));
            const activeItem = document.querySelector(`.wm-msg-item[data-id="${msgId}"]`);
            if (activeItem) {
                activeItem.classList.add('active');
                activeItem.classList.remove('unread');
            }

            document.getElementById('wmReaderEmpty').style.display = 'none';
            const readerContent = document.getElementById('wmReaderContent');
            readerContent.style.display = 'flex';

            // Mobile slide in
            document.getElementById('wmReaderPane').classList.add('active-mobile');

            fetch(`ajax.php?action=get_message&id=${msgId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        showToast(data.message || 'Error opening message', 'error');
                        return;
                    }
                    const msg = data.message;
                    document.getElementById('readerSubject').innerText = msg.subject || '(No Subject)';
                    document.getElementById('readerSenderName').innerText = msg.sender_name || msg.sender_email;
                    document.getElementById('readerSenderEmail').innerText = msg.sender_email;
                    document.getElementById('readerRecipientEmail').innerText = msg.recipient_email;
                    document.getElementById('readerDate').innerText = msg.date_received;
                    document.getElementById('readerAvatar').innerText = (msg.sender_name || msg.sender_email).charAt(0).toUpperCase();

                    // Attachments
                    const attBox = document.getElementById('readerAttachmentsBox');
                    const attList = document.getElementById('readerAttachmentList');
                    attList.innerHTML = '';
                    if (msg.attachments_list && msg.attachments_list.length > 0) {
                        attBox.style.display = 'block';
                        document.getElementById('readerAttachmentCount').innerText = msg.attachments_list.length;
                        msg.attachments_list.forEach((att, idx) => {
                            const sizeText = formatBytes(att.size);
                            const chip = document.createElement('a');
                            chip.className = 'wm-att-chip';
                            chip.href = `download.php?msg_id=${msg.id}&att_idx=${idx}`;
                            chip.innerHTML = `<em class="icon ni ni-download"></em> <strong>${escapeHtml(att.name)}</strong> (${sizeText})`;
                            attList.appendChild(chip);
                        });
                    } else {
                        attBox.style.display = 'none';
                    }

                    // Render Body in iframe
                    const iframe = document.getElementById('readerIframe');
                    const bodyHtml = msg.body_html || (msg.body_plain ? `<pre style="font-family: inherit; white-space: pre-wrap;">${escapeHtml(msg.body_plain)}</pre>` : '<p style="color: #94a3b8;">(Empty message body)</p>');
                    iframe.srcdoc = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset="utf-8">
                            <style>
                                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14.5px; line-height: 1.6; color: #1e293b; margin: 0; padding: 10px; }
                                img { max-width: 100%; height: auto; }
                                a { color: #0284c7; }
                            </style>
                        </head>
                        <body>${bodyHtml}</body>
                        </html>`;

                    updateCounts();
                });
        }

        // Toggle Star
        function toggleStar(msgId, btn) {
            const isStarred = btn.classList.contains('starred');
            const action = isStarred ? 'unstar' : 'star';
            btn.classList.toggle('starred');

            const formData = new FormData();
            formData.append('action', 'mark_message');
            formData.append('id', msgId);
            formData.append('mark_action', action);

            fetch('ajax.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    updateCounts();
                });
        }

        // Mark Single Message
        function markMessage(msgId, action) {
            const formData = new FormData();
            formData.append('action', 'mark_message');
            formData.append('id', msgId);
            formData.append('mark_action', action);

            fetch('ajax.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    showToast('Message updated', 'success');
                    loadFolder(currentFolder);
                    updateCounts();
                });
        }

        // Switch active mail account
        function switchMailAccount(accId) {
            const formData = new FormData();
            formData.append('action', 'switch_account');
            formData.append('account_id', accId);

            fetch('ajax.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        showToast(data.message || 'Error switching account', 'error');
                    }
                });
        }

        // Update Unread & Folder Counts
        function updateCounts() {
            fetch('ajax.php?action=get_counts')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.counts) {
                        document.getElementById('badgeInboxCount').innerText = data.counts.unread || 0;
                        document.getElementById('badgeStarredCount').innerText = data.counts.starred || 0;
                        document.getElementById('badgeSentCount').innerText = data.counts.sent || 0;
                        document.getElementById('badgeDraftsCount').innerText = data.counts.drafts || 0;
                        document.getElementById('badgeTrashCount').innerText = data.counts.trash || 0;
                    }
                });
        }

        // Copy Share Link
        function copyShareText(elementId) {
            const copyText = document.getElementById(elementId);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            showToast('Portal link copied to clipboard!', 'success');
        }

        // Toast Helper
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `wm-toast ${type}`;
            const icon = type === 'success' ? 'ni-check-circle-fill' : 'ni-alert-circle';
            toast.innerHTML = `<em class="icon ni ${icon}"></em> <span>${escapeHtml(message)}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // Utility Helpers
        function escapeHtml(text) {
            if (!text) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr.replace(' ', 'T'));
            const now = new Date();
            if (d.toDateString() === now.toDateString()) {
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            return d.toLocaleDateString([], { month: 'short', day: 'numeric' });
        }

        function formatBytes(bytes) {
            if (!bytes || bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>
