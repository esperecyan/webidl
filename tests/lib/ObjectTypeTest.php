<?php
namespace esperecyan\webidl\lib;

class ObjectTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param mixed $value
     * @param mixed $returnValue
     * @dataProvider objectProvider
     */
    public function testToObject($value, $returnValue)
    {
        $this->{is_object($value) ? 'assertSame' : 'assertEquals'}($returnValue, ObjectType::toObject($value));
    }
    
    public function objectProvider()
    {
        return [
            [$doc = new \DOMDocument(), $doc                      ],
            [true                     , (object)true              ],
            [null                     , new \stdClass()           ],
            [['block' => 'end']       , (object)['block' => 'end']],
        ];
    }
    
    /**
     * @param mixed $value
     * @param string $fullyQualifiedClassName
     * @param mixed $returnValue
     * @dataProvider interfaceProvider
     */
    public function testToInterface($value, $fullyQualifiedClassName, $returnValue)
    {
        $this->assertSame($returnValue, ObjectType::toInterface($value, $fullyQualifiedClassName));
    }
    
    public function interfaceProvider()
    {
        return [
            [$obj = new \stdClass()   , 'stdClass'   , $obj],
            [$doc = new \DOMDocument(), 'DOMDocument', $doc],
        ];
    }
    
    /**
     * @param mixed $value
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected an instance of ArrayObject, got
     * @dataProvider invalidInterfaceProvider
     */
    public function testInvalidInterface($value)
    {
        ObjectType::toInterface($value, 'ArrayObject');
    }
    
    /**
     * @param mixed $value
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected an instance of a class implementing Iterator, got
     * @dataProvider invalidInterfaceProvider
     */
    public function testInvalidInterface2($value)
    {
        ObjectType::toInterface($value, 'Iterator');
    }
    
    public function invalidInterfaceProvider()
    {
        return [
            [true],
            [0],
            [0.0],
            ['string'],
            [[]],
            [new \stdClass()],
            [xml_parser_create()], // resource
            [null],
        ];
    }
    
    /**
     * @param mixed $value
     * @param booelan $singleOperationCallbackInterface
     * @param object $returnValue
     * @dataProvider callbackInterfaceProvider
     */
    public function testToCallbackInterface($value, $singleOperationCallbackInterface, $returnValue)
    {
        $this->{is_object($value) ? 'assertSame' : 'assertEquals'}(
            $returnValue,
            ObjectType::toCallbackInterface($value, $singleOperationCallbackInterface)
        );
    }
    
    public function callbackInterfaceProvider()
    {
        return [
            [$object = new \stdClass()        , false, $object                                    ],
            [$doc = new \DOMDocument()        , false, $doc                                       ],
            [['handleEvent' => function () {
            }], false, (object)['handleEvent' => function () {
            }]],
            [[], false, new \stdClass()],
            [['DateTime', 'createFromFormat'], false, (object)['DateTime', 'createFromFormat']],
            [['DateTime', 'createFromFormat'], true , ['DateTime', 'createFromFormat']        ],
            ['var_dump'                      , true , 'var_dump'                              ],
            [$incomplete = new \__PHP_Incomplete_Class(), false, $incomplete],
        ];
    }
    
    /**
     * @param mixed $value
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a callback interface (a object or array), got
     * @dataProvider invalidCallbackInterfaceProvider
     * @dataProvider invalidNonSingleOperationCallbackInterfaceProvider
     */
    public function testInvalidCallbackInterface($value)
    {
        ObjectType::toCallbackInterface($value);
    }
    
    /**
     * @param mixed $value
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a single operation callback interface (a object, array or callable), got
     * @dataProvider invalidCallbackInterfaceProvider
     */
    public function testInvalidSingleOperationCallbackInterface($value)
    {
        ObjectType::toCallbackInterface($value, true);
    }
    
    public function invalidCallbackInterfaceProvider()
    {
        return [
            [true],
            [0],
            [0.0],
            ['string'],
            [xml_parser_create()], // resource
            [null],
        ];
    }
    
    public function invalidNonSingleOperationCallbackInterfaceProvider()
    {
        return [
            ['var_dump'],
        ];
    }
    
    /**
     * @param callable $value
     * @dataProvider callbackFunctionProvider
     */
    public function testToCallbackFunction($value)
    {
        $this->assertSame(ObjectType::toCallbackFunction($value), $value);
    }
    
    /* @used-by self::testToCallbackFunction() */
    public function __invoke()
    {
    }
    
    public function callbackFunctionProvider()
    {
        return [
            ['var_dump'],
            ['DateTime::createFromFormat'],
            [['DateTime', 'createFromFormat']],
            [[new \DOMDocument(), 'getElementById']],
            [[new \DOMDocument(), 'parent::removeChild']],
            [$this],
            [function () {
            }],
        ];
    }
    
    /**
     * @param int|float $value
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a callback function (a callable), got
     * @dataProvider invalidInterfaceProvider
     * @dataProvider invalidCallbackFunctionProvider
     */
    public function testInvalidCallbackFunction($value)
    {
        ObjectType::toCallbackFunction($value);
    }
    
    public function invalidCallbackFunctionProvider()
    {
        return [
            ['esperecyan\\undefinedFunction'],
            ['DateTime::undefinedMethod'],
            ['DateTime', 'undefinedMethod'],
            [[new \DOMDocument(), 'undefinedMethod']],
            [new \DOMDocument()],
        ];
    }
}
