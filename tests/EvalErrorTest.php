<?php
namespace esperecyan\webidl;

class EvalErrorTest extends \PHPUnit_Framework_TestCase
{
    /** @var EvalError */
    protected $object;

    protected function setUp()
    {
        $this->object = new EvalError();
    }
    
    public function testGetCode()
    {
        $this->assertSame('EvalError', $this->object->getCode());
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
