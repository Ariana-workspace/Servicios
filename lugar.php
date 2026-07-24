<?php
// ── CABECERAS CORS ───────────────────────────────────────────
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "Connection_postres.php";
$method = $_SERVER['REQUEST_METHOD'];
$objDb = new Connection();
$cn = $objDb->connect();

// ── GET ───────────────────────────────────────────────────────
// Devuelve todos los lugars o uno por ID
if ($method === 'GET') {

    $id_lugar = $_GET['id_lugar'] ?? null;

    if ($id_lugar) {

        $sql = "SELECT l.*, 
                       p.nom_postre, p.receta, p.precio, 
                       p.calorias, p.stock, p.disponible,
                       p.imagen AS imagen_postre
                FROM lugar l
                JOIN postres p ON l.id_postre = p.id_postre
                WHERE l.id_lugar = :id_lugar";

        $rs = $cn->prepare($sql);
        $rs->bindParam(':id_lugar', $id_lugar, PDO::PARAM_INT);
        $rs->execute();


    } else if (isset($_GET['id_postre'])) {

        $sql = "SELECT * 
                FROM lugar 
                WHERE id_postre = :id_postre";

        $rs = $cn->prepare($sql);
        $rs->execute([
            ':id_postre' => $_GET['id_postre']
        ]);


    } else {

        $sql = "SELECT * FROM lugar";

        $rs = $cn->prepare($sql);
        $rs->execute();
    }


    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
}

// ── POST ──────────────────────────────────────────────────────
// Crea un nuevo lugar
// Body JSON esperado: { "nombre": "...", "descripcion": "...", "precio": 10.5, "imagen": "..." }
// ── POST ──────────────────────────────────────────────────────
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $sql = "INSERT INTO lugar (titulo, subtitulo,latitud,longitud, radio, imagen)
            VALUES (:titulo, :subtitulo, :latitud, :longitud, :radio, :imagen)";
    $rs = $cn->prepare($sql);
    $rs->execute([
        ':titulo' => $data['titulo'],
        ':subtitulo' => $data['subtitulo'],
        ':imagen'      => $data['imagen'],
        ':longitud' => $data['longitud'] ?? 0,
        ':latitud' => $data['latitud'] ?? 0,
        ':radio' => $data['radio'] ?? 0,
    ]);

    http_response_code(201);
    echo json_encode([
        "message"   => "lugar creado",
        "id_lugar" => $cn->lastInsertId()
    ]);
}

// ── PUT ───────────────────────────────────────────────────────
// Actualiza un lugar existente
// URL esperada: ?id_lugar=3
// Body JSON esperado: { "nombre": "...", "descripcion": "...", "precio": 10.5, "imagen": "..." }
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($_GET['id_lugar'])) {
        http_response_code(400);
        echo json_encode(["error" => "Falta el id_lugar en la URL"]);
        exit();
    }

    $sql = "UPDATE lugar
            SET titulo = :titulo,
               subtitulo = :subtitulo,
                longitud = :longitud,
                latitud = latitud,
                radio = :radio,
                imagen = :imagen
            WHERE id_lugar = :id_lugar";
    $rs = $cn->prepare($sql);
    $rs->execute([
        ':titulo' => $data['titulo'],
        ':subtitulo' => $data['subtitulo'],
        ':imagen'      => $data['imagen'],
        ':longitud' => $data['longitud'] ?? 0,
        ':latitud' => $data['latitud'] ?? 0,
        ':radio' => $data['radio'] ?? 0,
    ]);

    echo json_encode(["message" => "lugar actualizado"]);
}

// ── DELETE ────────────────────────────────────────────────────
// Elimina un lugar por ID
// URL esperada: ?id_lugar=3
elseif ($method === 'DELETE') {
    if (!isset($_GET['id_lugar'])) {
        http_response_code(400);
        echo json_encode(["error" => "Falta el id_lugar en la URL"]);
        exit();
    }

    $sql = "DELETE FROM lugar WHERE id_lugar = :id_lugar";
    $rs = $cn->prepare($sql);
    $rs->execute([':id_lugar' => $_GET['id_lugar']]);

    echo json_encode(["message" => "lugar eliminado"]);
}

// ── MÉTODO NO SOPORTADO ───────────────────────────────────────
else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
?>