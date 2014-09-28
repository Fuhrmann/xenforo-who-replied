<?php
class WhoReplied_Extend_ControllerPublic_Thread extends XFCP_WhoReplied_Extend_ControllerPublic_Thread
{

    public function actionWhoreplied()
    {
        $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);

        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);

        if (!XenForo_Visitor::getInstance()->hasNodePermission($thread['node_id'], 'whoRepliedView')) {
            return $this->responseNoPermission();
        }

        $users = $this->_getWhoRepliedModel()->getUserAndCountPosts($thread);

        $viewParams = array(
            'users' => $users,
            'thread' => $thread,
            'forum' => $forum,
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
