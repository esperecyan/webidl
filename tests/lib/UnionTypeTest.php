<?php
namespace esperecyan\webidl\lib;

class UnionTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $value
     * @param string $unitTypeString
     * @param array[]|null $pseudoTypes
     * @param mixed $returnValue
     * @dataProvider unionProvider
     */
    public function testToUnion($value, $unitTypeString, $pseudoTypes = null, $returnValue = null)
    {
        $this->assertEquals(
            $returnValue === null ? $value : $returnValue,
            UnionType::toUnion($value, $unitTypeString, $pseudoTypes)
        );
    }
    
    public function unionProvider()
    {
        return [
            [
                null,
                '(double or (DateTime or Event) or (DOMNode or DOMString)?)',
            ],
            [
                'string',
                '(DOMNode or Event or (XMLHttpRequest or DOMString)? or sequence<(sequence<double> or DOMNodeList)>)',
            ],
            [
                'string',
                '(DOMNode or DOMString)',
            ],
            [
                'string',
                '(USVString or URLSearchParams)',
            ],
            [
                ['string'],
                '(DOMString or FrozenArray<DOMString>)',
            ],
            [
                ['memberA' => true],
                '(SomeDictionary or sequence<DOMString>)',
                ['SomeDictionary' => [
                    'memberA' => [
                        'type' => 'DOMString',
                    ],
                ]],
                ['memberA' => '1'],
            ],
        ];
    }
    
    /**
     * @param string $unionTypeString
     * @param string[] $flattenedMemberTypes
     * @param integer $numberOfNullableMemberTypes
     * @dataProvider unionTypeStringProvider
     */
    public function testGetFlattenedTypesAndNullableNums($unionTypeString, $flattenedMemberTypes, $numberOfNullableMemberTypes)
    {
        $this->assertSame(
            [
                'flattenedMemberTypes' => $flattenedMemberTypes,
                'numberOfNullableMemberTypes' => $numberOfNullableMemberTypes,
            ],
            UnionType::getFlattenedTypesAndNullableNums($unionTypeString)
        );
    }
    
    public function unionTypeStringProvider()
    {
        return [
            [
                '(double or (DateTime or Event) or (DOMNode or DOMString)?)',
                ['double', 'DateTime', 'Event', 'DOMNode', 'DOMString'],
                1,
            ],
            [
                '(DOMNode or Event or (XMLHttpRequest or DOMString)? or sequence<(sequence<double> or DOMNodeList)>)',
                ['DOMNode', 'Event', 'XMLHttpRequest', 'DOMString', 'sequence<(sequence<double> or DOMNodeList)>'],
                1,
            ],
            [
                '(DOMNode or DOMString)',
                ['DOMNode', 'DOMString'],
                0,
            ],
            [
                '(USVString or URLSearchParams)',
                ['USVString', 'URLSearchParams'],
                0,
            ],
            [
                '(ArrayBuffer or ArrayBufferView or Blob or DOMString)',
                ['ArrayBuffer', 'ArrayBufferView', 'Blob', 'DOMString'],
                0,
            ],
            [
                '(DOMString or ArrayBuffer)',
                ['DOMString', 'ArrayBuffer'],
                0,
            ],
            [
                '(Headers or sequence<sequence<ByteString>> or OpenEndedDictionary<ByteString>)',
                ['Headers', 'sequence<sequence<ByteString>>', 'OpenEndedDictionary<ByteString>'],
                0,
            ],
            [
                '(Headers or sequence<sequence<(ByteString or Dummy)>> or OpenEndedDictionary<(ByteString or Dummy)>)',
                ['Headers', 'sequence<sequence<(ByteString or Dummy)>>', 'OpenEndedDictionary<(ByteString or Dummy)>'],
                0,
            ],
            [
                '(Blob or BufferSource or FormData or URLSearchParams or USVString)',
                ['Blob', 'BufferSource', 'FormData', 'URLSearchParams', 'USVString'],
                0,
            ],
            [
                '(Request or USVString)',
                ['Request', 'USVString'],
                0,
            ],
            [
                '(File or USVString)',
                ['File', 'USVString'],
                0,
            ],
            [
                'DOMDocument or BodyInit',
                ['DOMDocument', 'BodyInit'],
                0,
            ],
            [
                'File or USVString',
                ['File', 'USVString'],
                0,
            ],
            [
                'Element or ProcessingInstruction',
                ['Element', 'ProcessingInstruction'],
                0,
            ],
            [
                'boolean or object',
                ['boolean', 'object'],
                0,
            ],
            [
                'DOMText or DOMElement or PseudoElement or DOMDocument',
                ['DOMText', 'DOMElement', 'PseudoElement', 'DOMDocument'],
                0,
            ],
        ];
    }
    
    /**
     * @param mixed $value
     * @param string $type
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Expected .+, got/u
     * @dataProvider invalidUnionProvider
     */
    public function testInvalidUnion($value, $type)
    {
        UnionType::toUnion($value, $type);
    }
    
    public function invalidUnionProvider()
    {
        return [
            [
                new \stdClass(),
                '(double or (DateTime or Event) or (DOMNode or DOMString)?)',
            ],
            [
                new \stdClass(),
                '(USVString or URLSearchParams)',
            ],
        ];
    }
}
