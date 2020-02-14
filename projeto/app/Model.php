<?php
require('functions.php');
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
        $select = Dados::pesquisaVisitante($connect,$connectc, $id_cpf);
        if ($select == false) {
            $resultFinal = false;
        } else {
            try {
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
                $resultFinal = ($statement->rowCount() === 0 ) ? false : true;
                // var_dump($statement);
            } catch (PDOException $e) {
                echo $e;
                $resultFinal = false;

            }
           
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
            $resultFinal = false;

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
        $id_cpf = $query;
        try {
            $sql = "SELECT V.*, L.sigla_lotacao, L.local_lotacao, L.desc_lotacao , VI.nome, S.nome_serv
                FROM (tb_visita AS V
                INNER JOIN db_coorporativo.tb_lotacao AS L ON V.id_lotacao_visita = L.id_lotacao
                INNER JOIN tb_visitante AS VI ON V.id_cpf= VI.id_cpf
                INNER JOIN db_coorporativo.tb_servidores AS S ON V.id_cpf_visitado = S.id_cpf) WHERE V.id_cpf LIKE '%" . $query . "%' OR V.id_cpf_visitado LIKE '%" . $query . "%' ORDER BY data_entrada DESC , hora_entrada DESC;";

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
}
