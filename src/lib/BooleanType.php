<?php
namespace esperecyan\webidl\lib;

class BooleanType
{
    use Utility;
    
    /**
     * 与えられた値が論理型に変換可能であれば真を返します。
     *
     * 次の型の値が論理型に変換可能であるとみなされます。
     * 論理型。整数型。浮動小数点型。文字列型。配列型。リソース型。null型。オブジェクト型のうち、SplType 以外のインスタンス、または SplBool のインスタンス。
     * @param mixed $value
     * @return boolean
     */
    public static function isBooleanCastable($value)
    {
        return !($value instanceof \SplType) || $value instanceof \SplBool;
    }

    /**
     * 与えられた値を論理型に変換して返します。
     * @link https://heycam.github.io/webidl/#idl-boolean Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-boolean Web IDL
     * @param mixed $value
     * @throws \InvalidArgumentException SplBool 以外の SplType が与えられた場合。
     * @return boolean
     */
    public static function toBoolean($value)
    {
        if (self::isBooleanCastable($value)) {
            return (boolean)$value;
        } else {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, 'a boolean'));
        }
    }
}
