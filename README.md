English / [日本語](README.ja.md)

Web IDL
=======
Provides the utility class for casting a given value in accordance with [Web IDL (Second Edition)] type to help with PHP type hinting.

[Web IDL (Second Edition)]: https://heycam.github.io/webidl/ "This document defines an interface definition language, Web IDL, that can be used to describe interfaces that are intended to be implemented in web browsers."

Description
-----------
This library makes [Type hinting help API] and the exceptions defined by Web IDL available in PHP.
This library is for Web standards API implementors and is not intended to be used directly by a PHP project.

If you want your users to install this library simultaneously with your library,
append `"esperecyan/webidl": "^1.3.1"` to [require property] in [composer.json] of your library, such as the following.

```json
{
	"name": "esperecyan/url",
	"description": "Makes the algorithms and APIs defined by URL Standard available on PHP.",
	"require": {
		"php": ">=5.5",
		"esperecyan/webidl": "^1.3.1"
	},
	"autoload": {
		"psr-4": {
			"esperecyan\\url\\": "src/"
		}
	},
}
```

For details of Composer, see [Composer documentation].

[Type hinting help API]: #type-hinting-help-api
[composer.json]: https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup "This file describes the dependencies of your project and may contain other metadata as well."
[require property]: https://getcomposer.org/doc/01-basic-usage.md#the-require-key "require takes an object that maps package names to package versions."
[Composer documentation]: https://getcomposer.org/doc/00-intro.md "Composer is a tool for dependency management in PHP. It allows you to declare the dependent libraries your project needs and it will install them in your project for you."

Example
-------
```php
<?php
require_once 'vendor/autoload.php';

use esperecyan\webidl\TypeHinter;
use esperecyan\webidl\DOMException;

class EventTarget
{
    private $eventListeners = [];
    
    public function addEventListener($type, $callback, $capture = false)
    {
        $listener = [
            'type' => TypeHinter::to('DOMString', $type, 0),
            'callback' => TypeHinter::to('EventListener?', $callback, 1, [
                'EventListener' => 'single operation callback interface',
            ]),
            'capture' => TypeHinter::to('boolean', $type, 2),
        ];
        if (!is_null($listener->callback) && !in_array($listener, $this->eventListeners, true)) {
            $this->eventListeners[] = $listener;
        }
    }
}

(new EventTarget())->addEventListener('load', 'invalid argument');
```

The above example will throw the chained exceptions:

* InvalidArgumentException: Expected a single operation callback interface (a object, array or callable), got 'invalid argument' in esperecyan/webidl/src/lib/ObjectType.php on line 66
* InvalidArgumentException: Expected EventListener? (EventListener or null) in esperecyan/webidl/src/lib/NullableType.php on line 29
* InvalidArgumentException: Argument 2 passed to EventTarget::addEventListener() is not of the expected type in esperecyan/webidl/src/TypeHinter.php on line 45

For actual examples, see the source code of [esperecyan/url].

[esperecyan/url]: https://github.com/esperecyan/url "Makes the algorithms and APIs defined by URL Standard available on PHP."

Type hinting help API
---------------------
All of the methods are static, and must be called from a class method.

### [esperecyan\webidl\TypeHinter::to($type, $value, $argNum, $pseudoTypes)]
Converts a given value in accordance with a given IDL type.
If the value is not castable, it will throw a [DomainException] or [InvalidArgumentException] including a message with method name etc.

#### string `$type`
Pass the IDL type (for example, `USVString`).

* If it is an [interface type] \(excluding callback interface), pass the fully qualified class name or interface name (for example, `esperecyan\webidl\TypeError`).
  Additionally, the leading backslash is unnecessary.
* If it is an integer type, can use `[EnforceRange]` extended attribute or `[Clamp]` extended attribute (for example, `[Clamp] octet`).
* Supports most types also including such as union types (for example, `(Node or DOMString)`), but there are some parts are different.
  See [The correspondence table of the types].

#### mixed `$value`
Pass the value being converted.

#### string `$argNum = 0`
Pass the argument offset that received the value being converted. **Arguments are counted starting from zero.** 
This argument value is used by a exception message.
If the caller method is [\__set()], this argument is ignored.

