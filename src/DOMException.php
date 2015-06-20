<?php
namespace esperecyan\webidl;

/**
 * This exception is that encapsulates a name, for compatibility with historically defined exceptions in the DOM.
 * @link https://heycam.github.io/webidl/#dfn-DOMException Web IDL (Second Edition)
 * @link https://heycam.github.io/webidl/#idl-DOMException Web IDL (Second Edition)
 */
class DOMException extends \DOMException implements Error
{
    /**
     * The array is an associative array with all the allowed error names as key and legacy integer code values.
     * @var (integer|null)[]
     * @link https://heycam.github.io/webidl/#idl-DOMException-error-names Web IDL (Second Edition)
     */
    private static $errorNamesAndLegacyIntegerCodeValues = [
        "IndexSizeError" => 1,
        "HierarchyRequestError" => 3,
        "WrongDocumentError" => 4,
        "InvalidCharacterError" => 5,
        "NoModificationAllowedError" => 7,
        "NotFoundError" => 8,
        "NotSupportedError" => 9,
        "InUseAttributeError" => 10,
        "InvalidStateError" => 11,
        "SyntaxError" => 12,
        "InvalidModificationError" => 13,
        "NamespaceError" => 14,
        "InvalidAccessError" => 15,
        "SecurityError" => 18,
        "NetworkError" => 19,
        "AbortError" => 20,
        "URLMismatchError" => 21,
        "QuotaExceededError" => 22,
        "TimeoutError" => 23,
        "InvalidNodeTypeError" => 24,
        "DataCloneError" => 25,
        "EncodingError" => null,
        "NotReadableError" => null,
        "UnknownError" => null,
        "ConstraintError" => null,
        "DataError" => null,
        "TransactionInactiveError" => null,
        "ReadOnlyError" => null,
        "VersionError" => null,
        "OperationError"  => null,
    ];
    
    /** @var string */
    public $code;
    
    /**
     * @param string $message The exception message to throw,
     *      which is an optional, that provides human readable details of the exception.
     * @param string|integer $code The error name, a string, which is the type of error the exception represents.
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message, $code, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $name = array_search($code, self::$errorNamesAndLegacyIntegerCodeValues);
        $this->code = $name && self::$errorNamesAndLegacyIntegerCodeValues[$name] ? $name : (string)$code;
    }
}
