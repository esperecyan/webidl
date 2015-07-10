<?php
namespace esperecyan\webidl\lib;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $type
     * @param mixed $value
     * @param array[]|null $pseudoTypes
     * @param mixed $returnValue
     * @dataProvider toProvider
     */
    public function testTo($type, $value, $pseudoTypes, $returnValue)
    {
        $this->assertSame($returnValue, Type::to($type, $value, $pseudoTypes));
    }
    
    public function toProvider()
    {
        return [
            [
                'DOMString?',
                0,
                null,
                '0',
            ],
            [
                'DOMString?',
                null,
                null,
                null,
            ],
            [
                '(double or (Date or Event) or (DOMNode or DOMString)?',
                $value = new \DateTime(),
                null,
                $value,
            ],
            [
                '(DOMNode or (Date or Event) or (XMLHttpRequest or DOMString)? or sequence<(sequence<double> or DOMNodeList)>)',
                [
                    [0.1, 0.2],
                    ['0'],
                ],
                null,
                [
                    [0.1, 0.2],
                    [0.0],
                ],
            ],
            [
                '(DOMNode or DOMString)',
                new \SplString('string'),
                null,
                'string',
            ],
            [
                '(USVString or URLSearchParams)',
                new StringCastable('string'),
                null,
                'string',
            ],
            [
                'CustomEventInit',
                [
                    'bubbles'    => null,
                    'cancelable' => 'string',
                    'detail'     => ($detail = new \SplFloat()),
                ],
                [
                    'CustomEventInit' => [
                        'bubbles'    => ['type' => 'boolean', 'default' => false],
                        'cancelable' => ['type' => 'boolean', 'default' => false],
                        'detail'     => ['type' => 'any'    , 'default' => null ],
                    ],
                ],
                [
                    'bubbles'    => false,
                    'cancelable' => true,
                    'detail'     => $detail,
                ],
            ],
        ];
    }
}
