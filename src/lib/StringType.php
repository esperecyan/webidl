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
     * 論理型。整数型。浮動小数点型。文字列型。null型。オブジェクト型のうち、__toString()メソッドを持つインスタンス、SplStringのインスタンス、またはSplEnumのインスタンスで値が文字列であるもの。
     * @param mixed $value
     * @return boolean
     */
    public static function isStringCastable($value)
    {
        return is_scalar($value) || $value === null
            || is_object($value) /* __PHP_Incomplete_Class でないことを確認 */ && method_exists($value, '__toString')
            || $value instanceof \SplString
            || $value instanceof \SplEnum && is_string(current($value));
    }
    
    /**
     * toUSVString() のエイリアスです。
     * @link https://heycam.github.io/webidl/#idl-DOMString Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-DOMString Web IDL
     * @param boolean|integer|float|string|object|null $value
     * @return string
     */
    public static function toDOMString($value)
    {
        return self::toUSVString($value);
    }

    /**
     * 与えられた値を文字列型に変換して返します。
     * @link https://heycam.github.io/webidl/#idl-ByteString Web IDL (Second Edition)
     * @param boolean|integer|float|string|object|null $value
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
     * 与えられた値を、符号化方式が utf-8 である文字列型に変換して返します。
     * @link https://heycam.github.io/webidl/#idl-USVString Web IDL (Second Edition)
     * @param boolean|integer|float|string|object|null $value
     * @throws \InvalidArgumentException 符号化方式が utf-8 でない文字列、配列、__toString()メソッドなどを持たないオブジェクト、またはリソースが与えられた場合。
     * @return string
     */
    public static function toUSVString($value)
    {
        if (self::isStringCastable($value) && mb_check_encoding(($string = (string)$value), 'utf-8')) {
            return $string;
        } else {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, 'USVString (a utf-8 string)'));
        }
    }
    
    /**
     * 与えられた値を文字列型に変換し、列挙値のいずれかに一致するかチェックして返します。
     * @param string|\SplEnum $value
     * @param string $identifier 列挙型の識別子。
     * @param string[]|string $enum 列挙値の配列、または SplEnum を継承したクラスの完全修飾名。
     * @throws \InvalidArgumentException $value が文字列化できない場合。
     *      $value が SplEnum かつ $enum が配列である場合。または、$value が SplEnum かつ $enum がクラス名で、$value が $enum のインスタンスでない場合。
     * @throws \DomainException $value が $enum で与えられた列挙値のいずれにも一致しなかった場合。
     * @return string
     */
    public static function toEnumerationValue($value, $identifier, $enum)
    {
        $expectedType = sprintf('DOMString (a utf-8 string) and valid %s value', $identifier);
        
        try {
            $string = self::toDOMString($value);
        } catch (\InvalidArgumentException $exception) {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, $expectedType), 0, $exception);
        }
        
        if ($value instanceof \SplEnum && (!is_string($enum) || !($value instanceof $enum))) {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, $expectedType));
        } elseif (!in_array($string, is_string($enum) ? (new $enum())->getConstList() : $enum, true)) {
            throw new \DomainException(ErrorMessageCreator::create($value, $expectedType));
        }
        
        return $string;
    }
}
