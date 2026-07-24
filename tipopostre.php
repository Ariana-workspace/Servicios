<?php
    //Conexión a la base de datos
    include "Connection_postres.php";
    $objDb = new Connection();
    $cn = $objDb->connect();
    //Consulta SQL para obtener las categorías
    if (isset($_GET['id_tipo'])) {
        // Si viene un ID, devuelve solo ese postre
        $sql = "SELECT * FROM tipo_postre WHERE id_tipo = :id_tipo";
        $rs = $cn->prepare($sql);
        $rs->execute([':id_tipo' => $_GET['id_tipo']]);
    } else {
        // Si no viene ID, devuelve todos
        $sql = "SELECT * FROM tipo_postre";
        $rs = $cn->prepare($sql);
        $rs->execute();
    }

    //Obtener los resultados como un array asociativo
    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
    //Devolver los resultados en formato JSON
    echo json_encode($rows);
?>
