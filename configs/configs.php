<?php

error_reporting(1);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Tehran');

const API_KEY = '';
const BOT_ID  = ;

$hostName = 'localhost';
$userName = '';
$password = '';
$dbName   = '';

try {
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"];
    $pdo = new PDO("mysql:host=$hostName;dbname=$dbName", $userName, $password, $options);
} catch (Exception $e) {
    echo 'connection faild!' . $e->getMessage();
}