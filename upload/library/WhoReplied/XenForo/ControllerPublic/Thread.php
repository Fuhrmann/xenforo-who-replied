<?php

class WhoReplied_XenForo_ControllerPublic_Thread extends XFCP_WhoReplied_XenForo_ControllerPublic_Thread
{

    public function actionWhoreplied()
    {
        $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);

        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);

        if (!XenForo_Visitor::getInstance()->hasNodePermission($thread['node_id'], 'whoRepliedView')) {
            return $this->responseNoPermission();
        }

        $visitor = XenForo_Visitor::getInstance();
        $whoRepliedModel = $this->_getWhoRepliedModel();

        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $usersPerPage = XenForo_Application::getOptions()->WhoReplied_usersPerPage;
        $totalUsers = $whoRepliedModel->countUsers($thread['thread_id']);

        $this->canonicalizePageNumber($page, $usersPerPage, $totalUsers, 'threads/whoreplied', $thread);
        $this->canonicalizeRequestUrl(
            XenForo_Link::buildPublicLink( 'threads/whoreplied', $thread, array('page' => $page))
        );

        $fetchOptions = array(
            'perPage' => $usersPerPage,
            'page' => $page
        );

        $users = $whoRepliedModel->getUsersAndCountPosts($thread, $fetchOptions);

        $viewParams = array(
            'canSearch' => $visitor->canSearch(),
            'users' => $users,
            'thread' => $thread,
            'forum' => $forum,
            'page' => $page,
            'usersPerPage' => $usersPerPage,
            'totalUsers' => $totalUsers
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

}
