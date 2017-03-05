<?php
namespace esperecyan\webidl;

/**
 * This ordered map corresponds to record on Web IDL type
 * and is substituted for native PHP array that can’t use numeric keys as string.
 */
class Record implements \Iterator, \ArrayAccess
{
    /** @var mixed[][] */
    protected $entries = [];
    
    /** @var string[] */
    protected $keys = [];
    
    /** @var mixed[] */
    protected $values = [];
    
    /**
     * @param mixed[][] $entries
     */
    public function __construct(array $entries)
    {
        foreach ($entries as $entry) {
            if (!in_array($entry[0], $this->keys, true)) {
                $this->entries[] = $entry;
                $this->keys[] = $entry[0];
                $this->values[] = $entry[1];
            }
        }
        $this->rewind();
    }
    
    public function rewind()
    {
        reset($this->entries);
    }
    
    public function next()
    {
        next($this->entries);
    }
    
    /**
     * @return bool
     */
    public function valid()
    {
        return key($this->entries) !== null;
    }
    
    /**
     * @return string
     */
    public function key()
    {
        return $this->valid() ? current($this->entries)[0] : null;
    }
    
    /**
     * @return mixed
     */
    public function current()
    {
        return $this->valid() ? current($this->entries)[1] : false;
    }
    
    /**
     * @param string
     * @return bool
     */
    public function offsetExists($offset)
    {
        return in_array($offset, $this->keys, true);
    }
    
    /**
     * @param string
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $indexes = array_keys($this->keys, $offset, true);
        if (!$indexes) {
            trigger_error("Undefined index: $offset");
        }
        return $indexes ? $this->values[$indexes[0]] : null;
    }
    
    /**
     * @param void $offset
     * @param void $value
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('An instance of esperecyan\\webidl\\Record is immutable');
    }
    
    /**
     * @param void $offset
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('An instance of esperecyan\\webidl\\Record is immutable');
    }
    
    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }
    
    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $indexes = array_keys($this->keys, $name, true);
        if (!$indexes) {
            trigger_error("Undefined property: esperecyan\webidl\Record::$$offset");
        }
        return $indexes ? $this->values[$indexes[0]] : null;
    }
    
    /**
     * @param void $name
     * @param void $value
     */
    public function __set($name, $value)
    {
        throw new \BadMethodCallException('An instance of esperecyan\\webidl\\Record is immutable');
    }
    
    /**
     * @param void $name
     */
    public function __unset($name)
    {
        throw new \BadMethodCallException('An instance of esperecyan\\webidl\\Record is immutable');
    }
}
