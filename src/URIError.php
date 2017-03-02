<?php
namespace esperecyan\webidl;

/**
 * Indicates that one of the global URI handling functions was used in a way that is incompatible with its definition.
 * @link https://www.w3.org/TR/WebIDL-1/#dfn-simple-exception WebIDL Level 1
 * @link https://www.ecma-international.org/ecma-262/7.0/index.html#sec-native-error-types-used-in-this-standard-urierror ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/URIError URIError - JavaScript | MDN
 */
class URIError extends \UnexpectedValueException implements Error
{
    use lib\Error;
}
