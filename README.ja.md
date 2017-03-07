[English](README.md) / 日本語

Web IDL
=======
[WebIDL (Web IDL)]の型に沿うように、与えられた値をキャストするユーティリティクラスを提供し、PHP の型宣言を補助します。

[WebIDL (Web IDL)]: https://triple-underscore.github.io/WebIDL-ja.html "この文書は、 Web ブラウザへの実装を目的とするインタフェースを記述するためのインタフェース定義言語， Web IDL を定義する。"

概要
----
当ライブラリは[型宣言補助API]を提供し、また Web IDL で定義されている例外を PHP から利用できるようにします。
当ライブラリは Web 標準 API の実装者向けであり、PHP プロジェクトからの直接利用は想定していません。

作成したライブラリと同時に当ライブラリをインストールしてもらうには、
ライブラリの[composer.json]の[requireプロパティ]に、以下のように `"esperecyan/webidl": "^2.0.0"` を追加します。

```json
{
	"name": "esperecyan/url",
	"description": "Makes the algorithms and APIs defined by URL Standard available on PHP.",
	"require": {
		"php": ">=7.1",
		"esperecyan/webidl": "^2.0.0"
	},
	"autoload": {
		"psr-4": {
			"esperecyan\\url\\": "src/"
		}
	},
}
```

Composer について詳しくは、[Composerドキュメント]をご覧ください。

[型宣言補助API]: #type-declarations-help-api
[composer.json]: https://kohkimakimoto.github.io/getcomposer.org_doc_jp/doc/01-basic-usage.html#-composer-json- "このファイルにはプロジェクトの依存情報が記述されます。"
[requireプロパティ]: https://kohkimakimoto.github.io/getcomposer.org_doc_jp/doc/01-basic-usage.html#-require- "requireはパッケージ名とパッケージバージョンで指定されたオブジェクトを扱います。"
[Composerドキュメント]: https://kohkimakimoto.github.io/getcomposer.org_doc_jp/doc/00-intro "ComposerはPHPの依存管理ツールです。 Composerはあなたのプロジェクトが必要とする依存ライブラリを定義できるようにして、インストールを行います。"

