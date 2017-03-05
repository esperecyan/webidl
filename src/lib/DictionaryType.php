<?php
namespace esperecyan\webidl\lib;

use esperecyan\webidl\Record;

/** @internal */
class DictionaryType
{
    use Utility;
    
    /**
     * 与えられた値を文字列キーでアクセス可能な値に変換する。
     * @param mixed $value
     * @return array|\ArrayAccess
     */
    private static function convertToArrayAccess($value)
    {
        if ($value instanceof \ArrayAccess) {
            $array = $value;
        } elseif ($value instanceof \Traversable) {
            $array = SequenceType::convertToRewindable($value);
            if ($array instanceof \Traversable) {
                set_error_handler(function ($severity, $message) {
                    return $severity === E_WARNING
                        ? $message === 'Illegal offset type'
                        : preg_match('/^Resource ID#([0-9]+) used as offset, casting to integer \\(\\1\\)$/', $message);
                }, E_WARNING | (PHP_MAJOR_VERSION === 5 ? E_STRICT : E_NOTICE));
                $array = iterator_to_array($array);
                restore_error_handler();
            }
        } else {
            $array = (array)$value;
        }
        
        return $array;
    }
    
    /**
     * 与えられた値を、指定された dictionary 型に変換して返します。
     * @link https://www.w3.org/TR/WebIDL-1/#idl-dictionary WebIDL Level 1
     * @link https://www.w3.org/TR/WebIDL-1/#idl-dictionaries WebIDL Level 1
     * @param mixed $value
     * @param string $identifier
     * @param array $pseudoTypes
     * @throws \DomainException dictionary メンバと同じキーを持つ $value の要素について、型が合致しない場合。
     * @return array
     */
    public static function toDictionary($value, $identifier, $pseudoTypes)
    {
        $array = self::convertToArrayAccess($value);
        
        $dictionary = [];
        
        foreach ($pseudoTypes[$identifier] as $dictionaryMemberIdentifier => $dictionaryMemberInfo) {
            if (isset($array[$dictionaryMemberIdentifier])) {
                $dictionaryMember = $array[$dictionaryMemberIdentifier];
                try {
                    $dictionary[$dictionaryMemberIdentifier]
                        = Type::to($dictionaryMemberInfo['type'], $dictionaryMember, $pseudoTypes);
                } catch (\LogicException $exception) {
                    if ($exception instanceof \InvalidArgumentException || $exception instanceof \DomainException) {
                        throw new \DomainException(sprintf(
                            'In "%s" member of %s, expected %s',
                            $dictionaryMemberIdentifier,
                            $identifier,
                            $dictionaryMemberInfo['type']
                        ), 0, $exception);
                    } else {
                        throw $exception;
                    }
                }
            } elseif (array_key_exists('default', $dictionaryMemberInfo)) {
                $dictionary[$dictionaryMemberIdentifier] = $dictionaryMemberInfo['default'];
            } elseif (isset($dictionaryMemberInfo['required'])) {
                throw new \DomainException(sprintf(
                    'In "%s" member of %s, expected %s, got none',
                    $dictionaryMemberIdentifier,
                    $identifier,
                    $dictionaryMemberInfo['type']
                ));
            }
        }
        
        return $dictionary;
    }
    
    /**
     * 与えられた値を、{@link \esperecyan\webidl\Record} に変換して返します。
     * @link https://heycam.github.io/webidl/#idl-record Web IDL
     * @param mixed $traversable
     * @param string $keyType record のキーの型 (record<K, V\> の K)。
     * @param string $valueType record の値の型 (record<K, V\> の V)。
     * @param array $pseudoTypes
     * @throws \DomainException 与えられた配列のキーまたは値が、指定された型に合致しない場合。
     * @return Record
     */
    public static function toRecord($traversable, $keyType, $valueType, $pseudoTypes = [])
    {
        $exceptionMessage = ErrorMessageCreator::create(
            null,
            sprintf('%s (an associative array including only %s)', "record<$keyType, $valueType>", $valueType),
            ''
        );
        
        $entries = [];
        
        foreach (SequenceType::convertToRewindable($traversable) as $key => $value) {
            try {
                $entry[0] = Type::to($keyType, $key, $pseudoTypes);
            } catch (\LogicException $exception) {
                if ($exception instanceof \InvalidArgumentException || $exception instanceof \DomainException) {
                    throw new \DomainException($exceptionMessage, 0, $exception);
                } else {
                    throw $exception;
                }
            }
            
            try {
                $entry[1] = Type::to($valueType, $value, $pseudoTypes);
            } catch (\LogicException $exception) {
                if ($exception instanceof \InvalidArgumentException || $exception instanceof \DomainException) {
                    throw new \DomainException($exceptionMessage, 0, $exception);
                } else {
                    throw $exception;
                }
            }
            
            $entries[] = $entry;
        }
        
        return new Record($entries);
    }
}
