<?php

namespace Libraries;

require_once "../libraries/ConnectDb.php";

use PDOException;
use PDO;
use Libraries\ConnectDb;

// Класс проверки и сохранения данных формы
class CostModel
{
    protected $stock_tablename;
    protected $supply_tablename;
    protected $order_tablename;
    protected $ObjDb;
    protected $supplyGoods;
    public $data = array();

    public function __construct()
    {
        session_start();
        $this->stock_tablename = 'stock';
        $this->supply_tablename = 'supply';
        $this->order_tablename = 'order_ls';
        $this->supplyGoods = [
            ['supply_number' => '1', 'good_name' => 'колбаса', 'count' => 300, 'supply_cost' => 5000, 'date' => '2021-01-01'],
            ['supply_number' => 't-500', 'good_name' => 'пармезан', 'count' => 10, 'supply_cost' => 6000, 'date' => '2021-01-02'],
            ['supply_number' => '12-TP-777', 'good_name' => 'левый носок', 'count' => 100, 'supply_cost' => 500, 'date' => '2021-01-13'],
            ['supply_number' => '12-TP-778', 'good_name' => 'левый носок', 'count' => 50, 'supply_cost' => 300, 'date' => '2021-01-14'],
            ['supply_number' => '12-TP-779', 'good_name' => 'левый носок', 'count' => 77, 'supply_cost' => 539, 'date' => '2021-01-20'],
            ['supply_number' => '12-TP-877', 'good_name' => 'левый носок', 'count' => 32, 'supply_cost' => 176, 'date' => '2021-01-30'],
            ['supply_number' => '12-TP-977', 'good_name' => 'левый носок', 'count' => 94, 'supply_cost' => 554, 'date' => '2021-02-01'],
            ['supply_number' => '12-TP-979', 'good_name' => 'левый носок', 'count' => 200, 'supply_cost' => 1000, 'date' => '2021-02-05'],
        ];
    }

