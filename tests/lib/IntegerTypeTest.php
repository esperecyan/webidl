<?php
namespace esperecyan\webidl\lib;

class IntegerTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param bool|int|float|string|array|object|resource|null $value
     * @param bool $castable
     * @dataProvider integerCastableProvider
     */
    public function testIsIntegerCastable($value, $castable)
    {
        $this->assertSame($castable, IntegerType::isIntegerCastable($value));
    }
    
    public function integerCastableProvider()
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
            [xml_parser_create()     , true ],
            
            // NULL
            [null                     , false],
        ];
    }
    
    /**
     * @param bool|int|float|string $value
     * @param int|null $enforcedByte
     * @param int $clampedByte
     * @param int $byte
     * @dataProvider byteProvider
     */
    public function testToByte($value, $enforcedByte, $clampedByte, $byte)
    {
        if ($enforcedByte !== null) {
            $this->assertSame($enforcedByte, IntegerType::toByte($value, '[EnforceRange]'));
        }
        $this->assertSame($clampedByte, IntegerType::toByte($value, '[Clamp]'));
        $this->assertSame($byte, IntegerType::toByte($value));
    }
    
    public function byteProvider()
    {
        return [
            // boolean
            [true ,    1,    1,    1],
            [false,    0,    0,    0],
            
            // integer
            [ -129, null, -128,  127],
            [ -128, -128, -128, -128],
            [    0,    0,    0,    0],
            [  127,  127,  127,  127],
            [  128, null,  127, -128],
            
            // float
            [ -INF, null, -128,    0],
            [-129.1,null, -128,  127],
            [-128.9,-128, -128, -128],
            [0.1   ,   0,    0,    0],
            [128.6 ,null,  127, -128],
            [  INF, null,  127,    0],
            [  NAN, null,    0,    0],
            
            // string
            [''   ,    0,    0,    0],
            ['string', 0,    0,    0],
            ['-129',null, -128,  127],
            ['-126',-126, -126, -126],
            ['-1' ,   -1,   -1,   -1],
            ['125',  125,  125,  125],
            ['129', null,  127, -127],
        ];
    }
    
    /**
     * @param int|float $value
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected byte (an integer in the range of -128 to 127), got
     * @dataProvider invalidEnforcedByteProvider
     */
    public function testInvalidEnforcedByte($value)
    {
        IntegerType::toByte($value, '[EnforceRange]');
    }
    
    public function invalidEnforcedByteProvider()
    {
        return [
            [-INF],
            [-129],
            [ 128],
            [ INF],
            [ NAN],
        ];
    }
    
    /**
     * @param object|array|null
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected byte (an integer in the range of -128 to 127), got
     * @dataProvider invalidByteProvider
     */
    public function testInvalidByte($value)
    {
        IntegerType::toByte($value);
    }
    
    public function invalidByteProvider()
    {
        return [
            [[]],
            [new \stdClass()],
            [function () {
            }],
            [null],
        ];
    }
    
    /**
     * @param int|float $value
     * @param int|null $enforcedOctet
     * @param int $clampedOctet
     * @param int $octet
     * @dataProvider octetProvider
     */
    public function testToOctet($value, $enforcedOctet, $clampedOctet, $octet)
    {
        if ($enforcedOctet !== null) {
            $this->assertSame($enforcedOctet, IntegerType::toOctet($value, '[EnforceRange]'));
        }
        $this->assertSame($clampedOctet, IntegerType::toOctet($value, '[Clamp]'));
        $this->assertSame($octet, IntegerType::toOctet($value));
    }
    
    public function octetProvider()
    {
        return [
            [-INF, null,    0,    0],
            [  -1, null,    0,  255],
            [   0,    0,    0,    0],
            [ 255,  255,  255,  255],
            [ 256, null,  255,    0],
            [ INF, null,  255,    0],
            [ NAN, null,    0,    0],
        ];
    }
    
    /**
     * @param int|float $value
     * @param int|null $enforcedShort
     * @param int $clampedShort
     * @param int $short
     * @dataProvider shortProvider
     */
    public function testToShort($value, $enforcedShort, $clampedShort, $short)
    {
        if ($enforcedShort !== null) {
            $this->assertSame($enforcedShort, IntegerType::toShort($value, '[EnforceRange]'));
        }
        $this->assertSame($clampedShort, IntegerType::toShort($value, '[Clamp]'));
        $this->assertSame($short, IntegerType::toShort($value));
    }
    
    public function shortProvider()
    {
        return [
            [  -INF,   null, -32768,      0],
            [-32769,   null, -32768,  32767],
            [-32768, -32768, -32768, -32768],
            [ 32767,  32767,  32767,  32767],
            [ 32768,   null,  32767, -32768],
            [   INF,   null,  32767,      0],
            [   NAN,   null,      0,      0],
        ];
    }
    
    /**
     * @param int|float $value
     * @param int|null $enforcedUnsignedShort
     * @param int $clampedUnsignedShort
     * @param int $unsignedShort
     * @dataProvider unsignedShortProvider
     */
    public function testToUnsignedShort($value, $enforcedUnsignedShort, $clampedUnsignedShort, $unsignedShort)
    {
        if ($enforcedUnsignedShort !== null) {
            $this->assertSame($enforcedUnsignedShort, IntegerType::toUnsignedShort($value, '[EnforceRange]'));
        }
        $this->assertSame($clampedUnsignedShort, IntegerType::toUnsignedShort($value, '[Clamp]'));
        $this->assertSame($unsignedShort, IntegerType::toUnsignedShort($value));
    }
    
    public function unsignedShortProvider()
    {
        return [
            [  -INF,  null,      0,      0],
            [    -1,  null,      0,  65535],
            [     0,     0,      0,      0],
            [ 65535, 65535,  65535,  65535],
            [ 65536,  null,  65535,      0],
            [   INF,  null,  65535,      0],
            [   NAN,  null,      0,      0],
        ];
    }
    
    /**
     * 2147483648 is float if PHP_INT_SIZE is 4.
     * @param int|float $value
     * @param int|null $enforcedLong
     * @param int $clampedLong
     * @param int $long
     * @dataProvider longProvider
     */
    public function testToLong($value, $enforcedLong, $clampedLong, $long)
    {
        if ($enforcedLong !== null) {
            $this->assertSame($enforcedLong, IntegerType::toLong($value, '[EnforceRange]'));
        }
        $this->assertSame($clampedLong, IntegerType::toLong($value, '[Clamp]'));
        $this->assertSame($long, IntegerType::toLong($value));
    }
    
    public function longProvider()
    {
        /** Integer literal -2147483648 is float -2147483648 if PHP_INT_SIZE is 4. */
        $longMin = (int)'-2147483648';
        return [
            [       -INF,        null,    $longMin,           0],
            [-2147483649,        null,    $longMin,  2147483647],
            [   $longMin,    $longMin,    $longMin,    $longMin],
            [ 2147483647,  2147483647,  2147483647,  2147483647],
            [ 2147483648,        null,  2147483647,    $longMin],
            [        INF,        null,  2147483647,           0],
            [        NAN,        null,           0,           0],
        ];
    }
    
    /**
     * 4294967295 and 4294967296 is float if PHP_INT_SIZE is 4.
     * @param int|float $value
     * @param int|float|null $enforcedUnsignedLong
     * @param int|float $clampedUnsignedLong
     * @param int|float $unsignedLong
     * @dataProvider unsignedLongProvider
     */
    public function testToUnsignedLong($value, $enforcedUnsignedLong, $clampedUnsignedLong, $unsignedLong)
    {
        if ($enforcedUnsignedLong !== null) {
            $this->assertSame($enforcedUnsignedLong, IntegerType::toUnsignedLong($value, '[EnforceRange]'));
        }
        $this->assertSame($clampedUnsignedLong, IntegerType::toUnsignedLong($value, '[Clamp]'));
        $this->assertSame($unsignedLong, IntegerType::toUnsignedLong($value));
    }
    
    public function unsignedLongProvider()
    {
        return [
            [       -INF,        null,           0,           0],
            [         -1,        null,           0,  4294967295],
            [          0,           0,           0,           0],
            [ 4294967295,  4294967295,  4294967295,  4294967295],
            [ 4294967296,        null,  4294967295,           0],
            [        INF,        null,  4294967295,           0],
            [        NAN,        null,           0,           0],
        ];
    }
    
    /**
     * Integer literal -9223372036854775809 is float -9223372036854775808 if PHP_INT_SIZE is 4 and 8.
     * -9223372036854775808, -9007199254740992 and 9007199254740992 are float if PHP_INT_SIZE is 4.
     * Integer literal 9223372036854775807 is float 9223372036854775808 if PHP_INT_SIZE is 4.
     * -9223372036854777856, -9223372036854775808 and 9223372036854775808 are float if PHP_INT_SIZE is 4 or 8.
     * @param int|float $value
     * @param int|float|null $enforcedLongLong
     * @param int|float $clampedLongLong
     * @param int|float $longLong
     * @dataProvider longLongProvider
     */
    public function testToLongLong($value, $enforcedLongLong, $clampedLongLong, $longLong)
    {
        if ($enforcedLongLong !== null) {
            $this->assertSame($enforcedLongLong, IntegerType::toLongLong($value, '[EnforceRange]'));
        }
        $this->assertSame($clampedLongLong, IntegerType::toLongLong($value, '[Clamp]'));
        $this->assertSame($longLong, IntegerType::toLongLong($value));
    }
    
    public function longLongProvider()
    {
        return \PHP_INT_SIZE === 4 ? [
            [                     -INF,                     null,        -9007199254740991,                        0],
            [     -9223372036854777856,                     null,        -9007199254740991,      9223372036854773760],
            [     -9223372036854775808,                     null,        -9007199254740991,     -9223372036854775808],
            [        -9007199254740992,                     null,        -9007199254740991,        -9007199254740992],
            [         9007199254740992,                     null,         9007199254740991,         9007199254740992],
            [      9223372036854775807,                     null,         9007199254740991,     -9223372036854775808],
            [      9223372036854775808,                     null,         9007199254740991,     -9223372036854775808],
            [                      INF,                     null,         9007199254740991,                        0],
            [                      NAN,                     null,                        0,                        0],
        ] : [
            [                     -INF,                     null,(int)-9223372036854775808,                        0],
            [     -9223372036854777856,                     null,(int)-9223372036854775808,      9223372036854773760],
            [(int)-9223372036854775808,(int)-9223372036854775808,(int)-9223372036854775808,(int)-9223372036854775808],
            [        -9007199254740992,        -9007199254740992,        -9007199254740992,        -9007199254740992],
            [         9007199254740992,         9007199254740992,         9007199254740992,         9007199254740992],
            [      9223372036854775806,      9223372036854775806,      9223372036854775806,      9223372036854775806],
            [      9223372036854775807,      9223372036854775807,      9223372036854775807,      9223372036854775807],
            [      9223372036854775808,                     null,      9223372036854775807,(int)-9223372036854775808],
            [                      INF,                     null,      9223372036854775807,                        0],
            [                      NAN,                     null,                        0,                        0],
        ];
    }
    
    /**
     * Integer literal 18446744073709551615 is float 18446744073709551616 if PHP_INT_SIZE is 4 or 8.
     * 18446744073709551616 is float if PHP_INT_SIZE is 4 or 8.
     * @param int|float $value
     * @param int|float|null $enforcedUnsignedLongLong
     * @param int|float $clampedUnsignedLongLong
     * @param int|float $unsignedLongLong
     * @dataProvider unsignedLongLongProvider
     */
    public function testToUnsignedLongLong($value, $enforcedUnsignedLongLong, $clampedUnsignedLongLong, $unsignedLongLong)
    {
        if ($enforcedUnsignedLongLong !== null) {
            $this->assertSame($enforcedUnsignedLongLong, IntegerType::toUnsignedLongLong($value, '[EnforceRange]'));
        }
        $this->assertSame($clampedUnsignedLongLong, IntegerType::toUnsignedLongLong($value, '[Clamp]'));
        $this->assertSame($unsignedLongLong, IntegerType::toUnsignedLongLong($value));
    }
    
    public function unsignedLongLongProvider()
    {
        if (\PHP_INT_SIZE === 4) {
            $max = 9007199254740991;
        } elseif (\PHP_INT_SIZE === 8) {
            $max = \PHP_INT_MAX;
        } else {
            $max = 18446744073709551615;
        }
        return [
            [                            -INF, null,    0,                    0],
            [                              -1, null,    0, 18446744073709551615],
            [                               0,    0,    0,                    0],
            [       1.8446744073709551616E+19, null, $max,                    0],
            [     '1.8446744073709551616E+19', null, $max,                    0],
            [            18446744073709551615, null, $max,                    0],
            [          '18446744073709551615', null, $max,                    0],
            [gmp_init('18446744073709551615'), null, $max,                    0],
            [            18446744073709551616, null, $max,                    0],
            [          '18446744073709551616', null, $max,                    0],
            [                             INF, null, $max,                    0],
            [                             NAN, null,    0,                    0],
        ];
    }
}
