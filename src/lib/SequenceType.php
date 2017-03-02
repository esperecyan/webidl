<?php
namespace esperecyan\webidl\lib;

/** @internal */
class SequenceType
{
    use Utility;
    
    /**
     * 与えられた値を foreach 構文などに渡しても巻き戻しエラーが発生しない値に変換する。
     * @param mixed $traversable
     * @return array|\Traversable
     */
    public static function convertToRewindable($traversable)
    {
        while ($traversable instanceof \IteratorAggregate) {
            $traversable = $traversable->getIterator();
        }
        
        if ($traversable instanceof \Iterator) {
            try {
                $traversable->rewind();
                if (!$traversable->valid()) {
                    $traversable = [];
                }
            } catch (\Exception $exception) {
                $traversable = $traversable->valid() ? new \NoRewindIterator($traversable) : [];
            }
        }
        
        return $traversable instanceof \Traversable ? $traversable : (array)$traversable;
    }
    
    /**
     * 与えられた値を、要素として指定された型のみを含む配列に変換して返します。
     * @link https://www.w3.org/TR/WebIDL-1/#idl-sequence WebIDL Level 1
     * @param mixed $traversable
     * @param string $type sequence の要素型 (sequence<T\> の T)。
     * @param array $pseudoTypes callback interface 型、列挙型、callback 関数型、または dictionary 型の識別子をキーとした型情報の配列。
     * @throws \DomainException 与えられた配列の要素が、指定された型に合致しない場合。
     * @return array
     */
    public static function toSequence($traversable, $type, $pseudoTypes = [])
    {
        $array = [];
        
        foreach (self::convertToRewindable($traversable) as $value) {
            try {
                $array[] = Type::to($type, $value, $pseudoTypes);
            } catch (\LogicException $exception) {
                if ($exception instanceof \InvalidArgumentException || $exception instanceof \DomainException) {
                    throw new \DomainException(ErrorMessageCreator::create(
                        null,
                        sprintf('%s (an array including only %s)', 'sequence<' . $type . '>', $type),
                        ''
                    ), 0, $exception);
                } else {
                    throw $exception;
                }
            }
        }
        
        return $array;
    }
    
    /**
     * toSequence() のエイリアスです。
     * @link https://heycam.github.io/webidl/#idl-frozen-array Web IDL
     * @param mixed $traversable
     * @param string $type 配列の要素型 (FrozenArray<T\> の T)。
     * @param array $pseudoTypes callback interface 型、列挙型、callback 関数型、または dictionary 型の識別子をキーとした型情報の配列。
     * @return array
     */
    public static function toFrozenArray($traversable, $type, $pseudoTypes = [])
    {
        return self::toSequence($traversable, $type, $pseudoTypes);
    }
}
