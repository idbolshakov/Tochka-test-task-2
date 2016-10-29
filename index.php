<?php

include 'src/includes.php';

use Tochka\Database\Mysql;
use Tochka\CBRWebService\CBRWebService;

// 
// Инициализация переменных
//
$cbr  = null; // soap-client
$to   = null; // конец периода
$from = null; // начало периода

$dbConfig  = null; // конфигурация базы данных
$tableName = null; // название таблицы куда будем выгружать данные


// 
// Читаем и парсим конфиг
//
$configString = \file_get_contents('config.json');

$config = \json_decode($configString, true);


//
// получаем конфигурацию базы данных
//
$dbConfig  = $config['mysqlConfig'];
$tableName = $dbConfig['tableName'];


// 
// в зависимости от параметра запуска производим нужные операции
//
$run_mode = $argv[1];

//
// Будем выгружать новые данные (старые удалим)
//
if ( '--export-new-data' === $run_mode ) {

    //
    // удаляем старую таблицу (если есть) и создаем пустую
    //
    Mysql::getInstance($dbConfig)->query("DROP TABLE IF EXISTS `$tableName`;");
    Mysql::getInstance($dbConfig)->query("

        CREATE TABLE `$tableName` (

            `id` INT AUTO_INCREMENT NOT NULL PRIMARY KEY,

            `date` VARCHAR(25),

            `cbr_code` VARCHAR(6),

            `nom` INT,

            `curs` DOUBLE

        ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ");

    //
    // конфигурируем запрос к веб сервису Центробанка
    //
    $to   = new \DateTime($config['endPeriodDate']);
    $from = clone $to;
    $from = $from->modify('-'.$config['periodLength']);


//
// Будем обновлять текущие данные
//
} elseif ( '--update-current-data' === $run_mode) {

    //
    // получаем последнюю дату динамики изменения цен в базе
    //
    $result = Mysql::getInstance($dbConfig)
        ->query("SELECT MAX(`date`) as latest FROM `$tableName`");

    $latestDate = \json_decode($result,true)[0]['latest'];

    //
    // конфигурируем запрос к веб сервису Центробанка
    //
    $from = new \DateTime($latestDate);
    $from = $from->modify('+1 day');
    $to   = new \DateTime('now');

//
// иначе ничего не делаем
//
} else {

    exit;
}


//
// для каждой валюты из конфига отправляем запрос к сервису Центробанка
//
$cbr = new CBRWebService();

$results = array();

foreach ($config['currencyList'] as $code) {

    $results[] = $cbr->getCursDynamicXML($from, $to, $code);
}


//
// записываем полученные данные в базу
//
foreach ($results as $result) {

    $xml = new SimpleXMLElement($result);

    foreach ($xml->ValuteCursDynamic as $row) {

        $tableName = $dbConfig['tableName'];

        $date = $row->CursDate;
        $code = $row->Vcode;
        $nom  = $row->Vnom;
        $curs = $row->VCurs;

        Mysql::getInstance($dbConfig)->query("

            INSERT 
                INTO `$tableName` (`date`, `cbr_code`, `nom`, `curs`) 
                VALUES ('$date', '$code', '$nom', '$curs')
        ");
    }
}

?>
