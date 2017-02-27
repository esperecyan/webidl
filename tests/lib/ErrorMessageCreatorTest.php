<?php
namespace esperecyan\webidl\lib;

class ErrorMessageCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $value
     * @param string $expectedType
     * @param string|null $message
     * @param string $errorMessage
     * @dataProvider messageProvider
     */
    public function testCreate($value, $expectedType, $message, $errorMessage)
    {
        $this->assertSame($errorMessage, ErrorMessageCreator::create($value, $expectedType, $message));
    }
    
    public function messageProvider()
    {
        return [
            [
                false,
                'USVString (a UTF-8 string)',
                null,
                'Expected USVString (a UTF-8 string), got false',
            ],
            [
                [],
                'ByteString (a string)',
                null,
                'Expected ByteString (a string), got array',
            ],
            [
                function () {
                },
                'ByteString (a string)',
                null,
                'Expected ByteString (a string), got instance of Closure',
            ],
            [
                new \__PHP_Incomplete_Class(),
                'sequence<DOMString> (an array including only DOMString)',
                '',
                'Expected sequence<DOMString> (an array including only DOMString)',
            ],
        ];
    }
    
    /**
     * @param mixed $value
     * @param string $stringRepresentation
     * @dataProvider typeProvider
     */
    public function testGetStringRepresentation($value, $stringRepresentation)
    {
        $this->assertSame($stringRepresentation, ErrorMessageCreator::getStringRepresentation($value));
    }
    
    public function typeProvider()
    {
        return [
            [true                         , 'true'                              ],
            [1                            , '1'                                 ],
            [9223372036854775808.0        , '9.2233720368547758E+18'            ],
            [INF                          , 'INF'                               ],
            [NAN                          , 'NAN'                               ],
            ['string'                     , '\'string\''                        ],
            [mb_convert_encoding('PEAR (🍐)', 'UTF-16', 'UTF-8'), 'non UTF-8 string'],
            [[]                           , 'array'                             ],
            [new \stdClass()              , 'instance of stdClass'              ],
            [new \__PHP_Incomplete_Class(), 'instance of __PHP_Incomplete_Class'],
            [$this                        , 'instance of ' . __CLASS__          ],
            [xml_parser_create()         , 'resource of type (xml)'            ],
            [null                         , 'NULL'                              ],
        ];
    }
}
