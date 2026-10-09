<?php
// ============================================================
// API REST para la tabla "tb_amistad" en MySQL/MariaDB
// Soporta los 4 métodos HTTP: GET, POST, PUT, DELETE
// ============================================================

// ── CABECERAS CORS ───────────────────────────────────────────

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

// ── GET: OBTENER amistades ──
// GET /api?id_usuario=5   -> solicitudes/amistades enviadas por ese usuario
// GET /api?id_amigo=5     -> solicitudes/amistades recibidas por ese usuario
// GET /api                -> trae todas

if ($method === "GET") {

    if (isset($_GET['id_usuario']) && isset($_GET['id_amigo'])) {

        $sql = "SELECT * FROM tb_amistad
                WHERE (id_usuario = :id_usuario AND id_amigo = :id_amigo)
                   OR (id_usuario = :id_amigo AND id_amigo = :id_usuario)";

        $rs = $cn->prepare($sql);
        $rs->execute([
            ':id_usuario' => $_GET['id_usuario'],
            ':id_amigo' => $_GET['id_amigo']
        ]);

    } else if (isset($_GET['id_usuario'])) {

        $sql = "SELECT * FROM tb_amistad WHERE id_usuario = :id_usuario";

        $rs = $cn->prepare($sql);
        $rs->execute([
            ':id_usuario' => $_GET['id_usuario']
        ]);

    } else if (isset($_GET['id_amigo'])) {

        $sql = "SELECT * FROM tb_amistad WHERE id_amigo = :id_amigo";

        $rs = $cn->prepare($sql);
        $rs->execute([
            ':id_amigo' => $_GET['id_amigo']
        ]);

    } else {

        $sql = "SELECT * FROM tb_amistad";

        $rs = $cn->prepare($sql);
        $rs->execute();
    }

    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
}else if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    // OJO: sin comas colgando antes del paréntesis de cierre
    
    $sql = "INSERT INTO tb_amistad (id_usuario, id_amigo, estado, fecha_solicitud, fecha_respuesta)
            VALUES (:id_usuario, :id_amigo, :estado, :fecha_solicitud, :fecha_respuesta)";
    $rs = $cn->prepare($sql);
    $rs->execute([
        ':id_usuario'      => $data['id_usuario'],
        ':id_amigo'        => $data['id_amigo'],
        ':estado'          => $data['estado'] ?? 'PENDIENTE',
        ':fecha_solicitud' => $data['fecha_solicitud'],
        ':fecha_respuesta' => $data['fecha_respuesta'] ?? null,
    ]);

    if ($rs->rowCount() > 0) {
        echo json_encode(['mensaje' => 'Agregado correctamente', 'id' => $cn->lastInsertId()]);
    } else {
        echo json_encode(['error' => $rs->errorInfo()]);
    }

// ── PUT: ACTUALIZAR una amistad existente ──
// URL: /api?id=X  (el id PROPIO de la fila en tb_amistad, no el id_usuario)
} else if ($method === "PUT") {
    $datos = json_decode(file_get_contents("php://input"), true);
    if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro id']);
    exit();
    }
    $id = $_GET["id"];

    // Placeholders distintos para SET y WHERE, y coincidiendo con lo que
    // realmente mandas en el body. Aquí lo normal es solo cambiar el estado
    // (aceptar/rechazar) y guardar la fecha de respuesta.
    $sql = "UPDATE tb_amistad
            SET estado = :estado, fecha_respuesta = :fecha_respuesta
            WHERE id = :id_where";
    $rs = $cn->prepare($sql);
    $rs->execute([
        ':estado'          => $datos['estado'],
        ':fecha_respuesta' => $datos['fecha_respuesta'],
        ':id_where'        => $id,
    ]);
    if ($rs->rowCount() > 0) {
    echo json_encode(['mensaje' => 'Actualizado correctamente']);
    } else {
    echo json_encode(['error' => 'No se actualizó ninguna fila']);
    }
// ── DELETE: ELIMINAR una amistad ──
// URL: /api?id=X  (id propio de la fila, no id_usuario)
} else if ($method === "DELETE") {
    $id = $_GET["id"];
    $sql = "DELETE FROM tb_amistad WHERE id = :id";
    $rs = $cn->prepare($sql);
    $rs->execute([':id' => $id]);
    echo json_encode(['mensaje' => 'Eliminado correctamente']);
}