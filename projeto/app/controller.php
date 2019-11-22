<?php
date_default_timezone_set('America/Belem');

header("Access-Control-Allow-Origin: *");
header("meta charset=UTF-8");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, no-store, must-revalidate"); // limpa o cache
header("Access-Control-Allow-Origin: *");

// clearstatcache(); // limpa o cache

require('model.php');
require('connection.php');

$dat = new Dados();

//Pegar URL página 
$url1 = $_SERVER['SERVER_NAME'];
$url2 = explode("?", $_SERVER['REQUEST_URI']);
$url = $url1 . $url2[0];

/** ************************************************************************************ */
/** **************************** PESQUISA VISITANTES *********************************** */
/** ************************************************************************************ */

if (isset($_GET['pesquisa'])) {
    $query = $_GET['pesquisa'];
    $dadosVisit = $dat->pesquisaVisitante($connect, $connectC, $query);
    // $send['pesquisa'] = array();
    $res_send = array();
    // print_r($dadosVisit);
    // echo json_encode($dadosVisit);
    if ($dadosVisit != false) {
        if (count($dadosVisit)) {
            foreach ($dadosVisit  as $dados) {
                if (isset($dados['nome'])) $nome = $dados['nome'];
                if (isset($dados['nome_serv'])) $nome = $dados['nome_serv'];

                $res_send[] = array(
                    'id_cpf' => $dados['id_cpf'],
                    'nome' => $nome
                );
            }
        }
        try {
            //array_push($send['pesquisa'], $res_send);
            //array_push($send['pesquisa'], $dadosVisit);
            http_response_code(200);
            //echo json_encode($dadosVisit,JSON_PRETTY_PRINT);
            //print_r($res_send);
            $msg = array(
                "status" => "Sucesso",
                "dados" => $res_send
            );
            echo json_encode($msg,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            // echo json_encode($res_send, JSON_PRETTY_PRINT);
            //echo json_encode($arr);
        } catch (Exception $e) {
            $err = $e->getMessage();
            $msg_err = 'Erro na pesquisa: ' . $err . "\n";
            // echo $msg_err;
            // set response code - 200 OK
            http_response_code(500);
            echo json_encode($msg_err);
        }
    } else {
        $msg = array(
            "status" => "Erro",
            "dados" => "CPF ou Nome não existe!"
        );
        echo json_encode($msg,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // echo json_encode("CPF ou Nome não existe", JSON_UNESCAPED_UNICODE);
    }
}
/** ************************************************************************************ */
/** **************************** CADASTRO VISITANTE *********************************** */
/** ************************************************************************************ */

if (isset($_GET['cadastro'])) {
    $postdata = file_get_contents("php://input");
    // print_r($postdata);
    //  echo $postdata;
    if (isset($postdata) && !empty($postdata)) {
        $query = json_decode($postdata, true);
        // print_r($query);
        // print_r($postdata);
        $id_cpf = $query['id_cpf'];
        $nome = $query['nome'];

        if (empty($nome)) {
            echo json_encode('O Nome está vazio');
        } else if (empty($id_cpf)) {
            echo json_encode('O CAMPO CPF ESTÁ VAZIO');
        } else {
            $cadastro = $dat->cadastroVisitante($connect, $connectC, $query);
            // print_r ($query);
            if ($cadastro == true) {
                http_response_code(201);
                $msg = array(
                    "status" => "Sucesso",
                    "dados" => "Cadastro realizado com sucesso!"
                );
                echo json_encode($msg,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                // echo json_encode('Cadastro realizado com sucesso');
            } else {
                http_response_code(500);
                $msg = array(
                    "status" => "Erro",
                    "dados" => "O CPF $id_cpf já está cadastrado!"
                );
                echo json_encode($msg,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                // echo json_encode('O CPF ' . $id_cpf . ' já está cadastrado!');
            }
        }
    }
}
/** ************************************************************************************ */
/** **************************** ALTERAR VISITANTE ************************************* */
/** ************************************************************************************ */
if (isset($_GET['alterar'])) {
    $postdata = file_get_contents("php://input");
    // echo $postdata;
    // echo $postdata;
    $query = json_decode($postdata, true);
    // echo json_encode($query);
    $alterar = $dat->alterarVisitante($connect, $connectC, $query);

    if ($alterar == true) {
        http_response_code(202);
        $msg = array(
            "status" => "Sucesso",
            "dados" => "Visitante Alterado com Sucesso!"
        );
        echo json_encode($msg,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // echo json_encode('Visitante Alterado com Sucesso');
    } else {
        http_response_code(500);
        $msg = array(
            "status" => "Erro",
            "dados" => "Erro ao alterar CPF!"
        );
        echo json_encode($msg,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // echo json_encode('CPF não existente');
    }
}
/** ************************************************************************************ */
/** **************************** DELETAR VISITANTE ************************************* */
/** ************************************************************************************ */
/*
if (isset($_GET['deletar'])) {
    $id_cpf = $_GET['deletar'];
    
    if (!is_numeric($id_cpf)) {
        echo json_encode('Apenas numeros são aceitos');
    } else {
        // echo json_encode($id_cpf);
        $deletar = $dat->deletarVisitante($connect, $id_cpf);
        // echo json_encode($deletar);
        if ($deletar == true) {
            http_response_code(203);
            echo json_encode('Visitante deletado com sucesso');
        } else {
            http_response_code(500);
            echo json_encode('Este visitante já foi deletado');
        }
    }
}
*/


/** ************************************************************************************ */
/** **************************** PESQUISAR LOCAIS ************************************** */
/** ************************************************************************************ */

if (isset($_GET['pesquisaLocal'])){
    $query = $_GET['pesquisaLocal'];

    $dadosVisit = $dat->pesquisaLocal($connectC, $query);
    // print_r($dadosVisit);
    // echo json_encode($dadosVisit);
    // $send['pesquisa'] = array();
    $res_send = array();
    //print_r($dadosVisit);
    if ($dadosVisit != false) {
        if (count($dadosVisit)) {
            foreach ($dadosVisit  as $dados) {
                $res_send[] = array(
                    'id_lotacao' => $dados['id_lotacao'],
                    'sigla_lotacao' => $dados['sigla_lotacao'],
                    'desc_lotacao' => $dados['desc_lotacao']
                );
            }
        }
        try {
            //array_push($send['pesquisa'], $res_send);
            //array_push($send['pesquisa'], $dadosVisit);
            http_response_code(200);
            //echo json_encode($dadosVisit,JSON_PRETTY_PRINT);
            //print_r($res_send);
            // print_r($res_send);

            // print_r($res_send);
            // $res_send = utf8_encode($res_send);
            // $res_send = utf8_string_array_encode($res_send);
            // $res_send = utf8_encode($res_send);
            // echo $res_send;
            $msg = array(
                "status" => "Sucesso",
                "dados" => $res_send
            );
            echo json_encode($msg,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            //echo json_encode($arr);
        } catch (Exception $e) {
            $err = $e->getMessage();
            $msg_err = 'Erro na pesquisa: ' . $err . "\n";
            // echo $msg_err;
            // set response code - 200 OK
            http_response_code(500);
            echo json_encode($msg_err);
        }
    } else {
        echo json_encode("Local não existe", JSON_UNESCAPED_UNICODE);
    }


}

/** ************************************************************************************ */
/** **************************** PESQUISAR SERVIDOR ************************************** */
/** ************************************************************************************ */
if(isset($_GET['pesquisaServ'])){
    // $postdata = file_get_contents("php://input");
    // $query = json_decode($postdata,true);
    $query = $_GET['pesquisaServ'];
    $dadosServ = $dat->pesquisaServ($connectC,$query);


    if($dadosServ != false){
       
        if (count($dadosServ)) {
            foreach ($dadosServ  as $dados) {
                $res_send[] = array(
                    'id_cpf' => $dados['id_cpf'],
                    'nome_serv' => $dados['nome_serv'],
                    'email_serv' => $dados['email_serv'],
                    'id_lotacao' => $dados['id_lotacao']
                );
            }
        }
        try {
            //array_push($send['pesquisa'], $res_send);
            //array_push($send['pesquisa'], $dadosServ);
            http_response_code(200);
            //echo json_encode($dadosServ,JSON_PRETTY_PRINT);
            //print_r($res_send);
            // print_r($res_send);

            // print_r($res_send);
            // $res_send = utf8_encode($res_send);
            // $res_send = utf8_string_array_encode($res_send);
            // $res_send = utf8_encode($res_send);
            // echo $res_send;
            $msg = array(
                "status" => "Sucesso",
                "dados" => $res_send
            );
            echo json_encode($msg,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            //echo json_encode($arr);
        } catch (Exception $e) {
            $err = $e->getMessage();
            $msg_err = 'Erro na pesquisa: ' . $err . "\n";
            // echo $msg_err;
            // set response code - 200 OK
            http_response_code(500);
            echo json_encode($msg_err);
        }
        // // print_r($dados);
        // // echo json_encode($dados);
        // // echo json_encode($dados, JSON_PRETTY_PRINT);
        // $msg = array(
        //     "status" => "Sucesso",
        //     "dados" => $dados
        // );
        // echo json_encode($msg, JSON_PRETTY_PRINT || JSON_UNESCAPED_UNICODE);
    }else{
        $msg = array(
            "status" => "Erro",
            "dados" => "Servidor não existe"
        );
        echo json_encode($msg, JSON_PRETTY_PRINT || JSON_UNESCAPED_UNICODE);
        

    }
}
/** ************************************************************************************ */
/** **************************** CADASTRO VISITA *************************************** */
/** ************************************************************************************ */
if (isset($_GET['cadastroVisita'])){
    $postdata = file_get_contents("php://input");
    $query = json_decode($postdata,true);
    $serv = $query['id_cpf_visitado'];
    $visit = $query['id_cpf'];
    $lotac = $query['id_lotacao_visita'];
    $vServ = $dat->pesquisaServ($connectC,$serv);
    $vVisit = $dat->pesquisaVisitante($connect,$connectC,$visit);
    $vLotac = $dat->pesquisaLocal($connectC,$lotac);
    if($vServ == false){
        $msg = array(
            "status" => "Erro",
            "mensagem" => "Esse Servidor não existe"
        );
        echo json_encode($msg,JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($vVisit == false){
        $msg = array(
            "status" => "Erro",
            "mensagem" => "Deu um erro ao verificar Visitante!"
        );
        echo json_encode($msg,JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($vLotac == false){
        $msg = array(
            "status" => "Erro",
            "mensagem" => "Local de Visita é inválido!"
        );
        echo json_encode($msg,JSON_UNESCAPED_UNICODE);
        exit;
    }
    //adicionando hora e data formatado. 
    $query['data_entrada'] = date('d-m-Y');
    $query['hora_entrada'] = date('H:i:s');
    $data = $query['data_entrada'];
    $data = date("Y-m-d",strtotime(str_replace('/','-',$data)));  
    $query['data_entrada']  = date('Y-m-d', strtotime($data));
    
    $dados = $dat->cadastroVisita($connect,$query);
    if($dados == true){
        $msg = array(
            "status" => "Sucesso",
            "mensagem" => "Visita Cadastrada Com Sucesso!"
        );
        echo json_encode($msg,JSON_UNESCAPED_UNICODE || JSON_PRETTY_PRINT);
        // echo json_encode('deu certo');
    }else{
        $msg = array(
            "status" => "Erro",
            "mensagem" => "Ops! Ocorreu um erro ao cadastrar Visita, Tente novamente!"
        );
        echo json_encode($msg,JSON_UNESCAPED_UNICODE || JSON_PRETTY_PRINT);
        // echo json_encode('Deu ruim');
    }
    // print_r($query);

}
/** ************************************************************************************ */
/** **************************** LISTAR VISITA *************************************** */
/** ************************************************************************************ */
if (isset($_GET['pesquisaVisita'])){
    $query = $_GET['pesquisaVisita'];
    

}

/** ************************************************************************************ */
/** **************************** LOGAR USUÁRIO ***************************************** */
/** ************************************************************************************ */

if(isset($_GET['logar'])){
    $postdata = file_get_contents('php://input');
    // echo $postdata;
    $query = json_decode($postdata,true);
    echo json_encode($query['username']);
    echo json_encode($query['password']);
    
}
/** ************************************************************************************ */
/** *************************** DESLOGAR USUÁRIO *************************************** */
/** ************************************************************************************ */

if(isset($_GET['deslogar'])){
    //code...
}