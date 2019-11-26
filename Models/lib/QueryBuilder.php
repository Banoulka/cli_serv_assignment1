<?php

//require_once "../Database.php";

class QueryBuilder
{
    private static $instance = null;

    private $className, $tableName, $keyValueArr;
    private $fetchAll, $orderby;
    private $stmt;
    private $sql;

    public function __construct()
    {
        $this->keyValueArr = [];
        $this->fetchAll = true;
    }

    private static function db()
    {
        return Database::getInstance()->getdbConnection();
    }
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new QueryBuilder();
        }
        return self::$instance;
    }

    public function table($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }
    public function fetchAs($className)
    {
        $this->className = $className;
        return $this;
    }
    public function insert($keyValueArr)
    {
        $this->keyValueArr = $keyValueArr;
        return $this;
    }
    public function where($colName, $value)
    {
        $this->keyValueArr[$colName] = $value;
        return $this;
    }

    /**
     * @return PDOStatement
     */
    private function prepareSelect()
    {
        $tableName = $this->tableName;
        $sql = "SELECT * FROM $tableName";
        // Check if their are no where clauses
        if (!empty($this->keyValueArr)) {

            $keys = array_keys($this->keyValueArr);
            $lastKey = end($keys);

            // Add the wheres
            $sql .= " WHERE";
            foreach ($this->keyValueArr as $key => $value) {
                $sql .= " $key = ";
                $sql .= is_numeric($value) ? "$value" : ('"' . $value . '"');
                $lastKey == $key ?: $sql .= " AND";
            }
        }

        $stmt = self::db()->prepare($sql);
        $this->className ? $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->className)
            : $stmt->setFetchMode(PDO::FETCH_OBJ);

        return $stmt;
    }

    private function execute(PDOStatement $stmt)
    {
        $stmt->execute();

        if($stmt->errorInfo()[2] != "") {
            var_dump($stmt->errorInfo());
        }

        self::resetData();
    }


    public function orderby(){}
    public function getAll(){}

    public function first(): stdClass
    {
        // Prepare the query
        $stmt = $this->prepareSelect();
        $this->execute($stmt);

        $result = $stmt->fetch();
        return $result;
    }

    private function resetData()
    {
        $this->fetchAll = true;
        $this->keyValueArr = [];
        $this->sql = null;
        $this->tableName = null;
        $this->className = null;
        $this->orderby = null;
        $this->stmt = null;
    }




}