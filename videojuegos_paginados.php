<?php
// ============================================================
// API REST para la tabla "videojuegos" en MySQL/MariaDB
// Soporta los 4 métodos HTTP: GET, POST, PUT, DELETE
// ============================================================

// ── CABECERAS CORS ───────────────────────────────────────────
// CORS = Cross-Origin Resource Sharing
// Le dice al navegador "sí, acepto peticiones desde otros dominios"
// Sin esto, tu frontend en React/Angular/etc. sería bloqueado
header("Access-Control-Allow-Origin: *");        // Permite peticiones desde CUALQUIER origen
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type"); // Solo permite este header

// Cuando el navegador hace una petición "extraña" (ej: PUT, DELETE),
// primero manda un OPTIONS para preguntar si está permitido.
// Aquí respondemos 200 OK y cortamos — sin procesar nada más.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluye el archivo con la conexión PDO a la base de datos
// Se asume que ahí está definida la variable $cn (objeto PDO)
include "Connection_games.php";

// Guardamos el método HTTP de la petición actual (GET, POST, PUT o DELETE)
$method = $_SERVER['REQUEST_METHOD'];



// 1. Captura de parámetros de paginación y ordenamiento
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1; // Página actual (por defecto 1)
$limite = isset($_GET['limite']) ? intval($_GET['limite']) : 10; // Cantidad de resultados por página (por defecto 10)
if($pagina < 1) $pagina = 1; // Evita páginas negativas
$offset = ($pagina - 1) * $limite; // Calcula el offset


//White-list para ordenamiento seguro (evita SQL Injection)
$columnas_permitidas = ['id', 'nombre', 'id_genero', 'desarrollador', 'anio', 'rating', 'imagen_url', 'pros', 'contras', 'duracion_horas', 'resena'];
$ordenar_por = isset($_GET['ordenar_por']) && in_array($_GET['ordenar_por'], $columnas_permitidas) ? $_GET['ordenar_por'] : 'id';
$direccion = isset($_GET['direccion']) && strtolower($_GET['direccion']) === 'desc' ? 'DESC' : 'ASC';

// 2. Lógica para el filtro de Búsqueda
$whereClause = "";
$params = [];

if(isset($_GET['texto_buscar']) && trim($_GET["texto_buscar"] !== "")) {
       $whereClause = "WHERE nombre LIKE :buscar OR desarrollador LIKE :buscar2 OR rating LIKE :buscar3";
       $busqueda = "%" . trim($_GET["texto_buscar"]) . "%";
       $params = [
        ":buscar" => $busqueda,
        ":buscar2" => $busqueda,
        ":buscar3" => $busqueda
       ];

    }
//3. Contar el total de registros(Necesario para la paginacion del frontend)
$sql_total = "SELECT COUNT(*) FROM videojuegos $whereClause";
$rs_total = $cn->prepare($sql_total);
$rs_total->execute($params);
$total_registros = $rs_total->fetchColumn();
$total_paginas = ceil($total_registros/$limite);

//4. Construir la consulta principal
//Nota. $limite y $offset se concatenan directamente porque ya fueron convertidos a (int),
//lo cual es seguro y evita tener que usar bindValue manualmente como en tu otro archivo.

// ── GET: OBTENER videojuegos ─────────────────────────────────
// 3 casos:
//   GET /api?id=5        → trae 1 videojuego por ID
//   GET /api?id_genero=2 → trae todos los de ese género
//   GET /api             → trae todos


if ($method === 'GET') {

    if (isset($_GET['id'])) {
        // Viene un ?id=X en la URL → busca ese videojuego específico
        $sql = "SELECT * FROM videojuegos WHERE id = :id";
        $rs = $cn->prepare($sql);
        $rs->execute([':id' => $_GET['id']]);

    } else if (isset($_GET['id_genero'])) {
        // Viene un ?id_genero=X → filtra por género
        $sql = "SELECT * FROM videojuegos WHERE id_genero = :id_genero";
        $rs = $cn->prepare($sql);
        $rs->execute([':id_genero' => $_GET['id_genero']]);

    } else {
        // No viene nada → trae todos los videojuegos
        $sql = "SELECT * FROM videojuegos $whereClause ORDER BY $ordenar_por $direccion LIMIT $limite OFFSET $offset";
        $rs = $cn->prepare($sql);
        $rs->execute($params);
    }

    // fetchAll devuelve todos los resultados como array asociativo
    // json_encode lo convierte a JSON para enviarlo como respuesta
    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        "paginacion" => [
            "total_registros" =>(int)$total_registros,
            "total_paginas" => (int)$total_paginas,
            "pagina_actual" => $pagina,
            "limite_por_pag" => $limite
        ],
        "datos" => $rows
    ], JSON_NUMERIC_CHECK);


