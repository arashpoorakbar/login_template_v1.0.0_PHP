<?php

$configuration_file = fopen("loginconfig.json", "r");
$login_configuration = (array) json_decode(fread($configuration_file, filesize("loginconfig.json")));
fclose($configuration_file);

$users_table = $login_configuration["users_table"];
$db_config = (array) $login_configuration["MYSQLConnection"];



$URL = $_SERVER["REQUEST_URI"];
$route = "";
$route_params = "";

$url_dissected = explode("/", $URL,5);
if(count($url_dissected)-2){
    $route = $url_dissected[2];
    $route_params = array_slice($url_dissected, 3);
}


mysqli_report(MYSQLI_REPORT_ALL);
set_error_handler("mysqli_warning_handler", E_WARNING);
$mysqli = new mysqli($db_config["host"], $db_config["user"], $db_config["password"], $db_config["database"]);
restore_error_handler();
function mysqli_warning_handler($no, $str){
    
    http_response_code(500);
    
    exit();
    
}


$route = filter_var($route, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

include "inclu/jwt.php";
include "inclu/email_sender.php";



session_start();



// route for registering users
if ($route === "register"){
    
    

    if (!empty($_POST["submit"]) && !empty($_POST["name"]) && !empty($_POST["username"]) && !empty($_POST["email"]) && !empty($_POST["password"])){
        
        
    
        //a better way to sznitize could be implemented
        $email = filter_var($_POST["email"], FILTER_VALIDATE_EMAIL);
        $email = filter_var($email,FILTER_SANITIZE_EMAIL,);
        
        $username = filter_var($_POST["username"], FILTER_SANITIZE_SPECIAL_CHARS);
        
        $password = $_POST["password"];

        $name = filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS);

        $lastname = empty($_POST["lastname"]) ? null : filter_var($_POST["lastname"], FILTER_SANITIZE_SPECIAL_CHARS);
        
        
    

        

        $stmt = $mysqli->prepare("SELECT * FROM ${users_table} WHERE 'username' = ? OR 'email' = ?;");
        
        $stmt->bind_param("ss", $username, $email);

        $query_success = $stmt->execute();

        //in case the query fails to get executed
        if (!$query_success){
            http_response_code(500);
            echo "Internal Error";
            exit();
        }

        $res = $stmt->get_result();
        
        $existing_user = [];
        
        if($res->num_rows==1 || $res->num_rows==2){
            while($row = $res->fetch_array(MYSQLI_ASSOC)){
                array_push($existing_user, $row);
            }    
        }
        
        if (empty($existing_user)){
            // registration process
            $password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO ${users_table} 
            (`id`, `email`, `name`, `username`, `lastname`, `password`,
             `registrationDate`, `accessLevel`, `isAdmin`, `isActivated`)
              VALUES (UUID(), ?, ?, ?, ?, ?, NOW(), 1, 0, 0); ");
            $stmt->bind_param("sssss", $email, $name, $username, $lastname, $password);
            try{
                $reg_query = $stmt->execute();
            }catch (Exception $e){
                echo $e;
                $reg_query = false; 
            }
            
            
            if($reg_query){
                //if there is no rewrite rule this must be used.
                //$email_for_verification = "http://".$_SERVER["SERVER_NAME"]."/users.php/email_verification/";
                //if there is a rewrite rule this could be used.
                $email_for_verification = "http://".$_SERVER["SERVER_NAME"]."/users/email_verification/";

                $jwt_verification = generate_jwt(array(
                    "exp"=> time()+$login_configuration["emailVerification"]->tokenMaxAgeSecond,
                    "email"=> $email
                    ),
                    $login_configuration["emailVerification"]->keyForToken);
                $email_for_verification = $email_for_verification.$jwt_verification;
                
                try{
                    send_email($email, $email_for_verification);
                    echo "Verification Email Sent.";
                    $timestmp = time();
                    $stmt = $mysqli->prepare("UPDATE ${users_table} SET timeEmailSent = ${timestmp} WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();


                    exit();
                } catch (Exception $e){
                    echo "INTERNAL ERROR. TRY AGAIN LATER";
                    
                    exit();
                }
                
                
                exit();
            }else{
                http_response_code(500);
                echo "internal error";
                exit();
            }
        }else{
            //already registered
            foreach($existing_user as $the_user){
                if ($the_user["email"]==$email){
                    //if there is no rewrite rule this must be used.
                    //header("location: /signup.php?failMessage=EMAIL_ALREADY_REGISTERED");
                    //if there is a rewrite rule this could be used.
                    header("location: /signup?failMessage=EMAIL_ALREADY_REGISTERED");
                    exit();
                }
            }
            //if there is no rewrite rule this must be used.
            //header("location: /signup.php?failMessage=USERNAME_TAKEN");
            //if there is a rewrite rule this could be used.
            header("location: /signup?failMessage=USERNAME_TAKEN");
            exit();
            
            
        }
        


    } else {
        http_response_code(400);
        exit();
    }
    
    
}





// route for logging in users
if ($route === "login"){
    
    


    if (isset($_POST["submit"]) && isset($_POST["username"]) && isset($_POST["password"])){
        
        //sanitizing the inputs. especially the username since it will be passsed in a sql query
        $username = filter_var($_POST["username"],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
        
        $password = filter_var($_POST["password"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        


        $found_user = find_user($username);
        if (isset($found_user)){
            //username registred
            
            if (password_verify($password, $found_user["password"])){
                //correct credentials

                if($found_user["isActivated"]){
                    //correct credentials and activated email
                    $_SESSION["logined_user"] = [
                        "id"=> $found_user["id"], 
                        "username" => $found_user["username"],
                        "is_admin" => $found_user["isAdmin"],
                        "access_level" => $found_user["accessLevel"],
                        "email" => $found_user["email"]
                    ];
                    
                    $_SESSION["is_authenticated"] = true;
                    

                    if($found_user["isAdmin"]){
                        
                        

                        $token_for_admin = generate_jwt(array("exp" => time() + $login_configuration["admin_access"]->tokenMaxAgeSecond), $login_configuration["admin_access"]->keyForToken);
                        echo $token_for_admin;
                        
                        $otp = otp_generator(10);
                        $_SESSION["otp_for_admin"] = $otp;
                        $_SESSION["access_level"] = $found_user["accessLevel"];

                        try{
                            send_email($found_user["email"],$otp);
                        }catch(Exception $e){
                            exit();
                        }
                        setcookie("auth_token", $token_for_admin, time()+$login_configuration["admin_access"]->tokenMaxAgeSecond,"/", $_REQUEST["HTTP_HOST"]);
                        //if there is no rewrite rule this must be used.
                        //header("location: /admindashboard.php");
                        //if there is a rewrite rule this could be used.
                        header("location: /admindashboard");
                        
                        exit();
                    }else{
                        
                        //if there is no rewrite rule this must be used
                        //header("location: /dashboard.php");
                        //if there is a rewrite rule this could be used.
                        header("location: /dashboard");
                        exit();
                    }
                    
                    //if there is no rewrite rule this must be used.
                    //header("location: /dashboard.php");
                    //if there is a rewrite rule this could be used.
                    header("location: /dashboard");
                    exit();

                    
                }else{
                    //the email is not activated
                    //must be changed to verification page
                    $_SESSION["needs_verification"] = true;
                    
                    //if there is no rewrite rule this must be used.
                    //header("location: /verification.php");
                    //if there is a rewrite rule this could be used.
                    header("location: /verification");
                    exit();
                }

            }else{
                //incorrect password
                
                //if there is no rewrite rule this must be used.
                //header("location: /login.php?failMessage=INCORRECT_CREDENTIALS");
                //if there is a rewrite rule this could be used.
                header("location: /login?failMessage=INCORRECT_CREDENTIALS");
                session_destroy();
                exit();
            }
        } else{
            //username not registered

            //if there is no rewrite rule this must be used.
            //header("location: /login.php?failMessage=INCORRECT_CREDENTIALS");
            //if there is a rewrite rule this could be used.
            header("location: /login?failMessage=INCORRECT_CREDENTIALS");
            session_destroy();
            exit();
        }
        
        
        
    }else {
        http_response_code(400);
        session_destroy();
        exit();
    }
    

    
    
}


if ($route === "email_verification"){
    if(isset($url_dissected[3])){
        $token_to_verify = $url_dissected[3];
        try{
            $decoded_token = validate_jwt($token_to_verify, $login_configuration["emailVerification"]->keyForToken);
            if($decoded_token){
                //token correct must be hanlded
                global $mysqli, $users_table;

                $stmt = $mysqli->prepare("UPDATE ${users_table} SET isActivated = '1' WHERE email = ?");
                $stmt->bind_param("s", $decoded_token->email);
                $activation_query = $stmt->execute();

                if ($activation_query) {
                    if ($stmt->affected_rows == 1){
                        echo "EMAIL VERIFIED";
                        exit();
                    }else{
                        echo "INTERNAL ERROR";
                        exit();
                    }
                }else{
                    echo "INTERNAL ERROR";
                    exit();
                }

            }else{
                //expired token
                echo "EXPIRED TOKEN";
                exit();
            }
        } catch(Exception $e){
            //could be handled carefully later
            echo "BAD TOKEN";
            exit();
        }
    }else{
        exit();
    }
    
}


if ($route === "verification"){
    

    if (isset($_SESSION["needs_verification"])){
        if (isset($_POST["submit"])){
            
            $email = filter_var($_POST["emailToVerify"], FILTER_SANITIZE_SPECIAL_CHARS);
            $stmt = $mysqli->prepare("SELECT * FROM ${users_table} WHERE email = ?");
            $stmt->bind_param("s", $email);
            $query_success = $stmt->execute();
            if ($query_success){
                $res = $stmt->get_result();
                if ($res->num_rows == 1){
                    $found_one = $res->fetch_all(MYSQLI_ASSOC)[0];
                    if ($found_one["isActivated"]){
                        //if there is no rewrite rule this must be used.
                        //header("location:/login.php?failMessage=ALREADY_ACTIVATED");
                        //if there is a rewrite rule this could be used.
                        header("location:/login?failMessage=ALREADY_ACTIVATED");
                        session_destroy();
                        exit();
                    }else{
                        if (isset($found_one["timeEmailSent"])){
                            if ((time()-$found_one["timeEmailSent"])<$login_configuration["emailVerification"]->timeForResendSecond){
                                //if there is no rewrite rule this must be used.
                                //header("location: /verification.php?failMessage=ALREADY_SENT");
                                //if there is a rewrite rule this could be used.
                                header("location: /verification?failMessage=ALREADY_SENT");
                                exit();
                            }else{
                                goto to_send_email;
                            }
                        }else{
                            to_send_email:

                            //if there is no rewrite rule this must be used.
                            //$email_for_verification = "http://".$_SERVER["SERVER_NAME"]."/users.php/email_verification/";
                            //if there is a rewrite rule this could be used.
                            $email_for_verification = "http://".$_SERVER["SERVER_NAME"]."/users/email_verification/";
                            $jwt_verification = generate_jwt(array(
                                "exp"=> time()+$login_configuration["emailVerification"]->tokenMaxAgeSecond,
                                "email"=> $email
                                ),
                                $login_configuration["emailVerification"]->keyForToken);
                            $email_for_verification = $email_for_verification.$jwt_verification;
                            
                            try{
                                send_email($email, $email_for_verification);
                                
                                $timestmp = time();
                                $stmt = $mysqli->prepare("UPDATE ${users_table} SET timeEmailSent = ${timestmp} WHERE email = ?");
                                $stmt->bind_param("s", $email);
                                $stmt->execute();
                                
                                //if there is no rewrite rule this must be used.
                                //header("location:/login.php?failMessage=SUCCESS");
                                //if there is a rewrite rule this could be used.
                                header("location:/login?failMessage=SUCCESS");
                                session_destroy();
                                exit();
                            } catch (Exception $e){
                                echo "INTERNAL ERROR. TRY AGAIN LATER";
                                
                                exit();
                            }



                        }
                    }
                }else{
                    //if there is no rewrite rule this must be used.
                    //header("location:/verification.php?failMessage=NOT_REGISTERED");
                    //if there is a rewirte rule this could be used.
                    header("location:/verification?failMessage=NOT_REGISTERED");
                    exit();
                }
            }else{
                http_response_code(500);
                echo "internal error";
                exit();
            }
        }else{
            exit();
        }
    }else{
        header("location:/");
        exit();
    }


}


if ($route === "getall"){
    
    
    if (isset($_COOKIE["auth_token"])){
        try{
            $decoded_token = validate_jwt($_COOKIE["auth_token"], $login_configuration["admin_access"]->keyForToken);

            


        } catch (Exception $e){
            http_response_code(400);
            echo "BAD TOKEN";
            exit();
        }



        if ($decoded_token){
                
            if ($route_params[0] == $_SESSION["otp_for_admin"]) {
                mysqli_report(MYSQLI_REPORT_OFF);
                $stmt = $mysqli->prepare("SELECT * FROM ${users_table} WHERE accessLevel<? ");
                
                $stmt->bind_param("i", $_SESSION["access_level"]);
                

                $dbquery = $stmt->execute();
                
                if ($dbquery){
                    $res = $stmt->get_result();
                    $users_to_send = $res->fetch_all(MYSQLI_ASSOC);
                    
                    $users_to_send = json_encode($users_to_send);
                    http_response_code(200);
                    echo $users_to_send;
                }else{
                    http_response_code(500);
                    exit();
                }

                
            }else{
                
                
                echo "Wrong credentials";
                exit();
            }








        }else{
            http_response_code(400);
            echo "TOKEN EXPIRED";
            exit();
        }
        
        
    }else{
        http_response_code(400);
        exit();
    }


    
    
    
    


}



if ($route === "updateusers"){
    if (isset($_COOKIE["auth_token"])){

        try{
            $decoded_token = validate_jwt($_COOKIE["auth_token"], $login_configuration["admin_access"]->keyForToken);

        } catch (Exception $e){
            http_response_code(400);
            echo "BAD TOKEN";
            exit();
        }


        if ($decoded_token){

            if ($route_params[0] == $_SESSION["otp_for_admin"]){
                
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                $the_update_query = "";

                if (isset($data['delete'])){
                    foreach ($data['delete'] as $delete_id){
                        $the_update_query .= "DELETE FROM ${users_table} WHERE id = '${delete_id}';";
                    }
                }

                if (isset($data['update'])){
                    foreach ($data['update'] as $user_to_update){
                        $the_update_query .= "UPDATE ${users_table} SET ";
                        foreach ($user_to_update as $key => $user_index){
                            if ($key != 'id' && $key != 'password'){
                                $the_update_query .= "`${key}` = '${user_index}',"; 
                            }
                            if ($key == 'password'){
                                $hashed_pass = password_hash($user_index,PASSWORD_DEFAULT);
                                $the_update_query .= "`${key}` = '{$hashed_pass}',"; 
                                
                            }                            
                        }
                        $the_update_query = substr($the_update_query, 0, -1);
                        $the_update_query .= " WHERE id = '{$user_to_update['id']}';"; 
                        
                    }
                }
                
                if (isset($data['insert'])){
                    
                    foreach ($data['insert'] as $user_to_insert){
                        $the_update_query .= "INSERT INTO {$users_table} ";
                        $insert_query_columns = "(id, ";
                        $insert_query_values = "VALUES(UUID(),";
                        foreach ($user_to_insert as $key => $user_index){
                            if ($key != 'id' && $key != 'password'){
                                $insert_query_columns .= "`{$key}`,";
                                $insert_query_values .= "'{$user_index}',";
                            }
                            if ($key == 'password'){
                                $insert_query_columns .= "`{$key}`,";
                                $hashed_pass = password_hash($user_index, PASSWORD_DEFAULT);
                                $insert_query_values .= "'{$hashed_pass}',";
                            }
                        }

                        $insert_query_columns = substr($insert_query_columns, 0, -1);
                        $insert_query_values = substr($insert_query_values, 0, -1);

                        $insert_query_columns .= ") ";
                        $insert_query_values .= ");";

                        $the_update_query .= $insert_query_columns;
                        $the_update_query .= $insert_query_values;
                    }
                }



                $update_result = 0;
                $mysqli->multi_query($the_update_query);

                do {
                    
                    if ($mysqli->affected_rows == 1){
                        $update_result ++;
                    }
                    
                    
                } while ($mysqli->next_result());


                http_response_code(200);
                echo "{$update_result} rows were affected!";
                session_destroy();
                exit();



            } else {
                http_response_code(400);
                echo "WRONG CREDENTIALS";
                session_destroy();
                exit();
            }



        } else {
            http_response_code(400);
            echo "TOKEN EXPIRED";
            session_destroy();
            exit();
        }



    } else {
        http_response_code(400);
        
        session_destroy();
        exit();
    }
}





//route for loging out
if ($route === "logout"){
    session_destroy();
    header("location: /");
    exit();
}




function find_user($username){
    global $mysqli, $users_table;

    $stmt = $mysqli->prepare("SELECT * FROM ${users_table} WHERE username = ?;");
    $stmt->bind_param("s", $username);
    $login_query = $stmt->execute();
    if ($login_query){
        $res = $stmt->get_result();
        if ($res->num_rows == 1){
            return $res->fetch_all(MYSQLI_ASSOC)[0];
        }else{
            return null;
            
        }
    }else{
        //if there is no rewrite rule this must be used.
        //header("location: /login.php?failMessage=INTERNAL_ERROR");
        //if there is a rewrite rule this could be used.
        header("location: /login?failMessage=INTERNAL_ERROR");
        exit();
    }




}


function otp_generator(int $len){
    if ($len <= 1){
        return null;
    }
    $otp = "";



    for($i=0; $i<$len; $i++){
        $char_type = rand(1,3);
        $char = 0;
        switch($char_type){
            case $char_type == 1:
                $char = rand(48, 57);
                break;
            case $char_type == 2:
                $char = rand(65, 90);
                break;
            case $char_type == 3:
                $char = rand(97, 122);
                break;
        }

        $otp = $otp.chr($char);


        
    }
    return $otp;
    
    


}



?>