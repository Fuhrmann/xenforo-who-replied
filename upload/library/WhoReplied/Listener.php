<?php
class WhoReplied_Listener
{
    public static function loadController($class, array &$extend)
    {
        switch($class)
        {
            case 'XenForo_ControllerPublic_Thread':
                $extend[] = 'WhoReplied_Extend_ControllerPublic_Thread';
                break;
        }
    }
}
