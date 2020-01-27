<?php
date_default_timezone_set('America/Belem');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("meta charset=UTF-8");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, no-store, must-revalidate"); // limpa o cache
header("Access-Control-Allow-Origin: *");

// clearstatcache(); // limpa o cache

require('model.php');
// require('api.php');
require('connection.php');

$dat = new Dados();
// $tok = new Token();

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
    if ($dadosVisit != false) {
        if (count($dadosVisit)) {
            foreach ($dadosVisit  as $dados) {
                $res_send[] = array(
                    'id_cpf' => $dados['id_cpf'],
                    'nome' => $dados['nome'],
                    'matricula' => $dados['matricula'],
                    'telefone' => $dados['telefone'],
                    'ci' => $dados['ci'],
                    'cargo' => $dados['cargo'],
                    'id_black_list' => $dados['id_black_list'],
                    'foto_visit' => $dados['foto_visit'],
                    'orgao_origem' => $dados['orgao_origem'],
                );
            }
        }
        try {
            http_response_code(200);
            echo json_encode($res_send, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            $err = $e->getMessage();
            $msg_err = 'Erro na pesquisa: ' . $err . "\n";
            // Error usuário nõa encontrado  - 500 
            http_response_code(500);
            echo json_encode($msg_err);
        }
    } else {
        http_response_code(501);

        $msg = "CPF ou Nome não existe!";
        echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // echo json_encode("CPF ou Nome não existe", JSON_UNESCAPED_UNICODE);
    }


    // $query = $_GET['pesquisa'];
    // $dadosVisit = $dat->pesquisaVisitante($connect, $connectC, $query);
    // // $send['pesquisa'] = array();
    // $res_send = array();
    // // print_r($dadosVisit);
    // // echo json_encode($dadosVisit);
    // if ($dadosVisit != false) {
    //     if (count($dadosVisit)) {
    //         foreach ($dadosVisit  as $dados) {
    //             if (isset($dados['nome'])) $nome = $dados['nome'];
    //             if (isset($dados['nome_serv'])) $nome = $dados['nome_serv'];

    //             // if (isset($dados['matricula']) && $dados['matricula'] == null) $matricula =' ';
    //             if (isset($dados['matricula'])) $matricula = $dados['matricula'];
    //             if (isset($dados['matricula_serv'])) $matricula = $dados['matricula_serv'];
    //             if (empty($matricula)) $matricula = '111';

    //             if (isset($dados['ci_serv'])) $ci = $dados['ci_serv'];
    //             if (isset($dados['ci'])) $ci = $dados['ci'];
    //             if(empty($ci)) $ci = '222';


    //             if (isset($dados['fone_serv'])) $telefone = $dados['fone_serv'];
    //             if (isset($dados['telefone'])) $telefone = $dados['telefone'];
    //             if(empty($telefone)) $telefone = '333';

    //             if (isset($dados['cargo'])) $cargo = $dados['cargo'];
    //             if (isset($dados['id_cargo'])) $cargo = $dados['id_cargo'];
    //             if(empty($cargo)) $cargo = '444';

    //             if (isset($dados['foto_visit'])) $foto_visit = $dados['foto_visit'];
    //             if (isset($dados['foto_serv'])) $foto_visit = $dados['foto_serv'];
    //             if (empty($foto_visit)) $foto_visit = ' 555';

    //             isset($dados['id_black_list']) ? $id_black_list = $dados['id_black_list'] : $id_black_list=0;
    //             isset($dados['orgao_origem']) ? $orgao_origem = $dados['orgao_origem'] : $orgao_origem=' 666';
    //             // if(empty($dados['orgao_origem'])) $orgao_origem = null;

    //             $res_send[] = array(
    //                 'id_cpf' => $dados['id_cpf'],
    //                 'nome' => $nome,
    //                 'matricula' => $matricula,
    //                 'telefone' => $telefone,
    //                 'ci' => $ci,
    //                 'matricula' => $matricula,
    //                 'cargo' => $cargo,
    //                 'id_black_list' => $id_black_list,
    //                 'foto_visit' => $foto_visit,
    //                 'orgao_origem' => $orgao_origem,
    //                 'teste' => 'teste'
    //             );
    //         }
    //     }
    //     try {
    //         //array_push($send['pesquisa'], $res_send);
    //         //array_push($send['pesquisa'], $dadosVisit);
    //         http_response_code(200);
    //         //echo json_encode($dadosVisit,JSON_PRETTY_PRINT);
    //         //print_r($res_send);
    //         // $msg = array(
    //         //     "status" => "Sucesso",
    //         //     "dados" => $res_send
    //         // );
    //         // echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    //         echo json_encode($res_send, JSON_PRETTY_PRINT);
    //         //echo json_encode($arr);
    //     } catch (Exception $e) {
    //         $err = $e->getMessage();
    //         $msg_err = 'Erro na pesquisa: ' . $err . "\n";
    //         // echo $msg_err;
    //         // set response code - 200 OK
    //         http_response_code(500);
    //         echo json_encode($msg_err);
    //     }
    // } else {
    //     http_response_code(501);

    //     $msg = array(
    //         "status" => "Erro",
    //         "dados" => "CPF ou Nome não existe!"
    //     );
    //     echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    //     // echo json_encode("CPF ou Nome não existe", JSON_UNESCAPED_UNICODE);
    // }
}
/** ************************************************************************************ */
/** **************************** CADASTRO VISITANTE ************************************ */
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
            http_response_code(502);
            echo json_encode('O Nome está vazio');
        } else if (empty($id_cpf)) {
            http_response_code(503);
            echo json_encode('O campo CPF está vazio');
        } else if (strlen($id_cpf) < 11) {
            http_response_code(509);
            echo json_encode('O campo CPF deve ter no mínimo 11 caracteres');
        } else {
            $cadastro = $dat->cadastroVisitante($connect, $connectC, $query);
            // print_r ($query);
            if ($cadastro == true) {
                http_response_code(201);
                $msg = array(
                    "status" => "Sucesso",
                    "dados" => "Cadastro realizado com sucesso!"
                );
                echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                // echo json_encode('Cadastro realizado com sucesso');
            } else {
                http_response_code(504);
                // $msg = array(
                //     "status" => "Erro",
                //     "dados" => "O CPF $id_cpf já está cadastrado!"
                // );
                $msg = "O CPF $id_cpf já está cadastrado!";
                echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
        echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // echo json_encode('Visitante Alterado com Sucesso');
    } else {
        http_response_code(505);
        $msg = array(
            "status" => "Erro",
            "dados" => "Erro ao alterar CPF!"
        );
        echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
/** **************************** PESQUISAR LOCAL *************************************** */
/** ************************************************************************************ */

if (isset($_GET['pesquisaLocal'])) {
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
                    'desc_lotacao' => $dados['desc_lotacao'],
                    'id_unidade' => $dados['id_unidade'],
                    'local_lotacao' => $dados['local_lotacao']
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
            $msg = $res_send;
            echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            //echo json_encode($arr);
        } catch (Exception $e) {
            $err = $e->getMessage();
            http_response_code(506);
            $msg_err = 'Erro na pesquisa: ' . $err . "\n";
            // echo $msg_err;
            // set response code - 200 OK
            // http_response_code(500);
            echo json_encode($msg_err);
        }
    } else {
        echo json_encode("Local não existe", JSON_UNESCAPED_UNICODE);
    }
}

