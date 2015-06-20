<?php
namespace esperecyan\webidl;

/**
 * Indicates a value that is not in the set or range of allowable values.
 * @link https://heycam.github.io/webidl/#dfn-simple-exception Web IDL (Second Edition)
 * @link http://www.ecma-international.org/ecma-262/6.0/index.html#sec-native-error-types-used-in-this-standard-rangeerror ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/RangeError RangeError - JavaScript | MDN
 */
class RangeError extends \RangeException implements Error
{
    use lib\Error;
}
