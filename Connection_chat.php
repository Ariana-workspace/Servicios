<?php
    header("Access-Control-Allow-Origin: *");
    class Connection_chat{
    public function connect(){
        $conn = new PDO("mysql:host=mysql-ariworkplace.alwaysdata.net;dbname=ariworkplace_chat", "ariworkplace", "valentinaypolita");
        $conn->query("SET CHARACTER SET utf8mb4");
        return $conn;
    }
    }
    
?>