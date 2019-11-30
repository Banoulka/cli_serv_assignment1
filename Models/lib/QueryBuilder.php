<?php

//require_once "../Database.php";

class QueryBuilder
{
    private static $instance = null;

    private $className, $tableName, $keyValueArr;
    private $fetchAll, $orderby, $cention, $limit;
    private $stmt;
    private $sql;

    const ORDER_ASC = "ASC", ORDER_DESC = "DESC";

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
        $stmt = $this->prepareInsert();
        $this->execute($stmt);

        $this->resetData();
    }
    public function where($colName, $value)
    {
        $this->keyValueArr[$colName] = $value;
        return $this;
    }
    public function limit($limitNumber)
    {
        $this->limit = $limitNumber;
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

        if (!is_null($this->orderby)) {
            $orderBy = $this->orderby;
            $cention = $this->cention;
            $sql .= " ORDER BY $orderBy $cention";
        }

        if (!is_null($this->limit)) {
            $sql .= " LIMIT $this->limit";
        }
//        var_dump($sql);
//        die();
        return self::db()->prepare($sql);
    }

    /**
     * @return PDOStatement
     */
    private function prepareInsert()
    {
        $tableName = $this->tableName;

        $sql = "INSERT INTO $tableName (";

        $keys = array_keys($this->keyValueArr);
        $lastKey = end($keys);

        foreach ($this->keyValueArr as $key => $value) {
            $sql .= "$key";
            $lastKey == $key ?: $sql .= ", ";
        }
        $sql .= ") VALUES (";
        foreach ($this->keyValueArr as $key => $value) {
            $sql .= is_numeric($value) ? "$value" : ('"' . $value . '"');
            $lastKey == $key ?: $sql .= " ,";
        }
        $sql .= ");";

        $stmt = self::db()->prepare($sql);
        return $stmt;
    }

    /**
     * @return PDOStatement
     */
    private function prepareCount()
    {
        $tableName = $this->tableName;

        $sql = "SELECT count(*) FROM $tableName";

        $keys = array_keys($this->keyValueArr);
        $lastKey = end($keys);

        // Add wheres
        if (!empty($this->keyValueArr)) {

            $sql .= " WHERE";
            foreach ($this->keyValueArr as $key => $value) {
                $sql .= " $key = ";
                $sql .= is_numeric($value) ? "$value" : ('"' . $value . '"');
                $lastKey == $key ?: $sql .= " AND";
            }

        }

        $stmt = self::db()->prepare($sql);
        return $stmt;
    }

    private function execute(PDOStatement $stmt)
    {
        $this->className ? $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->className)
            : $stmt->setFetchMode(PDO::FETCH_OBJ);
        $stmt->execute();

        if($stmt->errorInfo()[2] != "") {
            var_dump($stmt->errorInfo());
        }

        self::resetData();
    }


    public function orderby($colName, $cention = self::ORDER_ASC)
    {
        $this->orderby = $colName;
        $this->cention = $cention;
        return $this;
    }
    public function getAll()
    {
        $stmt = $this->prepareSelect();
        $this->execute($stmt);

        $result = $stmt->fetchAll();
        return $result;
    }
    public function first()
    {
        // Prepare the query
        $stmt = $this->prepareSelect();
        $this->execute($stmt);

        $result = $stmt->fetch();
        return $result;
    }
    public function count()
    {
        $stmt = $this->prepareCount();
        $this->execute($stmt);

        $stmt->setFetchMode(PDO::FETCH_NUM);
        $result = $stmt->fetch();
        return $result[0];
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
        $this->limit = null;
        $this->cention;
    }

}