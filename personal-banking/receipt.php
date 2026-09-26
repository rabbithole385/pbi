<?php
include("header.php");
include("../scripts/connect.php");

$token = isset($_GET['token']) ? filterString($_GET['token']) : '';
$ref = isset($_GET['ref']) ? filterString($_GET['ref']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$trx = null;
if (!empty($token)) {
    $q = $conn->query("SELECT * FROM transactions WHERE token = '$token' AND userid = '$userid' LIMIT 1");
    if ($q && $q->num_rows > 0) $trx = mysqli_fetch_assoc($q);
}
if (!$trx && !empty($ref)) {
    $q = $conn->query("SELECT * FROM transactions WHERE refNumber = '$ref' AND userid = '$userid' LIMIT 1");
    if ($q && $q->num_rows > 0) $trx = mysqli_fetch_assoc($q);
}
if (!$trx && $id > 0) {
    $q = $conn->query("SELECT * FROM transactions WHERE id = '$id' AND userid = '$userid' LIMIT 1");
    if ($q && $q->num_rows > 0) $trx = mysqli_fetch_assoc($q);
}

// Fallback to latest transaction if no parameters given
if (!$trx) {
    $q = $conn->query("SELECT * FROM transactions WHERE userid = '$userid' ORDER BY id DESC LIMIT 1");
    if ($q && $q->num_rows > 0) $trx = mysqli_fetch_assoc($q);
}
?>

<style>
.receipt-wrapper {
    max-width: 760px;
    margin: 20px auto 40px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    color: #1e293b;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
.receipt-head {
    background: linear-gradient(135deg, #033d75 0%, #0a2540 100%);
    color: #ffffff;
    padding: 32px 36px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    border-bottom: 3px solid #fe473a;
}
.receipt-brand img {
    max-height: 48px;
    width: auto;
    object-fit: contain;
    background: rgba(255, 255, 255, 0.95);
    padding: 4px 10px;
    border-radius: 6px;
}
.receipt-title-block h3 {
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    letter-spacing: 0.5px;
}
.receipt-title-block p {
    color: #cbd5e1;
    font-size: 13px;
    margin: 4px 0 0;
}
.receipt-body {
    padding: 32px 36px;
}
.receipt-status-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 22px;
    border-radius: 10px;
    margin-bottom: 28px;
}
.status-completed {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}
.status-pending {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #92400e;
}
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.receipt-amount-box {
    text-align: right;
}
.receipt-amount {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}
.receipt-amount-sub {
    font-size: 12px;
    color: #64748b;
    margin-top: 4px;
}
.receipt-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}
@media (max-width: 600px) {
    .receipt-grid { grid-template-columns: 1fr; }
}
.receipt-panel {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 8px;
    padding: 16px 18px;
}
.receipt-panel-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.6px;
    margin-bottom: 8px;
}
.receipt-panel-content strong {
    display: block;
    color: #0f172a;
    font-size: 15px;
}
.receipt-panel-content span {
    font-size: 13px;
    color: #475569;
}
.receipt-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0 28px;
}
.receipt-table tr {
    border-bottom: 1px solid #f1f5f9;
}
.receipt-table td {
    padding: 12px 6px;
    font-size: 14px;
}
.receipt-table td.field-name {
    color: #64748b;
    width: 40%;
}
.receipt-table td.field-value {
    color: #0f172a;
    font-weight: 600;
    text-align: right;
}
.receipt-footer-notes {
    font-size: 12px;
    color: #94a3b8;
    line-height: 1.6;
    padding-top: 18px;
    border-top: 1px dashed #cbd5e1;
    margin-bottom: 24px;
}
.receipt-seal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 10px;
}
.receipt-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
    padding: 20px 0 10px;
}

