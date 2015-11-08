<?php

/**
 * NixFifty/TrashCan/ControllerPublic/Thread
 *
 * @package    NixFifty: Move to Trash Can
 * @author     NixFifty / Aakif Nazir
 * @copyright  2014 Aakif Nazir
 */

class NixFifty_TrashCan_ControllerPublic_Thread extends XFCP_NixFifty_TrashCan_ControllerPublic_Thread
{
	public function actionTrash()
	{
        if (!XenForo_Visitor::getInstance()->hasPermission('forum', 'trashThreads'))
        {
            throw $this->getErrorOrNoPermissionResponseException(false);
        }

		if (!$targetNode = XenForo_Application::getOptions()->nixfifty_trash_can)
		{
			return $this->responseError(new XenForo_Phrase('trash_can_node_not_set'));
		}

		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
        list($thread, $forum) = $this->getHelper('ForumThreadPost')->assertThreadValidAndViewable($threadId);

        if ($this->isConfirmedPost())
        {
            $softDelete = $this->_input->filterSingle('trash_type', XenForo_Input::UINT);
            $trashType = ($softDelete ? 'move' : 'soft');

            $options = array(
                'reason' => $this->_input->filterSingle('reason', XenForo_Input::STRING)
            );

            $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
            $dw->setExistingData($threadId);
            $dw->set('node_id', $targetNode);
            $dw->save();

            if ($trashType == 'soft' && XenForo_Visitor::getInstance()->hasPermission('forum', 'softDeleteTrashedThreads'))
            {
                $this->_getThreadModel()->deleteThread($threadId, $trashType, $options);

                XenForo_Model_Log::logModeratorAction(
                    'thread', $thread, 'delete_' . $trashType, array('reason' => $options['reason'])
                );
            }

            XenForo_Model_Log::logModeratorAction('thread', $thread, 'move', array('from' => $forum['title']));
            $this->_updateModeratorLogThreadEdit($thread, $dw, array('node_id'));

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildPublicLink('forums', $forum)
            );
        }
        else
        {
        	return $this->responseView('', 'trash_thread', array('thread'=> $thread));
        }
	}
}