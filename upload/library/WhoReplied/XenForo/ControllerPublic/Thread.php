<?php

class WhoReplied_XenForo_ControllerPublic_Thread extends XFCP_WhoReplied_XenForo_ControllerPublic_Thread
{

    public function actionWhoreplied()
    {
        $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);

        // Filtering related
        $criteria = $this->_input->filterSingle('criteria', XenForo_Input::JSON_ARRAY);
        $criteria = $this->_filterUserSearchCriteria($criteria);

        $filter = $this->_input->filterSingle('_filter', XenForo_Input::ARRAY_SIMPLE);
        if ($filter && isset($filter['value']))
        {
            $criteria['username2'] = array($filter['value'], empty($filter['prefix']) ? 'lr' : 'r');
            $filterView = true;
        }
        else
        {
            $filterView = false;
        }

        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);

        if (!XenForo_Visitor::getInstance()->hasNodePermission($thread['node_id'], 'whoRepliedView')) {
            return $this->responseNoPermission();
        }

        $visitor = XenForo_Visitor::getInstance();
        $whoRepliedModel = $this->_getWhoRepliedModel();

        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $usersPerPage = XenForo_Application::getOptions()->WhoReplied_usersPerPage;
        $criteriaPrepared = $this->_prepareUserSearchCriteria($criteria);
        $totalUsers = $whoRepliedModel->countUsers($thread['thread_id'], $criteriaPrepared);

        $this->canonicalizePageNumber($page, $usersPerPage, $totalUsers, 'threads/whoreplied', $thread);
        $this->canonicalizeRequestUrl(
            XenForo_Link::buildPublicLink( 'threads/whoreplied', $thread, array('page' => $page))
        );

        $fetchOptions = array(
            'perPage' => $usersPerPage,
            'page' => $page
        );

        $users = $whoRepliedModel->getUsersAndCountPosts($thread, $fetchOptions, $criteriaPrepared);

        $viewParams = array(
            'canSearch' => $visitor->canSearch(),
            'users' => $users,
            'thread' => $thread,
            'forum' => $forum,
            'page' => $page,
            'usersPerPage' => $usersPerPage,
            'totalUsers' => $totalUsers,
            'filterView' => $filterView
        );

        return $this->responseView('WhoReplied_ViewPublic_Thread_WhoReplied', 'whoreplied_list', $viewParams);
    }

    /**
     * @return WhoReplied_Model_WhoReplied
     */
    protected function _getWhoRepliedModel()
    {
        return $this->getModelFromCache('WhoReplied_Model_WhoReplied');
    }

    /**
     * @return XenForo_ControllerHelper_UserCriteria
     */
    protected function _getCriteriaHelper()
    {
        return $this->getHelper('UserCriteria');
    }

    protected function _filterUserSearchCriteria(array $criteria)
    {
        return $this->_getCriteriaHelper()->filterUserSearchCriteria($criteria);
    }

    protected function _prepareUserSearchCriteria(array $criteria)
    {
        return $this->_getCriteriaHelper()->prepareUserSearchCriteria($criteria);
    }
}
