<?php
namespace esperecyan\webidl;

/**
 * Indicate that an invalid reference value has been detected.
 * @link https://www.w3.org/TR/WebIDL-1/#dfn-simple-exception WebIDL Level 1
 * @link https://www.ecma-international.org/ecma-262/7.0/index.html#sec-native-error-types-used-in-this-standard-referenceerror ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/ReferenceError ReferenceError - JavaScript | MDN
 */
class ReferenceError extends \RuntimeException implements Error
{
    use lib\Error;
}
