<?php
namespace esperecyan\webidl\lib;

class ArrayAccessible implements \ArrayAccess
{
    private $array;
    
    public function __construct($array = [])
    {
        $this->array = $array;
    }
    
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->array);
    }
    
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }
    
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }
}
