<?php

ini_set('display_errors', 'On');
require_once "../libraries/CostModel.php";

use Libraries\CostModel;

$cost = new CostModel();

$dbname = 'abc_stock';
$dbhost = 'localhost';
$dbusername = 'root';
$dbuserpassword = 'root';



$config = [
    'dns' => 'mysql:host=' . $dbhost . ';charset=utf8',
    'username' => $dbusername,
    'password' => $dbuserpassword,
];
try {
    $PDO = new PDO($config['dns'], $config['username'], $config['password']);
    $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);    
} catch (PDOException $PDOException) {
    echo $PDOException->getMessage();
}

if (!$PDO) {
    die("Не удалось подключиться к хосту $dbhost");
}

$databases = $PDO->query('show databases')->fetchAll(PDO::FETCH_COLUMN);

if (in_array($dbname, $databases)) {
    $cost->getInfo();
} else {
    require_once "createDbAllTables.php";
    $cost->saveSupplyGoods();
    $cost->saveOrderList();
    $cost->getInfo();
}

$cost->getMessage();
