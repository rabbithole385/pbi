<?php 
include ('functions.php');
include("userdata.php");
$j = $_SESSION['paymentdetails'];
$otp = $j[6];
if(empty($otp)){
  echo"<script>window.location.href='../personal-banking/pay-bills'; </script>";}
else{
  $j = $_SESSION['paymentdetails'];
  $otp = $j[6];
  }
if (isset($_POST)) {
	$code1 = $_POST["codeBox1"];
	$code2 = $_POST["codeBox2"];
	$code3 = $_POST["codeBox3"];
	$code4 = $_POST["codeBox4"];
	$code = "$code1$code2$code3$code4";
	 if (empty($code)) {
		echo "
             <script>
              toastr.error('OTP Code is required', 'Empty field', {\"progressBar\": true});
              document.getElementById('codeBox1').style.borderColor='red';
              document.getElementById('codeBox2').style.borderColor='red';
              document.getElementById('codeBox3').style.borderColor='red';
              document.getElementById('codeBox4').style.borderColor='red';
             </script>
            ";
		 die();
			}
           if($code != $j[6]){
           	echo"
            <script>
              toastr.error('Invalid OTP code', 'An Occured', {\"progressBar\": true});
              document.getElementById('codeBox1').style.borderColor='red';
              document.getElementById('codeBox2').style.borderColor='red';
              document.getElementById('codeBox3').style.borderColor='red';
              document.getElementById('codeBox4').style.borderColor='red';
             </script>
           	";
           	die();
           }

           if ($code == $otp) {
           	echo'
            <script>
            swal("successful", "You will be redirected shortly", "success");
            </script>
            ';
            echo"
            <script>
              document.getElementById('codeBox1').style.borderColor='green';
              document.getElementById('codeBox2').style.borderColor='green';
              document.getElementById('codeBox3').style.borderColor='green';
              document.getElementById('codeBox4').style.borderColor='green';
             </script>
            ";
           }
           ?>
      <meta http-equiv="refresh" content="2; url='../personal-banking/payment-successful'">
<script>
setTimeout(function(){ window.location.href = '../personal-banking/payment-successful'; }, 1000);
</script>
      <?php

       }



?>

