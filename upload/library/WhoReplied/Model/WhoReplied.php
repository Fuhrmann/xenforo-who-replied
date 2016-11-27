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
    public function getUsersAndCountPosts(array $thread, array $fetchOptions = array(), array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        $users = $this->fetchAllKeyed($this->limitQueryResults(
            'SELECT *
             FROM xf_thread_user_post posts
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

        $posterIds = array_keys($users);

        $userModel = $this->_getUserModel();

        $posters = $userModel->getUsersByIds($posterIds, array(
            'join' => XenForo_Model_User::FETCH_USER_PRIVACY,
            'followingUserId' => $viewingUser['user_id']
        ));

        if (!empty($posters))
        {
            foreach ($users as $userId => &$user)
            {
                if (isset($posters[$user['user_id']]))
                {
                    $postCount = $user['post_count'];
                    $user = $posters[$user['user_id']];
                    $user['post_count'] = $postCount;
                    $user['canStartConversation'] = $userModel->canStartConversationWithUser($user, $null, $viewingUser);
                }
                else
                {
                    unset($users[$userId]);
                }
            }
        }

        return $users;
    }

    /**
     * @return XenForo_Model_User
     */
    protected function _getUserModel()
    {
        return $this->getModelFromCache('XenForo_Model_User');
    }
}

