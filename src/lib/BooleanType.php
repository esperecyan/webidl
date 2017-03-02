<?php
namespace esperecyan\webidl\lib;

/** @internal */
class BooleanType
{
    use Utility;
    
    /**
     * 与えられた値を論理型に変換して返します。
     * @link https://www.w3.org/TR/WebIDL-1/#idl-boolean WebIDL Level 1
     * @param mixed $value
     * @return bool
     */
    public static function toBoolean($value)
    {
        return (bool)$value;
    }
}
