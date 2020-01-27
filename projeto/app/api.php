<?php

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
// header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once('../../vendor/autoload.php');
require('connection.php');


use \Firebase\JWT\JWT;
define('SECRET_KEY', base64_encode('3e018c951f88d0e7d1ed3c7bcf94d938SEJUS2020'));  //secret key para fazer a validação do token
define('ALGORITHM', 'HS256');   // tipo do algoritmo do token 


$postdata = file_get_contents("php://input");
$request = json_decode($postdata);


$action = $request->action;
// Seção de Login
if ($action == 'login') {

    $username = $request->username;

    //Buscar informações dos Usuários no Banco
    try {
        $sql = "SELECT U.*, T.desc_tipo_usuario, S.desc_sede FROM tb_usuario AS U
        INNER JOIN tb_tipo_usuario AS T ON U.id_tipo_usuario = T.id_tipo_usuario
        INNER JOIN tb_sede AS S ON U.id_sede = S.id_sede WHERE usuario='$username' OR id_cpf = '$username';";
        $obj = $connect->prepare($sql);
        $obj->execute();
        $user = $obj->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        echo 'Erro: ', $e->getMessage(), "\n";
    }
    
    //Verificar se username ou cpf é compatível com a senha e se senha está correta
    if (($username == $user['usuario'] || $username == $user['id_cpf'])  && password_verify($request->password,$user['hashSenha'])) {
        $iat = time(); // Tempo que o tokenn foi gertado
        $nbf = $iat + 1; //not before in seconds
        $exp = $iat + 120; // Tempo em que o token será expirado
        $iss = $_SERVER['REQUEST_URI'];
        $nome = $user['nome'];
        $token = array(
            // "iss" => "http://example.org",
            "iss" => $iss,
            // "aud" => "http://example.com",
            "aud" => $iss,
            "iat" => $iat,
            "nbf" => $nbf,
            "exp" => $exp,
            "data" => array(
                "id" => 11,
                "email" => $nome
            )
        );

        http_response_code(200);

        $jwt = JWT::encode($token, SECRET_KEY);


        $data_insert = array(
            'access_token' => $jwt,
            'id'   => '007',
            'name' => 'guilherme Sandoval Veras',
            'time' => time(),
            'username' => 'FreakyJolly',
            'email' => 'contact@freakyjolly.com',
            'status' => "success",
            'message' => "Login Realizado com Sucesso, Olá " . $nome . ""
        );
    } else {
        $data_insert = array(
            "data" => "0",
            "status" => "invalid",
            "message" => "Usuário ou Senha Inválidos"
        );
    }
    // $_SERVER['HTTP_AUTHORIZATION'] = $jwt;
}
// Get Dashboard stuff
else if ($action == 'stuff') {
    $authHeader = $request->token;
    $temp_header = explode(".", $authHeader);

    $header = $temp_header[0];
    $payload = $temp_header[1];
    $signature = $temp_header[2];

    try {
        JWT::$leeway = 10;

        $decoded = JWT::decode($authHeader, SECRET_KEY, array(ALGORITHM));


        // Access is granted. Add code of the operation here 

        $data_from_server = '{"Coords":[{"Accuracy":"65","Latitude":"53.277720488429026","Longitude":"-9.012038778269686","Timestamp":"Fri Jul 05 2013 11:59:34 GMT+0100 (IST)"},{"Accuracy":"65","Latitude":"53.277720488429026","Longitude":"-9.012038778269686","Timestamp":"Fri Jul 05 2013 11:59:34 GMT+0100 (IST)"},{"Accuracy":"65","Latitude":"53.27770755361785","Longitude":"-9.011979642121824","Timestamp":"Fri Jul 05 2013 12:02:09 GMT+0100 (IST)"},{"Accuracy":"65","Latitude":"53.27769091555766","Longitude":"-9.012051410095722","Timestamp":"Fri Jul 05 2013 12:02:17 GMT+0100 (IST)"},{"Accuracy":"65","Latitude":"53.27769091555766","Longitude":"-9.012051410095722","Timestamp":"Fri Jul 05 2013 12:02:17 GMT+0100 (IST)"}]}';


        $data_insert = array(
            "data" => json_decode($data_from_server),
            "status" => "success",
            "message" => "Request authorized"
        );
    } catch (Exception $e) {

        http_response_code(401);

        $data_insert = array(
            //"data" => $data_from_server,
            "jwt" => $authHeader,
            "status" => "error",
            "message" => $e->getMessage()
        );
    }
}
// } else if ($action == 'valid') {



//     $authHeader = $request->token;
//     // echo 'authheader: ',$authHeader;
//     $temp_header = explode(".", $authHeader);
//     print_r($temp_header);
//     $jwt = $temp_header[0];
//     echo $jwt;

//     // echo $token;
//     exit;
//     $part = explode(".", $token);
//     $header = $part[0];
//     $payload = $part[1];
//     $signature = $part[2];

//     // echo 'Header = ', $header, '<br>';
//     // echo 'Payload = ', $payload, '<br>';
//     // echo 'Signature = ', $signature, '<br>';

//     $valid = hash_hmac('sha256', "$header.$payload", SECRET_KEY, true);
//     $valid = base64_encode($valid);


//     echo 'Secret key ', $valid, '<br>';
//     echo 'signature ', $signature;
//     // echo $valid;
//     if (SECRET_KEY == $signature) {
//         echo " valid";
//     } else {
//         echo ' invalid';
//     }
//     exit;
// }

echo json_encode($data_insert);
// print_r( $data_insert);
// print_r (json_encode($data_insert));



        // exi
    

// echo json_encode($data_insert);
