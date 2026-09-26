 <?php include("header.php"); ?>

 <div class="nk-content">
 <div class="container-xl wide-lg">
        <div class="nk-content-body">
        <div class="nk-block-head">
         <div class="nk-block-head-sub">
         </div>
           <div class="nk-block-between-md g-4 card-bordered">
             <div class="nk-block-head-content">
              <h4 class="nk-block-title fw-normal">SMTP Settings.</h4>
                <div class="nk-block-des">
                  <p>
                   </p>
                      </div>
                       </div>
                            <div class="nk-block-head-content">
                                        <ul class="nk-block-tools gx-3">
                                            <li><a onclick="window.history.go(-1);" class="btn btn-primary"><span>Back</span> <em class="icon ni ni-arrow-left"></em></a></li>
                                            <li><a href="fund_user" class="btn btn-success"><span>Fund an account</span> <em class="icon ni ni-invest"></em></a></li>
                                        </ul>
                                    </div><!-- .nk-block-head-content -->
                                </div><!-- .nk-block-between -->
                            </div><!-- .nk-block-head -->
                        </div>
                    </div>


                          <div class="nk-block nk-block-lg">
                                    <div class="nk-block-head">
                                        <div class="nk-block-head-content">
                                            <h4 class="title nk-block-title">Email configuration</h4>
                                            <div class="nk-block-des">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-bordered">
                                        <div class="card-inner">
                                            <div class="card-head d-flex justify-content-between align-items-center flex-wrap">
                                                <h5 class="card-title">Email &amp; SMTP Settings</h5>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillResend()">
                                                    <em class="icon ni ni-bolt"></em> Auto-fill Resend
                                                </button>
                                            </div>
                                            
                                            <div class="alert alert-pro alert-primary my-3">
                                                <div class="alert-text">
                                                    <h6 class="alert-title mb-1">PBI Group Email Configuration</h6>
                                                    <p class="small text-muted mb-0">
                                                        <strong>Display Name:</strong> Set to <code>PBI Group</code>.<br>
                                                        <strong>Sender Addresses:</strong> Emails will be delivered from <code>info@pbigroups.com</code> and <code>support@pbigroups.com</code>.<br>
                                                        <strong>Using Resend?</strong> Click <em>Auto-fill Resend</em> above, paste your API key in the Password field, and hit Update.
                                                    </p>
                                                </div>
                                            </div>

                                            <form action="../scripts/update_smtp" class="gy-3" id="UpdateForm" method="post">
                                                <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label" for="site-name">SMTP host</label>
                                                            <span class="form-note">Specify your smtp Host. (e.g  gmail.com)</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="text" class="form-control" id="host" value="<?php echo$smtp_host?>" name="host">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                  <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label">Username</label>
                                                            <span class="form-note">Specify the username of your SMTP. (eg. admin@example.com).</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="text" class="form-control" id="username" value="<?php echo$smtp_username ?>" name="username">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label font-weight-bold">Info API Key / Password <span class="badge badge-primary">info@pbigroups.com</span></label>
                                                            <span class="form-note">Resend API key for info@ emails (Registration, Wire/Transfer OTP, Debit/Credit alerts, Password resets).</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="text" class="form-control" id="info_password" value="<?php echo !empty($info_password) ? htmlspecialchars($info_password) : htmlspecialchars($smtp_password); ?>" name="info_password" placeholder="re_...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label font-weight-bold">Support API Key / Password <span class="badge badge-info">support@pbigroups.com</span></label>
                                                            <span class="form-note">Resend API key for support@ emails (Support ticket replies, customer care, queries). If blank, Info key is used.</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="text" class="form-control" id="support_password" value="<?php echo !empty($support_password) ? htmlspecialchars($support_password) : ''; ?>" name="support_password" placeholder="re_...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" id="password" name="password" value="<?php echo htmlspecialchars($smtp_password); ?>">
                                                  <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label">SMTP</label>
                                                            <span class="form-note">SMTP authentication (TLS/SSL).</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="text" class="form-control" id="auth" value="<?php echo$smtp_auth ?>" name="auth">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                  <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label">Port</label>
                                                            <span class="form-note">Specify your smtp port.</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="text" class="form-control" id="port" value="<?php echo$smtp_port ?>" name="port">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                  <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label">Display Name</label>
                                                            <span class="form-note">Specify the Name that your mail will be displayed with.</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="text" class="form-control" id="name" value="<?php echo$display_name ?>" name="name">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                                                                <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label font-weight-bold">Send Test Email To</label>
                                                            <span class="form-note">Enter an email address (e.g. gideonprincewill5@gmail.com) to test your configuration immediately.</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="email" class="form-control" id="test_email" name="test_email" placeholder="gideonprincewill5@gmail.com" value="gideonprincewill5@gmail.com">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-lg-7 offset-lg-5">
                                                        <div id="updateResult"></div>
                                                        <div class="form-group mt-2">
                                                            <button type="submit" class="btn btn-lg btn-primary" id="btn1"><em class="icon ni ni-send"></em> Save &amp; Send Test Email</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div><!-- card -->
                                </div><!-- .nk-block -->

                                   <div class="nk-block nk-block-lg">
                                    <div class="nk-block-head">
                                        <div class="nk-block-head-content">
                                            <h4 class="title nk-block-title">SMS Management</h4>
                                            <div class="nk-block-des">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-bordered">
                                        <div class="card-inner">
                                            <div class="card-head">
                                                <h5 class="card-title">SMS Configuration</h5>
                                            </div>
                                            <form action="../scripts/auth?action=smsSetting" class="gy-3" id="smsUpdateForm" method="post">
                                                 <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label">Enable/Disavle SMS</label>
                                                            <span class="form-note">Enable or disable SMS Feature.</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <ul class="custom-control-group g-3 align-center flex-wrap">
                                                            <li>
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" class="custom-control-input" <?php if($sms == "1"){echo "checked";} ?> value="1" name="sms" id="reg-enable">
                                                                    <label class="custom-control-label" for="reg-enable">Enable</label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" class="custom-control-input" <?php if($sms == "0"){echo "checked";} ?> value="0" name="sms" id="reg-disable">
                                                                    <label class="custom-control-label" for="reg-disable">Disable</label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label" for="site-name">Sender ID(Maximum of 11 characters)</label>
                                                            <span class="form-note">Specify Alpha-numeric  senders ID (E.g Example plc).</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="text" maxlength="11"  class="form-control" required  value="<?php echo$sender_id ?>" name="sender_id">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row g-3 align-center">
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <label class="form-label">Api Key</label>
                                                            <span class="form-note">Register with <a target="_blank" href='https://gatewayapi.com'>https://gatewayapi.com</a> and fill in your API key here</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="password" class="form-control" id="site-email" value="<?php echo$api ?>" name="api" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                              <div id="Result"></div>
                                                <div class="row g-3">
                                                    <div class="col-lg-7 offset-lg-5">
                                                        <div class="form-group mt-2">
                                                            <button type="submit" class="btn btn-lg btn-primary" id="btn2">Update</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div><!-- card -->
                                </div><!-- .nk-block -->
                               </div>
  <script src="../js/jquery.min.js"></script>
