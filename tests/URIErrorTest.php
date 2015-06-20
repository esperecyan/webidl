<?php
namespace esperecyan\webidl;

class URIErrorTest extends \PHPUnit_Framework_TestCase
{
    /** @var URIError */
    protected $object;

    protected function setUp()
    {
        $this->object = new URIError();
    }
    
    public function testGetCode()
    {
        $this->assertSame('URIError', $this->object->getCode());
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
