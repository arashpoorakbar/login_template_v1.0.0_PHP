<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

/////////////////////////////////////////
$email_configuration = (array) $login_configuration["email_config"];

$mail = new PHPMailer(true);
$mail->IsSMTP();
$mail->SMTPDebug = false;  
$mail->SMTPAuth = TRUE;
$mail->SMTPSecure = "tls";
$mail->Port = 587;
$mail->Host = $email_configuration["service"];
$mail->Username = $email_configuration["user"];
$mail->Password = $email_configuration["pass"];





function send_email($recepient, $body){
  global $mail;
  
  try{
    
    $mail->IsHTML(true);
    $mail->AddAddress($recepient);
   
    $mail->Subject = "VerificationTest";
    


    $mail->MsgHTML($body); 

    $mail->send();
    return TRUE;



  } catch(Exception $e){
    throw new ErrorException($mail->ErrorInfo);
  }
}



?>