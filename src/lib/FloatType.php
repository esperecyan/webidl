<?php
namespace esperecyan\webidl\lib;

/** @internal */
class FloatType
{
    use Utility;
    
    /**
     * 与えられた値が浮動小数点型に変換可能であれば真を返します。
     *
     * 次の型の値が浮動小数点型に変換可能であるとみなされます。
     * 論理型。整数型。浮動小数点型。文字列型。リソース型。オブジェクト型のうち、GMP、または SplFloat のインスタンス。
     * @param mixed $value
     * @return boolean
     */
    public static function isFloatCastable($value)
    {
        return is_scalar($value) || is_resource($value) || $value instanceof \GMP || $value instanceof \SplFloat;
    }
    
    /**
     * toDouble() のエイリアスです。
     * @deprecated 1.1.0 float is dis-recommended in Web IDL (Second Edition).
     * @link https://heycam.github.io/webidl/#idl-float Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-float Web IDL
     * @param boolean|integer|float|string|resource|\GMP|\SplFloat $value
     * @return string
     */
    public static function toFloat($value)
    {
        return self::toDouble($value);
    }
    
    /**
     * toUnrestrictedDouble() のエイリアスです。
     * @deprecated 1.1.0 float is dis-recommended in Web IDL (Second Edition).
     * @link https://heycam.github.io/webidl/#idl-unrestricted-float Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-unrestricted-float Web IDL
     * @param boolean|integer|float|string|resource|\GMP|\SplFloat $value
     * @return string
     */
    public static function toUnrestrictedFloat($value)
    {
        return self::toUnrestrictedDouble($value);
    }

    /**
     * 与えられた値を、NAN、INFを含まない浮動小数点型に変換して返します。
     * @link https://heycam.github.io/webidl/#idl-double Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-double Web IDL
     * @param boolean|integer|float|string|resource|\GMP|\SplFloat $value
     * @throws \InvalidArgumentException 配列、NULL が与えられた場合。または、GMP、SplFloat 以外のオブジェクトが与えられた場合。
     * @throws \DomainException 変換後の値が、NAN、INF、-INF のいずれかになった場合。
     * @return float
     */
    public static function toDouble($value)
    {
        $expectedType = 'double (a float not NAN or INF)';
        
        try {
            $float = self::toUnrestrictedDouble($value);
        } catch (\InvalidArgumentException $exeception) {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, $expectedType));
        }
        
        if (is_finite($float)) {
            return $float;
        } else {
            throw new \DomainException(ErrorMessageCreator::create($value, $expectedType));
        }
    }

    /**
     * 与えられた値を、浮動小数点型に変換して返します。
     * @link https://heycam.github.io/webidl/#idl-unrestricted-double Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-unrestricted-double Web IDL
     * @param boolean|integer|float|string|resource|\GMP|\SplFloat $value
     * @throws \InvalidArgumentException 配列、NULL が与えられた場合。または、GMP、SplFloat 以外のオブジェクトが与えられた場合。
     * @return float
     */
    public static function toUnrestrictedDouble($value)
    {
        if (self::isFloatCastable($value)) {
            return (float)($value instanceof \GMP || is_resource($value) && get_resource_type($value) === 'GMP integer'
                ? gmp_strval($value)
                : $value);
        } else {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, 'double (a float)'));
        }
    }
}
