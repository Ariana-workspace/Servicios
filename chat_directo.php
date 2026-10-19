<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// Conexión a la base de datos
include "Connection_chat.php";
$objDb = new Connection_chat();
$cn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
    if (isset($_GET['id_chat_directo'])) {
        $sql = "SELECT * FROM tb_chat_directo WHERE id_chat_directo = :id_chat_directo";
        $rs = $cn->prepare($sql);
        $rs->execute([':id_chat_directo' => $_GET['id_chat_directo']]);
    }else if (isset($_GET['id_usuario']) && isset($_GET['id_amigo'])) {
    $sql = "SELECT * FROM tb_chat_directo
            WHERE (id_usuario = :u1 AND id_amigo = :a1)
               OR (id_usuario = :a2 AND id_amigo = :u2)";
    $rs = $cn->prepare($sql);
    $rs->execute([
        ':u1' => $_GET['id_usuario'], ':a1' => $_GET['id_amigo'],
        ':a2' => $_GET['id_amigo'],   ':u2' => $_GET['id_usuario']
    ]);
}else {
        $sql = "SELECT * FROM tb_chat_directo";
        $rs = $cn->prepare($sql);
        $rs->execute();
    }
    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);

// ── POST: INSERTAR una nueva solicitud de amistad ──
} else if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    // OJO: sin comas colgando antes del paréntesis de cierre
    
    $sql = "INSERT INTO tb_chat_directo (id_usuario, id_amigo, fecha_creacion)
            VALUES (:id_usuario, :id_amigo, :fecha_creacion)";
    $rs = $cn->prepare($sql);
    $rs->execute([
        ':id_usuario'      => $data['id_usuario'],
        ':id_amigo'        => $data['id_amigo'],
        ':fecha_creacion'  => date('Y-m-d H:i:s')  
    ]);

    if ($rs->rowCount() > 0) {
        echo json_encode(['mensaje' => 'Agregado correctamente', 'id' => $cn->lastInsertId()]);
    } else {
        echo json_encode(['error' => $rs->errorInfo()]);
    }

// ── PUT: ACTUALIZAR una amistad existente ──
// URL: /api?id=X  (el id PROPIO de la fila en tb_chat_directo, no el id_usuario)
} else if ($method === "PUT") {
    $datos = json_decode(file_get_contents("php://input"), true);
    if (!isset($_GET['id_chat_directo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro id']);
    exit();
    }
    $id_chat_directo = $_GET["id_chat_directo"];
    $sql = "UPDATE tb_chat_directo
            SET fecha_creacion = :fecha_creacion
            WHERE id_chat_directo = :id_where";
    $rs = $cn->prepare($sql);
    $rs->execute([
        ':fecha_creacion' => $datos['fecha_creacion'],
        ':id_where'        => $id_chat_directo,
    ]);
    if ($rs->rowCount() > 0) {
    echo json_encode(['mensaje' => 'Actualizado correctamente']);
    } else {
    echo json_encode(['error' => 'No se actualizó ninguna fila']);
    }
// ── DELETE: ELIMINAR una amistad ──
// URL: /api?id=X  (id propio de la fila, no id_usuario)
} else if ($method === "DELETE") {
    $id_chat_directo = $_GET["id_chat_directo"];
    $sql = "DELETE FROM tb_chat_directo WHERE id_chat_directo = :id_chat_directo";
    $rs = $cn->prepare($sql);
    $rs->execute([':id_chat_directo' => $id_chat_directo]);
    echo json_encode(['mensaje' => 'Eliminado correctamente']);
}