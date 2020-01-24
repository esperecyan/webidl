<?php
namespace esperecyan\webidl\lib;

/** @internal */
class StringType
{
    use Utility;
    
    /**
     * 与えられた値が文字列型に変換可能であれば真を返します。
     *
     * 次の型の値が文字列型に変換可能であるとみなされます。
     * 論理型。整数型。浮動小数点型。文字列型。null型。オブジェクト型のうち、__toString()メソッドを持つインスタンス。
     * @param mixed $value
     * @return bool
     */
    public static function isStringCastable($value)
    {
        return is_scalar($value) || $value === null
            || !($value instanceof \__PHP_Incomplete_Class) && method_exists($value, '__toString');
    }
    
    /**
     * toUSVString() のエイリアスです。
     * @link https://www.w3.org/TR/WebIDL-1/#idl-DOMString WebIDL Level 1
     * @param bool|int|float|string|object|null $value
     * @return string
     */
    public static function toDOMString($value)
    {
        return self::toUSVString($value);
    }

    /**
     * 与えられた値を文字列型に変換して返します。
     * @link https://www.w3.org/TR/WebIDL-1/#idl-ByteString WebIDL Level 1
     * @param bool|int|float|string|object|null $value
     * @throws \InvalidArgumentException 配列、__toString()メソッドなどを持たないオブジェクト、またはリソースが与えられた場合。
     * @return string
     */
    public static function toByteString($value)
    {
        if (self::isStringCastable($value)) {
            return (string)$value;
        } else {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, 'ByteString (a string)'));
        }
    }

    /**
     * 与えられた値を、符号化方式が UTF-8 である文字列型に変換して返します。
     * @link https://www.w3.org/TR/WebIDL-1/#idl-USVString WebIDL Level 1
     * @param bool|int|float|string|object|null $value
     * @throws \InvalidArgumentException 符号化方式が UTF-8 でない文字列、配列、__toString()メソッドなどを持たないオブジェクト、またはリソースが与えられた場合。
     * @return string
     */
    public static function toUSVString($value)
    {
        if (self::isStringCastable($value) && mb_check_encoding(($string = (string)$value), 'UTF-8')) {
            return $string;
        } else {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, 'USVString (a UTF-8 string)'));
        }
    }
    
    /**
     * 与えられた値を文字列型に変換し、列挙値のいずれかに一致するかチェックして返します。
     * @param string $value
     * @param string $identifier 列挙型の識別子。
     * @param string[] $enum 列挙値の配列。
     * @throws \InvalidArgumentException $value が文字列化できない場合。
     * @throws \DomainException $value が $enum で与えられた列挙値のいずれにも一致しなかった場合。
     * @return string
     */
    public static function toEnumerationValue($value, $identifier, $enum)
    {
        $expectedType = sprintf('DOMString (a UTF-8 string) and valid %s value', $identifier);
        
        try {
            $string = self::toDOMString($value);
        } catch (\InvalidArgumentException $exception) {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, $expectedType), 0, $exception);
        }
        
        if (!in_array($string, $enum, true)) {
            throw new \DomainException(ErrorMessageCreator::create($value, $expectedType));
        }
        
        return $string;
    }
}
