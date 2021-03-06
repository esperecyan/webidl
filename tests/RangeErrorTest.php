<?php
namespace esperecyan\webidl;

class RangeErrorTest extends \PHPUnit\Framework\TestCase
{
    /** @var RangeError */
    protected $object;

    protected function setUp(): void
    {
        $this->object = new RangeError();
    }
    
    public function testGetCode()
    {
        $this->assertSame('RangeError', $this->object->getCode());
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
            ['RangeException'],
        ];
    }
}
