<?php
namespace esperecyan\webidl\lib;

/** @internal */
class UnionType
{
    use Utility;
    
    /**
     * 与えられた値を、指定された型のいずれか一つに変換して返します。
     * @link https://www.w3.org/TR/WebIDL-1/#idl-union WebIDL Level 1
     * @link https://www.w3.org/TR/WebIDL-1/#es-union WebIDL Level 1
     * @link https://heycam.github.io/webidl/#es-union Web IDL
     * @param mixed $value
     * @param string $unitTypeString 共用体型。先頭、末尾の丸括弧も含む文字列。
     * @param array $pseudoTypes callback interface 型、列挙型、callback 関数型、または dictionary 型の識別子をキーとした型情報の配列。
     * @throws \InvalidArgumentException 指定された型のいずれにも一致しない値が与えられた場合。
     * @throws \DomainException 指定された型のいずれにも一致しない値が与えられた場合。
     * @return mixed
     */
    public static function toUnion($value, $unitTypeString, $pseudoTypes = [])
    {
        $flattenedTypesAndNullableNums = self::getFlattenedTypesAndNullableNums($unitTypeString);
        
        if ($flattenedTypesAndNullableNums['numberOfNullableMemberTypes'] === 1 && is_null($value)) {
            return null;
        }
        
        foreach ($flattenedTypesAndNullableNums['flattenedMemberTypes'] as $type) {
            $genericTypes[$type] = self::getGenericType($type, $pseudoTypes);
        }
        
        if (is_object($value) || $value instanceof \__PHP_Incomplete_Class) {
            foreach (array_keys($genericTypes, 'interface') as $interfaceType) {
                try {
                    return Type::to($interfaceType, $value, $pseudoTypes);
                } catch (\LogicException $exception) {
                    if ($exception instanceof \InvalidArgumentException) {
                        $lastInvalidArgumentException = $exception;
                    } elseif ($exception instanceof \DomainException) {
                        $lastDomainException = $exception;
                    } else {
                        throw $exception;
                    }
                }
            }
            if (isset($genericTypes['object'])) {
                return $value;
            }
        }
        
        if (is_callable($value) && array_search('callback function', $genericTypes)) {
            return $value;
        }
        
        try {
            if (is_array($value) || is_object($value) || $value instanceof \__PHP_Incomplete_Class || is_null($value)) {
                $dictionaryLike = array_search('record', $genericTypes) ?: array_search('dictionary', $genericTypes);
                $sequenceLike = array_search('sequence', $genericTypes) ?: array_search('FrozenArray', $genericTypes);
                
                if ($dictionaryLike !== false || $sequenceLike) {
                    if ($dictionaryLike !== false && $sequenceLike) {
                        $type = $sequenceLike;
                        $i = 0;
                        foreach (SequenceType::convertToRewindable($value) as $entryKey => $entryValue) {
                            if (!is_int($entryKey) || $entryKey !== $i) {
                                $type = $dictionaryLike;
                                break;
                            }
                            $i++;
                        }
                    } else {
                        $type = $sequenceLike ?: $dictionaryLike;
                    }

                    return Type::to($type, $value, $pseudoTypes);
                }
                
                foreach (array_keys($genericTypes, 'interface') as $interfaceType) {
                    if (isset($pseudoTypes[$interfaceType])
                        && ($pseudoTypes[$interfaceType] === 'callback interface'
                            || $pseudoTypes[$interfaceType] === 'single operation callback interface')) {
                        return Type::to($interfaceType, $value, $pseudoTypes);
                    }
                }
            }

            if (is_bool($value) && isset($genericTypes['boolean'])) {
                return $value;
            }

            if ((is_int($value) || is_float($value)) && ($type = array_search('numeric', $genericTypes))) {
                return Type::to($type, $value);
            }
            
            if (($type = array_search('string', $genericTypes) ?: array_search('numeric', $genericTypes))) {
                return Type::to($type, $value, $pseudoTypes);
            }
            
            if (isset($genericTypes['boolean'])) {
                return BooleanType::toBoolean($value);
            }
        } catch (\LogicException $exception) {
            if ($exception instanceof \InvalidArgumentException) {
                $lastInvalidArgumentException = $exception;
            } elseif ($exception instanceof \DomainException) {
                $lastDomainException = $exception;
            } else {
                throw $exception;
            }
        }
        
        $errorMessage = ErrorMessageCreator::create($value, $unitTypeString);
        if (isset($lastDomainException)) {
            throw new \DomainException($errorMessage, 0, $lastDomainException);
        } elseif (isset($lastInvalidArgumentException)) {
            throw new \InvalidArgumentException($errorMessage, 0, $lastInvalidArgumentException);
        } else {
            throw new \InvalidArgumentException($errorMessage);
        }
    }
    
