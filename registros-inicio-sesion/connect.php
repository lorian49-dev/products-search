<?php

    //Conexion a la base de datos
    $host = "localhost";
    $username = "root";
    $password = "";
    $db = "modelo_sgbd";

    //comprobar la conexion a la base de datos
    $connect = mysqli_connect($host, $username, $password, $db);

    //Validacion simple
    /*if($connect){
        echo "La conexion ha sido exitosa";
    }else{
        echo "Error al conectar la base de datos";
    }*/


?>