<?php
session_start();

if(isset($_SESSION["is_authenticated"])){
    if($_SESSION["is_authenticated"]){
        if ($_SESSION["logined_user"]["is_admin"]){
            //if there is nore rewrite rule this must be used
            //header("location: /admindashboard.php");
            //if there is a rewrite rule this could be used.
            header("location: /admindashboard");
            exit();
        }else{
            $is_authenticated = true;
        }
        
    } else {
        header("location: /");
        session_destroy();
        exit();
    }
} else {
    header("location: /");
    session_destroy();
    exit();
}


?>


<?php if($is_authenticated): ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="statics/style.css">
</head>
<body>
    <div class="container">
        <div class = 'containerrow'>
            This is the dashboard for
        </div>
        <div class = "containerrow">
            Username : <?php echo $_SESSION["logined_user"]["username"];?>
        </div>
        <div class= "containerrow">
            Id : <?php echo $_SESSION["logined_user"]["id"];?>
        </div>    
        <div class="containerrow">
            <!-- In case there is no rewrite rule this must be replaced
                action="users.php/logout"
            -->
            <form method="post" action="users/logout">
                <input type="submit" value="Logout">
            </form>    
        </div>
        
    </div>
    
    

</body>
</html>

<?php endif ?>