<?php
include 'config.php';

try{
    $opcoes = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
    );
    $connect= new PDO("mysql:host=".$host.";dbname=".$dbname, $user,$pass);
    $connectC=new PDO("mysql:host=".$host_c.";dbname=".$dbname_c.";charset=utf8", $user_c,$pass_c,$opcoes);

}
catch(PDOException $e){
    echo $e->getMessage();
}