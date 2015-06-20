<?php
namespace esperecyan\webidl\lib;

/**
 * 外部イテレータとして、現在位置を一つだけ進めたジェネレータを持つ IteratorAggregate。
 */
class GeneratorAggregate implements \IteratorAggregate
{
    /** @var array */
    private $array;
    
    /**
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    /**
     * @return \Generator
     */
    public function getIterator()
    {
        $generator = $this->createGenerator();
        $generator->next();
        return $generator;
    }
    
    /**
     * 新規のジェネレータを返す。
     * @return \Generator
     */
    public function createGenerator()
    {
        foreach ($this->array as $value) {
            yield $value;
        }
    }
}
