<?php

class WhoReplied_Model_WhoReplied extends XenForo_Model
{
    /**
     * Count the number of users who replied to a thread.
     *
     * @param int $threadId The ID of the thread to count
     *
     * @return int The number of users who replied to the thread
     */
    public function countUsers($threadId)
    {
        return $this->_getDb()->fetchOne(
            'SELECT COUNT(*)
                FROM xf_thread_user_post
                WHERE xf_thread_user_post.thread_id = ?',
            $threadId
        );
    }

    /**
     * Get all users from a specific threads and count their posts.
     *
     * @param array $thread       Thread data
     * @param array $fetchOptions User fetch options
     *
     * @return array
     */
    public function getUsersAndCountPosts(array $thread, array $fetchOptions = array())
    {
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        $users = $this->fetchAllKeyed($this->limitQueryResults(
            'SELECT post_count, user.*
                FROM xf_thread_user_post posts
                JOIN xf_user user ON user.user_id = posts.user_id
                WHERE posts.thread_id = ?
                ORDER BY post_count DESC',
            $limitOptions['limit'],
            $limitOptions['offset']
        ), 'user_id', array($thread['thread_id']));

        // remove the first post from the post count
        if (isset($users[$thread['user_id']]))
        {
            $users[$thread['user_id']]['post_count'] = $users[$thread['user_id']]['post_count'] - 1;
        }
        return $users;
    }
}

