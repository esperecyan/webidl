<?php
namespace esperecyan\webidl;

/**
 * Indicates that one of the global URI handling functions was used in a way that is incompatible with its definition.
 * @link https://heycam.github.io/webidl/#dfn-simple-exception Web IDL (Second Edition)
 * @link http://www.ecma-international.org/ecma-262/6.0/index.html#sec-native-error-types-used-in-this-standard-urierror ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/URIError URIError - JavaScript | MDN
 */
class URIError extends \UnexpectedValueException implements Error
{
    use lib\Error;
}
