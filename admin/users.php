<?php include("header.php");
checkInstallUrl($site_url);
?>
<div class="nk-content">
 <div class="container-xl wide-lg">
        <div class="nk-content-body">
        <div class="nk-block-head">
         <div class="nk-block-head-sub">
         </div>
           <div class="nk-block-between-md g-4 card-bordered">
             <div class="nk-block-head-content">
              <h4 class="nk-block-title fw-normal">Manage users account.</h4>
                <div class="nk-block-des">
                  <p>
                   </p>
                      </div>
                       </div>
                            <div class="nk-block-head-content">
                                        <ul class="nk-block-tools gx-3">
                                            <li><a href="open_user_account" class="btn btn-primary"><span>Open an account</span> <em class="icon ni ni-user-add-fill"></em></a></li>
                                            
                                        </ul>

                                    </div><!-- .nk-block-head-content -->
                                </div><!-- .nk-block-between -->
                            </div><!-- .nk-block-head -->
                        </div>
                    </div>
                 <div class="nk-block nk-block-lg">
                  <div class="nk-block-head">
                                        <div class="nk-block-head-content">
                                            <h4 class="nk-block-title">Registered Users</h4>
                                            <div class="nk-block-des">
                                                <p>Below is the list of registered users and their account information</p> 
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-preview">
                                        <div class="card-inner">
                                            <table class="datatable-init nk-tb-list nk-tb-ulist" data-auto-responsive="false">
                                                <thead>
                                                    <tr class="nk-tb-item nk-tb-head">
                                                        <th class="nk-tb-col nk-tb-col-check">
                                                            <div class="custom-control custom-control-sm custom-checkbox notext">
                                                                <input type="checkbox" class="custom-control-input" id="uid">
                                                                <label class="custom-control-label" for="uid"></label>
                                                            </div>
                                                        </th>
                                                        <th class="nk-tb-col"><span class="sub-text">Fullname</span></th>
                                                        <th class="nk-tb-col tb-col-mb"><span class="sub-text">Balance</span></th>
                                                        <th class="nk-tb-col tb-col-md"><span class="sub-text">Account Number</span></th>
                                                        <th class="nk-tb-col tb-col-lg"><span class="sub-text">Verified</span></th>
                                                        <th class="nk-tb-col tb-col-lg"><span class="sub-text">Date registered</span></th>
                                                        <th class="nk-tb-col tb-col-md"><span class="sub-text">Status</span></th>
                                                        <th class="nk-tb-col nk-tb-col-tools text-right">
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
   <?php 
    $userModals = '';
    $query = $conn->query("SELECT * FROM users WHERE id != 1 ORDER BY id DESC");
    while($userdetails = mysqli_fetch_array($query)){
        $firstname = $userdetails["firstname"];
        $middlename = $userdetails["middlename"];
        $lastname = $userdetails["lastname"];
        $email = $userdetails["email"];
        $accounttype = $userdetails["accounttype"];
        $passport = $userdetails["passport"];
        $dayOFBirth = $userdetails["dayOFBirth"];
        $accountbalance = $userdetails["accountbalance"];
        $address = $userdetails["address"];
        $state = $userdetails["state"];
        $country = $userdetails["country"];
        $phone = $userdetails["phone"];
        $secretCode = $userdetails["secretCode"];
        $zipcode = $userdetails["zipcode"];
        $income = $userdetails["income"];
        $occupation = $userdetails["occupation"];
        $nickname = $userdetails["nickname"];
        $securityQuestion = $userdetails["securityquestion"];
        $answer = $userdetails["answer"];
        $userid = $userdetails["id"];
        $usercurrency = $userdetails["usercurrency"];
        $fullname = trim("$firstname $middlename $lastname");
        $accountnumber = $userdetails["accountnumber"];
        $userimf = $userdetails['imf'];
        $usercot = $userdetails['cot'];
        $email_verify = $userdetails['email_verify'];
        $allowtransfer = $userdetails['allowtransfer'];
        $blocktransfer = $userdetails['blocktransfer'];
        $tfa = $userdetails['tfa'];

        if($email_verify != 0){
            $emailVerify = '<li><em class="icon text-success ni ni-check-circle"></em> <span>Email</span></li>';
        } else {
            $emailVerify = '<li><em class="icon text-danger ni ni-alert-circle"></em> <span>Email</span></li>';
        }

        if($userdetails['status'] == 'blocked'){
            $stat = '<span class="tb-status text-danger">Suspended</span>';
            $suspend = '<a href="#" data-toggle="modal" data-target="#unblockAccount'.$userid.'" class="btn btn-trigger btn-icon" title="Unblock account">
                         <em class="icon ni ni-user text-success"></em>
                        </a>';
        } else {
            $stat = '<span class="tb-status text-success">Active</span>';
            $suspend = '<a href="#" data-toggle="modal" data-target="#suspendAccount'.$userid.'" class="btn btn-trigger btn-icon" title="Suspend User">
                         <em class="icon ni ni-user-cross-fill text-danger"></em>
                        </a>';
        }
        
        if($userdetails['allowtransfer'] == '1'){
            $suspendTranfer = '<a href="#" data-toggle="modal" data-target="#suspendTranfer'.$userid.'" class="btn btn-trigger btn-icon" title="Block Funds Transfer">
                         <em class="icon ni ni-tranx text-danger"></em>
                        </a>';
        } else {
            $suspendTranfer = '<a href="#" data-toggle="modal" data-target="#unblockTranfer'.$userid.'" class="btn btn-trigger btn-icon" title="Unblock Funds Transfer">
                         <em class="icon ni ni-tranx text-success"></em>
                        </a>';
        }

        if($userdetails['approve'] == 0){
            $approveB = '
              <li class="nk-tb-action">
               <a href="approve_account?id='.$userid.'" class="btn btn-trigger btn-icon" data-toggle="tooltip" data-placement="top" title="Approve Account">
                <em class="icon ni ni-check-circle text-success"></em>
               </a>
            </li>
            ';
        } else {
            $approveB = "";
        }

        if($userdetails['tfa'] == 'active'){
            $twofa = '
              <li class="nk-tb-action">
               <a href="user2fa?id='.$userid.'&action=disable" class="btn btn-trigger btn-icon" data-toggle="tooltip" data-placement="top" title="Disable 2FA">
                <em class="icon ni ni-security text-danger"></em>
               </a>
            </li>
            ';
        } else {
            $twofa = '
              <li class="nk-tb-action">
               <a href="user2fa?id='.$userid.'&action=enable" class="btn btn-trigger btn-icon" data-toggle="tooltip" data-placement="top" title="Enable 2FA">
                <em class="icon ni ni-security text-success"></em>
               </a>
            </li>
            ';  
        }

        // Build modals for this user (rendered outside table)
        $userModals .= '
        <!-- Suspend Transfer Modal -->
        <div class="modal fade" tabindex="-1" id="suspendTranfer'.$userid.'">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a href="#" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                    <div class="modal-header text-white" style="background-color:#033d75; color: white;">
                        <h5 class="modal-title text-white">Suspend Funds Transfer</h5>
                    </div>
                    <div class="modal-body">
                       <strong>You are about to suspend '.htmlspecialchars($fullname).' from carrying out further transfer. The next transfer will be allowed to go through with a pending payment status, subsequent transfer after this won\'t go through at all.</strong>
                       <div class="suspendTranferResult'.$userid.' mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <form action="" method="post" class="w-100 d-flex justify-content-end">
                            <input type="hidden" name="suspendTranfer'.$userid.'" value="'.$userid.'" id="suspendTranferInput'.$userid.'">
                            <button type="button" class="btn btn-primary btn-sm mr-2 suspendTransferActionBtn" data-id="'.$userid.'">Suspend</button>
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cancel</button>
                        </form>
                    </div>     
                </div>
            </div>
        </div>

        <!-- Unblock Transfer Modal -->
        <div class="modal fade" tabindex="-1" id="unblockTranfer'.$userid.'">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a href="#" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                    <div class="modal-header text-white" style="background-color:#033d75; color: white;">
                        <h5 class="modal-title text-white">Unblock Funds Transfer</h5>
                    </div>
                    <div class="modal-body">
                       <strong>You are about to Unblock '.htmlspecialchars($fullname).' to carry out further transfer. The next transfer will be allowed to go through after unblocking this user.</strong>
                       <div class="unblockTranferResult'.$userid.' mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <form action="" method="post" class="w-100 d-flex justify-content-end">
                            <input type="hidden" name="unblockTranfer'.$userid.'" value="'.$userid.'" id="unblockTranferInput'.$userid.'">
                            <button type="button" class="btn btn-primary btn-sm mr-2 unblockTransferActionBtn" data-id="'.$userid.'">Unblock</button>
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cancel</button>
                        </form>
                    </div>     
                </div>
            </div>
        </div>

        <!-- Account Suspension Modal -->
        <div class="modal fade" tabindex="-1" id="suspendAccount'.$userid.'">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a href="#" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                    <div class="modal-header text-white" style="background-color:#033d75; color: white;">
                        <h5 class="modal-title text-white">Suspend User Account</h5>
                    </div>
                    <div class="modal-body">
                       <strong>You are about to Suspend '.htmlspecialchars($fullname).' from the system. The user will be restricted from accessing his/her account if you proceed with this action.</strong>
                       <div class="suspendResult'.$userid.' mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <form action="" method="post" class="w-100 d-flex justify-content-end">
                            <input type="hidden" name="suspend'.$userid.'" value="'.$userid.'" id="suspendInput'.$userid.'">
                            <button type="button" class="btn btn-primary btn-sm mr-2 suspendAccountActionBtn" data-id="'.$userid.'">Suspend</button>
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cancel</button>
                        </form>
                    </div>     
                </div>
            </div>
        </div>

        <!-- Unblock User Account Modal -->
        <div class="modal fade" tabindex="-1" id="unblockAccount'.$userid.'">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a href="#" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                    <div class="modal-header text-white" style="background-color:#033d75; color: white;">
                        <h5 class="modal-title text-white">Unblock User Account</h5>
                    </div>
                    <div class="modal-body">
                       <strong>You are about to unblock '.htmlspecialchars($fullname).' whose account was currently restricted from the system. The user will be able to access his/her account onwards if you proceed with this action.</strong>
                       <div class="unblockResult'.$userid.' mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <form action="" method="post" class="w-100 d-flex justify-content-end">
                            <input type="hidden" name="unblock'.$userid.'" value="'.$userid.'" id="unblockInput'.$userid.'">
                            <button type="button" class="btn btn-primary btn-sm mr-2 unblockAccountActionBtn" data-id="'.$userid.'">Unblock</button>
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cancel</button>
                        </form>
                    </div>     
                </div>
            </div>
        </div>
        ';
    ?>
        <tr class="nk-tb-item">
            <td class="nk-tb-col nk-tb-col-check">
                <div class="custom-control custom-control-sm custom-checkbox notext">
                    <input type="checkbox" class="custom-control-input" id="<?php echo$userid ?>">
                    <label class="custom-control-label" for="<?php echo$userid ?>"></label>
                </div>
            </td>
            <td class="nk-tb-col">
                <div class="user-card">
                    <div class="user-avatar bg-dim-primary d-none d-sm-flex">
                        <span><?php echo substr($fullname, 0, 2); ?></span>
                    </div>
                    <div class="user-info">
                        <span class="tb-lead"><?php echo$fullname ?><span class="dot dot-success d-md-none ml-1"></span></span>
                        <span><?php echo$email ?></span>
                    </div>
                </div>
            </td>
            <td class="nk-tb-col tb-col-mb" data-order="35040.34">
                <span class="tb-amount"><?php echo$accountbalance ?> <span class="currency"><?php echo$money ?></span></span>
            </td>
            <td class="nk-tb-col tb-col-md">
                <span><?php echo$accountnumber ?></span>
            </td>
            <td class="nk-tb-col tb-col-lg" data-order="Email Verified - Kyc Unverified">
                <ul class="list-status">
                    <?php echo$emailVerify; ?>
                </ul>
            </td>
            <td class="nk-tb-col tb-col-lg">
                <span><?php echo$userdetails['datecreated'] ?></span>
            </td>
            <td class="nk-tb-col tb-col-md">
                <?php echo$stat; ?>
            </td>
            <td class="nk-tb-col nk-tb-col-tools">
                <ul class="nk-tb-actions gx-1">
                    <li class="nk-tb-action">
                        <a href="fund_user?accountnumber=<?php echo$accountnumber ?>&id=<?php echo$userid?>" class="btn btn-trigger btn-icon" data-toggle="tooltip" data-placement="top" title="Fund account">
                            <em class="icon ni ni-wallet-fill"></em>
                        </a>
                    </li>
                    <li class="nk-tb-action">
                        <a href="email_user?email=<?php echo$email ?>" class="btn btn-trigger btn-icon" data-toggle="tooltip" data-placement="top" title="Send Email">
                            <em class="icon ni ni-mail-fill"></em>
                        </a>
                    </li>
                    <?php echo$approveB ?>
                    <?php echo$twofa ?>
                    <li class="nk-tb-action">
                        <?php echo$suspend; ?>
                    </li>
                    <li class="nk-tb-action">
                        <?php echo$suspendTranfer; ?>
                    </li>
                    <li>
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <ul class="link-list-opt no-bdr">
                                    <li><a href="user_profile?id=<?php echo$userid ?>"><em class="icon ni ni-eye"></em><span>View Details</span></a></li>
                                    <li><a href="user_transaction?id=<?php echo$userid ?>"><em class="icon ni ni-repeat"></em><span>Transaction</span></a></li>
                                    <li><a href="user_activity?id=<?php echo$userid ?>"><em class="icon ni ni-activity-round"></em><span>Activities</span></a></li>
                                    <li class="divider"></li>
                                    <li><a href="user_password_reset?id=<?php echo$userid; ?>"><em class="icon ni ni-shield-star"></em><span>Reset Pass</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </td>
        </tr>
    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div><!-- .card-preview -->
                                </div> <!-- nk-block -->
                            </div><!-- .components-preview -->

<?php echo $userModals; ?>

<script type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '.suspendTransferActionBtn', function(e) {
        e.preventDefault();
        var uid = $(this).data('id');
        var val = $('#suspendTranferInput' + uid).val();
        $.ajax({
            type: "POST",
            url: "../scripts/suspend_transfer.php?id=" + uid,
            data: { ["suspendTranfer" + uid]: val },
            success: function(data) {
                $('.suspendTranferResult' + uid).html(data);
            }
        });
    });

    $(document).on('click', '.unblockTransferActionBtn', function(e) {
        e.preventDefault();
        var uid = $(this).data('id');
        var val = $('#unblockTranferInput' + uid).val();
        $.ajax({
            type: "POST",
            url: "../scripts/unblock_transfer.php?id=" + uid,
            data: { ["unblockTranfer" + uid]: val },
            success: function(data) {
                $('.unblockTranferResult' + uid).html(data);
            }
        });
    });

    $(document).on('click', '.suspendAccountActionBtn', function(e) {
        e.preventDefault();
        var uid = $(this).data('id');
        var val = $('#suspendInput' + uid).val();
        $.ajax({
            type: "POST",
            url: "../scripts/restriction.php?id=" + uid + "&action=suspend",
            data: { ["suspend" + uid]: val },
            success: function(data) {
                $('.suspendResult' + uid).html(data);
            }
        });
    });

    $(document).on('click', '.unblockAccountActionBtn', function(e) {
        e.preventDefault();
        var uid = $(this).data('id');
        var val = $('#unblockInput' + uid).val();
        $.ajax({
            type: "POST",
            url: "../scripts/restriction.php?id=" + uid + "&action=unblock",
            data: { ["unblock" + uid]: val },
            success: function(data) {
                $('.unblockResult' + uid).html(data);
            }
        });
    });
});
</script>

<?php include("footer.php"); ?>