// ── POST: INSERTAR un nuevo videojuego ───────────────────────
// El frontend envía un JSON en el body con todos los campos
} else if ($method === 'POST') {

    // php://input lee el body crudo de la petición (el JSON que mandó el frontend)
    // json_decode lo convierte en un array PHP (true = array asociativo, no objeto)
    $datos = json_decode(file_get_contents("php://input"), true);

    $sql = "INSERT INTO videojuegos (nombre, id_genero, desarrollador,plataforma, anio, rating, imagen_url, pros, contras, duracion_horas, resena) 
        VALUES (:nombre, :id_genero, :desarrollador,:plataforma, :anio, :rating, :imagen_url, :pros, :contras, :duracion_horas, :resena)";

    $rs = $cn->prepare($sql);

    // execute mapea cada :parametro con su valor del JSON
    // Esto previene inyección SQL — nunca concatenes valores directo al SQL
    $rs->execute([
        ':nombre'         => $datos['nombre'],
        ':id_genero'      => $datos['id_genero'],
        ':desarrollador'  => $datos['desarrollador'],
        ':plataforma'     => $datos['plataforma'],
        ':anio'           => $datos['anio'],
        ':rating'         => $datos['rating'],
        ':imagen_url'     => $datos['imagen_url'],
        ':pros'           => $datos['pros'],
        ':contras'        => $datos['contras'],
        ':duracion_horas' => $datos['duracion_horas'],
        ':resena'         => $datos['resena'],
    ]);

    // ⚠️ BUG CORREGIDO: $resultado nunca existía, se usa rowCount() en su lugar
    // rowCount() devuelve cuántas filas fueron afectadas por la consulta
    if ($rs->rowCount() > 0) {
        echo json_encode(['mensaje' => 'Agregado correctamente']);
    } else {
        // errorInfo() devuelve un array con el código y mensaje del error SQL
        echo json_encode(['error' => $rs->errorInfo()]);
    }


// ── PUT: ACTUALIZAR un videojuego existente ──────────────────
// URL: /api?id=5  +  body JSON con los nuevos valores
} else if ($method === 'PUT') {

    // Igual que en POST, leemos el body JSON
    $datos = json_decode(file_get_contents("php://input"), true);

    // El ID viene por URL (?id=X) para saber qué fila actualizar
    $id = $_GET['id'];

    $sql = "UPDATE videojuegos SET nombre=:nombre, id_genero=:id_genero, desarrollador=:desarrollador, plataforma=:plataforma, anio=:anio, rating=:rating, imagen_url=:imagen_url, pros=:pros, contras=:contras, duracion_horas=:duracion_horas, resena=:resena WHERE id=:id";

    $rs = $cn->prepare($sql);
    $rs->execute([
        ':nombre'         => $datos['nombre'],
        ':id_genero'      => $datos['id_genero'],
        ':desarrollador'  => $datos['desarrollador'],
        ':plataforma'     => $datos['plataforma'],
        ':anio'           => $datos['anio'],
        ':rating'         => $datos['rating'],
        ':imagen_url'     => $datos['imagen_url'],
        ':pros'           => $datos['pros'],
        ':contras'        => $datos['contras'],
        ':duracion_horas' => $datos['duracion_horas'],
        ':resena'         => $datos['resena'],
        ':id'             => $id  // ← el WHERE necesita el ID también
    ]);
    echo json_encode(['mensaje' => 'Actualizado correctamente']);


// ── DELETE: ELIMINAR un videojuego ───────────────────────────
// URL: /api?id=5  → elimina esa fila
} else if ($method === 'DELETE') {

    // El ID viene por la URL (?id=X)
    $id = $_GET['id'];

    $sql = "DELETE FROM videojuegos WHERE id=:id";
    $rs = $cn->prepare($sql);
    $rs->execute([':id' => $id]);

    echo json_encode(['mensaje' => 'Eliminado correctamente']);
}
?>