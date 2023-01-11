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

$valid_messages = ["USERNAME_TAKEN", "EMAIL_ALREADY_REGISTERED", "INTERNAL_ERROR"];
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
    <title>Signup</title>
    <link rel="stylesheet" href="statics/style.css">
</head>
<body>
    
    
    
    <div class="container">
        
        <?php if($message_exists):?>
        <div class = "containerrow">
            <div class="rowelement, errortext">
            <?php echo $the_query[1]; ?>
        </div></div>
        <?php endif ?>
        <!-- 
            if there is no rewrite rule this must be replaced.
            action="/users.php/register"
        -->
        <form action="/users/register" method="post" class="form-group">
            <div class="containerrow">
                <div class="rowelement">
                    <fieldset class="fieldsetform">
                        <legend class="legendobject"><span style="color:red">*</span>Name</legend>
                        <input type="text" class="legendinput" id="nameInput" value="" name ="name" required>
                    </fieldset>
                </div>
                <div class="rowelement">    
                    <fieldset class="fieldsetform">
                        <legend class="legendobject">LastName</legend>
                        <input type="text" class="legendinput" id="lastnameInput" value="" name ="lastname">
                    </fieldset>
                </div>
            </div>
            <div class="containerrow">
                <div class="rowelement">
                    <fieldset class="fieldsetform">
                        <legend class="legendobject"><span style="color:red">*</span>Username</legend>
                        <input type="text" class="legendinput" id="usernameInput" value="" name ="username" required>
                    </fieldset>
                </div>
                <div class="rowelement">
                    <fieldset class="fieldsetform">
                        <legend class="legendobject"><span style="color:red">*</span>Password</legend>
                        <input type="password" class="legendinput" id="passwordInput" value="" name ="password" required>
                    </fieldset>
                </div>
            </div>
            <div class="containerrow">
                <div class="rowelement">
                    <fieldset class="fieldsetform">
                        <legend class="legendobject"><span style="color:red">*</span>Email</legend>
                        <input type="email" class="legendinput" id="emaildInput" value="" name ="email" required>
                    </fieldset>
                </div>
            </div>
            <div class="containerrow">
                <div class="rowelement">
                    <input type="submit" class="submitButton" name="submit" id="registerSubmit" value="Register">
                </div>
            </div>
        </form>
        
    </div>
    <script src="signup.js"></script>
</body>
</html>