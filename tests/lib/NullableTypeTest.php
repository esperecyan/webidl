<?php
namespace esperecyan\webidl\lib;

class NullableTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $value
     * @param string $type
     * @param array[]|null $pseudoTypes
     * @param mixed $returnValue
     * @dataProvider nullableProvider
     */
    public function testToNullable($value, $type, $pseudoTypes, $returnValue)
    {
        $this->assertSame($returnValue, NullableType::toNullable($value, $type, $pseudoTypes));
    }
    
    public function nullableProvider()
    {
        return [
            ['0'     , 'boolean'                   , null, false   ],
            [null    , 'boolean'                   , null, null    ],
            [false   , 'DOMString'                 , null, ''      ],
            [null    , 'DOMString'                 , null, null    ],
            ['string', '(DOMString or ArrayBuffer)', null, 'string'],
            [null    , '(DOMString or ArrayBuffer)', null, null    ],
            [true, '(DOMDocument or (Blob or BufferSource or FormData or URLSearchParams or USVString))', null, '1'],
            [$doc = new \DOMDocument(), '(DOMDocument or (Blob or BufferSource or FormData or URLSearchParams or USVString))', null, $doc],
            [null, '(DOMDocument or (Blob or BufferSource or FormData or URLSearchParams or USVString))', null, null],
        ];
    }
    
    /**
     * @param mixed $value
     * @param string $type
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Expected .+\? \(.+ or null\)$/u
     * @dataProvider invalidNullableProvider
     */
    public function testInvalidNullable($value, $type)
    {
        NullableType::toNullable($value, $type);
    }
    
    public function invalidNullableProvider()
    {
        return [
            [new \stdClass(), 'DOMString'],
            [new \stdClass(), '(DOMString or ArrayBuffer)'],
            [new \stdClass(), '(DOMDocument or (Blob or BufferSource or FormData or URLSearchParams or USVString))'],
        ];
    }

    /**
     * @param mixed $value
     * @param string $type
     * @expectedException \DomainException
     * @expectedExceptionMessageRegExp /^Expected .+\? \(.+ or null\)$/u
     * @dataProvider invalidNullableProvider2
     */
    public function testInvalidNullable2($value, $type)
    {
        NullableType::toNullable($value, $type);
    }
    public function invalidNullableProvider2()
    {
        return [
            [-1                          , '[EnforceRange] octet'],
            ['invalid regular expression', 'RegExp'              ],
            [['string', new \stdClass()] , 'DOMString[]'         ],
        ];
    }
}
