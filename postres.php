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
// Devuelve todos los postres o uno por ID
if ($method === 'GET') {
    if (isset($_GET['id_postre'])) {
        $sql = "SELECT * FROM postres WHERE id_postre = :id_postre";
        $rs = $cn->prepare($sql);
        $rs->execute([':id_postre' => $_GET['id_postre']]);
    } else {
        $sql = "SELECT * FROM postres";
        $rs = $cn->prepare($sql);
        $rs->execute();
    }
    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
}

// ── POST ──────────────────────────────────────────────────────
// Crea un nuevo postre
// Body JSON esperado: { "nombre": "...", "descripcion": "...", "precio": 10.5, "imagen": "..." }
// ── POST ──────────────────────────────────────────────────────
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $sql = "INSERT INTO postres (nom_postre, receta,id_tipo,descripcion, precio, imagen, stock, disponible, calorias)
            VALUES (:nom_postre, :receta, :id_tipo, :descripcion, :precio, :imagen, :stock, :disponible, :calorias)";
    $rs = $cn->prepare($sql);
    $rs->execute([
        ':nom_postre'        => $data['nom_postre'],
        ':receta'          => $data['receta'],
        ':id_tipo'         => $data['id_tipo'],
        ':descripcion'   => $data['descripcion'],
        ':precio'        => $data['precio'],
        ':imagen'        => $data['imagen'],
        ':stock'         => $data['stock'] ?? 0,        // si no mandan stock, default 0
        ':disponible'=> $data['disponible'] ?? 1, // si no mandan disponibilidad, default 1
        ':calorias'  => $data['calorias'] ?? 0 
       // ':longitud' => $data['longitud'] ?? 0,
        //':latitud' => $data['latitud'] ?? 0
    ]);

    http_response_code(201);
    echo json_encode([
        "message"   => "Postre creado",
        "id_postre" => $cn->lastInsertId()
    ]);
}

// ── PUT ───────────────────────────────────────────────────────
// Actualiza un postre existente
// URL esperada: ?id_postre=3
// Body JSON esperado: { "nombre": "...", "descripcion": "...", "precio": 10.5, "imagen": "..." }
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($_GET['id_postre'])) {
        http_response_code(400);
        echo json_encode(["error" => "Falta el id_postre en la URL"]);
        exit();
    }

    $sql = "UPDATE postres
            SET nom_postre = :nom_postre,
                receta = :receta,
                id_tipo = :id_tipo,
                descripcion = :descripcion,
                precio = :precio,
                imagen = :imagen,
                stock = :stock,
                disponible = :disponible,
                calorias = :calorias,
                longitud = :longitud,
                latitud = latitud
            WHERE id_postre = :id_postre";
    $rs = $cn->prepare($sql);
    $rs->execute([
        ':nom_postre' => $data['nom_postre'],
        ':receta' => $data['receta'],
        ':id_tipo' => $data['id_tipo'],
        ':descripcion' => $data['descripcion'],
        ':precio'      => $data['precio'],
        ':imagen'      => $data['imagen'],
        ':id_postre'   => $_GET['id_postre'],
        ':stock'       => $data['stock'] ?? 0,
        ':disponible' => $data['disponible'] ?? 1,
        ':calorias'   => $data['calorias'] ?? 0,
        ':longitud' => $data['longitud'] ?? 0,
        ':latitud' => $data['latitud'] ?? 0
    ]);

    echo json_encode(["message" => "Postre actualizado"]);
}

// ── DELETE ────────────────────────────────────────────────────
// Elimina un postre por ID
// URL esperada: ?id_postre=3
elseif ($method === 'DELETE') {
    if (!isset($_GET['id_postre'])) {
        http_response_code(400);
        echo json_encode(["error" => "Falta el id_postre en la URL"]);
        exit();
    }

    $sql = "DELETE FROM postres WHERE id_postre = :id_postre";
    $rs = $cn->prepare($sql);
    $rs->execute([':id_postre' => $_GET['id_postre']]);

    echo json_encode(["message" => "Postre eliminado"]);
}

// ── MÉTODO NO SOPORTADO ───────────────────────────────────────
else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
?>