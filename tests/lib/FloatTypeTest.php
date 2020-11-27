<?php
namespace esperecyan\webidl\lib;

class FloatTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param bool|int|float|string|array|object|resource|null $value
     * @param bool $castable
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

            // integer
            [-1                       , true ],
            [0                        , true ],
            [1                        , true ],
            
            // float
            [-0.1                     , true ],
            [0.0                      , true ],
            [0.1                      , true ],
            [NAN                      , true ],
            [INF                      , true ],
            [-INF                     , true ],
            
            // string
            [''                       , true ],
            ['string'                 , true ],
            ['9223372036854775808'    , true ],
            [gmp_init('9223372036854775808'), true],
            
            // array
            [[]                       , false],
            
            // object
            [new \stdClass()          , false], // without __toString()
            [new StringCastable('123'), false], // with __toString()
            [function () {
            }, false], // Callable
            
            // resource
            [tmpfile()                , true ],
            
            // NULL
            [null                     , false],
        ];
    }
    
    /**
     * @param bool|int|float|string $value
     * @param float $float
     * @dataProvider floatProvider
     */
    public function testToFloat($value, $float)
    {
        $this->assertSame($float, FloatType::toFloat($value));
        $this->assertSame($float, FloatType::toDouble($value));
    }
    
    /**
     * @param bool|int|float|string $value
     * @param float $float
     * @dataProvider floatProvider
     * @dataProvider unrestrictedFloatProvider
     */
    public function testToUnrestrictedFloat($value, $float)
    {
        $actualFloat = FloatType::toUnrestrictedFloat($value);
        $actualDouble = FloatType::toUnrestrictedDouble($value);
        if (is_nan($float)) {
            $this->assertNan($actualFloat);
            $this->assertNan($actualDouble);
        } else {
            $this->assertSame($float, $actualFloat);
            $this->assertSame($float, $actualDouble);
        }
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
            [               1.844674407371E+19,     1.844674407371E+19],
            
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
            [[]],
            [new \stdClass()],
            [function () {
            }],
            [null],
        ];
    }
}