#### (string|string\[]|array)\[] `$pseudoType = []`
Pass the associative array with the identifiers of callback interface types, enumeration types, callback function types or dictionary types (the strings passed in $type) as key.
The corresponding values have the following structure.

```php
[
    'A callback interface type name' => 'callback interface',
    'A single operation callback interface type name' => 'single operation callback interface',
    'A callback function type name' => 'callback function',
    'A enumeration type name A' => ['An array', 'of strings'],
    'A enumeration type name B' => 'A fully qualified name of a class extending SplEnum',
    'dictionary 型名' => [
        'A member key A' => [
            'type' => 'A type name',
            'default' => 'A default value',
        ],
        'A member key B' => [
            'type' => 'A type name',
            'required' => true,
        ],
    ],
]
```

### [esperecyan\webidl\TypeHinter::throwReadonlyException()]
Throws an exception with a message that represents a read-only property.
Must call from [\__set()] method.

### [esperecyan\webidl\TypeHinter::triggerVisibilityErrorOrDefineProperty()]
If a user tries setting to a private or protected property, it will trigger a fatal error.
If a user tries setting to a non-existing property, it will create a new public property.
Must call from [\__set()] method.

### [esperecyan\webidl\TypeHinter::triggerVisibilityErrorOrUndefinedNotice()]
If a user tries setting to a private or protected property, it will trigger a fatal error.
If a user tries getting to a non-existing property, it will trigger a notice.
Must call from [\__get()] method.

[esperecyan\webidl\TypeHinter::to($type, $value, $argNum, $pseudoTypes)]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_to
[DomainException]: http://uk3.php.net/manual/class.domainexception.php
[InvalidArgumentException]: http://uk3.php.net/manual/class.invalidargumentexception.php
[interface type]: https://heycam.github.io/webidl/#idl-interface
[The correspondence table of the types]: #the-correspondence-table-of-the-types
[\__set()]: http://uk3.php.net/manual/language.oop5.overloading.php#object.set
[esperecyan\webidl\TypeHinter::throwReadonlyException()]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_throwReadonlyException
[esperecyan\webidl\TypeHinter::triggerVisibilityErrorOrDefineProperty()]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_triggerVisibilityErrorOrDefineProperty
[esperecyan\webidl\TypeHinter::triggerVisibilityErrorOrUndefinedNotice()]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_triggerVisibilityErrorOrUndefinedNotice
[\__get()]: http://uk3.php.net/manual/language.oop5.overloading.php#object.get

The correspondence table of [the types]
--------------------------------------
| Web IDL                          | PHP                                  | Additional notes                           |
|----------------------------------|--------------------------------------|--------------------------------------------|
| [boolean]                        | [Booleans]                           |                                            |
| [byte]<br>[octet]<br>[short]<br>[unsigned short]<br>[long] | [Integers] |                                            |
| [unsigned long]                  | [Integers]\|[Floating&nbsp;point&nbsp;numbers] | On 32bit PHP or PHP for Windows, a number less than -2147483648 or greater than 2147483647 is the floating point number. |
| [long long]                      | [Integers]\|[Floating&nbsp;point&nbsp;numbers] | -9223372036854775808 to 9223372036854775807. However, on 32bit PHP or PHP for Windows, -9007199254740991 to 9007199254740991, and the number less than -2147483648 or greater than 2147483647 is the floating point number. |
| [unsigned long long]             | [Integers]\|[Floating&nbsp;point&nbsp;numbers] | 0 to 9223372036854775807. However, on 32bit PHP or PHP for Windows, 0 to 9007199254740991, and the number greater than 2147483647 is the floating point number. |
| <a name="^1"></a>[float] <sup>[*1]</sup><br>[unrestricted float] <sup>[*1]</sup><br>[double]<br>[unrestricted double] | [Floating&nbsp;point&nbsp;numbers] | `float` and `unrestricted float` is aliases of `double` and `unrestricted double`. |
| [DOMString]<br>[USVString]       | [Strings]                            | A valid UTF-8 string.                      |
| [ByteString]                     | [Strings]                            |                                            |
| [object]                         | [Objects]                            |                                            |
| [Interface types]                | [Objects]\|[Callables]               | If an interface is [single operation callback interface], there are cases where the PHP type is Callable. |
| [Dictionary types]               | [Arrays]                             | An array conforming the structure passed in [$pseudoType]. |
| [Enumeration types]              | [Strings]                            | A element of the array passed in [$pseudoType], or a constant value of the class passed in. |
| [Callback function types]        | [Callables]                          |                                            |
| [Sequences]<br><a name="^2"></a>[Arrays][idl-array] <sup>[*2]</sup><br>[Frozen arrays] | [Arrays] | New array.       |
| [Promise types]                  |                                      | Not supported. Instead, pass a fully qualified class name or interface name (for example, `React\Promise\PromiseInterface`). |
| [Union types]                    | [mixed]                              | A return value of [UnionType::toUnion()].  |
| [RegExp]                         | [Strings]                            | A UTF-8 string, and a valid [PCRE] pattern enclosed by delimiters. [e modifier] is invalid. |
| [Error]                          | [esperecyan\webidl\Error]            |                                            |
| [DOMException][idl-DOMException] | [DOMException]                       |                                            |
| [Buffer source types]            |                                      | Not supported. Instead, pass a fully qualified class name or interface name. |
| [OpenEndedDictionary\<T>]        |                                      | Not yet supported.                         |

