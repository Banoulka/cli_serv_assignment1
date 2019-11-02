<?php

class Validation
{
     private $error;

     private $name;
     private $value;

     public function addError($errMsg)
     {
         $errMsg = sprintf($errMsg, $this->name);
         array_push($this->error, array($this->name => $errMsg));
     }

     public function getErrors(): array
     {
        return $this->error;
     }

    /**
     * Set the name of the field being checked
     *
     * @param string $name
     * @return self
     */
     public function name(string $name): self
     {
        $this->name = $name;
        return $this;
     }

    /**
     * Set the value of the field being checked
     *
     * @param mixed $value
     * @return self
     */
    public function value($value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Check if the value exists and is not empty
     *
     * @return self
     */
    public function required(): self
    {
        if ($this->value == "" || $this->value == null) {
            $this->addError("Field %s is required");
        }
        return $this;
    }

    /**
     * Check the length of the string or number between a range
     *
     * @param int $min
     * @param int $max
     * @return self
     */
    public function length(int $min, int $max): self
    {
        $lengthError = false;
        if (is_string($this->value)) {
            // Process the value as a string length
            $lengthError = strlen($this->value) < $min || strlen($this->value) > $max;
        } else {
            // Process the value as a numeric
            $lengthError = $this->value < $min || $this->value > $max;
        }

        if ($lengthError) {
            $this->addError("Field %s must be between $min and $max");
        }

        return $this;
    }

    /**
     * Check if field value matches the value.
     *
     * @param mixed $value
     * @return self
     */
    public function equal($value): self
    {
        if ($this->value != $value) {
            $this->addError("Field %s must equal $value");
        }
        return $this;
    }

    /**
     * Check if field value matches a certain type
     *
     * @param  int $FILTER_TYPE
     * @return self
     */
    public function type(int $FILTER_TYPE): self
    {
        $var = filter_var($this->value, $FILTER_TYPE, FILTER_NULL_ON_FAILURE);
        if (!$var) {
            $this->addError("Field %s does not match type");
        }
        return $this;
    }
}