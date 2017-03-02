<?php
namespace esperecyan\webidl;

class TypeHinter
{
    use lib\Utility;

    /**
     * Converts a given value in accordance with a given IDL type.
     * @link https://github.com/esperecyan/webidl/blob/master/README.md#esperecyanwebidltypehintertotype-value-argnum-pseudotypes webidl/README.md at master · esperecyan/webidl
     * @param string $type The IDL type.
     * @param mixed $value The value being converted.
     * @param int $argNum The argument offset that received the value being converted.
     *      Arguments are counted starting from zero.  If the caller method is __set(), this argument are ignored.
     * @param (string|string[]|array)[] The associative array with the identifiers of
     *      callback interface types, enumeration types, callback function types or dictionary types
     *      (the strings passed in $type) as key.
     *      For the corresponding values, see the link.
     * @throws \InvalidArgumentException
     *      If $value is not castable. The exception includes a message with method name etc.
     * @throws \DomainException If $value is not castable. The exception includes a message with method name etc.
     * @return mixed
     */
    public static function to($type, $value, $argNum = 0, $pseudoTypes = [])
    {
        try {
            return lib\Type::to($type, $value, $pseudoTypes);
        } catch (\LogicException $exception) {
            $callerInfomation = self::getCallerInfomation();
            $errorMessage = $callerInfomation['function'] === '__set'
                ? sprintf(
                    'Value set to %s is not of the expected type',
                    $callerInfomation['class'] . '::' . $callerInfomation['args'][0]
                )
                : sprintf(
                    'Argument %d passed to %s is not of the expected type',
                    $argNum + 1,
                    $callerInfomation['class'] . '::' . $callerInfomation['function'] . '()'
                );
            if ($exception instanceof \DomainException) {
                throw new \DomainException($errorMessage, 0, $exception);
            } elseif ($exception instanceof \InvalidArgumentException) {
                throw new \InvalidArgumentException($errorMessage, 0, $exception);
            } else {
                throw $exception;
            }
        }
    }

    /**
     * Throws an exception with a message that represents a read-only property. Must call from __set() method.
     * @link https://github.com/esperecyan/webidl/blob/master/README.md#esperecyanwebidltypehinterthrowreadonlyexception webidl/README.md at master · esperecyan/webidl
     * @link https://www.w3.org/TR/WebIDL-1/#dfn-read-only WebIDL Level 1
     * @link https://wiki.php.net/rfc/propertygetsetsyntax#read-only_and_write-only_properties PHP: rfc:propertygetsetsyntax
     * @throws \BadMethodCallException 呼び出し元のメソッドが __set() 以外の場合。
     * @throws \LogicException An exception with a message that represents a read-only property.
     */
    public static function throwReadonlyException()
    {
        $callerInfomation = self::getCallerInfomation();
        if ($callerInfomation['function'] === '__set') {
            throw new \LogicException(sprintf(
                'Cannot write to readonly public property %s',
                $callerInfomation['class'] . '::' . $callerInfomation['args'][0]
            ));
        } else {
            throw new \BadMethodCallException(sprintf('%s must call inside a %s method', __METHOD__ . '()', '__set()'));
        }
    }

    /**
     * If a user tries setting to a private or protected property, it will trigger a fatal error.
     * If a user tries setting to a non-existing property, it will create a new public property.
     * Must call from __set() method.
     * @link https://github.com/esperecyan/webidl/blob/master/README.md#esperecyanwebidltypehinterthrowreadonlyexception webidl/README.md at master · esperecyan/webidl
     * @link https://www.w3.org/TR/WebIDL-1/#dfn-read-only WebIDL Level 1
     * @link https://wiki.php.net/rfc/propertygetsetsyntax#read-only_and_write-only_properties PHP: rfc:propertygetsetsyntax
     * @throws \BadMethodCallException 呼び出し元のメソッドが __set() 以外の場合。
     */
    public static function triggerVisibilityErrorOrDefineProperty()
    {
        $callerInfomation = self::getCallerInfomation();
        if ($callerInfomation['function'] === '__set') {
            if (!self::triggerVisibilityError($callerInfomation['class'], $callerInfomation['args'][0])) {
                \Closure::bind(function ($instance, $name, $value) {
                    $instance->{$name} = $value;
                }, null, $callerInfomation['object'])->__invoke(
                    $callerInfomation['object'],
                    $callerInfomation['args'][0],
                    $callerInfomation['args'][1]
                );
            }
        } else {
            throw new \BadMethodCallException(sprintf('%s must call inside a %s method', __METHOD__ . '()', '__set()'));
        }
    }

    /**
     * If a user tries setting to a private or protected property, it will trigger a fatal error.
     * If a user tries getting to a non-existing property, it will trigger a notice. Must call from __get() method.
     * @link https://github.com/esperecyan/webidl/blob/master/README.md#esperecyanwebidltypehintertriggervisibilityerrororundefinednotice webidl/README.md at master · esperecyan/webidl
     * @throws \BadMethodCallException 呼び出し元のメソッドが __get() 以外の場合。
     */
    public static function triggerVisibilityErrorOrUndefinedNotice()
    {
        $callerInfomation = self::getCallerInfomation();
        if ($callerInfomation['function'] === '__get') {
            if (!self::triggerVisibilityError($callerInfomation['class'], $callerInfomation['args'][0])) {
                trigger_error(sprintf(
                    'Undefined property: %s',
                    $callerInfomation['class'] . '::' . $callerInfomation['args'][0]
                ), E_USER_NOTICE);
            }
        } else {
            throw new \BadMethodCallException(sprintf('%s must call inside a %s method', __METHOD__ . '()', '__get()'));
        }
    }
    
    /**
     * アクセス権エラーを発生させる。
     * @param string $className 完全修飾クラス名。
     * @param string $propertyName プロパティ名 (クラス名を含まない)。
     * @return bool 成功した場合に真を、プロパティが存在しなかった場合に偽を返します。
     */
    private static function triggerVisibilityError($className, $propertyName)
    {
        $existed = property_exists($className, $propertyName);
        if ($existed) {
            trigger_error(sprintf(
                'Cannot access %s property %s',
                (new \ReflectionProperty($className, $propertyName))->isPrivate() ? 'private' : 'protected',
                $className . '::' . $propertyName
            ), E_USER_ERROR);
        }
        return $existed;
    }
    
    /**
     * 呼び出し元のメソッドから getCallerInfomation() を実行するまでに含まれうる最大のスタックフレーム数。
     * @internal
     */
    const STACK_FRAME_LIMIT = 5;
    
    /**
     * 呼び出し元のメソッドと引数の値を取得します。
     * @throws \BadMethodCallException クラスに属さない関数、または関数やメソッドの外から呼び出した場合。
     * @return (string|null|array|object)[]|null debug_backtrace() が返す要素と同じ。
     *      ただし function キーは、トレイトのエイリアス名ではなく元のメソッド名を返します。
     */
    private static function getCallerInfomation()
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, self::STACK_FRAME_LIMIT) as $stack) {
            if (isset($stack['class']) && $stack['class'] !== __CLASS__) {
                $stack['function'] = (new \ReflectionMethod($stack['class'], $stack['function']))->name;
                return $stack;
            }
        }
        
        throw new \BadMethodCallException(sprintf('%s must use inside a method', __CLASS__));
    }
}
