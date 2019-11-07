<?php

require_once "Database.php";

abstract class Model {

    // Model database handler and error array
    protected $errs;
    private static $_dbHandler;

    // Database table and class name to return
    protected static $tableName;
    protected static $className;

    // Dynamic attributes for each model class
    private $_attributes;

    /*
     * This constructor will simply create a new array of errors that the
     * controller or other model classes can access to send messages back
     * to the view via the controller
     * */
    protected function __construct($attributes)
    {
        $this->errs = array();
        $this->_attributes = $attributes;

        /*
         * Set the base attributes that can be set for public use
         * One fetch mode has been used then it will return a class
         * with these fields not as null
         * */
        foreach ($this->_attributes as $att) {
            $this->$att = null;
        }
        // Remove old attributes??
        unset($this->_attributes);
    }

    protected static function db()
    {
        self::$_dbHandler = Database::getInstance()->getdbConnection();
        return self::$_dbHandler;
    }

    protected function addError($errMsg)
    {
        array_push($this->errs, $errMsg);
    }

    public function getErrors()
    {
        return $this->errs;
    }

    /**
     * This is a reusable class that allows a sub class of model to find
     * all by setting a table name and getting all in the form of a class
     * with those stats.
     * @return array collection of class objects from the database
     * */
    protected static function getAllByTableName()
    {
        $tableName = self::$tableName;
        $sql = "SELECT * FROM $tableName";
        $stmt = self::db()->prepare($sql);
        $stmt->execute();
        if(!is_null($stmt->errorInfo()[2]))
            var_dump($stmt->errorInfo());
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, self::$className);
        return $stmt->fetchAll();
    }

    /**
     * This is a reusable method that allows a sub class of model to find a specific
     * row with a key value pair.
     * @param $keyValueArr ArrayObject associative array with key value pairs
     *
     * @return PDOStatement A pdo statement containing the prepared SQL and values
     */
    private static function findByKey($keyValueArr)
    {
        $tableName = self::$tableName;
        $sql = "SELECT * FROM $tableName WHERE ";
        foreach ($keyValueArr as $k => $v) {
            if ($v == end($keyValueArr))
                $sql .= $k . " = '" . $v . "'";
            else
                $sql .= $k . " = '" . $v . "' AND ";
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute();

        if(!is_null($stmt->errorInfo()[2]))
            var_dump($stmt->errorInfo());

        if (self::$className == "") {
            $stmt->setFetchMode(PDO::FETCH_OBJ);
        } else {
            $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, self::$className);
        }
        return $stmt;
    }

    /*
   * These next methods are using the find key value pair associative
   * array and using the generic method find key resulting in finding all or
   * one by a key.
   * */
    protected static function findOneByKey($keyValueArr)
    {
        return self::findByKey($keyValueArr)->fetch();
    }

    protected static function findAllByKey($keyValueArr)
    {
        return self::findByKey($keyValueArr)->fetchAll();
    }

    /*
     * Abstract methods that each model must include as part of their implementation
     * These base methods will include an all and at least one find method
     * Other subclasses can include a findAllByKey method if they choose
     * The methods that need to be implement does checking etc with that
     * specific model, this class only containst the base methods
     * */

    /**
     * Get all from table
     *
     * @return array
     * */
    protected abstract static function all();

    /**
     * Get one from table using keyValue
     *
     * @param $keyValueArr array
     *
     * @return mixed
     */
    protected abstract static function find($keyValueArr);

    /**
     * Set class and table to use by SQL queries.
     * Must always be called before doing data and class takes
     *
     * @return void
     */
    protected abstract static function setClassAndTable();

    /**
     * Save the model to the database
     *
     * @return void
     */
    protected abstract function save();

    // Base CRUD methods

    protected function saveModel()
    {
        // Get tablename
        $tableName = self::$tableName;

        $dataAttributes = $this->getAttributes();

        // Start building the SQL query
        $sql = "INSERT INTO $tableName ( ";
        foreach ($dataAttributes as $key => $value) {
            $sql .= $key;
            if ($value != end($dataAttributes))
                $sql .= ", ";
        }
        $sql .= ") VALUES (";
        foreach ($dataAttributes as $key => $value) {
            $sql .= "'" . $value . "'";
            if ($value != end($dataAttributes))
                $sql .= ", ";
        }
        $sql .= ");";

        // Prepare and execute the statement
        $stmt = self::db()->prepare($sql);
        $stmt->execute();

        // Display errors if there are any (shouldnt be if i can program right)
        if(!is_null($stmt->errorInfo()[2]))
            var_dump($stmt->errorInfo());

    }

    protected function updateModel($whereKeyValArr)
    {
        if (count($whereKeyValArr) > 1 ) {
            return false;
        }

        $tableName = self::$tableName;

        $dataAttributes = $this->getAttributes();
        $keys = array_keys($dataAttributes);
        $lastKey = end($keys);

        $sql = "UPDATE $tableName SET ";
        foreach ($dataAttributes as $attKey => $attValue) {
            if (is_numeric($attValue))
                $sql .= "$attKey = $attValue";
            else
                $sql .= "$attKey = '$attValue'";

            // Check if at the end of the attributes
            if ($attKey != $lastKey)
                $sql .= ", ";
        }
        $key = array_keys($whereKeyValArr)[0];
        $value = array_values($whereKeyValArr)[0];

        $sql .= " WHERE $key = '$value';";

        $stmt = self::db()->prepare($sql);
        $completed = $stmt->execute();

        // Display errors if there are any (shouldnt be if i can program right)
        if(!is_null($stmt->errorInfo()[2]))
            var_dump($stmt->errorInfo());

        return $completed;
    }

    private function getAttributes()
    {
        // Get array of data from the current model
        $dataAttributes = array();

        // Get the variables and attributes from the array and add them to the
        // sql query
        foreach ($this as $key => $value) {
            if ($key != "errs" && $key != "attributes" && !is_null($value))
                $dataAttributes[$key] = $value;
        }

        return $dataAttributes;
    }

    protected static function setCustomClassAndTable($className, $tableName)
    {
        self::$className = $className;
        self::$tableName = $tableName;
    }

    // Custom query builder thing

    protected function insert()
    {
        $tableName = self::$tableName;
        $this->values = array();
        $this->sql = "INSERT INTO $tableName (";
        return $this;
    }

    protected function delete()
    {
        $tableName = self::$tableName;
        $this->values = array();
        $this->sql = "DELETE FROM $tableName WHERE ";
        return $this;
    }

    protected function value($colName, $value)
    {
        $this->values[$colName] = $value;
        return $this;
    }

    protected function executeInsert()
    {
        end($this->values);
        $lastElementKey = key($this->values);
        foreach ($this->values as $colName => $value) {
            $this->sql .= "$colName";
            if ($colName != $lastElementKey)
                $this->sql .= ", ";
        }
        $this->sql .= ") VALUES (";
        foreach ($this->values as $colName => $value) {
            $this->sql .= "$value";
            if ($colName != $lastElementKey)
                $this->sql .= ", ";
        }
        $this->sql .= ");";

        $this->execute();
    }

    protected function executeDelete()
    {
        end($this->values);
        $lastElementKey = key($this->values);
        foreach ($this->values as $colName => $value) {
            $this->sql .= "$colName = $value";
            if ($colName != $lastElementKey)
                $this->sql .= " AND ";
        }
        $this->sql .= ";";
        $this->execute();
    }

    protected static function query($sql)
    {
        $stmt = self::db()->prepare($sql);
        if (self::$className == "") {
            $stmt->setFetchMode(PDO::FETCH_OBJ);
        } else {
            $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, self::$className);
        }
        $stmt->execute();
        if(!is_null($stmt->errorInfo()[2]))
            var_dump($stmt->errorInfo());

        return $stmt->fetchAll();
    }

    private function execute()
    {
        self::db()->query($this->sql);

        unset($this->values);
        unset($this->sql);
    }

    public function getLastID()
    {
        return self::db()->lastInsertId();
    }
}