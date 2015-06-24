<?php
namespace esperecyan\webidl;

/**
 * The Error type corresponds to the set of all possible non-null references to exception objects,
 * including simple exceptions and DOMExceptions.
 * If you need to construct an exception having the error name "Error", use {@link ErrorClass}.
 * @link https://heycam.github.io/webidl/#idl-exceptions  Web IDL (Second Edition)
 * @link https://heycam.github.io/webidl/#idl-Error Web IDL (Second Edition)
 * @link http://www.ecma-international.org/ecma-262/6.0/index.html#sec-error-objects ECMAScript 2015 Language Specification – ECMA-262 6th Edition
 * @link https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/Error Error - JavaScript | MDN
 */
interface Error
{
}