/** ************************************************************************************ */
/** **************************** PESQUISAR SERVIDOR ************************************ */
/** ************************************************************************************ */
if (isset($_GET['pesquisaServ'])) {
    // $postdata = file_get_contents("php://input");
    // $query = json_decode($postdata,true);
    $query = $_GET['pesquisaServ'];
    $dadosServ = $dat->pesquisaServ($connectC, $query);


    if ($dadosServ != false) {

        if (count($dadosServ)) {
            foreach ($dadosServ  as $dados) {
                $res_send[] = array(
                    'id_cpf' => $dados['id_cpf'],
                    'nome_serv' => $dados['nome_serv'],
                    // 'email_serv' => $dados['email_serv'],
                    'id_lotacao' => $dados['id_lotacao_atual'],
                    'sigla_lotacao' => $dados['sigla_lotacao'],
                    'desc_lotacao' => $dados['desc_lotacao'],
                    'matricula' => $dados['matricula_serv']

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
            $msg = $res_send;
            // $msg = array(
            //     "status" => "Sucesso",
            //     "dados" => $res_send
            // );
            echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            //echo json_encode($arr);
        } catch (Exception $e) {
            $err = $e->getMessage();
            http_response_code(507);
            $msg_err = 'Erro na pesquisa: ' . $err . "\n";
            // echo $msg_err;
            // set response code - 200 OK
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
    } else {
        http_response_code(508);
        $msg = "Servidor não existe";
        echo json_encode($msg, JSON_PRETTY_PRINT || JSON_UNESCAPED_UNICODE);
    }
}
/** ************************************************************************************ */
/** **************************** CADASTRO VISITA *************************************** */
/** ************************************************************************************ */
if (isset($_GET['cadastroVisita'])) {
    $postdata = file_get_contents("php://input");
    $query = json_decode($postdata, true);
    $serv = $query['id_cpf_visitado'];
    $visit = $query['id_cpf'];
    $lotac = $query['id_lotacao_visita'];
    $vServ = $dat->pesquisaServ($connectC, $serv);
    $vVisit = $dat->pesquisaVisitante($connect, $connectC, $visit);
    $vLotac = $dat->pesquisaLocal($connectC, $lotac);
    if ($vServ == false) { 
        http_response_code(512);
        $msg =  "Esse Servidor não existe";
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($vVisit == false) {
        http_response_code(513);
        $msg ="Deu um erro ao verificar Visitante!";
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($vLotac == false) {
        http_response_code(514);
        $msg ="Local de Visita é inválido!";
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        exit;
    }
    //adicionando hora e data formatado. 
    $query['data_entrada'] = date('d-m-Y');
    $query['hora_entrada'] = date('H:i:s');
    $data = $query['data_entrada'];
    $data = date("Y-m-d", strtotime(str_replace('/', '-', $data)));
    $query['data_entrada']  = date('Y-m-d', strtotime($data));

    $dados = $dat->cadastroVisita($connect, $query);
    if ($dados == true) {
        http_response_code(200);
        $msg = "Visita Cadastrada Com Sucesso!";
        echo json_encode($msg, JSON_UNESCAPED_UNICODE || JSON_PRETTY_PRINT);
        // echo json_encode('deu certo');
    } else {
        http_response_code(515);
        $msg ="Ops! Ocorreu um erro ao cadastrar Visita, Tente novamente!";
        echo json_encode($msg, JSON_UNESCAPED_UNICODE || JSON_PRETTY_PRINT);
        // echo json_encode('Deu ruim');
    }
    // print_r($query);

}
/** ************************************************************************************ */
/** **************************** LISTAR VISITA ***************************************** */
/** ************************************************************************************ */
if (isset($_GET['pesquisaVisita'])) {
    $query = $_GET['pesquisaVisita'];
    $dadosVisit = $dat->pesquisaVisita($connect, $query);
    // $send['pesquisa'] = array();
    $res_send = array();
    // print_r($dadosVisit);
    // exit;
    // echo json_encode($dadosVisit);
    if ($dadosVisit != false) {
        if (count($dadosVisit)) {
            foreach ($dadosVisit  as $dados) {
                $res_send[] = array(
                    'id_visita' => $dados['id_visita'],
                    'id_cpf' => $dados['id_cpf'],
                    'id_lotacao_visita' => $dados['id_lotacao_visita'],
                    'data_entrada' => $dat->data($dados['data_entrada']),
                    'hora_entrada' => $dados['hora_entrada'],
                    'id_cpf_visitado' => $dados['id_cpf_visitado'],
                    'txt_observacoes' => $dados['txt_observacoes'],
                    'txt_visitado' => $dados['txt_visitado'],
                    'id_cartao' => $dados['id_cartao'],
                    'sigla_lotacao' => $dados['sigla_lotacao'],
                    'local_lotacao' => $dados['local_lotacao'],
                    'desc_lotacao' => utf8_encode($dados['desc_lotacao']),
                    'nome' => $dados['nome'],
                    'nome_serv' => $dados['nome_serv']
                );
            }
        }
        try {
            //array_push($send['pesquisa'], $res_send);
            //array_push($send['pesquisa'], $dadosVisit);
            http_response_code(200);
            //echo json_encode($dadosVisit,JSON_PRETTY_PRINT);
            // print_r($res_send);
            // exit;
            $msg = $res_send;
            echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            // echo json_encode($res_send, JSON_PRETTY_PRINT);
            //echo json_encode($arr);
        } catch (Exception $e) {
            $err = $e->getMessage();
            $msg_err = 'Erro na pesquisa: ' . $err . "\n";
            // echo $msg_err;
            // set response code - 200 OK
            http_response_code(516);
            echo json_encode($msg_err);
        }
    } else {
        http_response_code(510);
        $msg = "Esta pessoa não fez visitas ou foi visitada!";
        echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // echo json_encode("CPF ou Nome não existe", JSON_UNESCAPED_UNICODE);
    }
}
/** ************************************************************************************ */
/** **************************** LISTAR USUARIO **************************************** */
/** ************************************************************************************ */
if (isset($_GET['pesquisaUsuario'])) {
    $query = $_GET['pesquisaUsuario'];
    $dadosVisit = $dat->pesquisaUsuario($connect, $query);
    // $send['pesquisa'] = array();
    $res_send = array();
    // print_r($dadosVisit);
    // echo json_encode($dadosVisit);
    if ($dadosVisit != false) {
        if (count($dadosVisit)) {
            foreach ($dadosVisit  as $dados) {
                $res_send[] = array(
                    'id_cpf' => $dados['id_cpf'],
                    'nome' => $dados['nome'],
                    'usuario' => $dados['usuario'],
                    'email' => $dados['email'],
                    'telefone' => $dados['telefone'],
                    'id_tipo_usuario' => $dados['id_tipo_usuario'],
                    'desc_tipo_usuario' => $dados['desc_tipo_usuario'],
                    'id_sede' => $dados['id_sede'],
                    'desc_sede' => $dados['desc_sede'],
                    'hashSenha' => $dados['hashSenha']
                );
            }
        }
        try {
            //array_push($send['pesquisa'], $res_send);
            //array_push($send['pesquisa'], $dadosVisit);
            http_response_code(200);
            //echo json_encode($dadosVisit,JSON_PRETTY_PRINT);
            //print_r($res_send);
            $msg = $res_send;
            echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
            "dados" => "Esta pessoa não fez visitas ou foi visitada!"
        );
        echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // echo json_encode("CPF ou Nome não existe", JSON_UNESCAPED_UNICODE);
    }
}
/** ************************************************************************************ */
/** **************************** CADASTRO USUÁRIO ************************************** */
/** ************************************************************************************ */

if (isset($_GET['cadastroUsuario'])) {
    $postdata = file_get_contents("php://input");
    // print_r($postdata);
    //  echo $postdata;
    if (isset($postdata) && !empty($postdata)) {
        $query = json_decode($postdata, true);
        // print_r($query);
        // print_r($postdata);
        $id_cpf = $query['id_cpf'];
        $nome = $query['nome'];
        $usuario = $query['usuario'];
        $id_tipo_usuario = $query['id_tipo_usuario'];
        $hashSenha = password_hash($query['hashSenha'], PASSWORD_DEFAULT);
        if (empty($nome)) {
            echo json_encode('O Nome está vazio');
        } else if (empty($id_cpf)) {
            echo json_encode('O CAMPO CPF ESTÁ VAZIO');
        } else if (empty($usuario)) {
            echo json_encode('O CAMPO usuário ESTÁ VAZIO');
        } else if (empty($id_tipo_usuario)) {
            echo json_encode('O CAMPO do tipo do usuario ESTÁ VAZIO');
        } else if (empty($hashSenha)) {
            echo json_encode('O CAMPO senha ESTÁ VAZIO');
        } else {
            $cadastro = $dat->cadastroUsuario($connect, $query);
            // print_r ($query);
            if ($cadastro == true) {
                http_response_code(201);
                $msg = array(
                    "status" => "Sucesso",
                    "dados" => "Cadastro realizado com sucesso!"
                );
                echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                // echo json_encode('Cadastro realizado com sucesso');
            } else {
                http_response_code(500);
                $msg = array(
                    "status" => "Erro",
                    "dados" => "O CPF $id_cpf já está cadastrado!"
                );
                echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                // echo json_encode('O CPF ' . $id_cpf . ' já está cadastrado!');
            }
        }
    }
}

/** ************************************************************************************ */
/** **************************** DELETAR USUARIO *************************************** */
/** ************************************************************************************ */

if (isset($_GET['deletarUsuario'])) {
    $id_cpf = $_GET['deletarUsuario'];

    if (!is_numeric($id_cpf)) {
        echo json_encode('Apenas numeros são aceitos');
    }
    if (empty($id_cpf)) {
        echo json_encode('Digite o cpf do usuario a ser deletado');
    } else {
        // echo json_encode($id_cpf);
        $deletar = $dat->deletarUsuario($connect, $id_cpf);
        // echo json_encode($deletar);
        if ($deletar == true) {
            http_response_code(203);
            echo json_encode('Usuário deletado com sucesso');
        } else {
            http_response_code(500);
            echo json_encode('Este usuário já foi deletado ou não existe!');
        }
    }
}

/** ************************************************************************************ */
/** **************************** ALTERAR USUARIO *************************************** */
/** ************************************************************************************ */

if (isset($_GET['alterarUsuario'])) {
    $postdata = file_get_contents("php://input");
    // echo $postdata;
    // echo $postdata;
    $query = json_decode($postdata, true);
    // echo json_encode($query);
    $alterar = $dat->alterarUsuario($connect, $query);

    if ($alterar == true) {
        http_response_code(202);
        $msg = array(
            "status" => "Sucesso",
            "dados" => "Usuario Alterado com Sucesso!"
        );
        echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // echo json_encode('Visitante Alterado com Sucesso');
    } else {
        http_response_code(500);
        $msg = array(
            "status" => "Erro",
            "dados" => "Erro ao alterar o usuario!"
        );
        echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // echo json_encode('CPF não existente');
    }
}

/** ************************************************************************************ */
/** **************************** LISTAR BLACK LIST ************************************* */
/** ************************************************************************************ */
if (isset($_GET['pesquisaBlackList'])) {
    $query = $_GET['pesquisaBlackList'];
    $dadosVisit = $dat->pesquisaBlackList($connect, $query);
    // $send['pesquisa'] = array();
    $res_send = array();
    // print_r($dadosVisit);
    // echo json_encode($dadosVisit);
    if ($dadosVisit != false) {
        if (count($dadosVisit)) {
            foreach ($dadosVisit  as $dados) {
                $data_saida = ($dados['data_saida'] == null) ? "Ativo" : $dados['data_saida'];
                $res_send[] = array(
                    'id_black_list' => $dados['id_black_list'],
                    'id_cpf_visitante' => $dados['id_cpf_visitante'],
                    'nome' => $dados['nome'],
                    'data_entrada' => $dat->data($dados['data_entrada']),
                    'data_saida' => $dat->data($data_saida)
                );
            }
        }
        try {
            //array_push($send['pesquisa'], $res_send);
            //array_push($send['pesquisa'], $dadosVisit);
            http_response_code(200);
            //echo json_encode($dadosVisit,JSON_PRETTY_PRINT);
            //print_r($res_send);
            $msg = $res_send;
            echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
            "dados" => "Esta pessoa não está na black List!"
        );
        echo json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // echo json_encode("CPF ou Nome não existe", JSON_UNESCAPED_UNICODE);
    }
}
/** ************************************************************************************ */
/** ********************* ADICIONAR VISITANTE Á BLACK LIST ***************************** */
/** ************************************************************************************ */
if (isset($_GET['blacklist'])) {
    $id_cpf = $_GET['blacklist'];

    if (!is_numeric($id_cpf)) {
        echo json_encode('Apenas numeros são aceitos');
        exit;
    }
    if (empty($id_cpf)) {
        echo json_encode('Digite o cpf do usuario a ser adicionado no BLACK LIST');
        exit;
    }
    $dadosVisit = $dat->pesquisaVisitante($connect, $connectC, $id_cpf);
    if ($dadosVisit === false) {
        echo json_encode('Esse visitante não existe');
        exit;
    }

    $dadosVisit2 = $dat->pesquisaBlackList($connect, $id_cpf);
    if ($dadosVisit2 != false) {
        if (count($dadosVisit2)) {
            foreach ($dadosVisit2  as $dados) {

                if (($dados['id_cpf_visitante']== $id_cpf) && ($dados['data_saida'] == null)) {
                    echo json_encode('Pessoa já cadastrada na Black List!');
                    exit;
                }

                $res_send[] = array(
                    'id_black_list' => $dados['id_black_list'],
                    'id_cpf_visitante' => $dados['id_cpf_visitante'],
                    'data_entrada' => $dados['data_entrada'],
                    'data_saida' => $dados['data_saida']
                );
            }
        }
    }
    // echo json_encode($res_send);



    // echo json_encode($dadosVisit2);
    // echo json_encode($dadosVisit2);
    // int sizeof(array arr);
    // echo sizeof($dadosVisit2);
    // if (sizeof($dadosVisit2) == 0) {


    $query['data_entrada'] = date('d-m-Y');
    $data = $query['data_entrada'];
    $data = date("Y-m-d", strtotime(str_replace('/', '-', $data)));
    $query['data_entrada']  = date('Y-m-d', strtotime($data));


    // echo json_encode($id_cpf);
    $adicionar = $dat->adicionarBlackList($connect, $id_cpf, $query['data_entrada']);
    // echo json_encode($adicionar);
    if ($adicionar == true) {
        http_response_code(203);
        echo json_encode('Operação realizada com sucesso!');
    } else {
        http_response_code(500);
        echo json_encode('Servidores não podem ser cadastrados na Black List');
    }
    // } else {
    //     // echo json_encode('Esse visitante já esta cadastra no Black List');

    //     exit;
    // }
}
/** ************************************************************************************ */
/** ******************* RETIRAR VISITANTE DA BLACK LIST ******************************** */
/** ************************************************************************************ */
if (isset($_GET['retiraBlackList'])) {
    $id_cpf = $_GET['retiraBlackList'];

    if (!is_numeric($id_cpf)) {
        echo json_encode('Apenas numeros são aceitos');
    }
    if (empty($id_cpf)) {
        echo json_encode('Digite o cpf do usuario a ser adicionado no BLACK LIST');
    }
    $dadosVisit = $dat->pesquisaVisitante($connect, $connectC, $id_cpf);
    if ($dadosVisit === false) {
        echo json_encode('Esse visitante não existe');
        exit;
    }

    $dadosVisit2 = $dat->pesquisaBlackList($connect, $id_cpf);
    // echo json_encode($dadosVisit2);
    // echo sizeof($dadosVisit2);
    // exit;
    // int sizeof(array arr);
    if (sizeof($dadosVisit2) >= 1) {


        $query['data_saida'] = date('d-m-Y');
        $data = $query['data_saida'];
        $data = date("Y-m-d", strtotime(str_replace('/', '-', $data)));
        $query['data_saida']  = date('Y-m-d', strtotime($data));


        // echo json_encode($id_cpf);
        $retirar = $dat->retirarBlackList($connect, $id_cpf, $query['data_saida']);
        // echo json_encode($retirar);
        if ($retirar == true) {
            http_response_code(203);
            echo json_encode('Operação realizada com sucesso!');
        } else {
            http_response_code(500);
            echo json_encode('CPF não está em um chamado aberto na Black List!');
        }
    } else {
        echo json_encode('Esse visitante não tem um cadastrado no Black List');
        exit;
    }
}

/** ************************************************************************************ */
/** **************************** LOGAR USUÁRIO ***************************************** */
/** ************************************************************************************ */

if (isset($_GET['logar'])) {
    $postdata = file_get_contents('php://input');
    // echo $postdata;
    $query = json_decode($postdata, true);
    echo json_encode($query['username']);
    echo json_encode($query['password']);
}
/** ************************************************************************************ */
/** *************************** DESLOGAR USUÁRIO *************************************** */
/** ************************************************************************************ */

if (isset($_GET['deslogar'])) {
    //code...
}
/** ************************************************************************************ */
/** *************************** VALIDAR TOKEN ****************************************** */
/** ************************************************************************************ */
// if(isset($_GET['validaToken'])){
//     $token = $_GET['validaToken'];

//     $dados = $tok->validarToken($token);
//     echo $dados;
//     // $part = explode (".",$token);
// }
