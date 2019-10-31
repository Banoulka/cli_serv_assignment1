<?php

require_once "Database.php";

abstract class Model {

    // Model database handler and error array
    protected $errs;
    private static $dbHandler;

    // Database table and class name to return
    protected static $tableName;
    protected static $className;

    // Dynamic attributes for each model class
    private $attributes;

    /*
     * This constructor will simply create a new array of errors that the
     * controller or other model classes can access to send messages back
     * to the view via the controller
     * */
    protected function __construct($attributes)
    {
        $this->errs = array();
        $this->attributes = $attributes;

        /*
         * Set the base attributes that can be set for public use
         * One fetch mode has been used then it will return a class
         * with these fields not as null
         * */
        foreach ($this->attributes as $att) {
            $this->$att = null;
        }
        // Remove old attributes??
        unset($this->attributes);
    }

    protected static function db()
    {
        self::$dbHandler = Database::getInstance()->getdbConnection();
        return self::$dbHandler;
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
     * @return GenericClass[] collection of class objects from the database
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
     * @return PDOStatement A pdo statement containing the prepared SQL and values
     */
    private static function findByKey($keyValueArr) {
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
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, self::$className);
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
    protected abstract static function all();
    protected abstract static function find($keyValueArr);
    protected abstract static function setClassAndTable(); // must always be called before doing data and class takes
    protected abstract function save();

    // Base CRUD methods
    protected function saveModel() {
        // Get tablename
        $tableName = self::$tableName;

        // Get array of data from the current model
        $dataAttributes = array();

        // Get the variables and attributes from the array and add them to the
        // sql query
        foreach ($this as $var => $value) {
            if($var != "errs")
            $dataAttributes[$var] = $value;
        }

        // Start building the SQL query
        $sql = "INSERT INTO $tableName ( ";
        foreach ($dataAttributes as $key => $value) {
            if(!is_null($value)) {
                $sql .= $key;
                if ($value != end($dataAttributes))
                    $sql .= ", ";
            }
        }
        $sql .= ") VALUES (";
        foreach ($dataAttributes as $key => $value) {
            if(!is_null($value)) {
                $sql .= "'" . $value . "'";
                if ($value != end($dataAttributes))
                    $sql .= ", ";
            }
        }
        $sql .= ");";

        // Prepare and execute the statement
        $stmt = self::db()->prepare($sql);
        $stmt->execute();

        // Display errors if there are any
        if(!is_null($stmt->errorInfo()[2]))
            var_dump($stmt->errorInfo());
    }

}