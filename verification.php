<?php

session_start();
if(isset($_SESSION["is_authenticated"])){
    if($_SESSION["is_authenticated"]){
        //if there is no rewrite rule this must be used.
        //header("location: /dashboard.php");
        //if there is a rewrite rule this could be used.
        header("location: /dashboard");
        exit();
    }
}
$can_load_page = false;
if (isset($_SESSION["needs_verification"])){
    $can_load_page = true;
}else{
    header("location: /");
    exit();
}


$message_exists = false;
$the_query = explode("=", $_SERVER["QUERY_STRING"]);

$valid_messages = ["ALREADY_SENT", "NOT_REGISTERED", "INTERNAL_ERROR", "SUCCESS"];
if ($the_query[0]==="failMessage" && in_array($the_query[1], $valid_messages)){
    $message_exists = true;
}

?>


<?php if($can_load_page): ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/statics/style.css">
    <title>Verify your email</title>
</head>
<body>
    <div class = "container">
  
        <?php if($message_exists): ?>
        <div class = "containerrow">
            <div class="rowelement, errortext">
            <?php
            if ($the_query[1] == "ALREADY_SENT"){
                echo "EMail is sent. Check your spam folder! OR wait and try again";
            }
            if ($the_query[1] == "NOT_REGISTERED"){
                echo "This email is not registered";
            }

            if ($the_query[1] == "INTERNAL_ERROR"){
                echo "There was an internal error!";
            }

            if ($the_query[1] == "SUCCESS"){
                echo "EMail successfully sent";
            }
            ?>
        </div></div>
        <?php endif ?>
        <!-- 
            in case there is no rewrite rule this must be replaced.
            action="/users.php/verification"
        -->
        <form action="/users/verification" method="post">
        <div class="containerrow" style="margin-top:10px">
                
            <div class="rowelement">
                <fieldset class="fieldsetform">
                    <legend class="legendobject">Email</legend>
                    <input type="email" class="legendinput" id="email-to-verify" placeholder="Your Email!" name ="emailToVerify" required>
                </fieldset>
                    
                
            </div>
            
        </div>
        <div class="containerrow">
            <div class="rowelement">
                <input type="submit" name = "submit" value="send verification email">
            </div>
        </div>
        </form>
    </div>
</body>
</html>
<?php endif ?>