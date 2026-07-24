<?php
    include "Connection_games.php";

    if (isset($_GET['id'])) {
        // Si viene un ID, devuelve solo ese postre
        $sql = "SELECT * FROM resenas_usuarios WHERE id = :id";
        $rs = $cn->prepare($sql);
        $rs->execute([':id' => $_GET['id']]);
    } else {
        // Si no viene ID, devuelve todos
        $sql = "SELECT * FROM resenas_usuarios";
        $rs = $cn->prepare($sql);
        $rs->execute();
    }

    $rows = $rs -> fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
?>
