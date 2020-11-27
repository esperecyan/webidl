<?php
namespace esperecyan\webidl\lib;

use esperecyan\webidl\Record;

class DictionaryTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array $value
     * @param string $identifier
     * @param array $returnValue
     * @dataProvider dictionaryProvider
     */
    public function testToDictionary($value, $identifier, $returnValue)
    {
        $this->assertSame($returnValue, DictionaryType::toDictionary($value, $identifier, [
            'CustomEventInit' => [
                'bubbles'    => ['type' => 'boolean', 'default' => false],
                'cancelable' => ['type' => 'boolean', 'default' => false],
                'detail'     => ['type' => 'any'    , 'default' => null ],
            ],
            'MutationObserverInit' => [
                'childList'             => ['type' => 'boolean', 'default' => false],
                'attributes'            => ['type' => 'boolean'                    ],
                'characterData'         => ['type' => 'boolean'                    ],
                'subtree'               => ['type' => 'boolean', 'default' => false],
                'attributeOldValue'     => ['type' => 'boolean'                    ],
                'characterDataOldValue' => ['type' => 'boolean'                    ],
                'attributeFilter'       => ['type' => 'sequence<DOMString>'        ],
            ],
        ]));
    }
    
    public function dictionaryProvider()
    {
        return [
            [
                [
                    'bubbles'    => null,
                    'cancelable' => 'string',
                    'detail'     => 0.0,
                ],
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => true,
                    'detail'     => 0.0,
                ],
            ],
            [
                [],
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => false,
                    'detail'     => null,
                ],
            ],
            [
                [
                    'bubbles'    => true,
                    'cancelable' => true,
                    'invalidIdentifier' => 1,
                ],
                'CustomEventInit',
                [
                    'bubbles'    => true,
                    'cancelable' => true,
                    'detail'     => null,
                ],
            ],
            [
                new ArrayAccessible(['bubbles' => true]),
                'CustomEventInit',
                [
                    'bubbles'    => true,
                    'cancelable' => false,
                    'detail'     => null,
                ],
            ],
            [
                new NonScalarKeyIterator(),
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => false,
                    'detail'     => null,
                ],
            ],
            [
                new \ArrayObject(),
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => false,
                    'detail'     => null,
                ],
            ],
            [
                new GeneratorAggregate([1, 2, 3]),
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => false,
                    'detail'     => null,
                ],
            ],
            [
                (new GeneratorAggregate([1, 2, 3]))->createGenerator(),
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => false,
                    'detail'     => null,
                ],
            ],
            [
                (new GeneratorAggregate([1, 2, 3]))->getIterator(),
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => false,
                    'detail'     => null,
                ],
            ],
            [
                (new GeneratorAggregate([1]))->getIterator(),
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => false,
                    'detail'     => null,
                ],
            ],
            [
                (new GeneratorAggregate([]))->getIterator(),
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => false,
                    'detail'     => null,
                ],
            ],
            [
                [],
                'MutationObserverInit',
                [
                    'childList'             => false,
                    'subtree'               => false,
                ],
            ],
            [
                [
                    'childList'             => true,
                    'attributes'            => true,
                    'characterData'         => true,
                    'subtree'               => true,
                    'attributeOldValue'     => true,
                    'characterDataOldValue' => true,
                    'attributeFilter'       => [0, 1, 2],
                ],
                'MutationObserverInit',
                [
                    'childList'             => true,
                    'attributes'            => true,
                    'characterData'         => true,
                    'subtree'               => true,
                    'attributeOldValue'     => true,
                    'characterDataOldValue' => true,
                    'attributeFilter'       => ['0', '1', '2'],
                ],
            ],
        ];
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage In "requiredMember" member of Dictionary, expected DOMString, got none
     */
    public function testInvalidDictionary()
    {
        DictionaryType::toDictionary([
            'stringMember' => 'string',
        ], 'Dictionary', [
            'Dictionary' => [
                'stringMember'   => ['type' => 'DOMString'],
                'requiredMember' => ['type' => 'DOMString', 'required' => true],
            ]
        ]);
    }
    
    /**
     * @param array|bool|int|float|string|object|null $value
     * @param string $k
     * @param string $v
     * @param array $record
     * @dataProvider recordProvider
     */
    public function testToRecord($value, $k, $v, $record)
    {
        $entries = [];
        foreach (DictionaryType::toRecord($value, $k, $v) as $key => $value) {
            $entries[] = [$key, $value];
        }
        $this->assertEquals($record, $entries);
    }
    
    public function recordProvider()
    {
        return [
            [['test' => 10, 'test2' => null], 'DOMString',  'DOMString?', [['test', 10], ['test2', null]]],
            [true                       , 'USVString',  'DOMString', [['0', '1']]],
            [false                       , 'USVString',  'DOMString?', [['0', '']]],
            [[
                '' => ['1', 2, 3.0],
                '1' => $nodeList = new \DOMNodeList(),
                '2' => [],
            ], 'DOMString', '(sequence<double> or DOMNodeList)', [
                ['', [1.0, 2.0, 3.0]],
                ['1', $nodeList],
                ['2', []],
            ]],
            
            // object
            'object does not implement Traversable 1'
                => [(object)[1, 2, 3]          , 'DOMString', 'DOMString', [['0', '1'], ['1', '2'], ['2', '3']]  ],
            'object does not implement Traversable 2'
                => [(object)['key' => 1, 2, 3] , 'DOMString', 'DOMString', [['key', '1'], ['0', '2'], ['1', '3']]],
            'object implements Iterator' => [
                new NonScalarKeyIterator([[null, 1], [new StringCastable('str'), 2]]),
                'DOMString',
                'DOMString',
                [['', '1'], ['str', '2']]
            ],
            'object implements IteratorAggregate'
                => [new \ArrayObject([1, 2, 3]), 'DOMString', 'DOMString',  [['0', '1'], ['1', '2'], ['2', '3']] ],

            // NULL
            'NULL' => [null                    , 'DOMString', 'DOMString', []                                    ],
            
            'ByteString key' => [
                [file_get_contents(__DIR__ . '/byte-string.png') => 'value'],
                'ByteString',
                'DOMString',
                [[file_get_contents(__DIR__ . '/byte-string.png'), 'value']],
            ],
        ];
    }
    
    /**
     * @param array|string|object|resource $invalidKey
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected record<DOMString, DOMString> (an associative array including only DOMString)
     * @dataProvider invalidKeyProvider
     */
    public function testInvalidRecordKey($invalidKey)
    {
        DictionaryType::toRecord(new Record([[$invalidKey, '']]), 'DOMString', 'DOMString');
    }
    
    public function invalidKeyProvider()
    {
        return [
            [[]],
            [new \stdClass()],
            [new \__PHP_Incomplete_Class()],
            [function () {
            }],
            [xml_parser_create()],
            [file_get_contents(__DIR__ . '/byte-string.png')],
        ];
    }
}
