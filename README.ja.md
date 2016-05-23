[English](README.md) / 日本語

Web IDL
=======
[Web IDL 第2版]の型に沿うように、与えられた値をキャストするユーティリティクラスを提供し、PHP のタイプヒンティングを補助します。

[Web IDL 第2版]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html "この文書は、 Web ブラウザへの実装を目的とするインタフェースを記述するためのインタフェース定義言語， Web IDL を定義する。"

概要
----
当ライブラリは、[タイプヒンティング補助API]、また Web IDL で定義されている例外を PHP から利用できるようにします。
当ライブラリは Web 標準 API の実装者向けであり、PHP プロジェクトからの直接利用は想定していません。

作成したライブラリと同時に当ライブラリをインストールしてもらうには、
ライブラリの[composer.json]の[requireプロパティ]に、以下のように `"esperecyan/webidl": "^1.3.1"` を追加します。

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

Composer について詳しくは、[Composerドキュメント]をご覧ください。

[タイプヒンティング補助API]: #タイプヒンティング補助-api
[composer.json]: https://kohkimakimoto.github.io/getcomposer.org_doc_jp/doc/01-basic-usage.html#-composer-json- "このファイルにはプロジェクトの依存情報が記述されます。"
[requireプロパティ]: https://kohkimakimoto.github.io/getcomposer.org_doc_jp/doc/01-basic-usage.html#-require- "requireはパッケージ名とパッケージバージョンで指定されたオブジェクトを扱います。"
[Composerドキュメント]: https://kohkimakimoto.github.io/getcomposer.org_doc_jp/doc/00-intro "ComposerはPHPの依存管理ツールです。 Composerはあなたのプロジェクトが必要とする依存ライブラリを定義できるようにして、インストールを行います。"

例
---
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

上の例を実行すると、以下のような連結された例外が投げ出されます。

* InvalidArgumentException: Expected a single operation callback interface (a object, array or callable), got 'invalid argument' in esperecyan/webidl/src/lib/ObjectType.php on line 66
* InvalidArgumentException: Expected EventListener? (EventListener or null) in esperecyan/webidl/src/lib/NullableType.php on line 29
* InvalidArgumentException: Argument 2 passed to EventTarget::addEventListener() is not of the expected type in esperecyan/webidl/src/TypeHinter.php on line 45

実際の使用例は、[esperecyan/url]のソースコードを参照してください。

[esperecyan/url]: https://github.com/esperecyan/url "URL Standard で定義されているアルゴリズム、および API を PHP から利用できるようにします。"

タイプヒンティング補助 API
-------------------------
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
    '列挙型名A' => ['文字列の', '配列'],
    '列挙型名B' => 'SplEnumを継承したクラスの完全修飾名',
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
[DomainException]: http://jp2.php.net/manual/class.domainexception.php
[InvalidArgumentException]: http://jp2.php.net/manual/class.invalidargumentexception.php
[interface型]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-interface
[型の対応表]: #型の対応表
[\__set()]: http://jp2.php.net/manual/language.oop5.overloading.php#object.set
[esperecyan\webidl\TypeHinter::throwReadonlyException()]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_throwReadonlyException
[esperecyan\webidl\TypeHinter::triggerVisibilityErrorOrDefineProperty()]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_triggerVisibilityErrorOrDefineProperty
[esperecyan\webidl\TypeHinter::triggerVisibilityErrorOrUndefinedNotice()]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeHinter.html#_triggerVisibilityErrorOrUndefinedNotice
[\__get()]: http://jp2.php.net/manual/language.oop5.overloading.php#object.get

