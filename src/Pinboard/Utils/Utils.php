<?php

namespace Pinboard\Utils;

/**
* Usefull methods
*/
class Utils 
{
    public static function generateColor()
    {
        return dechex(rand(0,10000000));
    }
}
