<?php
    //Conexión a la base de datos
    include "Connection_postres.php";
    $objDb = new Connection();
    $cn = $objDb->connect();
    //Consulta SQL para obtener las roles
    if (isset($_GET['id_rol'])) {
        // Si viene un ID, devuelve solo ese rol
        $sql = "SELECT * FROM tipo_rol WHERE id_rol = :id_rol";
        $rs = $cn->prepare($sql);
        $rs->execute([':id_rol' => $_GET['id_rol']]);
    } else {
        // Si no viene ID, devuelve todos
        $sql = "SELECT * FROM tipo_rol";
        $rs = $cn->prepare($sql);
        $rs->execute();
    }

    //Obtener los resultados como un array asociativo
    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
    //Devolver los resultados en formato JSON
    echo json_encode($rows);
?>
