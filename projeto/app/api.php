<?php

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

    //Buscar informações dos Usuários no Banco de Dados
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

    //  Verificar se username ou cpf é compatível com o usuário e se senha está correta
    if (($username == $user['usuario'] || $username == $user['id_cpf'])  && password_verify($request->password, $user['hashSenha'])) {
        $iat = time();  //  Timestamp de quando o token foi criado
        $nbf = $iat + 1; //  Timestamp de quando o token não pode ter sido criado
        $exp = $iat + 3600; //  Timestamp de quando o token irá expirar
        $iss = $_SERVER['REQUEST_URI']; //  Emissor do token
        $aud = "http://localhost:4200/#/visitantes"; // Destinatário do token, representa a aplicação que irá usá-lo.

        $id_cpf = $user['id_cpf'];
        $nome = $user['nome'];
        $usuario = $user['usuario'];
        $email = $user['email'];
        $telefone = $user['telefone'];
        $id_tipo_usuario = $user['id_tipo_usuario'];
        $id_sede = $user['id_sede'];

        $token = array(
            "iss" => $iss,
            "aud" => $aud,
            "iat" => $iat,
            "nbf" => $nbf,
            "exp" => $exp,
            "data" => array(
                "id_cpf"            =>  $id_cpf,
                "nome"              =>  $nome,
                "usuario"           =>  $usuario,
                "email"             =>  $email,
                "telefone"          =>  $telefone,
                "id_tipo_usuario"   =>  $id_tipo_usuario,
                "id_sede"           =>  $id_sede

            )
        );

        http_response_code(200);

        $jwt = JWT::encode($token, SECRET_KEY);


        $data_insert = array(
            'access_token' => $jwt,
            'name' => $nome,
            'time' => time(),
            'email' => $email,
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
}

// Validar Token recebido do Front-end
else if ($action == 'stuff') {

    $authHeader = $request->token;
    // $authHeader = getBearerToken();
    $temp_header = explode(".", $authHeader);

    $header = $temp_header[0];
    $payload = $temp_header[1];
    $signature = $temp_header[2];

    try {
        JWT::$leeway = 10;

        $decoded = JWT::decode($authHeader, SECRET_KEY, array(ALGORITHM));

        $data_insert = array(
            // "data" => json_decode($data_from_server),
            "status" => "success",
            "message" => "Request authorized"
        );
    } catch (Exception $e) {

        http_response_code(401);

        $data_insert = array(
            "jwt" => $authHeader,
            "status" => "error",
            "message" => $e->getMessage()
        );
    }
} else if ($action = 'teste') {
    $data_insert = validToken($request);
}


echo json_encode($data_insert);



//Resgatar Token do Header 
function getAuthorizationHeader()
{
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}
function getBearerToken()
{
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}
//Fim da função de resgatar token do header

//Função de validação do Token
function validToken($request)
{

    $authHeader = $request->token;
    // $authHeader = getBearerToken();
    $temp_header = explode(".", $authHeader);

    $header = $temp_header[0];
    $payload = $temp_header[1];
    $signature = $temp_header[2];

    try {
        JWT::$leeway = 10;

        $decoded = JWT::decode($authHeader, SECRET_KEY, array(ALGORITHM));


        // Se o token não foi modificado ou se não expirou ele será válido

        // $data_from_server = '{"Coords":[{"Accuracy":"65","Latitude":"53.277720488429026","Longitude":"-9.012038778269686","Timestamp":"Fri Jul 05 2013 11:59:34 GMT+0100 (IST)"},{"Accuracy":"65","Latitude":"53.277720488429026","Longitude":"-9.012038778269686","Timestamp":"Fri Jul 05 2013 11:59:34 GMT+0100 (IST)"},{"Accuracy":"65","Latitude":"53.27770755361785","Longitude":"-9.011979642121824","Timestamp":"Fri Jul 05 2013 12:02:09 GMT+0100 (IST)"},{"Accuracy":"65","Latitude":"53.27769091555766","Longitude":"-9.012051410095722","Timestamp":"Fri Jul 05 2013 12:02:17 GMT+0100 (IST)"},{"Accuracy":"65","Latitude":"53.27769091555766","Longitude":"-9.012051410095722","Timestamp":"Fri Jul 05 2013 12:02:17 GMT+0100 (IST)"}]}';


        $result = array(
            // "data" => json_decode($data_from_server),
            "status" => "success",
            "message" => "Request authorized"
        );
    } catch (Exception $e) {

        http_response_code(401);

        $result = array(
            "jwt" => $authHeader,
            "status" => "error",
            "message" => $e->getMessage()
        );
        return $result;
    }

}