    /**
     * 共用体型の平坦化メンバ型、および nullable メンバ型の個数を返す。
     * @link https://www.w3.org/TR/WebIDL-1/#dfn-flattened-union-member-types WebIDL Level 1
     * @link https://www.w3.org/TR/WebIDL-1/#dfn-number-of-nullable-member-types WebIDL Level 1
     * @param string $unionTypeString
     * @return (string[]|int)[] flattenedMemberTypesキーの値に平坦化メンバ型の配列、
     *      numberOfNullableMemberTypesキーの値に nullable メンバ型の個数。
     */
    public static function getFlattenedTypesAndNullableNums($unionTypeString)
    {
        /**
         * @var string[] 共用体のメンバ型
         * @link https://www.w3.org/TR/WebIDL-1/#dfn-union-member-type WebIDL Level 1
         */
        $unionMemberTypes = preg_split(
            '/^\\(|([^ (]*\\((?>[^()]+|(?1))*\\)[^ )]*)| or |\\)$/u',
            $unionTypeString,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
        
        $flattenedMemberTypes = [];
        $numberOfNullableMemberTypes = 0;
        
        foreach ($unionMemberTypes as $unionMemberType) {
            preg_match('/^(?<type>(?<union>\\(.+\\))|.+?)(?<nullable>\\??)$/u', $unionMemberType, $matches);
            
            if ($matches['nullable']) {
                // nullable 型であれば
                $numberOfNullableMemberTypes++;
            }
            
            if ($matches['union']) {
                // メンバ型、または内部型が共用体型であれば
                $flattenedTypesAndNullableNums = self::getFlattenedTypesAndNullableNums($matches['type']);
                $flattenedMemberTypes
                    = array_merge($flattenedMemberTypes, $flattenedTypesAndNullableNums['flattenedMemberTypes']);
                $numberOfNullableMemberTypes += $flattenedTypesAndNullableNums['numberOfNullableMemberTypes'];
            } else {
                $flattenedMemberTypes[] = $matches['type'];
            }
        }
        
        return [
            'flattenedMemberTypes' => $flattenedMemberTypes,
            'numberOfNullableMemberTypes' => $numberOfNullableMemberTypes,
        ];
    }
    
    /**
     * 次のいずれかを返します: any、boolean、numeric、string、
     *      object、interface、dictionary、callback function、nullable、sequence、record、FrozenArray、union。
     * @link https://www.w3.org/TR/WebIDL-1/#idl-types WebIDL Level 1
     * @link https://www.w3.org/TR/WebIDL-1/#es-union WebIDL Level 1
     * @link https://heycam.github.io/webidl/#es-union Web IDL
     * @param string $type
     * @param array $pseudoTypes
     * @return string
     */
    private static function getGenericType($type, $pseudoTypes)
    {
        $genericType = 'interface';
        if (in_array($type, ['any', 'boolean', 'object'])) {
            $genericType = (string)$type;
        } elseif (in_array($type, ['[EnforceRange] byte', '[Clamp] byte', 'byte',
            '[EnforceRange] octet', '[Clamp] octet', 'octet', '[EnforceRange] short', '[Clamp] short', 'short',
            '[EnforceRange] unsigned short', '[Clamp] unsigned short', 'unsigned short',
            '[EnforceRange] long', '[Clamp] long', 'long',
            '[EnforceRange] unsigned long', '[Clamp] unsigned long', 'unsigned long',
            '[EnforceRange] long long', '[Clamp] long long', 'long long',
            '[EnforceRange] unsigned long long', '[Clamp] unsigned long long', 'unsigned long long',
            'float', 'unrestricted float', 'double', 'unrestricted double'])) {
            $genericType = 'numeric';
        } elseif (in_array($type, ['DOMString', 'ByteString', 'USVString'])) {
            $genericType = 'string';
        } elseif (preg_match(
            '/^(?:(?<nullable>.+)\\?|sequence<(?<sequence>.+)>|record<(?<recordK>(?:DOMString|USVString|ByteString)), (?<recordV>.+)>|(?<union>\\(.+\\))|FrozenArray<(?<FrozenArray>.+)>)$/u',
            $type,
            $matches
        ) === 1) {
            if (!empty($matches['nullable'])) {
                $genericType = 'nullable';
            } elseif (!empty($matches['sequence'])) {
                $genericType = 'sequence';
            } elseif (!empty($matches['recordK'])) {
                $genericType = 'record';
            } elseif (!empty($matches['union'])) {
                $genericType = 'union';
            } elseif (!empty($matches['FrozenArray'])) {
                $genericType = 'FrozenArray';
            }
        } elseif (isset($pseudoTypes[$type])) {
            $pseudoType = $pseudoTypes[$type];
            if ($pseudoType === 'callback function') {
                $genericType = 'callback function';
            } elseif (is_string($pseudoType) || isset($pseudoType[0])) {
                $genericType = 'string';
            } else {
                $genericType = 'dictionary';
            }
        }
        return $genericType;
    }
}
