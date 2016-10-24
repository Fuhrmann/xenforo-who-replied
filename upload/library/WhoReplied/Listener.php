<?php

class WhoReplied_Listener
{
    public static function loadController($class, array &$extend)
    {
        $extend[] = 'WhoReplied_'.$class;
    }
}
