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
		return $this->fetchAllKeyed("
						SELECT COUNT(posts.post_id) as post_count, user.*
							FROM xf_post as posts
						INNER JOIN xf_user AS user
							ON user.user_id = posts.user_id
						WHERE posts.thread_id = '". $thread['thread_id'] . "' AND
							  posts.position <> 0 AND
							  posts.message_state ='visible'
						GROUP BY user.user_id
						ORDER BY post_count DESC
						"
				, "user_id");
	}


}


