<?php
namespace esperecyan\webidl\lib;

/** @internal */
class NullableType
{
    use Utility;
    
    /**
     * 与えられた値を、指定された型、または NULL 型に変換して返します。
     * @link https://www.w3.org/TR/WebIDL-1/#idl-nullable-type WebIDL Level 1
     * @param mixed $value
     * @param string $type nullable 型の内部型 (T? の T)。
     * @param array $pseudoTypes callback interface 型、列挙型、callback 関数型、または dictionary 型の識別子をキーとした型情報の配列。
     * @throws \InvalidArgumentException 指定された型、または NULL 型のいずれにも合致しない値が与えられた場合。
     * @throws \DomainException 指定された型、または NULL 型のいずれにも合致しない値が与えられた場合。
     * @return array
     */
    public static function toNullable($value, $type, $pseudoTypes = [])
    {
        if (is_null($value)) {
            $nullable = null;
        } else {
            try {
                $nullable = Type::to($type, $value, $pseudoTypes);
            } catch (\LogicException $exception) {
                $errorMessage = ErrorMessageCreator::create(null, sprintf('%s (%s or null)', $type . '?', $type), '');
                if ($exception instanceof \InvalidArgumentException) {
                    throw new \InvalidArgumentException($errorMessage, 0, $exception);
                } elseif ($exception instanceof \DomainException) {
                    throw new \DomainException($errorMessage, 0, $exception);
                } else {
                    throw $exception;
                }
            }
        }
        
        return $nullable;
    }
}
