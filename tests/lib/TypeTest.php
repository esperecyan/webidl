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
                '(double or (DateTime or Event) or (DOMNode or DOMString)?',
                $value = new \DateTime(),
                null,
                $value,
            ],
            [
                '(DOMNode or Event or (XMLHttpRequest or DOMString)? or sequence<(sequence<double> or DOMNodeList)>)',
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
                'string',
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
                    'detail'     => 0.0,
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
                    'detail'     => 0.0,
                ],
            ],
        ];
    }
    /**
     * @param string $type
     * @param \esperecyan\webidl\Error|\Error $exception
     * @dataProvider exceptionProvider
     */
    public function testToException($exception)
    {
        $this->assertSame($exception, Type::to('Error', $exception));
    }
    
    public function exceptionProvider()
    {
        return [
            [new \esperecyan\webidl\RangeError()],
            [new \esperecyan\webidl\TypeError()],
            [new \TypeError()],
            [new \Error()],
        ];
    }
}
