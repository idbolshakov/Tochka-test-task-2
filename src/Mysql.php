<?php

namespace Tochka\Database;

/**
 * Mysql
 *
 * класс описывает
 * синглтон-объект
 * для доступа к
 * базе данных
 * MySQL
 *
 * @version 1.0.0
 * @author idbolshakov@gmail.com
 */
class Mysql  {

    private static $instance = null;

    private $config = null;

    /**
     * конструктор
     *
     * устанавливаем соединение
     * с базой
     *
     * настройки для 
     * установки соединения
     * берем из конфига $this->config
     *
     * @param $config {array} - конфиг 
     *
     */
    private function __construct($config) {
    
        $this->config = $config;

        $db_server = \mysql_connect(

            $this->config['host'],
            $this->config['login'],
            $this->config['password']
        );

        if ($db_server) {

            $this->query("SET NAMES utf8 COLLATE utf8_unicode_ci");

            \mysql_select_db($this->config['db']);

        } else {

            exit();
        }
    }

    protected function __clone() {}

    /**
     * getInstance
     *
     * метод отвечает за доступ
     * к синглтон-объекту Database
     *
     * @param $config {array} - конфиг 
     * @return синглтон-объект Database
     */
    public static function getInstance($config) {

        if ( \is_null(self::$instance) ) {

            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * query
     *
     * отправляет запрос к базе
     *
     * @param $query - sql запрос в текстовом виде
     * @return результат выполнения запроса в json формате
     */
    public function query($query) {

        $result = \mysql_query($query);

        if (\gettype($result) === 'boolean') {

            return array( "result" => $result);
        }

        return $this->getJsonFromMysqlQueryResult($result);
    }

    private function getJsonFromMysqlQueryResult($result) {

        $rows = array();

        while ($row = \mysql_fetch_assoc($result)) {

            $rows[] = $row;
        }

        return \json_encode($rows);
    }
}

?>
