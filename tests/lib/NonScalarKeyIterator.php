<?php
namespace esperecyan\webidl\lib;

/**
 * キーが 0、1、2 のとき、代わりにオブジェクト、配列、リソースをキーとして返すイテレータ。
 */
class NonScalarKeyIterator implements \Iterator
{
    /**
     * @return \stdClass|array|resource|int|string
     */
    public function key()
    {
        return $this->valid ? current($this->pairs)[0] : null;
    }
    
    /** @var array[] */
    private $pairs;
    
    /** @var bool */
    private $valid = true;
    
    public function __construct(array $pairs = null)
    {
        $this->pairs = $pairs ? $pairs : [
            [new \stdClass()    , 1],
            [[]                 , 2],
            [xml_parser_create(), 3],
        ];
    }
    
    public function rewind()
    {
        reset($this->pairs);
        $this->valid = count($this->pairs) > 0;
    }
    
    public function next()
    {
        if (next($this->pairs) === false) {
            $this->valid = false;
        }
    }
    
    /**
     * @return bool
     */
    public function valid()
    {
        return $this->valid;
    }
    
    public function current()
    {
        return current($this->pairs)[1];
    }
}
