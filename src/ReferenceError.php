<?php
namespace esperecyan\webidl;

/**
 * Indicate that an invalid reference value has been detected.
 * @link https://heycam.github.io/webidl/#dfn-simple-exception Web IDL (Second Edition)
 * @link http://www.ecma-international.org/ecma-262/6.0/index.html#sec-native-error-types-used-in-this-standard-referenceerror ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/ReferenceError ReferenceError - JavaScript | MDN
 */
class ReferenceError extends \RuntimeException implements Error
{
    use lib\Error;
}
