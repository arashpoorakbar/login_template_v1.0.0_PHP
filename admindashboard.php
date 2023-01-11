<?php

session_start();

if(isset($_SESSION["is_authenticated"]) && isset($_SESSION["logined_user"]["is_admin"])){
    if($_SESSION["is_authenticated"] && $_SESSION["logined_user"]["is_admin"]){
        $is_authenticated = true;
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
    <link rel="stylesheet" media="screen and (min-width: 500px)" href="/statics/stylead.css">
    <link rel="stylesheet" media="screen and (max-width: 500px)" href="/statics/styleadm.css">
    <title>Dashboard ----- ADMIN</title>
</head>
<body>
    <div class="navbar">
        
            <div class="admin-id">
                Admin: <?php echo $_SESSION["logined_user"]["username"]; ?>
            </div>
            <div class="logout">
                <!-- In case there is no rewrite rule this must be replaced
                action="users.php/logout"
                -->
                <form method="post" action="users/logout">
                    <input type="submit" value="Logout">
                </form>
            </div>
        
    </div>

    <div class="main-container">
        
        <div class="table-container">
            <table id = "table" class="table">
                <thead>
                    <tr class="table-row">
                        <th class="table-column">User's ID</th>
                        <th class="table-column">Username</th>
                        <th class="table-column">Name</th>
                        <th class="table-column">Last Name</th>
                        <th class="table-column">Email</th>
                        <th class="table-column">Password</th>
                        <th class="table-column">Is Admin</th>
                        <th class="table-column">Is Activated</th>
                        <th class="table-column">Registration Date</th>
                        <th class="table-column">Access Level</th>
                    </tr>
                </thead>
                <tbody id="inside-table">

                </tbody>

                
            </table>
            
            
            
        </div>
        <div id="otp-input">
            Enter The Code Sent to Your Email!
            <input id="otp-for-load" type="text"  placeholder="YOUR OTP HERE!!!!">
            <div>
                <input type="button" value="Load Table" onclick="otpinput()">
            </div>
            
        </div>
    </div>
    <fieldset class="control-container">
        <legend>Controls</legend>
        
        <div class="add-button-container">
            <input type="button" value="add user" onclick="adduserrow()">
        </div>
        <div class="reload-button">
            <input type="button" onclick="reloadtable()" value="reload">
        </div>
        <div class="delete-buttons">
            <input type="text" id = "id-to-delete" placeholder="id to delete">
            <input type="button" value="delete" onclick="deleterow()">
        </div>
        <div class="box-to-submit">
            <input type="text" class= "otp-input-to-submit"placeholder="Enter your OTP here to submit the changes" id="otpInput">
    
            <input type="button" onclick="submitdata()" value="submit">
        </div>
        
        
    </fieldset>
    
    <fieldset class="review-table">
        <legend>Review</legend>
        <fieldset class = "changingData" id="changes-to-show">
            <legend>Updates and Inserts</legend>
            <div id="changes-to-show-text"></div>
        </fieldset>
        <fieldset class="delete-container">
            <legend>To Delete</legend>
            <div id="to-delete">
            </div>
            
            
        </fieldset>
        
    </fieldset>
   
    <div class="info-table">
        <h4>
            scroll me for info!
        </h4>
        <h5>leaving a username field empty means deleting the user</h5>
        <h6>* The modified data which do not meet the required format would not be implemented on the database</h6>
        <h6>** Access Level's modified by admin must be an integer less than 10 and less than admin's own access level</h6>
        <h6>*** If a new password is set, it will automatically be hashed before being saved to database</h6>
        <h6>IDs and registration dates are immutable</h6>
    </div>
    
    
    <div id="finalmessage" class="messagelastcontainer">
        <div class="messagelast">
        
        <p id="message"></p>
        <button onclick="window.location.href='../'">ok</button>
        </div>
  
    </div>
    
    <script src="statics/adminjs.js"></script>
</body>
</html>

<?php endif ?>