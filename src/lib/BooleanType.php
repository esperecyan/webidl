<?php
namespace esperecyan\webidl\lib;

/** @internal */
class BooleanType
{
    use Utility;
    
    /**
     * 与えられた値を論理型に変換して返します。
     * @link https://heycam.github.io/webidl/#idl-boolean Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-boolean Web IDL
     * @param mixed $value
     * @return boolean
     */
    public static function toBoolean($value)
    {
        return (boolean)$value;
    }
}
