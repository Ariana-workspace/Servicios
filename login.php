<?php
header("Access-Control-Allow-Origin: https://videojuegos-isil.vercel.app");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    die("OPTIONS llegó");
}

    include 'Connection_postres.php';
    $objDb = new Connection();
    $cn = $objDb->connect();
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $rs = $cn->prepare($sql);
    $rs->execute([':email' => $email]);
   
    if($rs->rowCount() == 0){
        //$rs->rowCount() permite obtener la cantidad de filas que devolvió la consulta
        echo -1;
        }
    else{
        $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
        //$rows es un arreglo con los resultados de la consulta
        //En este caso solo se espera una fila, por lo que se tomará el primer elemento
        //$rows[0] Hace referencia a la primera y única fila
        //$rows[0]["password"] Hace referencia al campo password de la primera fila
        if($rows[0]["password"] != $password){
            echo -2;
            }
        else{
            unset($rows[0]["password"]);//Eliminando el 
            echo json_encode($rows, JSON_NUMERIC_CHECK);
        }
    }
?>