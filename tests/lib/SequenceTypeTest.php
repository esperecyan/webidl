<?php
namespace esperecyan\webidl\lib;

class SequenceTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param boolean|integer|float|string|object|null $value
     * @param string $type
     * @param array $sequence
     * @dataProvider sequenceProvider
     */
    public function testToSequence($value, $type, $sequence)
    {
        $this->assertSame($sequence, SequenceType::toSequence($value, $type));
    }
    
    /**
     * @param boolean|integer|float|string|object|null $value
     * @param string $type
     * @param array $sequence
     * @dataProvider sequenceProvider
     */
    public function testToFrozenArray($value, $type, $sequence)
    {
        $this->assertSame($sequence, SequenceType::toFrozenArray($value, $type));
    }
    
    public function sequenceProvider()
    {
        /** @var \Generator rewindするとエラーが発生するオブジェクト。 */
        $errorRewindObject = $this->generatorFunction();
        $errorRewindObject->next();
        
        return [
            [true                       , 'DOMString', ['1']          ],
            [false                      , 'boolean'  , [false]        ],
            [[1, 2, 3]                  , 'double'   , [1.0, 2.0, 3.0]],
            [[
                ['1', 2, 3.0],
                $nodeList = new \DOMNodeList(),
                [],
            ], '(sequence<double> or DOMNodeList)', [
                [1.0, 2.0, 3.0],
                $nodeList,
                [],
            ]],
            
            // object
            [(object)[1, 2, 3]                  , 'DOMString', ['1', '2', '3']], // stdCalss (not implements Traversable)
            [new NonScalarKeyIterator([1, 2, 3]), 'DOMString', ['1', '2', '3']], // implements Iterator
            [(new GeneratorAggregate([1, 2, 3]))->createGenerator(), 'DOMString', ['1', '2', '3']], // 新規のジェネレータ
            [(new GeneratorAggregate([1, 2, 3]))->getIterator(), 'DOMString', ['2', '3']], // すでに開始したジェネレータ
            [(new GeneratorAggregate([1]))->getIterator(), 'DOMString', []], // 閉じられたジェネレータ
            [(new GeneratorAggregate([]))->getIterator(), 'DOMString', []], // rewindメソッドではエラーが発生しない閉じられたジェネレータ
            [new \ArrayObject([1, 2, 3])        , 'DOMString', ['1', '2', '3']], // implements IteratorAggregate
            [new GeneratorAggregate([1, 2, 3])  , 'DOMString', ['2', '3']], // すでに開始したジェネレータを持つ IteratorAggregate

            // NULL
            [null                       , 'DOMString', []             ],
        ];
    }
    
    /**
     * @return \Generator
     */
    private function generatorFunction()
    {
        for ($i = 1; $i <= 3; $i++) {
            yield $i;
        }
    }
    
    /**
     * @param array $sequence
     * @param string $type
     * @expectedException \DomainException
     * @expectedExceptionMessageRegExp /^Expected sequence<.+> \(an array including only .+\)$/u
     * @dataProvider invalidSequence
     */
    public function testInvalidSequence($sequence, $type)
    {
        SequenceType::toSequence($sequence, $type);
    }
    
    /**
     * @param array $sequence
     * @param string $type
     * @expectedException \DomainException
     * @expectedExceptionMessageRegExp /^Expected sequence<.+> \(an array including only .+\)$/u
     * @dataProvider invalidSequence
     */
    public function testInvalidFrozenArray($sequence, $type)
    {
        SequenceType::toFrozenArray($sequence, $type);
    }
    
    public function invalidSequence()
    {
        return [
            [['string', new \stdClass()]      , 'DOMString'                 ],
            [[[1.0, null], new \DOMNodeList()], '(sequence<double> or DOMNodeList)'],
        ];
    }
}
