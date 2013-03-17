<?php
class WhoReplied_Listener
{
	public static function template_hook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		// Normal and sticky threads
		if ($hookName == 'thread_list_threads' || $hookName == 'thread_list_stickies')
		{
			$contents = WhoReplied_Listener::searchAndReplaceReplyLink('threads', $template, $contents);
		}
	}

	public static function template_post_render($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
	{
		// XenPorta recent threads block
		if ($templateName == 'EWRblock_RecentThreads')
		{
			$content = WhoReplied_Listener::searchAndReplaceReplyLink('RecentThreads', $template, $content);
		}

		 if ($templateName == 'find_new_threads')
		 {
		 	$content = WhoReplied_Listener::searchAndReplaceReplyLink('threads', $template, $content);
		 }
	}

	public static function extend($class, array &$extend)
	{
		switch ($class)
		{
			// Extend this so we can create our own action to show the users who replied
			case 'XenForo_ControllerPublic_Thread':
				$extend[] = 'WhoReplied_Extend_ControllerPublic_Thread';
				break;
		}
	}

	public static function searchAndReplaceReplyLink($threadVar, $template, $contents)
	{
		$phrase = new XenForo_Phrase('whoreplied_whoreplied');

		$threads = $template->getParam($threadVar);
		$stickyThreads = $template->getParam('stickyThreads');
		if ($stickyThreads) {
			$threads = array_merge($threads, $stickyThreads);
		}

		foreach ($threads as $thread)
        {
        	// Only aply the link on threads that have replies
        	if ($thread['reply_count'] > 0) {
				$link = XenForo_Link::buildPublicLink('threads/whoreplied', $thread);
            	$replace = "<a href='".$link."' title='" . $phrase . "' class='OverlayTrigger' data-href='". $link ."'>$3</a>";
            	$pattern = '/(<li id="thread-'. $thread['thread_id'] .'" class="(?:[^"]*?)" data-author="(?:[^"]*?)"[^>]*>(.*?)<dl class="major"><dt>.*?<\/dt> <dd>)([0-9,]*)(<\/dd><\/dl>)/s';
            	$contents = preg_replace($pattern, '$1' . $replace . '$4', $contents);
        	}
        }

        return $contents;
	}
}