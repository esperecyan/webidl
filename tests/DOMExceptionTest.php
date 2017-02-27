<?php
namespace esperecyan\webidl;

class DOMExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var DOMException */
    protected $object;

    protected function setUp()
    {
        $this->object = new DOMException('error message', 'IndexSizeError');
    }
    
    /**
     * @param integer|string $codeOrName
     * @param string $name
     * @dataProvider codeProvider
     */
    public function testGetCode($codeOrName, $name)
    {
        $exception = (new DOMException('error message', $codeOrName));
        $this->assertSame($name, $exception->code);
        $this->assertSame($name, $exception->getCode());
    }
    
    public function codeProvider()
    {
        return [
            [DOM_INDEX_SIZE_ERR               , 'IndexSizeError'            ],
            [DOM_HIERARCHY_REQUEST_ERR        , 'HierarchyRequestError'     ],
            [DOM_WRONG_DOCUMENT_ERR           , 'WrongDocumentError'        ],
            [DOM_INVALID_CHARACTER_ERR        , 'InvalidCharacterError'     ],
            [DOM_NO_MODIFICATION_ALLOWED_ERR  , 'NoModificationAllowedError'],
            [DOM_NOT_FOUND_ERR                , 'NotFoundError'             ],
            [DOM_NOT_SUPPORTED_ERR            , 'NotSupportedError'         ],
            [DOM_INUSE_ATTRIBUTE_ERR          , 'InUseAttributeError'       ],
            [DOM_INVALID_STATE_ERR            , 'InvalidStateError'         ],
            [DOM_SYNTAX_ERR                   , 'SyntaxError'               ],
            [DOM_INVALID_MODIFICATION_ERR     , 'InvalidModificationError'  ],
            [DOM_NAMESPACE_ERR                , 'NamespaceError'            ],
            [DOM_INVALID_ACCESS_ERR           , 'InvalidAccessError'        ],
            [18                               , 'SecurityError'             ],
            ['19'                             , 'NetworkError'              ],
            [20                               , 'AbortError'                ],
            [21                               , 'URLMismatchError'          ],
            [22                               , 'QuotaExceededError'        ],
            [23                               , 'TimeoutError'              ],
            [24                               , 'InvalidNodeTypeError'      ],
            [25                               , 'DataCloneError'            ],
            ['EncodingError'                  , 'EncodingError'             ],
            ['NotReadableError'               , 'NotReadableError'          ],
            ['UnknownError'                   , 'UnknownError'              ],
            ['ConstraintError'                , 'ConstraintError'           ],
            ['DataError'                      , 'DataError'                 ],
            ['TransactionInactiveError'       , 'TransactionInactiveError'  ],
            ['ReadOnlyError'                  , 'ReadOnlyError'             ],
            ['VersionError'                   , 'VersionError'              ],
            ['OperationError'                 , 'OperationError'            ],
            ['NotAllowedError'                , 'NotAllowedError'           ],
            
            // invalid arguments
            [-1                               , '-1'                        ],
            [DOM_PHP_ERR                      , '0'                         ],
            [DOMSTRING_SIZE_ERR               , '2'                         ],
            [DOM_NO_DATA_ALLOWED_ERR          , '6'                         ],
            [DOM_VALIDATION_ERR               , '16'                        ],
            [17                               , '17'                        ],
            [26                               , '26'                        ],
            [''                               , ''                          ],
            ['NoDataAllowedError'             , 'NoDataAllowedError'        ],
            ['ValidationError'                , 'ValidationError'           ],
            [28                               , '28'                        ],
            ['5'                              , 'InvalidCharacterError'     ],
            [NAN                              , 'NAN'                       ],
            [INF                              , 'INF'                       ],
            [8.0                              , 'NotFoundError'             ],
            [NAN                              , 'NAN'                       ],
        ];
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
            ['DOMException'],
        ];
    }
    
    /**
     * @expectedException \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Missing argument 2 for esperecyan\webidl\DOMException::__construct()
     */
    public function testInvalidName2()
    {
        new DOMException('error message');
    }
    
    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage Object of class __PHP_Incomplete_Class could not be converted to int
     */
    public function testInvalidName3()
    {
        new DOMException('error message', new \__PHP_Incomplete_Class());
    }
    
    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage Array to string conversion
     */
    public function testInvalidName4()
    {
        new DOMException('error message', []);
    }
}
