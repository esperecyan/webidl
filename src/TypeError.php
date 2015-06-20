<?php
namespace esperecyan\webidl;

/**
 * Indicates the actual type of an operand is different than the expected type.
 * @link https://heycam.github.io/webidl/#dfn-simple-exception Web IDL (Second Edition)
 * @link http://www.ecma-international.org/ecma-262/6.0/index.html#sec-native-error-types-used-in-this-standard-typeerror ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/TypeError TypeError - JavaScript | MDN
 */
class TypeError extends \UnexpectedValueException implements Error
{
    use lib\Error;
}
