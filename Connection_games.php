<?php
    header("Access-Control-Allow-Origin: *");

   
$host = "mysql-ariworkplace.alwaysdata.net";
$dbname = "ariworkplace_games";
$user = "ariworkplace";
$password = "valentinaypolita";

$cn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
?>