<?php

class WhoReplied_ViewPublic_Thread_WhoReplied extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        /** @var XenForo_ViewRenderer_Json $renderer */
        $renderer = $this->_renderer;
        $output = $renderer->getDefaultOutputArray(null, $this->_params, $this->_templateName);

        // xenforo reverses the js output array in overlay mode for some reason
        $output['js'] = array_reverse($output['js']);

        return $output;
    }
}