<script type="text/javascript">
            $(document).ready(function (e) {
            $("#UpdateForm").on('submit',(function(e) {
            document.getElementById("btn1").disabled = true; 
            e.preventDefault();
            $.ajax({
            url: "../scripts/update_smtp",
            type: "POST",
            data:  new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data)
            {
            document.getElementById("btn1").disabled = false;    
            $("#updateResult").html(data);
            },
            error: function() 
            {
            }           
       });
    }));
});
//SMS CONFIGURATION
            $(document).ready(function (e) {
            $("#smsUpdateForm").on('submit',(function(e) {
            document.getElementById("btn2").disabled = true; 
            e.preventDefault();
            $.ajax({
            url: "../scripts/auth?action=smsSetting",
            type: "POST",
            data:  new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data)
            {
            document.getElementById("btn2").disabled = false;    
            $("#Result").html(data);
            },
            error: function() 
            {
            }           
       });
    }));
});            

function fillResend() {
    document.getElementById('host').value = 'smtp.resend.com';
    document.getElementById('username').value = 'resend';
    document.getElementById('auth').value = 'ssl';
    document.getElementById('port').value = '465';
    document.getElementById('name').value = 'PBI Group';
    var infoKey = document.getElementById('info_password');
    if (infoKey) infoKey.focus();
    Swal.fire({
        icon: 'info',
        title: 'Resend Details Populated',
        html: 'Host (smtp.resend.com), Port 465, SSL, and Display Name (<strong>PBI Group</strong>) filled!<br><br>Now paste your <strong>Info API Key</strong> (for info@pbigroups.com) and <strong>Support API Key</strong> (for support@pbigroups.com) into their respective fields and click <strong>Update</strong>.',
        confirmButtonText: 'Got it'
    });
}
</script>
 <?php include("footer.php") ?>