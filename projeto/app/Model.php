<?php
require('connection.php');
class Dados
{
    //FUNÇÃO PESQUISAR VISITANTE
    public function pesquisaVisitante($connect, $connectC, $query)
    {
        $sql = "select t1.id_cpf,
        t1.nome,
        t1.matricula,
        t1.telefone,
        t1.ci,
        t1.cargo,
        t1.id_black_list,
        t1.foto_visit,
        t1.orgao_origem
        from db_visitantes.tb_visitante t1
        where id_cpf like '%" . $query . "%' or nome like '%" . $query . "%'
        union all
        select t2.id_cpf,
        t2.nome_serv,
        t2.matricula_serv,
        t2.fone_serv,
        t2.ci_serv,
        t2.id_cargo,
        0,
        t2.foto_serv,
        null
        from db_coorporativo.tb_servidores t2
        where id_cpf like '%" . $query . "%' or nome_serv like '%" . $query . "%'
        order by nome;";
        // $sql = "select * from tb_visitante where id_cpf like '%" . $query . "%' or nome like '%" . $query . "%';";
        $obj = $connect->prepare($sql);
        $result = ($obj->execute()) ? $obj->fetchAll() : false;
        return $result;
        // print_r ($result);




        // try {
        //     /****************************************************************************************** */
        //     /****************************** CONFERE NO PRIMEIRO BANCO ********************************* */
        //     /****************************************************************************************** */

        //     $sql = "select * from tb_visitante where id_cpf like '%" . $query . "%' or nome like '%" . $query . "%';";
        //     $obj = $connect->prepare($sql);
        //     $result = ($obj->execute()) ? $obj->fetchAll() : false;

        //     /****************************************************************************************** */
        //     /****************************** CONFERE NO SEGUNDO BANCO ********************************** */
        //     /****************************************************************************************** */

        //     $sqlc = "select * from tb_servidores where id_cpf like '%" . $query . "%' or nome_serv like '%" . $query . "%';";
        //     $objc = $connectC->prepare($sqlc);
        //     $resultc = ($objc->execute()) ? $objc->fetchAll() : false;
        //     //return $resultfinal = array('tabela1'=> $result, 'tabela2' => $resultc);

        //     if (count($resultc) || count($result)) {
        //         // $resultFinal = $resultc + $result;

        //         $resultFinal = array_merge($result, $resultc);
        //         return $resultFinal;
        //     } else {
        //         return false;
        //     }
        // } catch (Exception $e) {
        //     echo 'Erro: ',  $e->getMessage(), "\n";
        // }
    }
    //FUNÇÃO CADASTRAR VISITANTE
    public function cadastroVisitante($connect, $connectC, $query)
    {
        $id_cpf = $query['id_cpf'];
        $nome = $query['nome'];
        $tele = empty($query['telefone']) ? null : $query['telefone'];
        $ci = empty($query['ci']) ? null : $query['ci'];
        $matricula = empty($query['matricula']) ? null : $query['matricula'];
        $cargo = empty($query['cargo']) ? null : $query['cargo'];
        $foto_visit = empty($query['foto_visit']) ? null : $query['foto_visit'];
        $orgao_origem = empty($query['orgao_origem']) ? null : $query['orgao_origem'];

        /****************************************************************************************** */
        /****************************** CONFERE NO PRIMEIRO BANCO ********************************* */
        /****************************************************************************************** */
        $sql = "select * from tb_visitante where id_cpf like '%" . $id_cpf . "%'; ";
        $obj = $connect->prepare($sql);
        $result = ($obj->execute()) ? $obj->fetchAll() : false;
        /****************************************************************************************** */
        /****************************** CONFERE NO SEGUNDO BANCO ********************************** */
        /****************************************************************************************** */

        $sqlc = "select * from tb_servidores where id_cpf like '%" . $id_cpf . "%';";
        $objc = $connectC->prepare($sqlc);
        $resultc = ($objc->execute()) ? $objc->fetchAll() : false;
        // SE TIVER ALGUM CPF FOR IGUAL NÃO CRIARA UM NOVO VISITANTE, MAS SE NÃO EXISTIR CRIARA UM NOVO VISITANTE NO BANCO DB_VISITANTES.
        if (count($resultc) || count($result)) {
            $resultFinal = false;
        } else {
            try {
                $sqlV = 'INSERT INTO tb_visitante (id_cpf, nome, telefone, ci, matricula, cargo, foto_visit,orgao_origem) VALUES(:id_cpf,:nome,:telefone,:ci,:matricula,:cargo,:foto_visit,:orgao_origem)';
                $obj = $connect->prepare($sqlV);
                $obj->execute(array(
                    ':id_cpf' => $id_cpf,
                    ':nome' => $nome,
                    ':telefone' => $tele,
                    ':ci' => $ci,
                    ':matricula' => $matricula,
                    ':cargo' => $cargo,
                    ':foto_visit' => $foto_visit,
                    ':orgao_origem' => $orgao_origem
                ));
                /*
                $sqlV = "insert into tb_visitante (id_cpf, nome, telefone, ci, matricula, cargo, foto_visit,orgao_origem) values('" . $id_cpf . "','" . $nome . "','" . $tele . "','" . $ci . "','" . $matricula . "','" . $cargo . "','" . $foto_visit . "','" . $orgao_origem . "')";
                $obj = $connect->prepare($sqlV);*/
                // if ($obj->execute()) {
                if ($obj->rowCount() > 0) {
                    $resultFinal = true;
                } else {
                    throw new PDOException("Erro ao tentar efetivar cadastro");
                }
                /*} else {
                    throw new PDOException("Erro: Não foi possível executar a declaração sql");
                }*/
            } catch (PDOException $e) {
                echo "Erro: " . $e->getMessage();
                exit;
            }
        }

        return $resultFinal;
    }