[型]の対応表
-----------
| Web IDL                                | PHP                        | 追記事項                                       |
|----------------------------------------|----------------------------|------------------------------------------------|
| [boolean]                              | [論理値]                   |                                                |
| [byte]<br>[octet]<br>[short]<br>[unsigned short]<br>[long] | [整数] |                                                |
| [unsigned long]                        | [整数]\|[浮動小数点数]     | 32bit 版 PHPと Windows 版 PHP では、 −2147483648 より小さい数と 2147483647 より大きい数は浮動小数点数。 |
| [long long]                            | [整数]\|[浮動小数点数]     | −9223372036854775808 〜 9223372036854775807。 ただし 32bit 版 PHP と Windows 版 PHP では −9007199254740991 〜 9007199254740991 であり、 −2147483648 より小さい数と 2147483647 より大きい数は浮動小数点数。 |
| [unsigned long long]                   | [整数]\|[浮動小数点数]     | 0 〜 9223372036854775807。 ただし 32bit 版 PHP と Windows 版 PHP では 0 〜 9007199254740991 であり、 2147483647 より大きい数は浮動小数点数。 |
| <a name="^1"></a>[float] <sup>[*1]</sup><br>[unrestricted float] <sup>[*1]</sup><br>[double]<br>[unrestricted double] | [浮動小数点数] | `float`、`unrestricted float` は、`double`、`unrestricted double` のエイリアス。 |
| [DOMString]<br>[USVString]             | [文字列]                   | 妥当な UTF-8 文字列。                          |
| [ByteString]                           | [文字列]                   |                                                |
| [object]                               | [オブジェクト]             |                                                |
| [interface型]                          | [オブジェクト]\|[Callable] | [単一演算 callback interface] なら、 Callable の場合がある。 |
| [dictionary型]                         | [配列]                     | [$pseudoType] で指定した構造に合致する配列。   |
| [列挙型]                               | [文字列]                   | [$pseudoType] で指定した配列の要素、 またはクラスの定数値。 |
| [callback関数型]                       | [Callable]                 |                                                |
| [sequence]<br><a name="^2"></a>[配列][idl-array] <sup>[*2]</sup><br>[凍結配列型] | [配列] | 新しい配列。             |
| [promise型]                            |                            | 非対応。 代わりに完全修飾形式のクラス名、 またはインターフェース名 (例:&nbsp;`React\Promise\PromiseInterface`) を指定。 |
| [共用体型]                             | [mixed]                    | [UnionType::toUnion()] の戻り値。              |
| [RegExp]                               | [文字列]                   | UTF-8 の文字列であり、 デリミタで囲まれた正しい [PCRE] のパターン。 [e修飾子] は不正とみなされる。 |
| [Error]                                | [esperecyan\webidl\Error]  |                                                |
| [DOMException][idl-DOMException]       | [DOMException]             |                                                |
| [buffer source型]                      |                            | 非対応。 代わりに完全修飾形式のクラス名、 またはインターフェース名を指定。 |
| [OpenEndedDictionary\<T>]              |                            | 未対応。                                       |

