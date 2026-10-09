<?php
// ============================================================
// API REST para la tabla "grupos" en MySQL/MariaDB
// Soporta los 4 métodos HTTP: GET, POST, PUT, DELETE
// ============================================================

// ── CABECERAS CORS ───────────────────────────────────────────
// CORS = Cross-Origin Resource Sharing
// Le dice al navegador "sí, acepto peticiones desde otros dominios"
// Sin esto, tu frontend en React/Angular/etc. sería bloqueado
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); //Permite peticiones desde CUALQUIER origen
header("Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS"); //Métodos permitidos
header("Access-Control-Allow-Headers:Content-Type"); //Solo permite este header

// Cuando el navegador hace una petición "extraña" (ej: PUT,DELETE),
// primero manda un OPTIONS para preguntar si está permitido.
// Aquí respondemos 200 OK y cortamos - sin procesar nada más.
if($_SERVER['REQUEST_METHOD'] === "OPTIONS"){
    http_response_code(200);
    exit();
}


//Conexión a la base de datos
    include "Connection_chat.php";
    $objDb = new Connection_chat();
    $cn = $objDb->connect();

    //Guardamos el método HTTP de la petición actual (GET, POST, PUT o DELETE)
    $method = $_SERVER['REQUEST_METHOD'];

    // ── GET: OBTENER grupos ──
    // 3 casos:
    //GET / api?id=5 → trae 1 grupo por ID
    //GET /api?id_rol=2 → trae todos los de ese rol
    //GET /api → trae todos
    if($method === "GET"){
        if(isset($_GET['id_grupo'])){
            //Viene un ?id=X en la URL → busca ese grupo específico
            $sql = "SELECT * FROM tb_usuario_grupo WHERE id_grupo = :id_grupo";
            $rs = $cn->prepare($sql);
            $rs->execute([':id_grupo' => $_GET['id_grupo']]);
        }else if(isset($_GET['id_usuario'])){
            //Viene un ?id_rol=X → filtra por rol
            $sql = "SELECT * FROM tb_usuario_grupo WHERE id_usuario = :id_usuario";
            $rs = $cn->prepare($sql);
            $rs->execute([':id_usuario' => $_GET['id_usuario']]);
        } else {
            //No viene nada → trae todos los grupos
            $sql = "SELECT * FROM tb_usuario_grupo";
            $rs = $cn->prepare($sql);
            $rs->execute();
        }
        // fetchAll devuelve todos los resultados como array asociativo
        // json_encode lo convierte a JSON para enviarlo como respuesta
        $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);

    // -- POST : INSERTAR un nuevo grupo -------------
    // El frontend enviará un JSON en el body con todos los campos
    } else if ($method === "POST") {
        //php://input lee el body crudo de la petición (el JSON que mandó el frontend)
        //json_decode convierte en un array PHP (true = array asociativo, no objeto)
        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "INSERT INTO tb_usuario_grupo (fecha_union,id_usuario,id_grupo) VALUES (:fecha_union,:id_usuario,:id_grupo)";
        $rs = $cn->prepare($sql);
        //execute mapea cada :parametro con su valor del JSON
        // Esto previene inyección SQL - nunca concatenes valores directo al SQL
        $rs->execute([
            ':fecha_union' => $data['fecha_union'],
            ':id_usuario' => $data['id_usuario'],
            ':id_grupo' => $data['id_grupo'],
        ]);

    // rowCount() devuelve cuántas filas fueron afectadas por la consulta
        if ($rs->rowCount() > 0) {
        echo json_encode(['mensaje' => 'Agregado correctamente']);
        } else {
        // errorInfo() devuelve un array con el código y mensaje del error SQL
        echo json_encode(['error' => $rs->errorInfo()]);
        }

        // -- PUT : ACTUALIZAR un grupo existente -------------
        // URL: /api?id_grupo=X + body JSON con los campos a actualizar

    }else if($method == "PUT"){
        // Igual que en POST, leemos el body JSON
        $datos = json_decode(file_get_contents("php://input"), true);

        //El ID viene por URL (?id=X) para saber qué fila actualizar
        $id = $_GET["id_grupo"];

        $sql = "UPDATE tb_usuario_grupo SET fecha_union=:fecha_union, id_usuario=:id_usuario WHERE id_grupo=:id_grupo";
        $rs = $cn->prepare($sql);
        $rs->execute([
            ':fecha_union' => $datos['fecha_union'],
            ':id_usuario' => $datos['id_usuario'],
            ':id_grupo' => $id // <- El WHERE necesita el ID también

        ]);
        echo json_encode(['mensaje' => 'Actualizado correctamente']);
    
    // -- DELETE : ELIMINAR un grupo -------------
    // URL: /api?id_grupo=X -> elimina ese grupo específico
    }else if ($method === "DELETE"){
        // El ID viene por la URL (?id=X)
        $id = $_GET["id_grupo"];
        $sql = "DELETE FROM tb_usuario_grupo WHERE id_grupo = :id_grupo";
        $rs = $cn->prepare($sql);
        $rs->execute([':id_grupo' => $id]);
        echo json_encode(['mensaje' => 'Eliminado correctamente']);
    }
    

    

?>