    //FUNÇÃO ALTERAR VISITANTE
    public function alterarVisitante($connect, $connectc, $query)
    {
        $id_cpf = $query['id_cpf'];

        $select = Dados::pesquisaVisitante($connect, $connectc, $id_cpf);

        if ($select == false) {
            $resultFinal = false;
        } else {
            $tele = empty($query['telefone']) ? null : $query['telefone'];
            $ci = empty($query['ci']) ? null : $query['ci'];
            $matricula = empty($query['matricula']) ? null : $query['matricula'];
            $cargo = empty($query['cargo']) ? null : $query['cargo'];
            $foto_visit = empty($query['foto_visit']) ? null : $query['foto_visit'];
            $orgao_origem = empty($query['orgao_origem']) ? null : $query['orgao_origem'];

            $sql = "update tb_visitante set telefone = ?, ci = ?, matricula = ?, cargo = ?,foto_visit = ?, orgao_origem = ? where id_cpf = ?";

            $statement = $connect->prepare($sql);

            $statement->bindParam(1, $tele);
            $statement->bindParam(2, $ci);
            $statement->bindParam(3, $matricula);
            $statement->bindParam(4, $cargo);
            $statement->bindParam(5, $foto_visit);
            $statement->bindParam(6, $orgao_origem);
            $statement->bindParam(7, $id_cpf);

            $statement->execute();


            // var_dump($statement);

            $resultFinal = true;
        }
        return $resultFinal;
    }
    //FUNÇÃO DELETAR VISITANTE
    public function deletarVisitante($connect, $id_cpf)
    {
        try {
            $stmt = $connect->prepare('DELETE FROM tb_visitante WHERE id_cpf = :id_cpf');
            $stmt->bindParam(':id_cpf', $id_cpf);
            $stmt->execute();

            // echo $stmt->rowCount(); 
            if ($stmt->rowCount() > 0) {
                $resultFinal = true;
            } else {
                $resultFinal = false;
            }
        } catch (Exception $e) {
            echo "Erro: " . $e->getMessage();
        }

        return $resultFinal;
    }

    /*
    public function logarUsuario($connect,$usuario,$hashSenha){
        // print_r($usuario);
        // print_r($hashSenha);
        $sql ='select * from tb_usuario where usuario="'.$usuario.'" and hashSenha = "'.$hashSenha.'";';
        // echo $sql;
        // $sql = "select * from tb_usuario where usuario " . $usuario . " and hashSenha  " . $hashSenha . ";";
        $obj = $connect->prepare($sql);
        $result = ($obj->execute()) ? $obj->fetchAll() : false;
        // echo $result;
        return $result;
    }*/