    public function saveSupplyGoods()
    {
        $this->ObjDb = new ConnectDb();
        $PDO = $this->ObjDb->connect();

        foreach ($this->supplyGoods as $key => $value) {
            $name = mb_strtolower(htmlspecialchars($value['good_name']));
            $fc = mb_strtoupper(mb_substr($name, 0, 1));
            $this->supplyGoods[$key]['good_name'] = $fc . mb_substr($name, 1);
            $this->supplyGoods[$key]['supply_number'] = htmlspecialchars($value['supply_number']);
            $this->supplyGoods[$key]['count'] = htmlspecialchars($value['count']);
            $this->supplyGoods[$key]['supply_cost'] = htmlspecialchars($value['supply_cost']);
            $this->supplyGoods[$key]['date'] = htmlspecialchars($value['date']);
        }

        $query = "START TRANSACTION;";

        try {
            $result = $PDO->query($query);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        try {
            $stmtSupply = $PDO->prepare("INSERT INTO $this->supply_tablename VALUES (
                NULL,
                :supply_number,
                :good_name,
                :good_id,
                :count,
                :supply_cost,
                :date
            )");
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        foreach ($this->supplyGoods as $key => $value) {
            // Проверяем есть ли товар в таблице stock_tablename
            try {
                $stmtStock = $PDO->prepare("SELECT id FROM $this->stock_tablename WHERE name = ?");
                $stmtStock->execute([$value['good_name']]);
            } catch (PDOException $exception) {
                echo $exception->getMessage();
                exit;
            }

            $query_data = $stmtStock->fetch(PDO::FETCH_ASSOC);
            $id = $query_data['id'] ?? null;
            // Товар новый заносим данные в таблицу stock_tablename
            if (!$id) {
                try {
                    $price = $value['supply_cost'] / $value['count'] + 0.3 * $value['supply_cost'] / $value['count'];
                    $stmtStock = $PDO->prepare("INSERT INTO $this->stock_tablename VALUES (
                        NULL,
                        :name,
                        :count,
                        :price,
                        :date
                    )");
                    $count = 0;
                    $stmtStock->bindParam(':name', $value['good_name']);
                    $stmtStock->bindParam(':count', $value['count']);
                    $stmtStock->bindParam(':price', $price);
                    $stmtStock->bindParam(':date', $value['date']);
                    $stmtStock->execute();
                } catch (PDOException $exception) {
                    echo $exception->getMessage();
                    exit;
                }
                $id = $PDO->lastInsertId();
            }
            try {
                $stmtSupply->bindParam(':supply_number', $value['supply_number']);
                $stmtSupply->bindParam(':good_name', $value['good_name']);
                $stmtSupply->bindParam(':good_id', $id);
                $stmtSupply->bindParam(':count', $value['count']);
                $stmtSupply->bindParam(':supply_cost', $value['supply_cost']);
                $stmtSupply->bindParam(':date', $value['date']);
                $stmtSupply->execute();
            } catch (PDOException $exception) {
                echo $exception->getMessage();
                exit;
            }
        }

        $query = "COMMIT;";

        try {
            $result = $PDO->query($query);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        $this->data[] = 'Данные поставок сохранены';
        $this->ObjDb->close();
    }

    public function saveOrderList()
    {
        $this->ObjDb = new ConnectDb();
        $PDO = $this->ObjDb->connect();

        $query = "START TRANSACTION;";

        try {
            $result = $PDO->query($query);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        // Получаем массив данных поставок
        $goodId = 3;
        try {
            $stmt = $PDO->prepare("
            SELECT good_name, good_id, count, supply_cost, date FROM $this->supply_tablename WHERE good_id = ?
            ");
            $stmt->execute([$goodId]);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $supplyData[] = $row;
        }
        $goodName = $supplyData[0]['good_name'];
        $goodId = $supplyData[0]['good_id'];
        $firstDate = $supplyData[0]['date'];
        $firstDate = strtotime($firstDate);
        $temp = end($supplyData);
        $lastDate = $temp['date'];
        $lastDate = strtotime($lastDate);

        // Числа фибоначи
        $i = 0;
        $j = 0; // Индекс массива $supplyData - данных поставок
        $first = 0;
        $second = 1;
        $date = $firstDate;
        $multiplyCost = 0;
        $sumCount = 0;
        $mainCost = 0;
        $current = 0;
        $stockCount = 0;
        $run = true;

        while ($run) {
            if (($date > $lastDate) && ($stockCount <= 0)) {
                $run = false;
                break;
            }
            $strDate = date("Y-m-d", $date);
            if (isset($supplyData[$j])) {
                $secondDate = strtotime($supplyData[$j]['date']);
            }

            if (($date === $secondDate)) {
                // for ($k = 0; $k <= $j; $k++) {
                    $multiplyCost = $supplyData[$j]['supply_cost'] +
                    $stockCount * $mainCost;
                    $sumCount = $supplyData[$j]['count'] + $stockCount;
                // }
                $stockCount = $stockCount + $supplyData[$j]['count'];
                $mainCost = $multiplyCost / $sumCount;
                $j++;
            }
            if ($i === 0 || $i === 1) {
                $current = $i;
                $tempStockCount = $stockCount - $current;
                if ($tempStockCount < 0) {
                    $currentEval = $stockCount;
                    $stockCount = 0;
                } else {
                    $currentEval = $current;
                    $stockCount = $stockCount - $current;
                }
                $sumPrice = $currentEval * $mainCost + $currentEval * $mainCost * 0.3;
                $price = $mainCost + $mainCost * 0.3;
                try {
                    $stmtOrder = $PDO->prepare("INSERT INTO $this->order_tablename VALUES (
                        NULL,
                        :good_name,
                        :good_id,
                        :count,
                        :stock_count,                        
                        :cost_price,
                        :price,
                        :sum_price,
                        :date
                    )");

                    $stmtOrder->bindParam(':good_name', $goodName);
                    $stmtOrder->bindParam(':good_id', $goodId);
                    $stmtOrder->bindParam(':count', $currentEval);
                    $stmtOrder->bindParam(':stock_count', $stockCount);
                    $stmtOrder->bindParam(':cost_price', $mainCost);
                    $stmtOrder->bindParam(':price', $price);
                    $stmtOrder->bindParam(':sum_price', $sumPrice);
                    $stmtOrder->bindParam(':date', $strDate);
                    $stmtOrder->execute();
                } catch (PDOException $exception) {
                    echo $exception->getMessage();
                    exit;
                }
                $this->updateStock($PDO, $stockCount, $price, $strDate, $goodId);
            } else {
                $current = $first + $second;
                $first = $second;
                $second = $current;
                $tempStockCount = $stockCount - $current;

                if ($tempStockCount < 0) {
                    $currentEval = $stockCount;
                    $stockCount = 0;
                } else {
                    $currentEval = $current;
                    $stockCount = $stockCount - $current;
                }
                $sumPrice = $currentEval * $mainCost + $currentEval * $mainCost * 0.3;
                $price = $mainCost + $mainCost * 0.3;
                try {
                    $stmtOrder = $PDO->prepare("INSERT INTO $this->order_tablename VALUES (
                        NULL,
                        :good_name,
                        :good_id,
                        :count,
                        :stock_count,                        
                        :cost_price,
                        :price,
                        :sum_price,
                        :date
                    )");

                    $stmtOrder->bindParam(':good_name', $goodName);
                    $stmtOrder->bindParam(':good_id', $goodId);
                    $stmtOrder->bindParam(':count', $currentEval);
                    $stmtOrder->bindParam(':stock_count', $stockCount);
                    $stmtOrder->bindParam(':cost_price', $mainCost);
                    $stmtOrder->bindParam(':price', $price);
                    $stmtOrder->bindParam(':sum_price', $sumPrice);
                    $stmtOrder->bindParam(':date', $strDate);
                    $stmtOrder->execute();
                } catch (PDOException $exception) {
                    echo $exception->getMessage();
                    exit;
                }
                $this->updateStock($PDO, $stockCount, $price, $strDate, $goodId);
            }
            $i++;
            $date += 24 * 60 * 60;
        }

        $query = "COMMIT;";

        try {
            $result = $PDO->query($query);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        $this->data[] = 'Данные предзаказов сохранены';
        $this->ObjDb->close();
    }

    public function getInfo()
    {
        $this->ObjDb = new ConnectDb();
        $PDO = $this->ObjDb->connect();

        $date = htmlspecialchars($_POST['date']);
        $date = '2020-01-27';

        if (empty($date)) {
            self::errorMessage("Введите дату!");
        }

        $dateArray = date_parse($date);
        if (!checkdate($dateArray['month'], $dateArray['day'], $dateArray['year'])) {
            self::errorMessage("Неверный формат даты!");
        }

        try {
            $stmt = $PDO->prepare("SELECT id, name FROM $this->stock_tablename");
            $stmt->execute();
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ids[] = $row['id'];
            $names[$row['id']] = $row['name'];
        }

        foreach ($ids as $id) {
            try {
                $stmt = $PDO->prepare(
                    "SELECT good_name, good_id, stock_count, price, date FROM $this->order_tablename WHERE good_id = ?"
                );
                $stmt->execute([$id]);
            } catch (PDOException $exception) {
                echo $exception->getMessage();
                exit;
            }

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $orderDataArray[$id][] = $row;
            }
            if ($row === false && !isset($orderDataArray[$id])) {
                try {
                    $stmtStock = $PDO->prepare("SELECT * FROM $this->stock_tablename WHERE id = ?");
                    $stmtStock->execute([$id]);
                } catch (PDOException $exception) {
                    echo $exception->getMessage();
                    exit;
                }

                $stockData = $stmtStock->fetch(PDO::FETCH_ASSOC);
                $orderDataArray[$id][0]['good_name'] = $stockData['name'];
                $orderDataArray[$id][0]['good_id'] = $stockData['id'];
                $orderDataArray[$id][0]['stock_count'] = $stockData['count'];
                $orderDataArray[$id][0]['price'] = $stockData['price'];
                $orderDataArray[$id][0]['date'] = $stockData['date'];
            }
        }

        $date = strtotime($date);
        $message = '';
        foreach ($orderDataArray as $id => $orderData) {
            $count = count($orderData);
            for ($i = 0; $i < $count; $i++) {
                if (
                    ($i + 1 < $count) &&
                    ($date >= strtotime($orderData[$i]['date']) &&
                    $date <= strtotime($orderData[$i + 1]['date']))
                ) {
                    $message .= 'На складе в наличии: ';
                    $message .= $orderData[$i]['stock_count'] . ' .шт ';
                    $message .= ' ' . $orderData[$i]['good_name'];
                    $message .= ' #id= ' . $orderData[$i]['good_id'];
                    $message .= ' по цене ';
                    $message .= $orderData[$i]['price'];
                    $message .= '<br>';
                    break;
                } elseif (($i + 1 === $count) && $date >= strtotime($orderData[$i]['date'])) {
                    $message .= 'На складе в наличии: ';
                    $message .= $orderData[$i]['stock_count'] . ' .шт ';
                    $message .= ' ' . $orderData[$i]['good_name'];
                    $message .= ' #id= ' . $orderData[$i]['good_id'];
                    $message .= ' по цене ';
                    $message .= $orderData[$i]['price'];
                    $message .= '<br>';
                    break;
                } elseif ($date < strtotime($orderData[$i]['date'])) {
                    break;
                }
            }            
        }
        if ($message == '') {
            $message .= 'Поставок еще не было, товар отсутствует';
        }
        $this->data[] = $message;
        $this->ObjDb->close();
    }

    public function updateStock($PDO, $stockCount, $stockPrice, $date, $id)
    {
        try {
            $stmtStock = $PDO->prepare("UPDATE $this->stock_tablename SET count = :count, price = :price, date = :date WHERE id = :id");
            $stmtStock->bindParam(':count', $stockCount);
            $stmtStock->bindParam(':price', $stockPrice);
            $stmtStock->bindParam(':date', $date);
            $stmtStock->bindParam(':id', $id);
            $stmtStock->execute();
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }
        $id = $PDO->lastInsertId();

        return $id;
    }

    public function checkData()
    {
        $this->ObjDb = new ConnectDb();
        $PDO = $this->ObjDb->connect();

        // Проверяем есть ли товар в таблице stock_tablename
        try {
            $stmt = $PDO->prepare("SELECT * FROM $this->order_tablename");
            $stmt->execute();
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        $query_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($query_data) {
            return true;
        } else {
            return false;
        }
    }

    public function getMessage()
    {
        foreach ($this->data as $val) {
            echo $val . '<br/>';
        }
    }

    private function errorMessage($msg)
    {
        echo $msg;
        exit;
    }
}
