<?php
namespace esperecyan\webidl;

class TypeErrorTest extends \PHPUnit_Framework_TestCase
{
    /** @var TypeError */
    protected $object;

    protected function setUp()
    {
        $this->object = new TypeError();
    }
    
    public function testGetCode()
    {
        $this->assertSame('TypeError', $this->object->getCode());
    }
    
    /**
     * @param string $name
     * @dataProvider extendsAndImplements
     */
    public function testExtendsAndImplements($name)
    {
        $this->assertInstanceOf($name, $this->object);
    }
    
    public function extendsAndImplements()
    {
        return [
            ['esperecyan\\webidl\\Error'],
            ['UnexpectedValueException'],
        ];
    }
}
