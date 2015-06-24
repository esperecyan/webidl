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
     * @link https://heycam.github.io/webidl/#idl-sequence Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-sequence Web IDL
     * @param mixed $traversable
     * @param string $type sequence の要素型 (sequence<T> の T)。
     * @param array $pseudoTypes callback interface 型、列挙型、callback 関数型、または dictionary 型の識別子をキーとした型情報の配列。
     * @throws \InvalidArgumentException SplType のインスタンスが与えられた場合。
     * @throws \DomainException 与えられた配列の要素が、指定された型に合致しない場合。
     * @return array
     */
    public static function toSequence($traversable, $type, $pseudoTypes = [])
    {
        $expectedType = sprintf('%s (an array including only %s)', 'sequence<' . $type . '>', $type);
        
        if ($traversable instanceof \SplType) {
            throw new \InvalidArgumentException(ErrorMessageCreator::create($traversable, $expectedType));
        }
        
        $array = [];
        
        foreach (self::convertToRewindable($traversable) as $value) {
            try {
                $array[] = Type::to($type, $value, $pseudoTypes);
            } catch (\LogicException $exception) {
                if ($exception instanceof \InvalidArgumentException || $exception instanceof \DomainException) {
                    throw new \DomainException(ErrorMessageCreator::create(null, $expectedType, ''), 0, $exception);
                } else {
                    throw $exception;
                }
            }
        }
        
        return $array;
    }
    
    /**
     * toSequence() のエイリアスです。
     * @link https://heycam.github.io/webidl/#idl-array Web IDL (Second Edition)
     * @link http://www.w3.org/TR/WebIDL/#idl-array Web IDL
     * @param mixed $traversable
     * @param string $type 配列の要素型 (T[] の T)。
     * @param array $pseudoTypes callback interface 型、列挙型、callback 関数型、または dictionary 型の識別子をキーとした型情報の配列。
     * @return array
     */
    public static function toArray($traversable, $type, $pseudoTypes = [])
    {
        return self::toSequence($traversable, $type, $pseudoTypes);
    }
}
