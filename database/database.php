<?php

$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=localhost;dbname=database1", $username, $password);
}catch (PDOException $e){
    echo $e->getMessage();
}

