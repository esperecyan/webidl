<?php
namespace esperecyan\webidl\lib;

class FloatTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param boolean|integer|float|string|array|object|resource|null $value
     * @param boolean $castable
     * @dataProvider floatCastableProvider
     */
    public function testIsFloatCastable($value, $castable)
    {
        $this->assertSame(FloatType::isFloatCastable($value), $castable);
    }
    
    public function floatCastableProvider()
    {
        return [
            // boolean
            [true                     , true ],
            [false                    , true ],
            [new \SplBool()           , false],

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
            [new \SplFloat()          , true ],
            
            // string
            [''                       , true ],
            ['string'                 , true ],
            [new \SplString('string') , false],
            ['9223372036854775808'    , true ],
            [gmp_init('9223372036854775808'), true],
            
            // array
            [[]                       , false],
            
            // object
            [new \stdClass()          , false], // without __toString()
            [new StringCastable('123'), false], // with __toString()
            [new Enum(),                false], // instance of SplEnum
            [function () {
            }, false], // Callable
            
            // resource
            [xml_parser_create()     , true ],
            
            // NULL
            [null                     , false],
        ];
    }
    
    /**
     * @param boolean|integer|float|string $value
     * @param float $float
     * @dataProvider floatProvider
     */
    public function testToFloat($value, $float)
    {
        $this->assertSame($float, FloatType::toFloat($value));
        $this->assertSame($float, FloatType::toDouble($value));
    }
    
    /**
     * @param boolean|integer|float|string $value
     * @param float $float
     * @dataProvider floatProvider
     * @dataProvider unrestrictedFloatProvider
     */
    public function testToUnrestrictedFloat($value, $float)
    {
        $this->assertSame($float, FloatType::toUnrestrictedFloat($value));
        $this->assertSame($float, FloatType::toUnrestrictedDouble($value));
    }
    
    public function floatProvider()
    {
        return [
            // boolean
            [                             true,                    1.0],
            [                            false,                    0.0],
            
            // integer
            [                               -1,                   -1.0],
            [                                0,                    0.0],
            [                       2147483647,           2147483647.0],
            [              9223372036854775807,  9223372036854775808.0],
            
            // float
            [                           -129.1,                 -129.1],
            [                              0.0,                    0.0],
            [                              0.1,                    0.1],
            [                            128.6,                  128.6],
            [new \SplFloat(1.844674407371E+19),     1.844674407371E+19],
            
            // string
            [                               '',                    0.0],
            [                         'string',                    0.0],
            [                           '-1.5',                   -1.5],
            [                             '-1',                   -1.0],
            [                            'NAN',                    0.0],
            [            '9223372036854775807',  9223372036854775808.0],
            [             '1.844674407371E+19',     1.844674407371E+19],
            [gmp_init('18446744073709551615'), 18446744073709551616.0],
        ];
    }
    
    public function unrestrictedFloatProvider()
    {
        return [
            [-INF, -INF],
            [ INF,  INF],
            [ NAN,  NAN],
        ];
    }
    
    /**
     * @param float|float $value
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected double (a float not NAN or INF), got
     * @dataProvider unrestrictedFloatProvider
     */
    public function testInvalidFloat($value)
    {
        FloatType::toFloat($value);
    }
    
    /**
     * @param object|array|null
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected double (a float not NAN or INF), got
     * @dataProvider invalidFloatProvider
     */
    public function testInvalidFloat2($value)
    {
        FloatType::toFloat($value);
    }
    
    /**
     * @param object|array|null
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected double (a float), got
     * @dataProvider invalidFloatProvider
     */
    public function testInvalidUnrestrictedFloat($value)
    {
        FloatType::toUnrestrictedFloat($value);
    }
    
    /**
     * @param float|float $value
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected double (a float not NAN or INF), got
     * @dataProvider unrestrictedFloatProvider
     */
    public function testInvalidDouble($value)
    {
        FloatType::toDouble($value);
    }
    
    /**
     * @param object|array|null
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected double (a float not NAN or INF), got
     * @dataProvider invalidFloatProvider
     */
    public function testInvalidDouble2($value)
    {
        FloatType::toDouble($value);
    }
    
    /**
     * @param object|array|null
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected double (a float), got
     * @dataProvider invalidFloatProvider
     */
    public function testInvalidUnrestrictedDouble($value)
    {
        FloatType::toUnrestrictedDouble($value);
    }
    
    public function invalidFloatProvider()
    {
        return [
            [new \SplBool()],
            [new \SplInt()],
            [new \SplString()],
            [[]],
            [new \stdClass()],
            [new Enum()],
            [function () {
            }],
            [null],
        ];
    }
}
