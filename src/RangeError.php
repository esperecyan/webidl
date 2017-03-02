<?php
namespace esperecyan\webidl;

/**
 * Indicates a value that is not in the set or range of allowable values.
 * @link https://www.w3.org/TR/WebIDL-1/#dfn-simple-exception WebIDL Level 1
 * @link https://www.ecma-international.org/ecma-262/7.0/index.html#sec-native-error-types-used-in-this-standard-rangeerror ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/RangeError RangeError - JavaScript | MDN
 */
class RangeError extends \RangeException implements Error
{
    use lib\Error;
}
