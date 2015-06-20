<?php
namespace esperecyan\webidl\lib;

class ErrorMessageCreator
{
    use Utility;
    
    /**
     * タイプヒンティング用のエラーメッセージを生成する。
     * @param mixed $value 与えられた値。
     * @param string $expectedType 要求される型。
     * @param string|null $message 指定されていれば、$valueを無視し、代わりに追加のメッセージとして表示する。
     * @return string
     */
    public static function create($value, $expectedType, $message = null)
    {
        if ($message === null) {
            $errorMessage = sprintf('Expected %s, got %s', $expectedType, self::getStringRepresentation($value));
        } elseif ($message === '') {
            $errorMessage = sprintf('Expected %s', $expectedType);
        } else {
            $errorMessage = sprintf('Expected %s. %s', $expectedType, $message);
        }
        return $errorMessage;
    }
    
    /**
     * 与えられた値の文字列表現を取得する。
     * @param mixed $value
     * @return string
     */
    public static function getStringRepresentation($value)
    {
        if (is_scalar($value) || $value === null) {
            $stringRepresentation = is_string($value) && !mb_check_encoding($value, 'utf-8')
                ? 'non utf-8 string'
                : var_export($value, true);
        } elseif (is_array($value)) {
            $stringRepresentation = 'array';
        } elseif (is_resource($value)) {
            $stringRepresentation = 'resource of type (' . get_resource_type($value) . ')';
        } else {
            $stringRepresentation = 'instance of ' . get_class($value);
        }
        return $stringRepresentation;
    }
}
