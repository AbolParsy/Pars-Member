<?php

error_reporting(1);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Tehran');

const API_KEY = '1010361809:u9favCTJqt5zgmHkMAhO2sBJYqMUcsMkCCiycx1D';
const BOT_ID  = 1010361809;

$socket = '/data/data/com.termux/files/usr/tmp/mysql.sock'; // مسیر واقعی سوکت

$userName = 'root';
$password = 'Pars2500';
$dbName   = 'iranzobodb';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"
    ];
    $pdo = new PDO("mysql:unix_socket=$socket;dbname=$dbName;charset=utf8mb4", $userName, $password, $options);
} catch (Exception $e) {
    echo 'connection faild! ' . $e->getMessage();
}
