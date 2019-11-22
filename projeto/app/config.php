<?php
//version php: 7.2.18

if(getenv("HOST_HOME")=='claressa'){
    $host = 'localhost';
    $dbname = 'db_coorporativo';
    $user = 'root';
    $pass = 'alanis00';

    $host_c = 'localhost';
    $dbname_c = 'db_visitantes';
    $user_c = 'root';
    $pass_c = 'alanis00';
}
else if(getenv("HOST_HOME")=='guilherme'){
    $host_c = 'localhost';
    $dbname_c = 'db_coorporativo';
    $user_c = 'root';
    $pass_c = '';

    $host = 'localhost';
    $dbname = 'db_visitantes';
    $user = 'root';
    $pass = '';
}

else{
    
}
//echo getenv("HOST_HOME")."<BR>";