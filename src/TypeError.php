<?php
namespace esperecyan\webidl;

/**
 * Indicates the actual type of an operand is different than the expected type.
 * @link https://www.w3.org/TR/WebIDL-1/ WebIDL Level 1
 * @link https://www.ecma-international.org/ecma-262/7.0/index.html#sec-native-error-types-used-in-this-standard-typeerror ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/TypeError TypeError - JavaScript | MDN
 * @link https://secure.php.net/manual/class.typeerror.php PHP: TypeError — Manual
 */
class TypeError extends \TypeError implements Error
{
    use lib\Error;
}
