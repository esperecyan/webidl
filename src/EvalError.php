<?php
namespace esperecyan\webidl;

/**
 * This exception is not currently used within the ECMAScript specification.
 * This object remains for compatibility with previous editions of the ECMAScript specification.
 * @link https://www.w3.org/TR/WebIDL-1/#dfn-simple-exception WebIDL Level 1
 * @link https://www.ecma-international.org/ecma-262/7.0/index.html#sec-native-error-types-used-in-this-standard-evalerror ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/EvalError EvalError - JavaScript | MDN
 */
class EvalError extends \RuntimeException implements Error
{
    use lib\Error;
}
