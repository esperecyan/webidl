<?php
namespace esperecyan\webidl\lib;

class StringTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param boolean|integer|float|string|array|object|resource|null $value
     * @param boolean $castable
     * @dataProvider stringCastableProvider
     */
    public function testIsStringCastable($value, $castable)
    {
        $this->assertSame($castable, StringType::isStringCastable($value));
    }
    
    public function stringCastableProvider()
    {
        return [
            // boolean
            [true                    , true ],
            [false                   , true ],
            [new \SplBool()          , false],

            // integer
            [-1                      , true ],
            [0                       , true ],
            [1                       , true ],
            [new \SplInt(0)          , false],
            
            // float
            [-0.1                    , true ],
            [0.0                     , true ],
            [0.1                     , true ],
            [NAN                     , true ],
            [INF                     , true ],
            [-INF                    , true ],
            [new \SplFloat(0.0)      , false],
            
            // string
            [''                      , true ],
            ['string'                , true ],
            [new \SplString('string'), true ],
            
            // array
            [[]                      , false],
            
            // object
            [new \stdClass()         , false], // without __toString()
            [new StringCastable('str'), true], // with __toString()
            [new \__PHP_Incomplete_Class(), false], // incomplete object
            [new Enum('string')      , true ], // instance of SplEnum
            [new Enum(null)          , false], // instance of SplEnum
            [function () {
            }, false], // Callable
            
            // resource
            [xml_parser_create()    , false],
            
            // NULL
            [null                    , true ],
        ];
    }
    
    /**
     * @param boolean|integer|float|string|object|null $value
     * @param string|true $string
     * @dataProvider stringProvider
     */
    public function testToDOMString($value, $string = true)
    {
        $returnValue = StringType::toDOMString($value);
        if ($string === true) {
            $this->assertInternalType('string', $returnValue);
        } else {
            $this->assertSame($string, $returnValue);
        }
    }
    
    /**
     * @param boolean|integer|float|string|object|null $value
     * @param string $string
     * @dataProvider stringProvider
     * @dataProvider byteStringProvider
     */
    public function testToByteString($value, $string = null)
    {
        $this->assertSame($string === null ? (string)$value : $string, StringType::toByteString($value));
    }
    
    /**
     * @param boolean|integer|float|string|object|null $value
     * @param string|true $string
     * @dataProvider stringProvider
     */
    public function testToUSVString($value, $string = true)
    {
        $returnValue = StringType::toUSVString($value);
        if ($string === true) {
            $this->assertInternalType('string', $returnValue);
        } else {
            $this->assertSame($string, $returnValue);
        }
    }
    
    public function stringProvider()
    {
        return [
            // boolean
            [true                 , '1'],
            [false                , ''],

            // integer
            [-1                   , '-1'],
            [0                    , '0' ],
            [1                    , '1' ],
            
            // float
            // The decimal point character depends on the script's locale
            [-0.1],
            [0.0 ],
            [0.1 ],
            [NAN                  , 'NAN'],
            [INF                  , 'INF'],
            
            // string
            [''                   , ''],
            ['string'             , 'string'],
            [new \SplString('str'), 'str'],
            
            // object with __toString()
            [new StringCastable('str'), 'str'],
            
            // NULL
            [null,                  ''],
        ];
    }
    
    public function byteStringProvider()
    {
        return [
            // UTF-16
            [mb_convert_encoding('PEAR (🍐)', 'UTF-16', 'UTF-8')],
            // binary
            [file_get_contents(__DIR__ . '/byte-string.png')],
        ];
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected USVString (a UTF-8 string), got
     * @dataProvider invalidStringProvider
     * @dataProvider byteStringProvider
     */
    public function testInvalidDOMString($value)
    {
        StringType::toDOMString($value);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected ByteString (a string), got
     * @dataProvider invalidStringProvider
     */
    public function testInvalidByteString($value)
    {
        StringType::toByteString($value);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected USVString (a UTF-8 string), got
     * @dataProvider invalidStringProvider
     * @dataProvider byteStringProvider
     */
    public function testInvalidUSVString($value)
    {
        StringType::toUSVString($value);
    }
    
    public function invalidStringProvider()
    {
        return [
            [new \SplBool()],
            [new \SplInt()],
            [new \SplFloat()],
            [[]],
            [new \stdClass()],
            [new \__PHP_Incomplete_Class()],
            [new Enum()],
            [function () {
            }],
            [xml_parser_create()],
        ];
    }
    
    /**
     * @param string|\SplEnum $value
     * @param string $identifier
     * @param string[]|string $enum
     * @param string $string
     * @dataProvider enumerationValueProvider
     */
    public function testToEnumerationValue($value, $identifier, $enum, $string)
    {
        $this->assertSame($string, StringType::toEnumerationValue($value, $identifier, $enum));
    }
    
    public function enumerationValueProvider()
    {
        return [
            ['string'          , 'EnumTest', ['string', 'string2'],           'string'],
            [new Enum('string'), 'EnumTest', 'esperecyan\\webidl\\lib\\Enum', 'string'],
            ['string'          , 'EnumTest', 'esperecyan\\webidl\\lib\\Enum', 'string'],
        ];
    }
    
    /**
     * @param mixed $value
     * @param string[]|string $enum
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected DOMString (a UTF-8 string) and valid EnumTest value, got
     * @dataProvider invalidStringProvider
     * @dataProvider byteStringProvider
     * @dataProvider invalidEnumerationValueProvider
     */
    public function testInvalidEnumerationValue($value, $enum = ['string', 'string2'])
    {
        StringType::toEnumerationValue($value, 'EnumTest', $enum);
    }
    
    public function invalidEnumerationValueProvider()
    {
        return [
            [new Enum(/* null */)     , 'esperecyan\\webidl\\lib\\Enum' ],
            [new InvalidEnum('string'), 'esperecyan\\webidl\\lib\\Enum' ],
            [new Enum('string')       , ['string', 'string2', 'string3']],
        ];
    }
    
    /**
     * @param string $value
     * @param string[]|string $enum
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected DOMString (a UTF-8 string) and valid EnumTest value, got
     * @dataProvider invalidEnumerationValueProvider2
     */
    public function testInvalidEnumerationValue3($value, $enum)
    {
        StringType::toEnumerationValue($value, 'EnumTest', $enum);
    }
    
    public function invalidEnumerationValueProvider2()
    {
        return [
            ['string4', ['string', 'string2', 'string3']],
            ['string4', 'esperecyan\\webidl\\lib\\Enum' ],
        ];
    }
}
