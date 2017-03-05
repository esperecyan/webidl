<?php
namespace esperecyan\webidl;

class RecordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed[][] $entries
     * @param mixed[][] $expected
     * @dataProvider entriesProvider
     */
    public function testForeach(array $entries, array $expected = [])
    {
        $actual = [];
        foreach (new Record($entries) as $key => $value) {
            $actual[] = [$key, $value];
        }
        $this->assertEquals($expected ?: $entries, $actual);
    }
    
    /**
     * @param mixed[][] $entries
     * @param mixed[][] $expected
     * @dataProvider entriesProvider
     */
    public function testIteratorToArray(array $entries, array $expected = [])
    {
        if (!$expected) {
            $expected = $entries;
        }
        $this->assertEquals(
            array_combine(array_column($expected, 0), array_column($expected, 1)),
            iterator_to_array(new Record($entries))
        );
    }
    
    /**
     * @param mixed[][] $entries
     * @param mixed[][] $expected
     * @dataProvider entriesProvider
     */
    public function testArrayAccess(array $entries, array $expected = [])
    {
        if (!$expected) {
            $expected = $entries;
        }
        $record = new Record($entries);
        $actual = [];
        foreach ($expected as $entry) {
            $actual[] = [$entry[0], $record[$entry[0]]];
        }
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @param mixed[][] $entries
     * @param mixed[][] $expected
     * @dataProvider entriesProvider
     */
    public function testGet(array $entries, array $expected = [])
    {
        if (!$expected) {
            $expected = $entries;
        }
        $record = new Record($entries);
        $actual = [];
        foreach ($expected as $entry) {
            $actual[] = [$entry[0], $record->{$entry[0]}];
        }
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @param mixed[][] $entries
     * @param mixed[][] $expected
     * @dataProvider entriesProvider
     */
    public function testIsset(array $entries, array $expected = [])
    {
        $record = new Record($entries);
        $this->assertFalse(isset($record['invalid']));
        $this->assertFalse(isset($record->invalid));
        foreach ($expected ?: $entries as $entry) {
            $this->assertTrue(isset($record[$entry[0]]));
            $this->assertTrue(isset($record->{$entry[0]}));
        }
    }
    
    public function entriesProvider()
    {
        return [
            [
                [
                    ['key1', 'value1'],
                    ['key2', 'value2'],
                    ['key3', 'value3'],
                ],
            ],
            [
                [
                    ['0', 'value1'],
                    ['1', 'value2'],
                    ['2', 'value3'],
                ],
            ],
            [
                [
                    ['key1', 'value1'],
                    ['key2', 'value2'],
                    ['key1', 'value3'],
                    ['key3', 'value4'],
                ],
                [
                    ['key1', 'value1'],
                    ['key2', 'value2'],
                    ['key3', 'value4'],
                ],
            ],
            [
                [
                    ['boolean', false              ],
                    ['integer', 0                  ],
                    ['float'  , 0.0                ],
                    ['array'  , []                 ],
                    ['object' , new \DOMException()],
                    ['NULL'   , null               ],
                ],
            ],
        ];
    }
    
    /**
     * @param \Closure $operation
     * @expectedException \PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage Indirect modification of overloaded element of esperecyan\webidl\Record has no effect
     * @dataProvider gettingOffsetProvider
     */
    public function testGetOffset(\Closure $operation)
    {
        $operation(new Record([['key', 'value']]));
    }
    
    public function gettingOffsetProvider()
    {
        return [
            [function (Record $record) {
                $record['key']++;
            }],
        ];
    }
    
    /**
     * @param \Closure $operation
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage An instance of esperecyan\webidl\Record is immutable
     * @dataProvider settingOffsetProvider
     */
    public function testSetOffset(\Closure $operation)
    {
        $operation(new Record([['key', 'value']]));
    }
    
    public function settingOffsetProvider()
    {
        return [
            [function (Record $record) {
                $record['key'] = 'value';
            }],
            [function (Record $record) {
                $record[] = 'value';
            }],
        ];
    }
}
