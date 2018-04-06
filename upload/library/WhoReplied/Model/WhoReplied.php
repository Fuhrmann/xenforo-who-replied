<?php

class WhoReplied_Model_WhoReplied extends XenForo_Model
{
    /**
     * Count the number of users who replied to a thread.
     *
     * @param int $threadId The ID of the thread to count
     * @param     $conditions
     * @return int The number of users who replied to the thread
     */
    public function countUsers($threadId, array $conditions)
    {
        $whereClause = $this->prepareUserConditions($conditions);

        return $this->_getDb()->fetchOne(
            "SELECT COUNT(*)
                FROM xf_thread_user_post as tup
                LEFT JOIN xf_user as `user` ON user.user_id=tup.user_id
                WHERE tup.thread_id = ?
                AND $whereClause
            ",
            $threadId
        );
    }

    /**
     * Get all users from a specific threads and count their posts.
     *
     * @param array      $thread       Thread data
     * @param array      $fetchOptions User fetch options
     * @param array      $conditions
     * @param array|null $viewingUser
     * @return array
     */
    public function getUsersAndCountPosts(array $thread, array $fetchOptions = array(), array $conditions, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        $whereClause = $this->prepareUserConditions($conditions);

        $users = $this->fetchAllKeyed($this->limitQueryResults(
            "SELECT *
             FROM xf_thread_user_post posts
             LEFT JOIN xf_user as `user` ON user.user_id=posts.user_id
             WHERE posts.thread_id = ?
             AND $whereClause
             ORDER BY posts.post_count DESC, posts.user_id",
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
     * @return XenForo_Model|XenForo_Model_User
     */
    protected function _getUserModel()
    {
        return $this->getModelFromCache('XenForo_Model_User');
    }

    private function prepareUserConditions($conditions)
    {
        $db = $this->_getDb();
        $sqlConditions = array();

        if (!empty($conditions['username2']))
        {
            if (is_array($conditions['username2']))
            {
                $sqlConditions[] = 'user.username LIKE ' . XenForo_Db::quoteLike($conditions['username2'][0], $conditions['username2'][1], $db);
            }
            else
            {
                $sqlConditions[] = 'user.username LIKE ' . XenForo_Db::quoteLike($conditions['username2'], 'lr', $db);
            }
        }
        return $this->getConditionsForClause($sqlConditions);
    }
}

