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
     * 論理型。整数型。浮動小数点型。文字列型。リソース型。オブジェクト型のうち、GMPのインスタンス。
     * @param mixed $value
     * @return bool
     */
    public static function isFloatCastable($value)
    {
        return is_scalar($value) || is_resource($value) || $value instanceof \GMP;
    }
    
    /**
     * toDouble() のエイリアスです。
     * @deprecated 1.1.0 double should be used rather than float.
     * @link https://www.w3.org/TR/WebIDL-1/#idl-float WebIDL Level 1
     * @param bool|int|float|string|resource|\GMP $value
     * @return string
     */
    public static function toFloat($value)
    {
        return self::toDouble($value);
    }
    
    /**
     * toUnrestrictedDouble() のエイリアスです。
     * @deprecated 1.1.0 double should be used rather than float.
     * @link https://www.w3.org/TR/WebIDL-1/#idl-unrestricted-float WebIDL Level 1
     * @param bool|int|float|string|resource|\GMP $value
     * @return string
     */
    public static function toUnrestrictedFloat($value)
    {
        return self::toUnrestrictedDouble($value);
    }

    /**
     * 与えられた値を、NAN、INFを含まない浮動小数点型に変換して返します。
     * @link https://www.w3.org/TR/WebIDL-1/#idl-double WebIDL Level 1
     * @param bool|int|float|string|resource|\GMP $value
     * @throws \InvalidArgumentException 配列、NULL が与えられた場合。または、GMP 以外のオブジェクトが与えられた場合。
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
     * @link https://www.w3.org/TR/WebIDL-1/#idl-unrestricted-double WebIDL Level 1
     * @param bool|int|float|string|resource|\GMP $value
     * @throws \InvalidArgumentException 配列、NULL が与えられた場合。または、GMP 以外のオブジェクトが与えられた場合。
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
