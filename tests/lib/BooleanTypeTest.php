<?php
namespace esperecyan\webidl\lib;

class BooleanTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param boolean|integer|float|string|array|object|resource|null $value
     * @param boolean $castable
     * @dataProvider booleanCastableProvider
     */
    public function testIsBooleanCastable($value, $castable)
    {
        $this->assertSame($castable, BooleanType::isBooleanCastable($value));
    }
    
    public function booleanCastableProvider()
    {
        return [
            // boolean
            [true                     , true ],
            [false                    , true ],
            [new \SplBool()           , true ],

            // integer
            [-1                       , true ],
            [0                        , true ],
            [1                        , true ],
            [new \SplInt()            , false],
            
            // float
            [-0.1                     , true ],
            [0.0                      , true ],
            [0.1                      , true ],
            [NAN                      , true ],
            [INF                      , true ],
            [-INF                     , true ],
            [new \SplFloat()          , false],
            
            // string
            [''                       , true ],
            ['string'                 , true ],
            [new \SplString('string') , false],
            
            // array
            [[]                       , true ],
            
            // object
            [new \stdClass()          , true ], // without __toString()
            [new \SimpleXMLElement('<root/>'), true ], // SimpleXML
            [new StringCastable('123'), true ], // with __toString()
            [new Enum(),                false], // instance of SplEnum
            [function () {
            }, true], // Callable
            
            // resource
            [xml_parser_create()     , true ],
            
            // NULL
            [null                     , true ],
        ];
    }
    
    /**
     * @param boolean|integer|float|string|array|object|resource|null $value
     * @param boolean $boolean
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
            [new \SplBool(true)       , true ],
            [new \SplBool(false)      , false],

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
    
    /**
     * @param \SplType
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a boolean, got
     * @dataProvider invalidBooleanProvider
     */
    public function testInvalidBoolean($value)
    {
        BooleanType::toBoolean($value);
    }
    
    public function invalidBooleanProvider()
    {
        return [
            [new \SplInt()],
            [new \SplFloat()],
            [new \SplString()],
            [new Enum()],
        ];
    }
}