<a name="*1"></a><sup>[*1](#^1)</sup> float is dis-recommended in Web IDL (Second Edition). Deprecated.  
<a name="*2"></a><sup>[*2](#^2)</sup> Arrays are obsolete in Web IDL (Second Edition) (heycam/webidl@079cbb8). Deprecated.
[*1]: #*1 "float is dis-recommended in Web IDL (Second Edition). Deprecated."
[*2]: #*2 "Arrays are obsolete in Web IDL (Second Edition) (heycam/webidl@079cbb8). Deprecated."

[the types]: https://heycam.github.io/webidl/#idl-types
[boolean]: https://heycam.github.io/webidl/#idl-boolean
[byte]: https://heycam.github.io/webidl/#idl-byte
[octet]: https://heycam.github.io/webidl/#idl-octet
[short]: https://heycam.github.io/webidl/#idl-short
[unsigned short]: https://heycam.github.io/webidl/#idl-unsigned-short
[long]: https://heycam.github.io/webidl/#idl-long
[unsigned long]: https://heycam.github.io/webidl/#idl-unsigned-long
[long long]: https://heycam.github.io/webidl/#idl-long-long
[unsigned long long]: https://heycam.github.io/webidl/#idl-unsigned-long-long
[float]: https://heycam.github.io/webidl/#idl-float
[unrestricted float]: https://heycam.github.io/webidl/#idl-unrestricted-float
[double]: https://heycam.github.io/webidl/#idl-double
[unrestricted double]: https://heycam.github.io/webidl/#idl-unrestricted-double
[DOMString]: https://heycam.github.io/webidl/#idl-DOMString
[USVString]: https://heycam.github.io/webidl/#idl-USVString
[ByteString]: https://heycam.github.io/webidl/#idl-ByteString
[object]: https://heycam.github.io/webidl/#idl-object
[Interface types]: https://heycam.github.io/webidl/#idl-interface
[Dictionary types]: https://heycam.github.io/webidl/#idl-dictionary
[Enumeration types]: https://heycam.github.io/webidl/#idl-enumeration
[Callback function types]: https://heycam.github.io/webidl/#idl-callback-function
[Sequences]: https://heycam.github.io/webidl/#idl-sequence
[idl-array]: http://www.w3.org/TR/WebIDL/#idl-array
[Frozen arrays]: https://heycam.github.io/webidl/#idl-frozen-array
[Promise types]: https://heycam.github.io/webidl/#idl-promise
[Union types]: https://heycam.github.io/webidl/#idl-union
[RegExp]: https://heycam.github.io/webidl/#idl-RegExp
[Error]: https://heycam.github.io/webidl/#idl-Error
[idl-DOMException]: https://heycam.github.io/webidl/#idl-DOMException
[Buffer source types]: https://heycam.github.io/webidl/#idl-buffer-source-types
[OpenEndedDictionary\<T>]: https://fetch.spec.whatwg.org/#headersinit

[Booleans]: http://uk3.php.net/manual/language.types.boolean.php
[Integers]: http://uk3.php.net/manual/language.types.integer.php
[Floating&nbsp;point&nbsp;numbers]: http://uk3.php.net/manual/language.types.float.php
[Strings]: http://uk3.php.net/manual/language.types.string.php
[Objects]: http://uk3.php.net/manual/language.types.object.php
[Callables]: http://uk3.php.net/manual/language.types.callable.php
[single operation callback interface]: https://heycam.github.io/webidl/#dfn-single-operation-callback-interface
[Arrays]: http://uk3.php.net/manual/language.types.array.php
[mixed]: http://uk3.php.net/manual/language.pseudo-types.php#language.types.mixed
[$pseudoType]: #user-content-stringstringarray-pseudotype--
[UnionType::toUnion()]: src/lib/UnionType.php#L20
[PCRE]: http://uk3.php.net/manual/book.pcre.php
[e modifier]: http://uk3.php.net/manual/reference.pcre.pattern.modifiers.php#reference.pcre.pattern.modifiers.eval
[esperecyan\webidl\Error]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.Error
[DOMException]: http://uk3.php.net/manual/class.domexception.php

The correspondence table of [the exceptions]
------------------------------------------
| Web IDL                          | PHP                                      |
|----------------------------------|------------------------------------------|
| Error                            | [esperecyan\webidl\Error interface] \(If you need to construct an exception having this error name, write `new esperecyan\webidl\lib\Error('error message')`) |
| EvalError                        | [esperecyan\webidl\EvalError class]      |
| RangeError                       | [esperecyan\webidl\RangeError class]     |
| ReferenceError                   | [esperecyan\webidl\ReferenceError class] |
| TypeError                        | [esperecyan\webidl\TypeError class]      |
| URIError                         | [esperecyan\webidl\URIError class]       |
| [DOMException][idl-DOMException] | [esperecyan\webidl\DOMException class]   |

[the exceptions]: https://heycam.github.io/webidl/#idl-exceptions
[esperecyan\webidl\Error interface]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.Error
[esperecyan\webidl\EvalError class]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.EvalError
[esperecyan\webidl\RangeError class]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.RangeError
[esperecyan\webidl\ReferenceError class]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.Referencerror
[esperecyan\webidl\TypeError class]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeError
[esperecyan\webidl\URIError class]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.URIError
[esperecyan\webidl\DOMException class]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.DOMException

Requirement
-----------
* PHP 5.5 or later
* [mbstring extension module]

[mbstring extension module]: http://uk3.php.net/manual/book.mbstring.php "mbstring provides multibyte specific string functions that help you deal with multibyte encodings in PHP."

Contribution
------------
1. Fork it ( https://github.com/esperecyan/webidl )
2. Create your feature branch `git checkout -b my-new-feature`
3. Commit your changes `git commit -am 'Add some feature'`
4. Push to the branch `git push origin my-new-feature`
5. Create new Pull Request

Or

Create new Issue

If you find any mistakes of English in the README or Doc comments or any flaws in tests, please report by such as above means.
I also welcome translations of README too.

Acknowledgement
---------------
I use [Web IDL (Second Edition) — Japanese translation] as reference in creating this library.

HADAA helped me translate README to English.

[Web IDL (Second Edition) — Japanese translation]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html "この ページ は、 W3C により，副題の日付にて編集者草案（ Editor's Draft ）として公開された Web IDL （第２版）を日本語に翻訳したものです。 この翻訳の正確性は保証されません。 この仕様の公式な文書は英語版であり、この日本語訳は公式のものではありません。"

Licence
-------
This library is licensed under the [Mozilla Public License Version 2.0] \(MPL-2.0).

[Mozilla Public License Version 2.0]: https://www.mozilla.org/MPL/2.0/
