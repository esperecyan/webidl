<?php
namespace esperecyan\webidl;

/**
 * This class is defined to construct an exception having the error name "Error".
 * If you catch an exception having the error name "Error", use esperecyan\webidl\Error.
 * @see Error
 */
class ErrorClass extends \RuntimeException implements Error
{
    /**
     * @param string $message
     * @param \Exception $previous
     */
    public function __construct($message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->code = 'Error';
    }
}
