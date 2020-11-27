<?php
namespace esperecyan\webidl;

class ReferenceErrorTest extends \PHPUnit\Framework\TestCase
{
    /** @var ReferenceError */
    protected $object;

    protected function setUp(): void
    {
        $this->object = new ReferenceError();
    }
    
    public function testGetCode()
    {
        $this->assertSame('ReferenceError', $this->object->getCode());
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