@media print {
    /* Hide everything except the receipt */
    body * { visibility: hidden; }
    .receipt-wrapper, .receipt-wrapper * { visibility: visible; }
    .receipt-wrapper {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: 100%;
        border: none;
        box-shadow: none;
        margin: 0;
        padding: 0;
    }
    .receipt-actions, .nk-header, .nk-sidebar, .nk-footer, .breadcrumb {
        display: none !important;
    }
}
</style>

<div class="nk-content nk-content-fluid">
    <div class="container-xl wide-lg">
        <div class="nk-content-body">
            
            <?php if (!$trx): ?>
                <div class="card card-bordered text-center p-5">
                    <div class="card-inner">
                        <em class="icon ni ni-file-text display-1 text-muted"></em>
                        <h4 class="mt-3">No Transaction Receipt Found</h4>
                        <p class="text-muted">The transaction details could not be retrieved or you do not have any transactions yet.</p>
                        <a href="dashboard" class="btn btn-primary mt-2">Return to Dashboard</a>
                    </div>
                </div>
            <?php else: 
                $amount = $trx['amount'];
                $refNumber = $trx['refNumber'];
                $accountholder = !empty($trx['accountholder']) ? $trx['accountholder'] : $fullname;
                $bankname = !empty($trx['bankname']) ? $trx['bankname'] : $sitename;
                $accountnumberB = !empty($trx['accountnumber']) ? $trx['accountnumber'] : 'N/A';
                $routineB = !empty($trx['routineNumber']) ? $trx['routineNumber'] : '';
                $dated = $trx['dated'];
                $avalbal = $trx['accountbalance'];
                $desc = !empty($trx['description']) ? $trx['description'] : 'Funds Transfer';
                $scope = !empty($trx['scope']) ? $trx['scope'] : 'Transfer';
                $type = !empty($trx['type']) ? $trx['type'] : 'Debit';
                $isCompleted = ($trx['status'] == 1);
            ?>

            <div class="receipt-wrapper" id="receiptContent">
                <!-- Receipt Head -->
                <div class="receipt-head">
                    <div class="receipt-brand">
                        <img src="../<?php echo !empty($darklogo) ? $darklogo : (!empty($logo) ? $logo : 'images/logo.png'); ?>" alt="<?php echo htmlspecialchars($sitename); ?>">
                    </div>
                    <div class="receipt-title-block text-right">
                        <h3>TRANSACTION RECEIPT</h3>
                        <p><?php echo htmlspecialchars($sitename); ?> · Electronic Transfer Advice</p>
                    </div>
                </div>

                <!-- Receipt Body -->
                <div class="receipt-body">
                    <!-- Status Banner -->
                    <div class="receipt-status-banner <?php echo $isCompleted ? 'status-completed' : 'status-pending'; ?>">
                        <div>
                            <div class="status-badge">
                                <em class="icon ni <?php echo $isCompleted ? 'ni-check-circle-fill' : 'ni-alert-circle'; ?>"></em>
                                <span><?php echo $isCompleted ? 'Transaction Successful' : 'Transaction Processing'; ?></span>
                            </div>
                            <small style="color: inherit; opacity: 0.85;">Ref: <?php echo htmlspecialchars($refNumber); ?></small>
                        </div>
                        <div class="receipt-amount-box">
                            <div class="receipt-amount"><?php echo $money . ' ' . number_format((float)$amount, 2); ?></div>
                            <div class="receipt-amount-sub"><?php echo strtoupper($scope); ?> (<?php echo strtoupper($type); ?>)</div>
                        </div>
                    </div>

                    <!-- Sender / Beneficiary Grid -->
                    <div class="receipt-grid">
                        <div class="receipt-panel">
                            <div class="receipt-panel-title">Originating Account (Sender)</div>
                            <div class="receipt-panel-content">
                                <strong><?php echo htmlspecialchars($fullname); ?></strong>
                                <span>Account: <?php echo substr($accountnumber, 0, 4) . '******' . substr($accountnumber, -2); ?></span><br>
                                <span>Type: <?php echo htmlspecialchars($accounttype); ?></span>
                            </div>
                        </div>

                        <div class="receipt-panel">
                            <div class="receipt-panel-title">Beneficiary Details (Recipient)</div>
                            <div class="receipt-panel-content">
                                <strong><?php echo htmlspecialchars($accountholder); ?></strong>
                                <span>Bank: <?php echo htmlspecialchars($bankname); ?></span><br>
                                <span>Account / Ref: <?php echo htmlspecialchars($accountnumberB); ?></span>
                                <?php if (!empty($routineB)): ?>
                                    <br><span><?php echo htmlspecialchars($routine); ?>: <?php echo htmlspecialchars($routineB); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown Table -->
                    <table class="receipt-table">
                        <tr>
                            <td class="field-name">Transaction Reference</td>
                            <td class="field-value font-monospace"><?php echo htmlspecialchars($refNumber); ?></td>
                        </tr>
                        <tr>
                            <td class="field-name">Date &amp; Time</td>
                            <td class="field-value"><?php echo htmlspecialchars($dated); ?></td>
                        </tr>
                        <tr>
                            <td class="field-name">Transaction Category</td>
                            <td class="field-value"><?php echo htmlspecialchars($scope); ?></td>
                        </tr>
                        <tr>
                            <td class="field-name">Payment Channel</td>
                            <td class="field-value">Online Internet Banking (Secured Channel)</td>
                        </tr>
                        <tr>
                            <td class="field-name">Description / Narration</td>
                            <td class="field-value"><?php echo htmlspecialchars($desc); ?></td>
                        </tr>
                        <tr>
                            <td class="field-name">Transaction Fee</td>
                            <td class="field-value"><?php echo $money; ?> 0.00 (Included)</td>
                        </tr>
                        <tr>
                            <td class="field-name">Available Account Balance</td>
                            <td class="field-value text-primary"><?php echo $money . ' ' . number_format((float)$avalbal, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="field-name">Verification Status</td>
                            <td class="field-value"><span class="badge badge-dim <?php echo $isCompleted ? 'badge-success' : 'badge-warning'; ?>"><?php echo $isCompleted ? 'VERIFIED &amp; COMPLETED' : 'IN REVIEW'; ?></span></td>
                        </tr>
                    </table>

                    <!-- Legal Disclaimer & Security Notice -->
                    <div class="receipt-footer-notes">
                        <p class="mb-1">
                            <strong>Official Banking Advice:</strong> This advice is electronically generated via <?php echo htmlspecialchars($sitename); ?> secured banking system. All fund transfers are governed by our Electronic Fund Transfer Agreement and regulatory compliance policies.
                        </p>
                        <p class="mb-0">
                            If you have questions regarding this transaction, contact customer support at <strong><?php echo htmlspecialchars($siteemail); ?></strong> quoting the transaction reference above.
                        </p>
                    </div>

                    <div class="receipt-seal">
                        <small class="text-muted">Issued: <?php echo date("Y-m-d H:i:s"); ?> UTC</small>
                        <div class="text-right">
                            <span class="badge badge-outline-primary" style="letter-spacing: 1px;">
                                <em class="icon ni ni-shield-check"></em> SECURED DIGITAL RECEIPT
                            </span>
                        </div>
                    </div>

                    <!-- Print / Navigate Actions -->
                    <div class="receipt-actions">
                        <button type="button" class="btn btn-primary btn-lg" onclick="window.print()">
                            <em class="icon ni ni-printer"></em> Print / Save as PDF
                        </button>
                        <a href="transfer" class="btn btn-outline-primary btn-lg">
                            <em class="icon ni ni-send"></em> Make Another Transfer
                        </a>
                        <a href="summary" class="btn btn-light btn-lg">
                            <em class="icon ni ni-histroy"></em> Statement History
                        </a>
                        <a href="dashboard" class="btn btn-light btn-lg">
                            <em class="icon ni ni-home"></em> Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include("footer.php"); ?>
