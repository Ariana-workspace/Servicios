<?php
// ============================================================
// API REST para la tabla "grupos" en MySQL/MariaDB
// Soporta los 4 métodos HTTP: GET, POST, PUT, DELETE
// ============================================================

// ── CABECERAS CORS ───────────────────────────────────────────
// CORS = Cross-Origin Resource Sharing
// Le dice al navegador "sí, acepto peticiones desde otros dominios"
// Sin esto, tu frontend en React/Angular/etc. sería bloqueado
header("Access-Control-Allow-Origin: *"); //Permite peticiones desde CUALQUIER origen
header("Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS"); //Métodos permitidos
header("Access-Control-Allow-Headers:Content-Type"); //Solo permite este header
header("Content-Type: application/json");

// Cuando el navegador hace una petición "extraña" (ej: PUT,DELETE),
// primero manda un OPTIONS para preguntar si está permitido.
// Aquí respondemos 200 OK y cortamos - sin procesar nada más.
if($_SERVER['REQUEST_METHOD'] === "OPTIONS"){
    http_response_code(200);
    exit();
}

    include "Connection_chat.php";
    $objDb = new Connection_chat();
    $cn = $objDb->connect();

    $method = $_SERVER['REQUEST_METHOD'];
 if($method === "GET"){
        if(isset($_GET['id_grupo'])){
            $sql = "SELECT * FROM tb_grupo WHERE id_grupo = :id_grupo";
            $rs = $cn->prepare($sql);
            $rs->execute([':id_grupo' => $_GET['id_grupo']]);
        }else if(isset($_GET['nombre'])){
            $sql = "SELECT * FROM tb_grupo WHERE nombre = :nombre";
            $rs = $cn->prepare($sql);
            $rs->execute([':nombre' => $_GET['nombre']]);
        } else {
            $sql = "SELECT * FROM tb_grupo";
            $rs = $cn->prepare($sql);
            $rs->execute();
        }
        $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);

    } else if ($method === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "INSERT INTO tb_grupo (nombre,descripcion) VALUES (:nombre,:descripcion)";
        $rs = $cn->prepare($sql);
        $rs->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion']
        ]);

        if ($rs->rowCount() > 0) {
        echo json_encode(['mensaje' => 'Agregado correctamente']);
        } else {
        echo json_encode(['error' => $rs->errorInfo()]);
        }

    }else if($method == "PUT"){
        $datos = json_decode(file_get_contents("php://input"), true);
        $id = $_GET["id_grupo"];

        $sql = "UPDATE tb_grupo SET nombre=:nombre, descripcion=:descripcion, imagen =:imagen WHERE id_grupo=:id_grupo";
        $rs = $cn->prepare($sql);
        $rs->execute([
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':imagen' => $datos['imagen'],
            ':id_grupo' => $id // <- El WHERE necesita el ID también

        ]);
        echo json_encode(['mensaje' => 'Actualizado correctamente']);
    
    }else if ($method === "DELETE"){
        $id = $_GET["id_grupo"];
        $sql = "DELETE FROM tb_grupo WHERE id_grupo = :id_grupo";
        $rs = $cn->prepare($sql);
        $rs->execute([':id_grupo' => $id]);
        echo json_encode(['mensaje' => 'Eliminado correctamente']);
    }
?>