<?php

ini_set('display_errors', 'On');

require_once "../libraries/ConnectDb.php";

use Libraries\ConnectDb;

/**
 * Создание базы данных и всех таблиц для работы сайта
 *
 *
 */
$dbname = 'abc_stock';
$dbhost = 'localhost';
$dbusername = 'root';
$dbuserpassword = 'root';

function db_connect($dbhost, $dbusername, $dbuserpassword)
{
    $config = [
        'dns' => 'mysql:host=' . $dbhost . ';charset=utf8',
        'username' => $dbusername,
        'password' => $dbuserpassword,
    ];
    try {
        $db = new PDO($config['dns'], $config['username'], $config['password']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $PDOException) {
        echo $PDOException->getMessage();
    }
    return false;
}
function db_close($PDO)
{
        $PDO = null;
}

$PDO = db_connect($dbhost, $dbusername, $dbuserpassword);

if (!$PDO) {
    die("Не удалось подключиться к хосту $dbhost");
}

try {
    $PDO->query('CREATE DATABASE IF NOT EXISTS `' . $dbname . '`');
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания базы данных" . $dbname;
    exit;
}

echo "База данных $dbname успешно создана.<br>";

db_close($PDO);



$ObjDb = new ConnectDb();
$PDO = $ObjDb->connect();
if (!$PDO) {
    die('Ошибка подключения к базе данных');
}

# Cоздание таблицы товаров
$stock_tablename = 'stock';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "name VARCHAR(30) BINARY NOT NULL,";
$table_def .= "count INT UNSIGNED NOT NULL,";
$table_def .= "price FLOAT(7,2) UNSIGNED NOT NULL,";
$table_def .= "date TIMESTAMP,";
$table_def .= "PRIMARY KEY (id),";
$table_def .= "UNIQUE (name)";

try {
    $PDO->query("CREATE TABLE $stock_tablename ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных " . $stock_tablename;
    exit;
}

echo "Таблица $stock_tablename успешно создана.<br />";


# Cоздание таблицы поставок
$supply_tablename = 'supply';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "supply_number VARCHAR(20) BINARY NOT NULL,";
$table_def .= "good_name VARCHAR(30) BINARY NOT NULL,";
$table_def .= "good_id INT NOT NULL,";
$table_def .= "count INT UNSIGNED NOT NULL,";
$table_def .= "supply_cost INT UNSIGNED NOT NULL,";
$table_def .= "date TIMESTAMP,";
$table_def .= "PRIMARY KEY (id)";

try {
    $PDO->query("CREATE TABLE $supply_tablename ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных" . $supply_tablename;
    exit;
}

echo "Таблица $supply_tablename успешно создана.<br />";

# Cоздание таблицы предзаказов
$order_tablename = 'order_ls';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "good_name VARCHAR(30) BINARY NOT NULL,";
$table_def .= "good_id INT NOT NULL,";
$table_def .= "count INT UNSIGNED NOT NULL,";
$table_def .= "stock_count INT UNSIGNED NOT NULL,";
$table_def .= "cost_price FLOAT(7,2) UNSIGNED NOT NULL,";
$table_def .= "price FLOAT(7,2) UNSIGNED NOT NULL,";
$table_def .= "sum_price FLOAT(10,2) UNSIGNED NOT NULL,";
$table_def .= "date TIMESTAMP,";
$table_def .= "PRIMARY KEY (id),";
$table_def .= "FOREIGN KEY (good_name) REFERENCES stock(name) ON UPDATE CASCADE ON DELETE RESTRICT,";
$table_def .= "FOREIGN KEY (good_id) REFERENCES stock(id) ON UPDATE CASCADE ON DELETE RESTRICT";

try {
    $PDO->query("CREATE TABLE $order_tablename ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных " . $order_tablename;
    exit;
}

echo "Таблица $order_tablename успешно создана.<br />";

$ObjDb->close();
