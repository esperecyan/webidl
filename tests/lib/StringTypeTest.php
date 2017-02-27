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

            // integer
            [-1                      , true ],
            [0                       , true ],
            [1                       , true ],
            
            // float
            [-0.1                    , true ],
            [0.0                     , true ],
            [0.1                     , true ],
            [NAN                     , true ],
            [INF                     , true ],
            [-INF                    , true ],
            
            // string
            [''                      , true ],
            ['string'                , true ],
            
            // array
            [[]                      , false],
            
            // object
            [new \stdClass()         , false], // without __toString()
            [new StringCastable('str'), true], // with __toString()
            [new \__PHP_Incomplete_Class(), false], // incomplete object
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
            [[]],
            [new \stdClass()],
            [new \__PHP_Incomplete_Class()],
            [function () {
            }],
            [xml_parser_create()],
        ];
    }
    
    /**
     * @param string $value
     * @param string $identifier
     * @param string[] $enum
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
            ['string', 'EnumTest', ['string', 'string2'], 'string'],
        ];
    }
    
    /**
     * @param string $value
     * @param string[] $enum
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected DOMString (a UTF-8 string) and valid EnumTest value, got
     * @dataProvider invalidEnumerationValueProvider
     */
    public function testInvalidEnumerationValue($value, $enum)
    {
        StringType::toEnumerationValue($value, 'EnumTest', $enum);
    }
    
    public function invalidEnumerationValueProvider()
    {
        return [
            ['string4', ['string', 'string2', 'string3']],
        ];
    }
}