<a name="*1"></a><sup>[*1](#^1)</sup> float は Web IDL 第2版 で推奨されない。非推奨。  
<a name="*2"></a><sup>[*2](#^2)</sup> 配列は Web IDL 第2版 で廃止 (heycam/webidl@079cbb8)。非推奨。
[*1]: #*1 "float は Web IDL 第2版 で推奨されない。非推奨。"
[*2]: #*2 "配列は Web IDL 第2版 で廃止 (heycam/webidl@079cbb8)。非推奨。"

[型]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-types
[boolean]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-boolean
[byte]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-byte
[octet]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-octet
[short]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-short
[unsigned short]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-unsigned-short
[long]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-long
[unsigned long]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-unsigned-long
[long long]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-long-long
[unsigned long long]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-unsigned-long-long
[float]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-float
[unrestricted float]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-unrestricted-float
[double]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-double
[unrestricted double]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-unrestricted-double
[DOMString]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-DOMString
[USVString]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-USVString
[ByteString]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-ByteString
[object]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-object
[interface型]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-interface
[dictionary型]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-dictionary
[列挙型]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-enumeration
[callback関数型]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-callback-function
[sequence]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-sequence
[idl-array]: http://www.w3.org/TR/WebIDL/#idl-array
[凍結配列型]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-frozen-array
[promise型]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-promise
[共用体型]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-union
[RegExp]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-RegExp
[Error]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-Error
[idl-DOMException]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-DOMException
[buffer source型]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-buffer-source-types
[OpenEndedDictionary\<T>]: https://fetch.spec.whatwg.org/#headersinit

[論理値]: http://jp2.php.net/manual/language.types.boolean.php
[整数]: http://jp2.php.net/manual/language.types.integer.php
[浮動小数点数]: http://jp2.php.net/manual/language.types.float.php
[文字列]: http://jp2.php.net/manual/language.types.string.php
[オブジェクト]: http://jp2.php.net/manual/language.types.object.php
[Callable]: http://jp2.php.net/manual/language.types.callable.php
[単一演算 callback interface]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#dfn-single-operation-callback-interface
[配列]: http://jp2.php.net/manual/language.types.array.php
[mixed]: http://jp2.php.net/manual/language.pseudo-types.php#language.types.mixed
[$pseudoType]: #user-content-stringstringarray-pseudotype--
[UnionType::toUnion()]: src/lib/UnionType.php#L20
[PCRE]: http://jp2.php.net/manual/book.pcre.php
[e修飾子]: http://jp2.php.net/manual/reference.pcre.pattern.modifiers.php#reference.pcre.pattern.modifiers.eval
[esperecyan\webidl\Error]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.Error
[DOMException]: http://jp2.php.net/manual/class.domexception.php

[例外]の対応表
-------------
| Web IDL                          | PHP                                      |
|----------------------------------|------------------------------------------|
| Error                            | [esperecyan\webidl\Errorインターフェース]<br>(このエラー名の例外を作成する場合は `new esperecyan\webidl\lib\Error('エラーメッセージ')`) |
| EvalError                        | [esperecyan\webidl\EvalErrorクラス]      |
| RangeError                       | [esperecyan\webidl\RangeErrorクラス]     |
| ReferenceError                   | [esperecyan\webidl\ReferenceErrorクラス] |
| TypeError                        | [esperecyan\webidl\TypeErrorクラス]      |
| URIError                         | [esperecyan\webidl\URIErrorクラス]       |
| [DOMException][idl-DOMException] | [esperecyan\webidl\DOMExceptionクラス]   |

[例外]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html#idl-exceptions
[esperecyan\webidl\Errorインターフェース]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.Error
[esperecyan\webidl\EvalErrorクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.EvalError
[esperecyan\webidl\RangeErrorクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.RangeError
[esperecyan\webidl\ReferenceErrorクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.Referencerror
[esperecyan\webidl\TypeErrorクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.TypeError
[esperecyan\webidl\URIErrorクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.URIError
[esperecyan\webidl\DOMExceptionクラス]: https://esperecyan.github.io/webidl/class-esperecyan.webidl.DOMException

要件
----
* PHP 5.5 以上
* [mbstring拡張モジュール]

[mbstring拡張モジュール]: http://jp2.php.net/manual/book.mbstring.php "mbstring はマルチバイト対応の文字列関数を提供し、 PHP でマルチバイトエンコーディングを処理することを容易にします。"

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

[Web IDL （第２版 — 日本語訳）]: http://www.hcn.zaq.ne.jp/___/WEB/WebIDL-ja.html "この ページ は、 W3C により，副題の日付にて編集者草案（ Editor's Draft ）として公開された Web IDL （第２版）を日本語に翻訳したものです。 この翻訳の正確性は保証されません。 この仕様の公式な文書は英語版であり、この日本語訳は公式のものではありません。"

ライセンス
---------
当ライブラリのライセンスは [Mozilla Public License Version 2.0] \(MPL-2.0) です。

[Mozilla Public License Version 2.0]: https://www.mozilla.org/MPL/2.0/
