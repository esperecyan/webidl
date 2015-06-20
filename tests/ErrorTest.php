<?php
namespace esperecyan\webidl;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ErrorClass */
    protected $object;

    protected function setUp()
    {
        $this->object = new ErrorClass();
    }
    
    public function testGetCode()
    {
        $this->assertSame('Error', $this->object->getCode());
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
            ['RuntimeException'],
        ];
    }
}
