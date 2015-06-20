<?php
namespace esperecyan\webidl\lib;

class StringCastable
{
    private $string;
    
    public function __construct($string = '')
    {
        $this->string = $string;
    }

    public function __toString()
    {
        return $this->string;
    }
}
