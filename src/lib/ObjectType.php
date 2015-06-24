<?php
namespace esperecyan\webidl\lib;

/** @internal */
class ObjectType
{
    use Utility;

    /**
     * 与えられた値をオブジェクト型に変換して返します。
     * @link https://heycam.github.io/webidl/#idl-object Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-object Web IDL
     * @param mixed $value
     * @return object
     */
    public static function toObject($value)
    {
        return (object)$value;
    }

    /**
     * 与えられた値が、指定されたクラスのインスタンス、指定されたクラスを継承したクラスのインスタンス、または指定されたインターフェースを実装したクラスのインスタンスかチェックして返します。
     * @link https://heycam.github.io/webidl/#idl-interface Web IDL (Second Edition)
     * @link https://heycam.github.io/webidl/#idl-interfaces Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-interface Web IDL
     * @link http://www.w3.org/TR/WebIDL/#idl-interfaces Web IDL
     * @param object $value
     * @param string $fullyQualifiedName クラス、またはインターフェースの完全修飾名。
     * @throws \InvalidArgumentException オブジェクト以外が与えられた場合、または指定されたクラス名、インターフェース名に合致しないオブジェクトが与えられた場合。
     * @return object
     */
    public static function toInterface($value, $fullyQualifiedName)
    {
        if ($value instanceof $fullyQualifiedName) {
            return $value;
        } else {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, sprintf(
                interface_exists($fullyQualifiedName) ? 'an instance of a class implementing %s' : 'an instance of %s',
                $fullyQualifiedName
            )));
        }
    }

    /**
     * 与えられた値をオブジェクト型、または callable に変換して返します。
     * @link https://heycam.github.io/webidl/#idl-interface Web IDL (Second Edition)
     * @link https://heycam.github.io/webidl/#dfn-callback-interface Web IDL (Second Edition)
     * @link https://heycam.github.io/webidl/#dfn-single-operation-callback-interface Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-interface Web IDL
     * @link http://www.w3.org/TR/WebIDL/#dfn-callback-interface Web IDL
     * @link http://www.w3.org/TR/WebIDL/#dfn-single-operation-interface Web IDL
     * @param object|callable $value
     * @param boolean $singleOperationCallbackInterface 単一演算 callback interface であれば真。
     * @throws \InvalidArgumentException 与えられた値がオブジェクトでも配列でもない場合。
     *      ただし $singleOperationCallbackInterface が真であれば、callable である文字列に対しては例外を発生させない。
     * @return object|callable $singleOperationCallbackInterface が偽なら常にオブジェクト。
     *      $singleOperationCallbackInterface が真なら、callbale の場合がある。
     */
    public static function toCallbackInterface($value, $singleOperationCallbackInterface = false)
    {
        
        if ($singleOperationCallbackInterface && is_callable($value)) {
            $callbackInterface = $value;
        } elseif (!is_scalar($value) && !is_resource($value) && !is_null($value)) {
            $callbackInterface = (object)$value;
        } else {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, $singleOperationCallbackInterface
                ? 'a single operation callback interface (a object, array or callable)'
                : 'a callback interface (a object or array)'));
        }
        return $callbackInterface;
    }

    /**
     * 与えられた値が callable かチェックして返します。
     * @link https://heycam.github.io/webidl/#idl-callback-function Web IDL (Second Edition)
     * @link https://heycam.github.io/webidl/#idl-callback-functions Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-callback-function Web IDL
     * @link http://www.w3.org/TR/WebIDL/#idl-callback-functions Web IDL
     * @param callable $value
     * @throws \InvalidArgumentException callable でない値が与えられた場合。
     * @return object
     */
    public static function toCallbackFunction($value)
    {
        if (is_callable($value)) {
            return $value;
        } else {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($value, 'a callback function (a callable)'));
        }
    }
}
