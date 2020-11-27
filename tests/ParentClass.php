<?php
namespace esperecyan\webidl;

class ParentClass extends \PHPUnit\Framework\TestCase
{
    public function callee($value, $type)
    {
        return TypeHinter::to($type, $value, 0, [
            'CustomEventInit' => [
                'bubbles'    => ['type' => 'boolean', 'default' => false],
                'cancelable' => ['type' => 'boolean', 'default' => false],
                'detail'     => ['type' => 'any'    , 'default' => null ],
            ],
        ]);
    }
}
