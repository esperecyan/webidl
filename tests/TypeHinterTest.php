<?php
namespace esperecyan\webidl;

class TypeHinterTest extends ParentClass
{
    /**
     * @param mixed $value
     * @param string $type
     * @param array[]|null $pseudoTypes
     * @param mixed $returnValue
     * @dataProvider toProvider
     */
    public function testTo($value, $type, $returnValue)
    {
        $actualReturnValue = $this->callee($value, $type);
        if ($actualReturnValue instanceof Record) {
            $entries = [];
            foreach ($actualReturnValue as $key => $value) {
                $entries[] = [$key, $value];
            }
            $actualReturnValue = $entries;
        }
        $this->assertSame($returnValue, $actualReturnValue);
    }
    
    public function callee($value, $type)
    {
        return TypeHinter::to($type, $value, 0, [
            'CustomEventInit' => [
                'bubbles'    => ['type' => 'boolean', 'default' => false],
                'cancelable' => ['type' => 'boolean', 'default' => false],
                'detail'     => ['type' => 'any'    , 'default' => null ],
            ],
        ]);
    }
    
    public function toProvider()
    {
        return [
            [
                0,
                'DOMString?',
                '0',
            ],
            [
                null,
                'DOMString?',
                null,
            ],
            [
                $value = new \DateTime(),
                '(double or (DateTime or Event) or (DOMNode or DOMString)?',
                $value,
            ],
            [
                [
                    [0.1, 0.2],
                    ['0'],
                ],
                '(DOMNode or Event or (XMLHttpRequest or DOMString)? or sequence<(sequence<double> or DOMNodeList)>)',
                [
                    [0.1, 0.2],
                    [0.0],
                ],
            ],
            [
                'string',
                '(DOMNode or DOMString)',
                'string',
            ],
            [
                'string',
                '(USVString or URLSearchParams)',
                'string',
            ],
            [
                [
                    'bubbles'    => null,
                    'cancelable' => 'string',
                    'detail'     => 0.0,
                ],
                'CustomEventInit',
                [
                    'bubbles'    => false,
                    'cancelable' => true,
                    'detail'     => 0.0,
                ],
            ],
            [
                ($array = [new \DOMException(), new ErrorClass(), new TypeError()]),
                'FrozenArray<Error>',
                $array,
            ],
            [
                ['key1' => 'value1', 'key2' => 'value2'],
                '(sequence<sequence<USVString>> or record<USVString, USVString> or USVString)',
                [['key1', 'value1'], ['key2', 'value2']],
            ],
        ];
    }
    
    /**
     * @param mixed $value
     * @param string $type
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to esperecyan\webidl\TypeHinterTest::callee() is not of the expected type
     * @dataProvider invalidTypeProvider
     */
    public function testInvalidArgumentType($value, $type)
    {
        $this->callee($value, $type);
    }
    
    /**
     * @param mixed $value
     * @param string $type
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to esperecyan\webidl\ParentClass::callee() is not of the expected type
     * @dataProvider invalidTypeProvider
     */
    public function testInvalidArgumentTypeParent($value, $type)
    {
        parent::callee($value, $type);
    }
    
    public function invalidTypeProvider()
    {
        return [
            [
                new \stdClass(),
                '(double or (Date or Event) or (DOMNode or DOMString)?)',
            ],
            [
                new \stdClass(),
                '(USVString or URLSearchParams)',
            ],
        ];
    }
    
    /**
     * @param mixed $value
     * @param string $type
     * @param mixed $returnValue
     * @expectedException \DomainException
     * @expectedExceptionMessage Argument 1 passed to esperecyan\webidl\TypeHinterTest::callee() is not of the expected type
     * @dataProvider invalidTypeProvider2
     */
    public function testInvalidType2($value, $type)
    {
        $this->callee($value, $type);
    }
    
    public function invalidTypeProvider2()
    {
        return [
            [
                [
                    [1.5, null],
                ],
                '(DOMNode or Event or (XMLHttpRequest or DOMString)? or sequence<(sequence<double> or DOMNodeList)>)',
            ],
        ];
    }
    
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Value set to esperecyan\webidl\TypeHinterTest::octetProperty is not of the expected type
     */
    public function testInvalidPropertyType()
    {
        $this->__set('octetProperty', -1);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Value set to esperecyan\webidl\TypeHinterTest::octetProperty is not of the expected type
     */
    public function testInvalidPropertyType2()
    {
        $this->__set('octetProperty', null);
    }
    
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot write to readonly public property esperecyan\webidl\TypeHinterTest::readonlyProperty
     */
    public function testReadonlyProperty()
    {
        $this->__set('readonlyProperty', 'value');
    }
    
    /** @used-by TypeHinterTest::testSetPrivateProperty() */
    private $privateProperty;
    
    /**
     * @expectedException \PHPUnit\Framework\Error\Error
     * @expectedExceptionMessage Cannot access private property esperecyan\webidl\TypeHinterTest::privateProperty
     */
    public function testSetPrivateProperty()
    {
        $this->__set('privateProperty', 'value');
    }
    
    /**
     * @expectedException \PHPUnit\Framework\Error\Error
     * @expectedExceptionMessage Cannot access protected property esperecyan\webidl\TypeHinterTest::protectedProperty
     */
    public function testSetProtectedProperty()
    {
        $this->__set('protectedProperty', 'value');
    }
    
    protected $protectedProperty;
    
    public function testSetUndefinedProperty()
    {
        $this->__set('property', 'value');
        $this->assertSame('value', $this->property);
    }
    
    public function __set($name, $value)
    {
        switch ($name) {
            case 'octetProperty':
                TypeHinter::to('[EnforceRange] octet', $value);
                break;
            
            case 'readonlyProperty':
                TypeHinter::throwReadonlyException();
                break;
            
            default:
                TypeHinter::triggerVisibilityErrorOrDefineProperty();
        }
    }
    
    /**
     * @expectedException \PHPUnit\Framework\Error\Error
     * @expectedExceptionMessage Cannot access private property esperecyan\webidl\TypeHinterTest::privateProperty
     */
    public function testGetPrivateProperty()
    {
        $this->__get('privateProperty', 'value');
    }
    
    /**
     * @expectedException \PHPUnit\Framework\Error\Error
     * @expectedExceptionMessage Cannot access protected property esperecyan\webidl\TypeHinterTest::protectedProperty
     */
    public function testGetProtectedProperty()
    {
        $this->__get('protectedProperty', 'value');
    }
    
    /**
     * @expectedException \PHPUnit\Framework\Error\Notice
     * @expectedExceptionMessage Undefined property: esperecyan\webidl\TypeHinterTest::undefinedProperty
     */
    public function testGetUndefinedProperty()
    {
        $this->__get('undefinedProperty');
    }
    
    public function __get($name)
    {
        TypeHinter::triggerVisibilityErrorOrUndefinedNotice();
    }
}
