<?php
namespace esperecyan\webidl\lib;

/**
 * キーが 0、1、2 のとき、代わりにオブジェクト、配列、リソースをキーとして返すイテレータ。
 */
class NonScalarKeyIterator implements \Iterator
{
    /**
     * @return stdClass|array|resource|integer|string
     */
    public function key()
    {
        if ($this->valid) {
            $key = key($this->array);
            switch ($key) {
                case 0:
                    $key = new \stdClass();
                    break;
                case 1:
                    $key = [];
                    break;
                case 2:
                    $key = xml_parser_create();
                    break;
            }
        } else {
            $key = null;
        }
        return $key;
    }
    
    /** @var array */
    private $array;
    
    /** @var boolean */
    private $valid = true;
    
    public function __construct(array $array = [1, 2, 3])
    {
        $this->array = $array;
    }
    
    public function rewind()
    {
        reset($this->array);
        $this->valid = count($this->array) > 0;
    }
    
    public function next()
    {
        if (next($this->array) === false) {
            $this->valid = false;
        }
    }
    
    /**
     * @return boolean
     */
    public function valid()
    {
        return $this->valid;
    }
    
    public function current()
    {
        return current($this->array);
    }
}
