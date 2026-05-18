<?php

$host = 'localhost';
$dbname = 'kampus';
$username ='root';
$password ='';

try{

$dsn ="mysql:host=$host;dbname=$dbname; charset=utf8mb4";

$pdo =new PDO($dsn ,$username,$password);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("SET NAMES utf8mb4");

}
catch(PDOException $e) {
  
    die("Bağlantı hatası: " . $e->getMessage());

}
?>