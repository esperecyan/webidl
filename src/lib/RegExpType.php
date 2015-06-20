<?php
namespace esperecyan\webidl\lib;

class RegExpType
{
    use Utility;
    
    /**
     * 与えられた値が妥当な正規表現の文字列に変換され得る型であれば真を返します。
     *
     * 次の型の値が妥当な正規表現の文字列に変換され得る型であるとみなされます。
     * 文字列型。オブジェクト型のうち、__toString()メソッドを持つインスタンス、SplStringのインスタンス。
     * @param mixed $value
     * @return boolean
     */
    public static function isRegExpCastable($value)
    {
        return (is_string($value) || method_exists($value, '__toString') || $value instanceof \SplString)
            && mb_check_encoding($value, 'utf-8');
    }

    /**
     * 与えられた値が妥当な正規表現 (PCRE) パターンかチェックして返します。
     * @link https://heycam.github.io/webidl/#idl-RegExp Web IDL (Second Edition)
     * @param string $value
     * @throws \InvalidArgumentException 論理値、整数、浮動小数点数、符号化方式が utf-8 でない文字列、配列、
     *      __toString()メソッドなどを持たないオブジェクト、リソース、または NULL が与えられた場合。
     * @throws \DomainException 妥当でない正規表現パターンが与えられた場合
     * @return string
     */
    public static function toRegExp($value)
    {
        $expectedType = 'RegExp (a utf-8 string and valid regular expression pattern)';
        
        if (!self::isRegExpCastable($value)) {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, $expectedType));
        }
        
        $string = (string)$value;
        
        set_error_handler(function ($severity, $message) use ($expectedType) {
            if (strpos($message, 'preg_replace(): ') === 0) {
                throw new \DomainException(ErrorMessageCreator::create(
                    null,
                    $expectedType,
                    str_replace('preg_replace(): ', '', $message)
                ));
            } else {
                return false;
            }
        }, E_WARNING | E_DEPRECATED);
        preg_replace($string, '', '');
        restore_error_handler();
        
        if (PHP_VERSION_ID < 50500 && preg_match('/e[\\n a-zA-Z]*$/u', $string) === 1) {
            throw new \DomainException(ErrorMessageCreator::create(
                null,
                $expectedType,
                'The /e modifier is deprecated, use preg_replace_callback instead'
            ));
        }
        
        return $string;
    }
}
