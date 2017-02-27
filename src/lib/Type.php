<?php
namespace esperecyan\webidl\lib;

/** @internal */
class Type
{
    use Utility;
    
    /**
     * 与えられた値を、指定された型に変換して返します。
     * @link https://heycam.github.io/webidl/#idl-types Web IDL (Second Edition)
     * @link https://www.w3.org/TR/WebIDL/#idl-types Web IDL
     * @param string $type
     * @param mixed $value
     * @param array $pseudoTypes callback interface 型、列挙型、callback 関数型、または dictionary 型の識別子をキーとした型情報の配列。
     * @return mixed
     */
    public static function to($type, $value, $pseudoTypes = null)
    {
        switch ($type) {
            case 'any':
                $returnValue = $value;
                break;
            case 'boolean':
                $returnValue = BooleanType::toBoolean($value);
                break;
            case '[EnforceRange] byte':
                $returnValue = IntegerType::toByte($value, '[EnforceRange]');
                break;
            case '[Clamp] byte':
                $returnValue = IntegerType::toByte($value, '[Clamp]');
                break;
            case 'byte':
                $returnValue = IntegerType::toByte($value);
                break;
            case '[EnforceRange] octet':
                $returnValue = IntegerType::toOctet($value, '[EnforceRange]');
                break;
            case '[Clamp] octet':
                $returnValue = IntegerType::toOctet($value, '[Clamp]');
                break;
            case 'octet':
                $returnValue = IntegerType::toOctet($value);
                break;
            case '[EnforceRange] short':
                $returnValue = IntegerType::toShort($value, '[EnforceRange]');
                break;
            case '[Clamp] short':
                $returnValue = IntegerType::toShort($value, '[Clamp]');
                break;
            case 'short':
                $returnValue = IntegerType::toShort($value);
                break;
            case '[EnforceRange] unsigned short':
                $returnValue = IntegerType::toUnsignedShort($value, '[EnforceRange]');
                break;
            case '[Clamp] unsigned short':
                $returnValue = IntegerType::toUnsignedShort($value, '[Clamp]');
                break;
            case 'unsigned short':
                $returnValue = IntegerType::toUnsignedShort($value);
                break;
            case '[EnforceRange] long':
                $returnValue = IntegerType::toLong($value, '[EnforceRange]');
                break;
            case '[Clamp] long':
                $returnValue = IntegerType::toLong($value, '[Clamp]');
                break;
            case 'long':
                $returnValue = IntegerType::toLong($value);
                break;
            case '[EnforceRange] unsigned long':
                $returnValue = IntegerType::toUnsignedLong($value, '[EnforceRange]');
                break;
            case '[Clamp] unsigned long':
                $returnValue = IntegerType::toUnsignedLong($value, '[Clamp]');
                break;
            case 'unsigned long':
                $returnValue = IntegerType::toUnsignedLong($value);
                break;
            case '[EnforceRange] long long':
                $returnValue = IntegerType::toLongLong($value, '[EnforceRange]');
                break;
            case '[Clamp] long long':
                $returnValue = IntegerType::toLongLong($value, '[Clamp]');
                break;
            case 'long long':
                $returnValue = IntegerType::toLongLong($value);
                break;
            case '[EnforceRange] unsigned long long':
                $returnValue = IntegerType::toUnsignedLongLong($value, '[EnforceRange]');
                break;
            case '[Clamp] unsigned long long':
                $returnValue = IntegerType::toUnsignedLongLong($value, '[Clamp]');
                break;
            case 'unsigned long long':
                $returnValue = IntegerType::toUnsignedLongLong($value);
                break;
            case 'float':
                $returnValue = FloatType::toFloat($value);
                break;
            case 'unrestricted float':
                $returnValue = FloatType::toUnrestrictedFloat($value);
                break;
            case 'double':
                $returnValue = FloatType::toDouble($value);
                break;
            case 'unrestricted double':
                $returnValue = FloatType::toUnrestrictedDouble($value);
                break;
            case 'DOMString':
                $returnValue = StringType::toDOMString($value);
                break;
            case 'ByteString':
                $returnValue = StringType::toByteString($value);
                break;
            case 'USVString':
                $returnValue = StringType::toUSVString($value);
                break;
            case 'object':
                $returnValue = ObjectType::toObject($value);
                break;
            case 'Error':
                $returnValue = self::to('(esperecyan\\webidl\\Error or DOMException)', $value, $pseudoTypes);
                break;
            case 'DOMException':
                $returnValue = ObjectType::toInterface($value, 'DOMException');
                break;
            default:
                $pattern = '/^(?:(?<nullable>.+)\\?|sequence<(?<sequence>.+)>|(?<union>\\(.+\\))|FrozenArray<(?<FrozenArray>.+)>)$/u';
                if (preg_match($pattern, $type, $matches) === 1) {
                    if (!empty($matches['nullable'])) {
                        $returnValue = NullableType::toNullable($value, $matches['nullable'], $pseudoTypes);
                    } elseif (!empty($matches['sequence'])) {
                        $returnValue = SequenceType::toSequence($value, $matches['sequence'], $pseudoTypes);
                    } elseif (!empty($matches['union'])) {
                        $returnValue = UnionType::toUnion($value, $matches['union'], $pseudoTypes);
                    } elseif (!empty($matches['FrozenArray'])) {
                        $returnValue = SequenceType::toFrozenArray($value, $matches['FrozenArray'], $pseudoTypes);
                    }
                } elseif (isset($pseudoTypes[$type])) {
                    $pseudoType = $pseudoTypes[$type];
                    switch ($pseudoType) {
                        case 'callback interface':
                        case 'single operation callback interface':
                            $returnValue = ObjectType::toCallbackInterface(
                                $value,
                                $pseudoType === 'single operation callback interface'
                            );
                            break;
                        case 'callback function':
                            $returnValue = ObjectType::toCallbackFunction($value);
                            break;
                        default:
                            if (is_string($pseudoType) || isset($pseudoType[0])) {
                                $returnValue = StringType::toEnumerationValue($value, $type, $pseudoType);
                            } else {
                                $returnValue = DictionaryType::toDictionary($value, $type, $pseudoTypes);
                            }
                    }
                } else {
                    $returnValue = ObjectType::toInterface($value, $type);
                }
        }
        
        return $returnValue;
    }
}