    //FUNÇÃO PESQUISAR LOCAIS
    public function pesquisaLocal($connectC, $query)
    {
        try { //select * from tb_lotacao where desc_lotacao like '%sec%' or sigla_lotacao like '%sec%';
            $sqlc = "select * from tb_lotacao where desc_lotacao like '%" . $query . "%' or sigla_lotacao like '%" . $query . "%' or id_lotacao like '%" . $query . "%';";
            // $sqlc = "select * from tb_lotacao where local_lotacao like '%" . $query . "%' ;";
            $objc = $connectC->prepare($sqlc);
            $resultc = ($objc->execute()) ? $objc->fetchAll() : false;
        } catch (Exception $e) {
            echo 'Erro: ',  $e->getMessage(), "\n";
        }
        return $resultc;
    }
    //FUNÇÃO CADASTRAR VISITAS
    public function cadastroVisita($connect, $query)
    {

        $id_cpf = $query['id_cpf'];
        $id_lotacao_visita = $query['id_lotacao_visita'];
        // $id_lotacao_visita = intval($id_lotacao_visita);
        $data_entrada = $query['data_entrada'];
        $hora_entrada = $query['hora_entrada'];
        // $id_cpf_visitado = $query['id_cpf_visitado'];
        $id_cpf_visitado = empty($query['id_cpf_visitado']) ? null : $query['id_cpf_visitado'];
        $txt_observacoes = empty($query['txt_observacoes']) ? null : $query['txt_observacoes'];
        $txt_visitado = empty($query['txt_visitado']) ? null : $query['txt_visitado'];

        // if (($id_cpf_visitado == null) || (empty($id_cpf_visitado))) {
        //     $id_cpf_visitado = '-----------';
        // }

        // echo '<br>' . $id_cpf;
        // echo '<br>' . $id_lotacao_visita;
        // echo gettype($id_lotacao_visita), "\n";
        // echo '<br>' . $data_entrada;
        // echo '<br>' . $hora_entrada;
        // echo '<br>' . $id_cpf_visitado;
        // echo '<br>' . $txt_observacoes;
        // echo '<br>' . $txt_visitado;

        $sql = "INSERT INTO tb_visita(id_cpf, id_lotacao_visita, data_entrada, hora_entrada, id_cpf_visitado, txt_observacoes, txt_visitado) 
                VALUES (:id_cpf, :id_lotacao_visita, :data_entrada, :hora_entrada, :id_cpf_visitado, :txt_observacoes, :txt_visitado);";
        // $sql = "INSERT INTO tb_visita (id_cpf, id_lotacao_visita, data_entrada, hora_entrada, id_cpf_visitado, txt_observacoes, txt_visitado) 
        //         VALUES (:id_cpf, :id_lotacao_visita, :data_entrada, :hora_entrada, :id_cpf_visitado, :txt_observacoes, :txt_visitado);";
        try {
            $stmt = $connect->prepare($sql);
            $stmt->bindValue(':id_cpf', $id_cpf, PDO::PARAM_STR);
            $stmt->bindValue(':id_lotacao_visita', $id_lotacao_visita, PDO::PARAM_STR);
            $stmt->bindValue(':data_entrada', $data_entrada, PDO::PARAM_STR);
            $stmt->bindValue(':hora_entrada', $hora_entrada, PDO::PARAM_STR);
            $stmt->bindValue(':id_cpf_visitado', $id_cpf_visitado, PDO::PARAM_STR);
            $stmt->bindValue(':txt_observacoes', $txt_observacoes, PDO::PARAM_STR);
            $stmt->bindValue(':txt_visitado', $txt_visitado, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $resultFinal = true;
            } else {
                throw new PDOException("Erro ao tentar efetivar cadastro");
                $resultFinal = false;
            }
        } catch (PDOException $e) {
            // printf("%s","Erro : " . $e->getMessage().$e);
            echo "ERROR: $e";
        }

        return $resultFinal;
    }
    //FUNÇÃO PESQUISAR SERVIDOR
    public function pesquisaServ($connectC, $query)
    {
        try {
            // $sql = "SELECT * FROM tb_servidores WHERE id_cpf LIKE '%" . $query . "%' OR nome_serv LIKE '%" . $query . "%';";
            $sql = "SELECT V.id_cpf, V.matricula_serv,V.nome_serv, V.id_lotacao_atual, V.foto_serv, V.ci_serv , L.sigla_lotacao, L.desc_lotacao FROM tb_servidores AS V
            INNER JOIN db_coorporativo.tb_lotacao AS L ON V.id_lotacao_atual = L.id_lotacao WHERE id_cpf LIKE '%" . $query . "%' OR nome_serv LIKE '%" . $query . "%' order by nome_serv;";
            $obj = $connectC->prepare($sql);
            $result = ($obj->execute()) ? $obj->fetchAll() : false;
        } catch (PDOException $e) {
            echo 'Erro: ',  $e->getMessage(), "\n";
        }
        return $result;
    }
    //FUNÇÂO PESQUISAR VISITA
    public function pesquisaVisita($connect, $query)
    {
        try {
            $sql = "SELECT V.*, L.sigla_lotacao, L.local_lotacao, L.desc_lotacao , VI.nome, S.nome_serv
                FROM (tb_visita AS V
                INNER JOIN db_coorporativo.tb_lotacao AS L ON V.id_lotacao_visita = L.id_lotacao
                INNER JOIN tb_visitante AS VI ON V.id_cpf= VI.id_cpf
                INNER JOIN db_coorporativo.tb_servidores AS S ON V.id_cpf_visitado = S.id_cpf) ORDER BY data_entrada DESC , hora_entrada DESC;";

            $obj = $connect->prepare($sql);
            $result = ($obj->execute()) ? $obj->fetchAll() : false;
        } catch (PDOException $e) {
            echo 'Erro: ',  $e->getMessage(), "\n";
        }
        return $result;
    }
    //FUNÇÂO PESQUISAR USUARIO
    public function pesquisaUsuario($connect, $query)
    {
        try {
            // $sql = "SELECT * FROM tb_usuario WHERE id_cpf LIKE '%" . $query . "%' OR nome LIKE '%" . $query . "%' OR usuario LIKE '%" . $query . "%';";
            $sql = "SELECT U.*, S.desc_sede, T.desc_tipo_usuario FROM tb_usuario AS U
            INNER JOIN tb_sede AS S ON U.id_sede = S.id_sede
            INNER JOIN tb_tipo_usuario AS T ON U.id_tipo_usuario = T.id_tipo_usuario WHERE id_cpf LIKE '%" . $query . "%' OR nome LIKE '%" . $query . "%' OR usuario LIKE '%" . $query . "%';";
            $obj = $connect->prepare($sql);
            $result = ($obj->execute()) ? $obj->fetchAll() : false;
        } catch (PDOException $e) {
            echo 'Erro: ',  $e->getMessage(), "\n";
        }
        return $result;
    }
    //FUNÇÃO CADASTRAR Usuario
    public function cadastroUsuario($connect, $query)
    {
        $id_cpf = $query['id_cpf'];
        $nome = $query['nome'];
        $usuario = $query['usuario'];
        $hashSenha = password_hash($query['hashSenha'], PASSWORD_DEFAULT);
        $id_tipo_usuario = $query['id_tipo_usuario'];
        $id_sede = $query['id_sede'];
        $tele = empty($query['telefone']) ? null : $query['telefone'];
        $email = empty($query['email']) ? null : $query['email'];

        /****************************************************************************************** */
        /****************************** CONFERE NO  BANCO ********************************* */
        /****************************************************************************************** */
        $sql = "select * from tb_usuario where id_cpf like '%" . $id_cpf . "%'; ";
        $obj = $connect->prepare($sql);
        $result = ($obj->execute()) ? $obj->fetchAll() : false;
        // SE TIVER ALGUM CPF FOR IGUAL NÃO CRIARA UM NOVO VISITANTE, MAS SE NÃO EXISTIR CRIARA UM NOVO VISITANTE NO BANCO DB_VISITANTES.
        if (count($result)) {
            $resultFinal = false;
        } else {
            try {
                $sqlV = 'INSERT INTO tb_usuario (id_cpf, nome, usuario,email,telefone,id_tipo_usuario,id_sede,hashSenha) 
                VALUES(:id_cpf, :nome, :usuario, :email, :telefone, :id_tipo_usuario, :id_sede, :hashSenha)';
                $obj = $connect->prepare($sqlV);
                $obj->execute(array(
                    ':id_cpf' => $id_cpf,
                    ':nome' => $nome,
                    ':usuario' => $usuario,
                    ':email' => $email,
                    ':telefone' => $tele,
                    ':id_tipo_usuario' => $id_tipo_usuario,
                    ':id_sede' => $id_sede,
                    ':hashSenha' => $hashSenha
                ));
                /*
                $sqlV = "insert into tb_visitante (id_cpf, nome, telefone, ci, matricula, cargo, foto_visit,orgao_origem) values('" . $id_cpf . "','" . $nome . "','" . $tele . "','" . $ci . "','" . $matricula . "','" . $cargo . "','" . $foto_visit . "','" . $orgao_origem . "')";
                $obj = $connect->prepare($sqlV);*/
                // if ($obj->execute()) {
                if ($obj->rowCount() > 0) {
                    $resultFinal = true;
                } else {
                    throw new PDOException("Erro ao tentar efetivar cadastro");
                }
                /*} else {
                    throw new PDOException("Erro: Não foi possível executar a declaração sql");
                }*/
            } catch (PDOException $e) {
                echo "Erro: " . $e->getMessage();
                exit;
            }
        }

        return $resultFinal;
    }
    //FUNÇÃO DELETAR Usuario
    public function deletarUsuario($connect, $id_cpf)
    {
        try {
            $stmt = $connect->prepare('DELETE FROM tb_usuario WHERE id_cpf = :id_cpf');
            $stmt->bindParam(':id_cpf', $id_cpf);
            $stmt->execute();

            // echo $stmt->rowCount(); 
            if ($stmt->rowCount() > 0) {
                $resultFinal = true;
            } else {
                $resultFinal = false;
            }
        } catch (Exception $e) {
            echo "Erro: " . $e->getMessage();
        }

        return $resultFinal;
    }
    //FUNÇÃO ALTERAR USUARIO
    public function alterarUsuario($connect, $query)
    {
        $id_cpf = $query['id_cpf'];

        $select = Dados::pesquisaUsuario($connect, $id_cpf);

        if ($select == false) {
            $resultFinal = false;
        } else {
            $id_cpf = $query['id_cpf'];
            $usuario = $query['usuario'];
            $hashSenha = $query['hashSenha'];
            $id_tipo_usuario = $query['id_tipo_usuario'];
            $id_sede = $query['id_sede'];
            $tele = empty($query['telefone']) ? null : $query['telefone'];
            $email = empty($query['email']) ? null : $query['email'];

            $sql = "update tb_usuario set telefone = ?, usuario = ?, hashSenha = ?, id_tipo_usuario = ?, id_sede = ? , email = ? where id_cpf = ?";

            $statement = $connect->prepare($sql);

            $statement->bindParam(1, $tele);
            $statement->bindParam(2, $usuario);
            $statement->bindParam(3, $hashSenha);
            $statement->bindParam(4, $id_tipo_usuario);
            $statement->bindParam(5, $id_sede);
            $statement->bindParam(6, $email);
            $statement->bindParam(7, $id_cpf);

            $statement->execute();


            // var_dump($statement);

            $resultFinal = true;
        }
        return $resultFinal;
    }
    //FUNÇÂO PESQUISAR BLACK LIST
    public function pesquisaBlackList($connect, $query)
    {
        try {
            $sql = "SELECT B.*, V.nome  FROM tb_black_list AS B INNER JOIN tb_visitante as V ON B.id_cpf_visitante = V.id_cpf;";
            // $sql = "SELECT * FROM tb_black_list WHERE id_cpf_visitante LIKE '%" . $query . "%';";
            $obj = $connect->prepare($sql);
            $result = ($obj->execute()) ? $obj->fetchAll() : false;
        } catch (PDOException $e) {
            echo 'Erro: ',  $e->getMessage(), "\n";
        }
        return $result;
    }
    //FUNÇÃO ADICIONAR BLACK LIST
    public function adicionarBlackList($connect, $id_cpf, $data_entrada)
    {
        $id_black_list =  date("BHis");
        // echo $data_entrada;
        $data_saida = NULL;
        // echo $id_black_list;

        try {
            $sql = "INSERT INTO tb_black_list (id_black_list,id_cpf_visitante,data_entrada,data_saida) 
                    values (:id_black_list, :id_cpf_visitante, :data_entrada, :data_saida);";
            $stmt = $connect->prepare($sql);
            $stmt->bindParam(':id_black_list', $id_black_list);
            $stmt->bindParam(':id_cpf_visitante', $id_cpf);
            $stmt->bindParam(':data_entrada', $data_entrada);
            $stmt->bindParam(':data_saida', $data_saida);
            $stmt->execute();

            $sqlc = "UPDATE tb_visitante set id_black_list = 1 where id_cpf = '$id_cpf';";
            $stmtc = $connect->prepare($sqlc);
            // $stmtc->bindParam(':id_cpf', $id_cpf);
            $stmtc->execute();

            // echo $stmt->rowCount(); 
            // echo $stmtc->rowCount();

            if (($stmt->rowCount() > 0) && ($stmtc->rowCount() > 0)) {
                $resultFinal = true;
            } else {
                $resultFinal = false;
            }
        } catch (PDOException $e) {
            echo "Erro: " . $e->getMessage();
            exit;
        }

        return $resultFinal;
    }
    //FUNÇÃO RETIRAR BLACK LIST
    public function retirarBlackList($connect, $id_cpf, $data_saida)
    {
        // {   $id_black_list =  date("BHis");
        // echo $data_entrada;
        // $data_saida = NULL;
        // echo $id_black_list;

        try {
            $sql = "UPDATE tb_black_list SET data_saida = '$data_saida' WHERE id_cpf_visitante = '$id_cpf' AND data_saida is null;";
            $stmt = $connect->prepare($sql);
            $stmt->execute();

            $sqlc = "UPDATE tb_visitante set id_black_list = 0 where id_cpf = '$id_cpf';";
            $stmtc = $connect->prepare($sqlc);
            $stmtc->execute();

            // echo $stmt->rowCount(); 
            if ($stmt->rowCount() > 0) {
                $resultFinal = true;
            } else {
                $resultFinal = false;
            }
        } catch (PDOException $e) {
            echo "Erro: " . $e->getMessage();
            exit;
        }

        return $resultFinal;
    }
    public function data($data)
    {
        if ($data == null || $data == 'Ativo') {
            return null;
        } else {
            return date("d/m/Y", strtotime($data));
        }
    }
    /*
global $_DELETE = array();
global $_PUT = array();

if (!strcasecmp($_SERVER['REQUEST_METHOD'], 'DELETE')) {
    parse_str(file_get_contents('php://input'), $_DELETE);
}
if (!strcasecmp($_SERVER['REQUEST_METHOD'], 'PUT')) {
    parse_str(file_get_contents('php://input'), $_PUT);
}
    /*public function inserirVisitante($connect,$connectC,){

    
}
/*
public function registrarUsuarioFace($conection, $nome, $sobrenome, $email, $idFacebook){

    $id=$conection->prepare("select max(id_usu) from tb_usuario;");

    $id->execute();

    $id_max=$id->fetch(PDO::FETCH_OBJ);

    foreach ($id_max as $id );
    $id++;
    $sql="insert into tb_usuario (id_usu, nome_usu, sobrenome_usu, email_usu, id_facebook_usu, sis_id_insert_usu)
          values(". $id .", '". $nome ."' , '". $sobrenome ."' , '". $email ."' , '". $idFacebook ."', ". $id ." )";

    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    return ($obj) ? $obj : false;
}



/*public function pesquisaUsuario($conection, $email){
    $obj = $conection->prepare("SELECT 
                            id_usu,
                            nome_usu,
                            sobrenome_usu,
                            email_usu,
                            id_facebook_usu
                            
                        FROM 
                            tb_usuario
                        WHERE 
                        
                            email_usu = '".$email."'
                                                ");

    return ($obj->execute()) ? $obj->fetch(PDO::FETCH_OBJ) : false;
}

public function confirmarCadastro($conection, $conf){


    $sql="update tb_usuario set conf_cadastro_usu=".$conf." ;";

    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    return ($obj) ? $obj : false;


}

public function getAutentica($conection, $email, $senha){
    $senha1=md5($senha);

    $obj = $conection->prepare("SELECT 
                            id_usu,
                            nome_usu,
                            sobrenome_usu,
                            email_usu,
                            id_facebook_usu
                            
                        FROM 
                            tb_usuario
                        WHERE 
                        
                            email_usu = '".$email."' and pass_usu = '".$senha1."'
                                                ");

    return ($obj->execute()) ? $obj->fetch(PDO::FETCH_OBJ) : false;
}

public function insereImagens($conection, $idProd, $idusu, $principImg, $frenteImg, $costaImg, $etiqImg, $det1Img, $det2Img){


    $sql="insert into tb_prod_img (id_prod_img, id_usu_img, principal_img, frente_img, costas_img, etiqueta_img, det1_img, det2_img) values(".$idProd.", ".$idusu.", '".$principImg."', '".$frenteImg."', '".$costaImg."', '".$etiqImg."','".$det1Img."', '".$det2Img."'  );";
    //echo $sql;
    //exit;
    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    return ($obj) ? $obj : false;
}

public function registrarDadosPessoaisUsu($conection, $idusu, $cpfCnpj, $cep, $end, $comp, $bairro, $cidade, $uf, $telefone, $idIn){

    $sql="insert into tb_usu_dados_pessoais (id_usu_pess, cpf_cnpj_usu_pess, cep_usu_pess, end_usu_pess, comp_usu_pess, bairro_usu_pess,
                                              cidade_usu_pess, uf_usu_pess, telefone_usu_pess, sis_id_insert_pess) 
                                              values (".$idusu." , '".$cpfCnpj."' , '".$cep."' , '".$end."' , '".$comp."' , '".$bairro."' ,  '".$cidade."' ,
                                              '".$uf."' , '".$telefone."' , '".$idIn."');";


    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    return ($obj) ? $obj : false;
}

public function registrarLoja($conection,  $idUsu, $nomeLoja,  $idIn, $imgLoja){
    $idImg=0;
    $id=$conection->prepare("select max(id_loja) from tb_loja;");

    $id->execute();

    $id_max=$id->fetch(PDO::FETCH_OBJ);

    foreach ($id_max as $idLoja );
    $idLoja++;

    $sql="insert into tb_loja (id_loja, id_usu, nome_loja , sis_id_insert_loja) 
            values (".$idLoja." , ".$idUsu." , '".$nomeLoja."', ".$idIn.");";

    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    if($obj){
        $id=$conection->prepare("select max(id_img) from tb_img_loja;");

        $id->execute();

        $id_max=$id->fetch(PDO::FETCH_OBJ);

        foreach ($id_max as $idImg );
        $idImg++;

        $sql="insert into tb_img_loja (id_loja, id_img, img_loja, sis_id_insert_img ) 
            values (".$idLoja." ,".$idImg." ,'".$imgLoja."' , ".$idUsu." );";

        $insert=$conection->prepare($sql);
        $obj = $insert->execute();
        if($obj){
            return $idImg;
        }
    }
    else{
        return false;
    }

}

public function insereProduto($conection, $idLoja,  $descprod, $modeloprod, $tamanhoprod, $corprod, $precoprod, $pesoprod, $freteprod, $situacao,$quantidade , $idIn, $ativo, $obsevacao, $faixaEt){
    $id=$conection->prepare("select max(id_prod) from tb_produtos;");
    $id->execute();
    $id_max=$id->fetch(PDO::FETCH_OBJ);
    foreach ($id_max as $idProd );
    $idProd++;


    $sql="insert into tb_produtos ( id_prod, id_loja , desc_prod , modelo_prod , tamanho_prod , cor_prod , preco_prod , 
       peso_prod , frete_prod, situacao_prod , quantidade_prod ,sis_id_insert_prod, ativo_prod, observacao_prod, faixa_etaria) 
            values (".$idProd." , ".$idLoja." ,'".$descprod."' , '".$modeloprod."', '".$tamanhoprod."' , '".$corprod."', 
           '".$precoprod."' , '".$pesoprod."' , '".$freteprod."' , '".$situacao."' ,".$quantidade.", ".$idIn.", ".$ativo.", '".$obsevacao."', '".$faixaEt."');";
    //echo $sql;
    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    return ($obj) ? $obj : false;
}

public function getLoja($conection, $idUsu){

    $obj = $conection->prepare("SELECT * FROM tb_loja  WHERE  id_usu= ".$idUsu." ; ");

    return ($obj->execute()) ? $obj->fetch(PDO::FETCH_OBJ) : false;
}

public function getImagens($conection, $idProd){

    $obj = $conection->prepare("SELECT * FROM tb_prod_img WHERE id_prod_img= ".$idProd." ; ");

    return ($obj->execute()) ? $obj->fetch(PDO::FETCH_OBJ) : false;
}

public function updateImagens($conection, $idProd,  $principImg, $frenteImg, $costaImg, $etiqImg, $idIn){


    $sql="update tb_prod_img  set  principal_img = '".$principImg."' , frente_img = '".$frenteImg."', costas_img = '".$costaImg."',
            etiqueta_img = '".$etiqImg."', sis_id_update_img = ".$idIn."  where id_prod_img = ".$idProd." ;";

    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    return ($obj) ? $obj : false;
}

public function updateProdutos($conection, $idProd,  $descProduto, $modeloProduto, $tamanhoProduto, $corProduto, $preco_produto,
                               $peso_prod, $freteProduto, $idIn){
    $date = date('Y-m-d H:i');

    $sql="update tb_produtos  set  desc_prod = '".$descProduto."' , modelo_prod = '".$modeloProduto."', tamanho_prod = '"
        .$tamanhoProduto."',  cor_prod  = '".$corProduto."', preco_prod  = '".$preco_produto."', peso_prod = '".$peso_prod."' , frete_prod = '".$freteProduto."', sis_id_update_prod = '".$idIn."', sis_dt_update_prod = '".$date."' where id_prod = ".$idProd." ;";

    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    return ($obj) ? $obj : false;
}

public function getProduto($conection, $idProd){

    $obj = $conection->prepare("SELECT * FROM tb_produtos  WHERE  id_prod= ".$idProd." ; ");

    return ($obj->execute()) ? $obj->fetch(PDO::FETCH_OBJ) : false;
}
public function getVestidos($conection){

    $obj = $conection->prepare("select * from tb_produtos left join tb_prod_img on tb_produtos.id_prod=tb_prod_img.id_prod_img where ativo_prod=1  order by sis_dt_insert_prod desc; ");

    return ($obj->execute()) ? $obj->fetchAll() : false;
}
public function getProdutoDesc($conection, $desc){

    $obj = $conection->prepare("SELECT * FROM tb_produtos  WHERE desc_prod = '".$desc."' and  ativo_prod=1  ; ");

    return ($obj->execute()) ? $obj->fetch(PDO::FETCH_OBJ) : false;
}

public function getProdutoEImg($conection, $idProd){

    $obj = $conection->prepare("select * from tb_produtos inner join  tb_prod_img on tb_prod_img.id_prod_img=tb_produtos.id_prod where id_prod= ".$idProd." and ativo_prod=1 ;");

    return ($obj->execute()) ? $obj->fetchAll() : false;
}

public function bolsaTemp($conection,  $idUsu, $idProd, $bolsaIn, $valor){
    $id=$conection->prepare("select max(id_compra_temp) from tb_compra_temp;");

    $id->execute();

    $id_max=$id->fetch(PDO::FETCH_OBJ);

    foreach ($id_max as $idCompra );
    $idCompra++;

    $sql="insert into tb_compra_temp (id_compra_temp, id_usu_comprador_ct, id_produto_ct, bolsa_ct, valor_cp) 
            values (".$idCompra." , ".$idUsu." , ".$idProd.", ".$bolsaIn.", '".$valor."' );";

    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    return ($obj) ? $obj : false;
}

public function selectProdutoBolsa($conection, $idUsu){

    $obj = $conection->prepare("select * from tb_compra_temp inner join (tb_produtos inner join
                              tb_prod_img on tb_produtos.id_prod=tb_prod_img.id_prod_img)
    on tb_produtos.id_prod=tb_compra_temp.id_produto_ct where id_usu_comprador_ct='".$idUsu."' and ativo_prod=1;  ;");

    return ($obj->execute()) ? $obj->fetchAll() : false;
}
public function selectProdutoBolsaIndex($conection, $idUsu){

    $obj = $conection->prepare("select * from tb_compra_index_temp inner join (tb_produtos inner join
                              tb_prod_img on tb_produtos.id_prod=tb_prod_img.id_prod_img)
    on tb_produtos.id_prod=tb_compra_index_temp.id_produto_temp where id_usu_temp='".$idUsu."' and ativo_prod=1;  ;");

    return ($obj->execute()) ? $obj->fetchAll() : false;
}
public function acessoIndex($conection,  $ip){
    $id=$conection->prepare("select max(id_acess) from tb_acesso_index;");

    $id->execute();

    $id_max=$id->fetch(PDO::FETCH_OBJ);

    foreach ($id_max as $id );
    $id++;

    $sql="insert into tb_acesso_index (id_acess, sys_ip_acces) 
            values (".$id." ,  '".$ip."' );";

    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    return ($obj) ? $obj : false;
}


public function esvaziarBolsa($conection, $idUsu){

    $obj = $conection->prepare("delete from tb_compra_temp where id_usu_comprador_ct = '".$idUsu."' ");
    $delete= $obj->execute();

    return  ($delete) ? $delete : false;
}

public function getTodosDadosUsu($conection, $idUsu){
    $obj = $conection->prepare("select * from tb_usuario right join tb_usu_dados_pessoais 
    on tb_usuario.id_usu=tb_usu_dados_pessoais.id_usu_pess where id_usu='".$idUsu."' ;  ");

    return ($obj->execute()) ? $obj->fetchAll() : false;
}
/************************** ****************************************/
    /******************* Verifica os dados pessoais ********************/
    /************************** ****************************************/
    /*public function getDadosUsu($conection, $idUsu){
    $obj = $conection->prepare("select * from  tb_usu_dados_pessoais where id_usu_pess='".$idUsu."' ;  ");

    return ($obj->execute()) ? $obj->fetchAll() : false;
}
public function getDadosUsuPrimy($conection, $idUsu){
    $obj = $conection->prepare("select * from  tb_usuario where id_usu='".$idUsu."' ;  ");

    return ($obj->execute()) ? $obj->fetchAll() : false;
}
public function insereFavorito($conection, $idUsu,  $idProd){


    $sql="insert into tb_favoritos ( id_usu_fav, id_prod_fav ) 
            values (".$idUsu." , ".$idProd." );";

    $insert=$conection->prepare($sql);
    $obj = $insert->execute();
    return ($obj) ? $obj : false;
}

public function selectFavoritos($conection, $idUsu){
    $obj = $conection->prepare("SELECT * FROM tb_favoritos inner join (tb_produtos inner join tb_prod_img on tb_produtos.id_prod = tb_prod_img.id_prod_img ) on tb_favoritos.id_prod_fav = tb_produtos.id_prod where id_usu_fav = ".$idUsu." and ativo_prod=1;   ");

    return ($obj->execute()) ? $obj->fetchAll() : false;
}

public function excluirFavorito($conection, $idUsu, $idProd){

    $obj = $conection->prepare("delete from  tb_favoritos where id_usu_fav = ".$idUsu." and
    id_prod_fav = ".$idProd." ;");
    $delete= $obj->execute();

    return  ($delete) ? $delete : false;
}*/
}
