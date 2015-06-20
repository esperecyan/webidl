<?php
namespace esperecyan\webidl\lib;

class DictionaryTypeTest extends \PHPUnit_Framework_TestCase
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
                    'detail'     => ($detail = new \SplFloat()),
                ],
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => true,
                    'detail'     => $detail,
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected Dictionary, got instance of SplBool
     */
    public function testInvalidDictionary()
    {
        DictionaryType::toDictionary(new \SplBool(), 'Dictionary', []);
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage In "stringMember" member of Dictionary, expected DOMString
     */
    public function testInvalidDictionary2()
    {
        DictionaryType::toDictionary([
            'stringMember'   => new \SplBool(),
            'requiredMember' => 'string',
        ], 'Dictionary', [
            'Dictionary' => [
                'stringMember'   => ['type' => 'DOMString'],
                'requiredMember' => ['type' => 'DOMString', 'required' => true],
            ]
        ]);
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage In "requiredMember" member of Dictionary, expected DOMString, got none
     */
    public function testInvalidDictionary3()
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
}