例
---
```php
<?php
require_once 'vendor/autoload.php';

use esperecyan\webidl\TypeHinter;

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

上の例を実行すると、以下のような連結された例外が投げ出されます。

* InvalidArgumentException: Expected a single operation callback interface (a object, array or callable), got 'invalid argument' in esperecyan/webidl/src/lib/ObjectType.php on line 66
* InvalidArgumentException: Expected EventListener? (EventListener or null) in esperecyan/webidl/src/lib/NullableType.php on line 29
* InvalidArgumentException: Argument 2 passed to EventTarget::addEventListener() is not of the expected type in esperecyan/webidl/src/TypeHinter.php on line 45

実際の使用例は、[esperecyan/url]のソースコードを参照してください。

[esperecyan/url]: https://github.com/esperecyan/url "URL Standard で定義されているアルゴリズム、および API を PHP から利用できるようにします。"

<a name="type-declarations-help-api">型宣言補助 API</a>
-------------------------------------------------
いずれも静的メソッドで、クラスに属するメソッドから呼び出す必要があります。

### [esperecyan\webidl\TypeHinter::to($type, $value, $argNum, $pseudoTypes)]
指定された値を、指定された IDL 型に沿うよう変換して返します。
キャストできない値だった場合、メソッド名などを含むメッセージを含む[DomainException]、または[InvalidArgumentException]を投げ出します。

#### string `$type`
IDL 型 (例: `USVString`) を指定します。

* [interface型] \(callback interface を除く) については、完全修飾形式のクラス名、またはインターフェース名 (例: `esperecyan\webidl\TypeError`) を指定します。
  なお、先頭のバックスラッシュは不要です。
* 整数型については、`[EnforceRange]` 拡張属性、または `[Clamp]` 拡張属性も指定可能です (例: `[Clamp] octet`) 。
* 共用体型 (例: `(Node or DOMString)`) などを含むほとんどの型に対応していますが、一部異なる部分があります。
  [型の対応表]を参照してください。

#### mixed `$value`
変換したい値を指定します。

#### string `$argNum = 0`
変換対象の値が、 **0から数えて** 何番目の引数で受け取ったか指定します。
この引数値は例外メッセージに使用されます。
呼び出し元のメソッドが [\__set()] の場合、この指定は無視されます。

#### (string|string\[]|array)\[] `$pseudoType = []`
callback interface 型、列挙型、callback 関数型、または dictionary 型の識別子 ($type の中で指定した文字列) をキーとした連想配列を指定します。
キーに対応する値は、型に応じて以下のような構造を取ります。

```php
[
    'callback interface 型名' => 'callback interface',
    '単一演算 callback interface 型名' => 'single operation callback interface',
    'callback 関数型名' => 'callback function',
    '列挙型名' => ['文字列の', '配列'],
    'dictionary 型名' => [
        'メンバキーA' => [
            'type' => '型名',
            'default' => '既定値',
        ],
        'メンバキーB' => [
            'type' => '型名',
            'required' => true,
        ],
    ],
]
```

### [esperecyan\webidl\TypeHinter::throwReadonlyException()]
読み取り専用プロパティであることを示すメッセージを含む例外を投げ出します。
[\__set()] メソッドの中から呼び出す必要があります。

### [esperecyan\webidl\TypeHinter::triggerVisibilityErrorOrDefineProperty()]
private、または protected が指定されたプロパティに代入しようとしていたときは致命的なエラーを発生させ、
存在しないプロパティに代入しようとしていたときは public プロパティとして新規作成します。
[\__set()] メソッドの中から呼び出す必要があります。

### [esperecyan\webidl\TypeHinter::triggerVisibilityErrorOrUndefinedNotice()]
private、または protected が指定されたプロパティの値を取得しようとしていたときは致命的なエラーを、
存在しないプロパティの値を取得しようとしていたときは警告を発生させます。
[\__get()] メソッドの中から呼び出す必要があります。

[esperecyan\webidl\TypeHinter::to($type, $value, $argNum, $pseudoTypes)]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_to
[DomainException]: https://secure.php.net/manual/class.domainexception.php
[InvalidArgumentException]: https://secure.php.net/manual/class.invalidargumentexception.php
[interface型]: https://triple-underscore.github.io/WebIDL-ja.html#idl-interface
[型の対応表]: #型の対応表
[\__set()]: https://secure.php.net/manual/language.oop5.overloading.php#object.set
[esperecyan\webidl\TypeHinter::throwReadonlyException()]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_throwReadonlyException
[esperecyan\webidl\TypeHinter::triggerVisibilityErrorOrDefineProperty()]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_triggerVisibilityErrorOrDefineProperty
[esperecyan\webidl\TypeHinter::triggerVisibilityErrorOrUndefinedNotice()]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_triggerVisibilityErrorOrUndefinedNotice
[\__get()]: https://secure.php.net/manual/language.oop5.overloading.php#object.get

[型]の対応表
-----------
| Web IDL                                | PHP                        | 追記事項                                       |
|----------------------------------------|----------------------------|------------------------------------------------|
| [boolean]                              | [論理値]                   |                                                |
| [byte]<br>[octet]<br>[short]<br>[unsigned short]<br>[long] | [整数] |                                                |
| [unsigned long]                        | [整数]\|[浮動小数点数]     | 32bit 版 PHPと Windows 版 PHP 5.6 以前では、 −2147483648 より小さい数と 2147483647 より大きい数は浮動小数点数。 |
| [long long]                            | [整数]\|[浮動小数点数]     | −9223372036854775808 〜 9223372036854775807。 ただし 32bit 版 PHP と Windows 版 PHP では −9007199254740991 〜 9007199254740991 であり、 −2147483648 より小さい数と 2147483647 より大きい数は浮動小数点数。 |
| [unsigned long long]                   | [整数]\|[浮動小数点数]     | 0 〜 9223372036854775807。 ただし 32bit 版 PHP と Windows 版 PHP 5.6以前では 0 〜 9007199254740991 であり、 2147483647 より大きい数は浮動小数点数。 |
| <a name="^1"></a>[float] <sup>[*1]</sup><br>[unrestricted float] <sup>[*1]</sup><br>[double]<br>[unrestricted double] | [浮動小数点数] | `float`、`unrestricted float` は、`double`、`unrestricted double` のエイリアス。 |
| [DOMString]<br>[USVString]             | [文字列]                   | 妥当な UTF-8 文字列。                          |
| [ByteString]                           | [文字列]                   |                                                |
| [object]                               | [オブジェクト]             |                                                |
| [interface型]                          | [オブジェクト]\|[Callable] | [単一演算 callback interface] なら、 Callable の場合がある。 |
| [dictionary型]                         | [配列]                     | [$pseudoType] で指定した構造に合致する配列。   |
| [列挙型]                               | [文字列]                   | [$pseudoType] で指定した配列の要素、 またはクラスの定数値。 |
| [callback関数型]                       | [Callable]                 |                                                |
| [sequence]<br>[凍結配列型]             | [配列]                     | 新しい配列。                                   |
| [record\<K, V>]                        | [esperecyan\webidl\Record] |                                                |
| [promise型]                            |                            | 非対応。 代わりに完全修飾形式のクラス名、 またはインターフェース名 (例:&nbsp;`React\Promise\PromiseInterface`) を指定。 |
| [共用体型]                             | [mixed]                    | [UnionType::toUnion()] の戻り値。              |
| [Error]                                | [esperecyan\webidl\Error]\|[DOMException] |                                 |
| [DOMException][idl-DOMException]       | [DOMException]             |                                                |
| [buffer source型]                      |                            | 非対応。 代わりに完全修飾形式のクラス名、 またはインターフェース名を指定。 |

<a name="*1"></a><sup>[*1](#^1)</sup> float の代わりに double を使うべきとされている。非推奨。 
[*1]: #*1 "float の代わりに double を使うべきとされている。非推奨。"

[型]: https://triple-underscore.github.io/WebIDL-ja.html#idl-types
[boolean]: https://triple-underscore.github.io/WebIDL-ja.html#idl-boolean
[byte]: https://triple-underscore.github.io/WebIDL-ja.html#idl-byte
[octet]: https://triple-underscore.github.io/WebIDL-ja.html#idl-octet
[short]: https://triple-underscore.github.io/WebIDL-ja.html#idl-short
[unsigned short]: https://triple-underscore.github.io/WebIDL-ja.html#idl-unsigned-short
[long]: https://triple-underscore.github.io/WebIDL-ja.html#idl-long
[unsigned long]: https://triple-underscore.github.io/WebIDL-ja.html#idl-unsigned-long
[long long]: https://triple-underscore.github.io/WebIDL-ja.html#idl-long-long
[unsigned long long]: https://triple-underscore.github.io/WebIDL-ja.html#idl-unsigned-long-long
[float]: https://triple-underscore.github.io/WebIDL-ja.html#idl-float
[unrestricted float]: https://triple-underscore.github.io/WebIDL-ja.html#idl-unrestricted-float
[double]: https://triple-underscore.github.io/WebIDL-ja.html#idl-double
[unrestricted double]: https://triple-underscore.github.io/WebIDL-ja.html#idl-unrestricted-double
[DOMString]: https://triple-underscore.github.io/WebIDL-ja.html#idl-DOMString
[USVString]: https://triple-underscore.github.io/WebIDL-ja.html#idl-USVString
[ByteString]: https://triple-underscore.github.io/WebIDL-ja.html#idl-ByteString
[object]: https://triple-underscore.github.io/WebIDL-ja.html#idl-object
[interface型]: https://triple-underscore.github.io/WebIDL-ja.html#idl-interface
[dictionary型]: https://triple-underscore.github.io/WebIDL-ja.html#idl-dictionary
[列挙型]: https://triple-underscore.github.io/WebIDL-ja.html#idl-enumeration
[callback関数型]: https://triple-underscore.github.io/WebIDL-ja.html#idl-callback-function
[sequence]: https://triple-underscore.github.io/WebIDL-ja.html#idl-sequence
[凍結配列型]: https://triple-underscore.github.io/WebIDL-ja.html#idl-frozen-array
[record\<K, V>]: https://triple-underscore.github.io/WebIDL-ja.html#idl-record
[promise型]: https://triple-underscore.github.io/WebIDL-ja.html#idl-promise
[共用体型]: https://triple-underscore.github.io/WebIDL-ja.html#idl-union
[Error]: https://triple-underscore.github.io/WebIDL-ja.html#idl-Error
[idl-DOMException]: https://triple-underscore.github.io/WebIDL-ja.html#idl-DOMException
[buffer source型]: https://triple-underscore.github.io/WebIDL-ja.html#idl-buffer-source-types

[論理値]: https://secure.php.net/manual/language.types.boolean.php
[整数]: https://secure.php.net/manual/language.types.integer.php
[浮動小数点数]: https://secure.php.net/manual/language.types.float.php
[文字列]: https://secure.php.net/manual/language.types.string.php
[オブジェクト]: https://secure.php.net/manual/language.types.object.php
[Callable]: https://secure.php.net/manual/language.types.callable.php
[単一演算 callback interface]: https://triple-underscore.github.io/WebIDL-ja.html#dfn-single-operation-callback-interface
[配列]: https://secure.php.net/manual/language.types.array.php
[mixed]: https://secure.php.net/manual/language.pseudo-types.php#language.types.mixed
[$pseudoType]: #user-content-stringstringarray-pseudotype--
[esperecyan\webidl\Record]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.Record
[UnionType::toUnion()]: src/lib/UnionType.php#L20
[esperecyan\webidl\Error]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.Error
[DOMException]: https://secure.php.net/manual/class.domexception.php

[例外]の対応表
-------------
| Web IDL                                | PHP                                      |
|----------------------------------------|------------------------------------------|
| <a name="^2"></a>Error <sup>[*2]</sup> | [esperecyan\webidl\Errorインターフェース]<br>(このエラー名の例外を作成する場合は `new esperecyan\webidl\ErrorClass('エラーメッセージ')`) |
| EvalError                              | [esperecyan\webidl\EvalErrorクラス]      |
| RangeError                             | [esperecyan\webidl\RangeErrorクラス]     |
| ReferenceError                         | [esperecyan\webidl\ReferenceErrorクラス] |
| TypeError                              | [esperecyan\webidl\TypeErrorクラス]      |
| URIError                               | [esperecyan\webidl\URIErrorクラス]       |
| [DOMException][idl-DOMException]       | [DOMExceptionクラス][DOMException]       |

<a name="*2"></a><sup>[*2](#^2)</sup> 「Error」単純例外型 (※ Error IDL型ではない) はW3C編集者草案で廃止された。非推奨。
[*2]: #*2 "「Error」単純例外型 (Error IDL型ではない) はW3C編集者草案で廃止された。非推奨。"

[例外]: https://triple-underscore.github.io/WebIDL-ja.html#idl-exceptions
[esperecyan\webidl\Errorインターフェース]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.Error
[esperecyan\webidl\EvalErrorクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.EvalError
[esperecyan\webidl\RangeErrorクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.RangeError
[esperecyan\webidl\ReferenceErrorクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.Referencerror
[esperecyan\webidl\TypeErrorクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeError
[esperecyan\webidl\URIErrorクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.URIError
[esperecyan\webidl\DOMExceptionクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.DOMException

要件
----
* PHP 5.4 以上 **(PHP 5.4、および 5.5 は非推奨)**
	+ SPL Types PECL ライブラリには非対応

貢献
----
1. Fork します ( https://github.com/esperecyan/webidl )
2. branch を作成します `git checkout -b my-new-feature`
3. 変更を commit します `git commit -am 'Add some feature'`
4. branch に push します `git push origin my-new-feature`
5. Pull Request を作成します

もしくは

Issue を作成します

README や Doc コメントの英文の間違い、またテストの不備などを見つけたら、Pull Request や Issue などからご連絡ください。
README の翻訳も歓迎いたします。

謝辞
----
ライブラリの作成に当たり、[Web IDL （第２版 — 日本語訳）]を参考にさせていただきました。

READMEの英訳をハダーさんに協力していただきました。

[Web IDL （第２版 — 日本語訳）]: https://triple-underscore.github.io/WebIDL-ja.html "この ページ は、 W3C により，副題の日付にて編集者草案（ Editor's Draft ）として公開された Web IDL （第２版）を日本語に翻訳したものです。 この翻訳の正確性は保証されません。 この仕様の公式な文書は英語版であり、この日本語訳は公式のものではありません。"

ライセンス
---------
当ライブラリのライセンスは [Mozilla Public License Version 2.0] \(MPL-2.0) です。

[Mozilla Public License Version 2.0]: https://www.mozilla.org/MPL/2.0/
