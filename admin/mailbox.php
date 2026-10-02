<?php 
require_once("header.php");
require_once("../scripts/mailbox_functions.php");

// Ensure mailbox database tables are initialized
mb_init_database();
$currentAccount = mb_get_default_account();

$actualHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$scriptDir = dirname($_SERVER['REQUEST_URI']);
$baseDir = rtrim(str_replace('/admin', '', $scriptDir), '/');
$shareableLoginUrl = $protocol . $actualHost . $baseDir . '/mail/login';
$webmailAppUrl = $protocol . $actualHost . $baseDir . '/mail/';
?>

<div class="nk-content">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <!-- Page Head -->
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">
                                <em class="icon ni ni-inbox-in text-primary"></em> Admin Mailbox &amp; Webmail
                            </h3>
                            <div class="nk-block-des text-soft">
                                <p>Built-in communication suite for sending, receiving, and managing official <?php echo htmlspecialchars($sitename); ?> mail.</p>
                            </div>
                        </div><!-- .nk-block-head-content -->
                        <div class="nk-block-head-content">
                            <div class="toggle-wrap nk-block-tools-toggle">
                                <ul class="nk-block-tools g-3">
                                    <li>
                                        <a href="<?php echo htmlspecialchars($webmailAppUrl); ?>" target="_blank" class="btn btn-outline-primary">
                                            <em class="icon ni ni-external"></em>
                                            <span>Open Standalone Webmail</span>
                                        </a>
                                    </li>
                                    <li>
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#adminAddAccountModal">
                                            <em class="icon ni ni-user-add"></em>
                                            <span>Create Mailbox Account</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div><!-- .nk-block-head-content -->
                    </div><!-- .nk-block-between -->
                </div><!-- .nk-block-head -->

                <!-- Quick Share Credentials Card (for sharing to client / assistant) -->
                <div class="card card-bordered mb-4" style="background: linear-gradient(135deg, #091322 0%, #16243b 100%); color: #ffffff; border-radius: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                    <div class="card-inner">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-6">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge badge-primary mr-2" style="background: #0284c7; font-size: 11px; padding: 4px 10px;">
                                        <em class="icon ni ni-share mr-1"></em> SHAREABLE WEBMAIL ACCESS
                                    </span>
                                    <span style="font-size: 12px; color: #94a3b8;">Share with team member / client</span>
                                </div>
                                <h5 class="text-white mb-1">Separate Login Portal for <?php echo htmlspecialchars($sitename); ?></h5>
                                <p style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 0;">
                                    You can share this direct link and login credentials with anyone you want to manage emails without granting them access to your banking admin panel.
                                </p>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-3" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px;">
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary">
                                        <span style="font-size: 12.5px; color: #94a3b8;">Portal URL:</span>
                                        <div class="d-flex align-items-center">
                                            <code style="color: #38bdf8; background: rgba(0,0,0,0.3); padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 6px;"><?php echo htmlspecialchars($shareableLoginUrl); ?></code>
                                            <button class="btn btn-xs btn-outline-light" onclick="copyToClipboard('<?php echo htmlspecialchars($shareableLoginUrl); ?>', 'Portal Link')">
                                                <em class="icon ni ni-copy"></em>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span style="font-size: 12.5px; color: #94a3b8;">Login Email:</span>
                                        <div class="d-flex align-items-center">
                                            <code style="color: #f1f5f9; background: rgba(0,0,0,0.3); padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 6px;"><?php echo htmlspecialchars($currentAccount['email'] ?? 'admin@pbigroups.com'); ?></code>
                                            <button class="btn btn-xs btn-outline-light" onclick="copyToClipboard('<?php echo htmlspecialchars($currentAccount['email'] ?? 'admin@pbigroups.com'); ?>', 'Email')">
                                                <em class="icon ni ni-copy"></em>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span style="font-size: 12.5px; color: #94a3b8;">Password:</span>
                                        <div class="d-flex align-items-center">
                                            <code style="color: #f1f5f9; background: rgba(0,0,0,0.3); padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 6px;">Admin@2026</code>
                                            <button class="btn btn-xs btn-outline-light" onclick="copyToClipboard('Admin@2026', 'Password')">
                                                <em class="icon ni ni-copy"></em>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Embedded Full Webmail Interface -->
                <div class="card card-bordered card-preview" style="border-radius: 14px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-2 px-3">
                        <div class="d-flex align-items-center">
                            <span class="badge badge-dot badge-success mr-2"></span>
                            <span style="font-size: 13.5px; font-weight: 600; color: #1e293b;">Active Mailbox: <?php echo htmlspecialchars($currentAccount['email'] ?? 'Default Mailbox'); ?></span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-light" onclick="document.getElementById('webmailFrame').contentWindow.location.reload();" title="Refresh Inbox">
                                <em class="icon ni ni-reload"></em> Refresh
                            </button>
                            <a href="<?php echo htmlspecialchars($webmailAppUrl); ?>" target="_blank" class="btn btn-sm btn-primary ml-1">
                                <em class="icon ni ni-expand"></em> Fullscreen
                            </a>
                        </div>
                    </div>
                    <div style="background: #f8fafc; padding: 0;">
                        <iframe id="webmailFrame" src="../mail/index.php" style="width: 100%; height: 820px; border: none; display: block;"></iframe>
                    </div>
                </div>

            </div><!-- .nk-content-body -->
        </div><!-- .nk-content-inner -->
    </div><!-- .container-fluid -->
</div><!-- .nk-content -->

<!-- MODAL: ADD MAILBOX ACCOUNT -->
<div class="modal fade" id="adminAddAccountModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #164e63; color: white;">
                <h5 class="modal-title text-white"><em class="icon ni ni-user-add mr-1"></em> Create Mailbox Account</h5>
                <a href="#" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
            </div>
            <form id="adminNewAccountForm">
                <div class="modal-body bg-light">
                    <p class="text-soft mb-3" style="font-size: 13px;">Create a dedicated mailbox account (e.g., support, billing, or assistant) that can log into the standalone Webmail portal.</p>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 12.5px;">Display Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Support Team, Account Manager" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 12.5px;">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="support@yourdomain.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 12.5px;">Login Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter secure password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 12.5px;">Account Role</label>
                        <select name="role" class="form-control">
                            <option value="staff" selected>Staff / Assistant (Webmail Only)</option>
                            <option value="admin">Administrator (Full Access)</option>
                        </select>
                    </div>
                    <div id="adminNewAccountResult"></div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitNewAcc">Create &amp; Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text, label) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            toastr.success(label + ' copied to clipboard!', 'Success', {"progressBar": true});
        });
    } else {
        const temp = document.createElement('textarea');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        toastr.success(label + ' copied to clipboard!', 'Success', {"progressBar": true});
    }
}

document.getElementById('adminNewAccountForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitNewAcc');
    btn.disabled = true;
    btn.innerText = 'Creating...';

    const formData = new FormData(this);
    formData.append('action', 'create_account');

    fetch('../mail/ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = 'Create & Save';
        if (data.success) {
            toastr.success(data.message, 'Mailbox Created', {"progressBar": true});
            $('#adminAddAccountModal').modal('hide');
            document.getElementById('adminNewAccountForm').reset();
            document.getElementById('webmailFrame').contentWindow.location.reload();
        } else {
            toastr.error(data.message || 'Error creating account', 'Error', {"progressBar": true});
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = 'Create & Save';
        toastr.error('Request failed: ' + err, 'Error', {"progressBar": true});
    });
});
</script>

<?php require_once("footer.php"); ?>
