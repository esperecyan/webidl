<?php
namespace esperecyan\webidl\lib;

class BooleanTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param bool|int|float|string|array|object|resource|null $value
     * @param bool $boolean
     * @dataProvider booleanProvider
     */
    public function testToBoolean($value, $boolean)
    {
        $this->assertSame($boolean, BooleanType::toBoolean($value));
    }
    
    public function booleanProvider()
    {
        return [
            // boolean
            [true                     , true ],
            [false                    , false],

            // integer
            [-1                       , true ],
            [0                        , false],
            [1                        , true ],
            
            // float
            [-0.1                     , true ],
            [0.0                      , false],
            [0.1                      , true ],
            [NAN                      , true ],
            [INF                      , true ],
            [-INF                     , true ],
            
            // string
            [''                       , false],
            ['string'                 , true ],
            ['0'                      , false],
            
            // array
            [[]                       , false],
            [[0]                      , true ],
            
            // object
            [new \stdClass()          , true ], // without __toString()
            [new \SimpleXMLElement('<root/>'), false], // SimpleXML
            [new \SimpleXMLElement('<root>0</root>'), true ], // SimpleXML
            [new StringCastable(''),    true ], // with __toString()
            [function () {
            }, true], // Callable
            
            // resource
            [xml_parser_create()     , true ],
            
            // NULL
            [null                     , false],
        ];
    }
}
