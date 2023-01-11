
<?php 
session_start();



if(isset($_SESSION["is_authenticated"])){
    if($_SESSION["is_authenticated"]){
        //if there is no rewrite rule this must be used.
        //header("location: /dashboard.php");
        //if there is a rewrite rule this could be used.
        header("location: /dashboard");
        exit();
    } else {
        //in case there is no rewrite rule this should be the redirect
        //header("location: /login.php");

        //in case of rewrite rule this could be the redirect. However the previous will still work.
        //But it won't make sense since there is a rewrite rule in use.
        header("location: /login");
        
        exit();
    }
} else {
        //in case there is no rewrite rule this should be the redirect
        //header("location: /login.php");

        //in case of rewrite rule this could be the redirect. However the previous will still work.
        //But it won't make sense since there is a rewrite rule in use.
        header("location: /login");
    
    exit();
}


?>
