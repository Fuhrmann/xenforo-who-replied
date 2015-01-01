<?php

class WhoReplied_Model_WhoReplied extends XenForo_Model
{
	/**
	 * Get all users from a specific threads and count their posts
	 * @param  [array] $thread Thread info
	 * @return [array]
	 */
	public function getUserAndCountPosts($thread)
	{
        $users = $this->fetchAllKeyed("
            SELECT post_count, user.*
            FROM xf_thread_user_post posts
            JOIN xf_user user ON user.user_id = posts.user_id
            WHERE posts.thread_id = '". $thread['thread_id'] . "'
            ORDER BY post_count DESC
            ", "user_id");
        // remove the first post from the post count
        if (isset($users[$thread['user_id']]))
        {
            $users[$thread['user_id']]['post_count'] = $users[$thread['user_id']]['post_count'] - 1;
        }
		return $users;
	}


}


