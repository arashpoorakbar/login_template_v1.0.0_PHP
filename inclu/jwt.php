<?php

function base64url_encode($str){
    return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
}

function base64url_decode($str){
    return base64_decode(strtr($str, '-_', '+/'));
}



function generate_jwt($payload, $secret){
    $header_encoded = base64url_encode(json_encode(array('alg'=> 'HS256', 'typ'=> 'JWT')));
    $payload_encoded = base64url_encode(json_encode($payload));


    $signature = hash_hmac("sha256", "$header_encoded.$payload_encoded", $secret, true);
    $signature_encoded = base64url_encode($signature);

    return "$header_encoded.$payload_encoded.$signature_encoded";
    
}


function validate_jwt($token, $secret){

    $token_parts = explode(".", $token);
    
    if (count($token_parts) !== 3){
        throw new ErrorException("WRONG_FORMAT");
    }
    
    $header_encoded = $token_parts[0];
    $payload_encoded = $token_parts[1];
    $signature_encoded = $token_parts[2];

    $header_decoded = json_decode(base64url_decode($token_parts[0]));
    $payload_decoded = json_decode(base64url_decode($token_parts[1]));
    $signature_decoded = base64url_decode($token_parts[2]);


    
   
   

    
   
    

    $signature_to_test = hash_hmac("sha256", "$header_encoded.$payload_encoded", $secret, true);
    

    if (!($signature_to_test == $signature_decoded)){
        throw new ErrorException("CORRUPTED_JWT");
    } else {
        if(!($header_decoded->typ == "JWT" && $header_decoded->alg == "HS256")){
            throw new ErrorException("WRONG_TYPE_OF_JWT");
        }

        if (isset($payload_decoded->exp)){
            $exp = $payload_decoded->exp;
            $is_expired = ($exp - time())<0 ? true: false;
            if($is_expired){
                return false;
                //returning false meaning the expiry of token
            }
        }
        return $payload_decoded;
    }

    

}

?>