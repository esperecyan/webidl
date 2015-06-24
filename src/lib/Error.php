<?php
namespace esperecyan\webidl\lib;

/** @internal */
trait Error
{
    /**
     * @param string $message The exception message to throw,
     *      which is an optional, that provides human readable details of the exception.
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->code = (new \ReflectionClass(__CLASS__))->getShortName();
    }
}
