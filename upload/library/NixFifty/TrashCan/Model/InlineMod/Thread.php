<?php

/**
 * NixFifty/TrashCan/Model/InlineMod/Thread
 *
 * @package    NixFifty: Move to Trash Can
 * @author     NixFifty / Aakif Nazir
 * @copyright  2014 Aakif Nazir
 */

class NixFifty_TrashCan_Model_InlineMod_Thread extends XFCP_NixFifty_TrashCan_Model_InlineMod_Thread
{
    public function trashThreads(array $threadIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        if (!$targetNode = XenForo_Application::getOptions()->nixfifty_trash_can)
        {
            return $this->responseError(new XenForo_Phrase('trash_can_node_not_set'));
        }

        $options = array_merge(
            array(
                'deleteType' => '',
                'reason' => ''
            ), $options
        );

        if (!$options['deleteType'])
        {
            throw new XenForo_Exception('No deletion type specified.');
        }

        list($threads, $forums) = $this->getThreadsAndParentData($threadIds, $viewingUser);

        if (!XenForo_Visitor::getInstance()->hasPermission('forum', 'trashThreads'))
        {
            return false;
        }

        foreach ($threads AS $thread)
        {
            $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
            if (!$dw->setExistingData($thread))
            {
                continue;
            }

            if (array_key_exists($dw->get('node_id'), $forums))
            {
                $dw->setExtraData(XenForo_DataWriter_Discussion_Thread::DATA_FORUM, $forums[$dw->get('node_id')]);
            }

            $dw->set('node_id', $targetNode);
            $dw->save();

            if ($options['deleteType'] == 'soft')
            {
                $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
                if (!$dw->setExistingData($thread))
                {
                    continue;
                }

                $dw->setExtraData(XenForo_DataWriter_Discussion::DATA_DELETE_REASON, $options['reason']);
                $dw->set('discussion_state', 'deleted');
                $dw->save();
            }

            if ($this->enableLogging)
            {
                if ($thread['node_id'] != $targetNode)
                {
                    $forum = $this->_getForumFromThread($thread, $forums);
                    $forumTitle = ($forum ? $forum['title'] : '');

                    XenForo_Model_Log::logModeratorAction('thread', $thread, 'move', array('from' => $forumTitle));
                }

                if ($options['deleteType'] == 'soft' && !$thread['discussion_state'] == 'deleted')
                {
                    XenForo_Model_Log::logModeratorAction('thread', $thread, 'delete_soft', array('reason' => $options['reason']));
                }
            }
        }
        return true;
    }
} 