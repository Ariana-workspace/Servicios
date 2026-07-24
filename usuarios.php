<?php
// ============================================================
// API REST para la tabla "usuarios" en MySQL/MariaDB
// Soporta los 4 métodos HTTP: GET, POST, PUT, DELETE
// ============================================================

// ── CABECERAS CORS ───────────────────────────────────────────
// CORS = Cross-Origin Resource Sharing
// Le dice al navegador "sí, acepto peticiones desde otros dominios"
// Sin esto, tu frontend en React/Angular/etc. sería bloqueado
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
    include "Connection_postres.php";
    $objDb = new Connection();
    $cn = $objDb->connect();

    //Guardamos el método HTTP de la petición actual (GET, POST, PUT o DELETE)
    $method = $_SERVER['REQUEST_METHOD'];

    // ── GET: OBTENER usuarios ──
    // 3 casos:
    //GET / api?id=5 → trae 1 usuario por ID
    //GET /api?id_rol=2 → trae todos los de ese rol
    //GET /api → trae todos
    if($method === "GET"){
        if(isset($_GET['id_usuario'])){
            //Viene un ?id=X en la URL → busca ese usuario específico
            $sql = "SELECT * FROM usuarios WHERE id_usuario = :id_usuario";
            $rs = $cn->prepare($sql);
            $rs->execute([':id_usuario' => $_GET['id_usuario']]);
        }else if(isset($_GET['id_rol'])){
            //Viene un ?id_rol=X → filtra por rol
            $sql = "SELECT * FROM usuarios WHERE id_rol = :id_rol";
            $rs = $cn->prepare($sql);
            $rs->execute([':id_rol' => $_GET['id_rol']]);
        } else {
            //No viene nada → trae todos los usuarios
            $sql = "SELECT * FROM usuarios";
            $rs = $cn->prepare($sql);
            $rs->execute();
        }
        // fetchAll devuelve todos los resultados como array asociativo
        // json_encode lo convierte a JSON para enviarlo como respuesta
        $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);

    // -- POST : INSERTAR un nuevo usuario -------------
    // El frontend enviará un JSON en el body con todos los campos
    } else if ($method === "POST") {
        //php://input lee el body crudo de la petición (el JSON que mandó el frontend)
        //json_decode convierte en un array PHP (true = array asociativo, no objeto)
        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "INSERT INTO usuarios (nombre, email,password,telefono, id_rol) VALUES (:nombre, :email, :password, :telefono, :id_rol)";
        $rs = $cn->prepare($sql);
        //execute mapea cada :parametro con su valor del JSON
        // Esto previene inyección SQL - nunca concatenes valores directo al SQL
        $rs->execute([
            ':nombre' => $data['nombre'],
            ':email' => $data['email'],
            ':password' => $data['password'],
            ':telefono' => $data['telefono'],
            ':id_rol' => $data['id_rol']
        ]);

    // rowCount() devuelve cuántas filas fueron afectadas por la consulta
        if ($rs->rowCount() > 0) {
        echo json_encode(['mensaje' => 'Agregado correctamente']);
        } else {
        // errorInfo() devuelve un array con el código y mensaje del error SQL
        echo json_encode(['error' => $rs->errorInfo()]);
        }

        // -- PUT : ACTUALIZAR un usuario existente -------------
        // URL: /api?id_usuario=X + body JSON con los campos a actualizar

    }else if($method == "PUT"){
        // Igual que en POST, leemos el body JSON
        $datos = json_decode(file_get_contents("php://input"), true);

        //El ID viene por URL (?id=X) para saber qué fila actualizar
        $id = $_GET["id_usuario"];

        $sql = "UPDATE usuarios SET nombre=:nombre, email=:email, password=:password, telefono=:telefono, id_rol=:id_rol WHERE id_usuario=:id_usuario";
        $rs = $cn->prepare($sql);
        $rs->execute([
            ':nombre' => $datos['nombre'],
            ':email' => $datos['email'],
            ':password' => $datos['password'],
            ':telefono' => $datos['telefono'],
            ':id_rol' => $datos['id_rol'],
            ':id_usuario' => $id // <- El WHERE necesita el ID también

        ]);
        echo json_encode(['mensaje' => 'Actualizado correctamente']);
    
    // -- DELETE : ELIMINAR un usuario -------------
    // URL: /api?id_usuario=X -> elimina ese usuario específico
    }else if ($method === "DELETE"){
        // El ID viene por la URL (?id=X)
        $id = $_GET["id_usuario"];
        $sql = "DELETE FROM usuarios WHERE id_usuario = :id_usuario";
        $rs = $cn->prepare($sql);
        $rs->execute([':id_usuario' => $id]);
        echo json_encode(['mensaje' => 'Eliminado correctamente']);
    }
    

    

?>