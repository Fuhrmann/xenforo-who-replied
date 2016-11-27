<?php

class WhoReplied_Installer
{
    public static function install($installedAddon, array $addonData, SimpleXMLElement $xml)
    {
        $version = isset($installedAddon['version_id']) ? $installedAddon['version_id'] : 0;

        $db = XenForo_Application::getDb();

        if ($version == 0)
        {
            // make sure XenForo_Model_User is extended
            XenForo_Model::create("XenForo_Model_User");

            $db->query("insert ignore into xf_permission_entry_content (content_type, content_id, user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                select distinct content_type, content_id, " . XenForo_Model_User::$defaultRegisteredGroupId . ", 0, convert(permission_group_id using utf8), 'viewReportPost', permission_value, permission_value_int
                from xf_permission_entry_content
                where permission_group_id = 'forum' and permission_id in ('viewContent')
            ");

            $db->query("insert ignore into xf_permission_entry (user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                select distinct " . XenForo_Model_User::$defaultRegisteredGroupId . ", 0, convert(permission_group_id using utf8), 'viewReportPost', permission_value, permission_value_int
                from xf_permission_entry
                where permission_group_id = 'forum' and permission_id in ('viewContent')
            ");
        }

        XenForo_Application::defer('Permission', array(), 'Permission', true);
    }

    public static function uninstall()
    {
        $db = XenForo_Application::getDb();

        $db->query("
            delete from xf_permission_entry
            where permission_id in (
                'whoRepliedView'
        )");

        $db->query("
            delete from xf_permission_entry_content
            where permission_id in (
                'whoRepliedView'
        )");

        XenForo_Application::defer('Permission', array(), 'Permission');
    }
}