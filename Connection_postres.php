<?php
    header("Access-Control-Allow-Origin: *");
    class Connection{
    public function connect(){
        $conn = new PDO("mysql:host=mysql-ariworkplace.alwaysdata.net;dbname=ariworkplace_postres", "ariworkplace", "valentinaypolita");
        $conn->query("SET CHARACTER SET utf8mb4");
        return $conn;
    }
    }
    
?>