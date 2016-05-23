<?php
namespace esperecyan\webidl\lib;

class RegExpTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param boolean|integer|float|string|array|object|resource|null $value
     * @param boolean $castable
     * @dataProvider regExpCastableProvider
     */
    public function testIsRegExpCastable($value, $castable)
    {
        $this->assertSame($castable, RegExpType::isRegExpCastable($value));
    }
    
    public function regExpCastableProvider()
    {
        return [
            // boolean
            [true                       , false],
            [false                      , false],
            [new \SplBool()             , false],

            // integer
            [1                          , false],
            [new \SplInt()              , false],
            
            // float
            [0.1                        , false],
            [NAN                        , false],
            [INF                        , false],
            [-INF                       , false],
            [new \SplFloat()            , false],
            
            // string
            [''                         , true ],
            ['str'                      , true ],
            ["/\x00/"                   , true ],
            ['/\\x00/'                  , true ],
            ["\xE6str\xE6"              , false], // delimiters is invalid byte sequence for UTF-8
            ['(str('                    , true ],
            ['{str{'                    , true ],
            ['[str['                    , true ],
            ['<str<'                    , true ],
            ['/str/Z'                   , true ], // invalid modifier
            ['/str/e'                   , true ], // deprecated modifier
            ["/\xC0\xAF/"               , false], // 0xC0 0xAF is a overlong form for "/"
            ['/(str/'                   , true ], // invalid regular expression
            ['/' . mb_convert_encoding('ｓｔｒ', 'UTF-16', 'UTF-8') . '/', false],
            ['/str/'                    , true ],
            [new \SplString('/str/')    , true ],
            
            // array
            [[]                         , false],
            
            // object
            [new \stdClass()            , false], // without __toString()
            [new StringCastable('/str/'), true ], // with __toString()
            [new StringCastable('str'),   true ], // with __toString()
            [new Enum()                 , false], // instance of SplEnum
            [function () {
            }, false], // Callable
            
            // resource
            [xml_parser_create()       , false],
            
            // NULL
            [null                       , false],
        ];
    }
    
    /**
     * @param string|\SplString $value
     * @param string $regExp
     * @dataProvider regExpProvider
     */
    public function testToRegExp($value, $regExp)
    {
        $this->assertSame($regExp, RegExpType::toRegExp($value));
    }
    
    public function regExpProvider()
    {
        return [
            ['/str/'                    , '/str/'],
            [new \SplString('/str/')    , '/str/'],
            [new StringCastable('/str/'), '/str/'],
            ['/str/'                    , '/str/'],
            ['{\\p{L}}'                 , '{\\p{L}}'],
            ['[[a-zA-Z]]'               , '[[a-zA-Z]]'],
            ['/PEAR (🍐)/'              , '/PEAR (🍐)/'],
            ['/PEAR (🍐)/u'             , '/PEAR (🍐)/u'],
            ["\x01\x01i"                 , "\x01\x01i"],
            ["\t\n\v\f\r /str/ \niuiu"  , "\t\n\v\f\r /str/ \niuiu"],
            ["\t\n\v\f\r (s(t)r) \niuiu", "\t\n\v\f\r (s(t)r) \niuiu"],
        ];
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected RegExp (a UTF-8 string and valid regular expression pattern), got
     * @dataProvider invalidRegExpProvider
     */
    public function testInvalidRegExp($value)
    {
        RegExpType::toRegExp($value, __METHOD__, 2);
    }
    
    public function invalidRegExpProvider()
    {
        return [
            // boolean
            [true],
            [false],
            [new \SplBool()],

            // integer
            [1],
            [new \SplInt()],
            
            // float
            [0.1],
            [NAN],
            [INF],
            [-INF],
            [new \SplFloat()],
            
            // string
            ["\xE6str\xE6"], // delimiters is invalid byte sequence for UTF-8
            ["/\xC0\xAF/"], // 0xC0 0xAF is a overlong form for "/"
            ['/' . mb_convert_encoding('ｓｔｒ', 'UTF-16', 'UTF-8') . '/'],
            
            // array
            [[]],
            
            // object
            [new \stdClass()], // without __toString()
            [new Enum()], // instance of SplEnum
            [function () {
            }], // Callable
            
            // resource
            [xml_parser_create()],
            
            // NULL
            [null],
        ];
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected RegExp (a UTF-8 string and valid regular expression pattern). Empty regular expression
     */
    public function testInvalidRegExp2()
    {
        RegExpType::toRegExp('', __METHOD__, 2);
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected RegExp (a UTF-8 string and valid regular expression pattern). Delimiter must not be alphanumeric or backslash
     */
    public function testInvalidRegExp3()
    {
        RegExpType::toRegExp('str', __METHOD__, 2);
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected RegExp (a UTF-8 string and valid regular expression pattern). Null byte in regex
     */
    public function testInvalidRegExp4()
    {
        RegExpType::toRegExp("/\x00/", __METHOD__, 2);
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected RegExp (a UTF-8 string and valid regular expression pattern). No ending matching delimiter ')' found
     */
    public function testInvalidRegExp5()
    {
        RegExpType::toRegExp('(str(', __METHOD__, 2);
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected RegExp (a UTF-8 string and valid regular expression pattern). Unknown modifier 'Z'
     */
    public function testInvalidRegExp6()
    {
        RegExpType::toRegExp('/str/Z', __METHOD__, 2);
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected RegExp (a UTF-8 string and valid regular expression pattern). The /e modifier is deprecated, use preg_replace_callback instead
     */
    public function testInvalidRegExp7()
    {
        RegExpType::toRegExp('/str/e', __METHOD__, 2);
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Expected RegExp (a UTF-8 string and valid regular expression pattern). Compilation failed: missing ) at offset 4
     */
    public function testInvalidRegExp8()
    {
        RegExpType::toRegExp('/(str/', __METHOD__, 2);
    }
}
