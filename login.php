
<?php 
session_start();
if(isset($_SESSION["is_authenticated"])){
    if($_SESSION["is_authenticated"]){
        //if there is not a rewrite rule this must be used.
        //header("location: /dashboard.php");
        //if there is a rewrite rule this could be used.
        header("location: /dashboard");
        exit();
    }
}



$message_exists = false;
$the_query = explode("=", $_SERVER["QUERY_STRING"]);

$valid_messages = ["INCORRECT_CREDENTIALS", "EMAIL_NOT_VERIFIED", "NOT_REGISTERED", "ALREADY_ACTIVATED", "SUCCESS"];
if ($the_query[0]==="failMessage" && in_array($the_query[1], $valid_messages)){
    $message_exists = true;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>

    
    <link rel="stylesheet" href="statics/style.css">
</head>
<body>
    
    <div class = "container">
       
        <?php if($message_exists): ?>
        <div class = "containerrow">
            <div class="rowelement, errortext">
            <?php echo $the_query[1];?>
        </div></div>
        <?php endif?>
        <!-- 
            if there is not a rewrite rule this must be used instead.
            action="./users.php/login"
        -->
        <form method="post" action="./users/login">
            <div class="containerrow" style="margin-top:10px">
                
                <div class="rowelement">
                    <fieldset class="fieldsetform">
                        <legend class="legendobject">Username</legend>
                        <input type="text" class="legendinput" id="usernamefield" value="" name ="username" required>
                    </fieldset>
                    
                </div>

            </div>
            <div class="containerrow">
                
                <div class="rowelement">
                    <fieldset class="fieldsetform">
                        <legend class="legendobject">Password</legend>
                        <input type="password" class="legendinput" id="password" value="" name ="password" required>
                    </fieldset>
                
                </div>
            </div>
            <div class="containerrow">
                <div class="rowelement">
                    <input type="submit" value="Login" class="submitButton" name ="submit">
                </div>
            </div>
        </form>
        
    </div>

    
</body>
</html>



