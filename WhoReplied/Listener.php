<?php
class WhoReplied_Listener
{

    public static function templateHookListStickies($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {
        $contents = WhoReplied_Listener::searchAndReplaceReplyLink('threads', $template, $contents);
    }

    public static function templateHookListThreads($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {
        $contents = WhoReplied_Listener::searchAndReplaceReplyLink('threads', $template, $contents);
    }

    public static function templatePostRenderFindNewPosts($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
    {
        $content = WhoReplied_Listener::searchAndReplaceReplyLink('threads', $template, $content);
    }

    // XenPorta recent threads block
    public static function templatePostRenderEWRBlock($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
    {
        $content = WhoReplied_Listener::searchAndReplaceReplyLink('RecentThreads', $template, $content);
    }

    public static function extendControllerPublicThread($class, array &$extend)
    {
        $extend[] = 'WhoReplied_Extend_ControllerPublic_Thread';
    }

    public static function searchAndReplaceReplyLink($threadVar, $template, $contents)
    {
        $phrase = new XenForo_Phrase('whoreplied_whoreplied');

        $threads = $template->getParam($threadVar);
        $stickyThreads = $template->getParam('stickyThreads');
        if ($stickyThreads) {
            $threads = array_merge($threads, $stickyThreads);
        }

        foreach ($threads as $thread) {

            if (XenForo_Visitor::getInstance()->hasNodePermission($thread['node_id'], 'whoRepliedView')) {
                if ($thread['reply_count'] > 0) {
                    $link = XenForo_Link::buildPublicLink('threads/whoreplied', $thread);
                    $replace = "<a href='".$link."' title='" . $phrase . "' class='OverlayTrigger' data-href='". $link ."'>$3</a>";
                    $pattern = '/(<li id="thread-'. $thread['thread_id'] .'" class="(?:[^"]*?)" data-author="(?:[^"]*?)"[^>]*>(.*?)<dl class="major"><dt>.*?<\/dt> <dd>)([0-9,]*)(<\/dd><\/dl>)/s';
                    $contents = preg_replace($pattern, '$1' . $replace . '$4', $contents);
                }
            }
        }

        return $contents;
    }
}
