<?php
namespace esperecyan\webidl;

/**
 * The Error type corresponds to the set of all possible non-null references to exception objects,
 * including simple exceptions, but excluding DOMExceptions.
 * If you need to construct a deprecated Error simple exception (an exception having the error name “Error”),
 * use {@link ErrorClass}.
 * @link https://www.w3.org/TR/WebIDL-1/#idl-exceptions WebIDL Level 1
 * @link https://www.w3.org/TR/WebIDL-1/#idl-Error WebIDL Level 1
 * @link https://www.ecma-international.org/ecma-262/7.0/index.html#sec-error-objects ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/Error Error - JavaScript | MDN
 */
interface Error
{
}
