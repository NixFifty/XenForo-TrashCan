<?php

/**
 * NixFifty/TrashCan/ControllerPublic/InlineMod/Thread
 *
 * @package    NixFifty: Move to Trash Can
 * @author     NixFifty / Aakif Nazir
 * @copyright  2014 Aakif Nazir
 */

class NixFifty_TrashCan_ControllerPublic_InlineMod_Thread extends XFCP_NixFifty_TrashCan_ControllerPublic_InlineMod_Thread
{
    public function actionTrash()
    {
        if ($this->isConfirmedPost())
        {
            $threadIds = $this->getInlineModIds(false);
            $softDelete = $this->_input->filterSingle('trash_type', XenForo_Input::UINT);

            $options = array(
                'deleteType' => ($softDelete ? 'move' : 'soft'),
                'reason' => $this->_input->filterSingle('reason', XenForo_Input::STRING)
            );

            $deleted = $this->_getInlineModThreadModel()->trashThreads($threadIds, $options, $errorPhraseKey);

            if (!$deleted)
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }

            $this->clearCookie();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->getDynamicRedirect(false, false)
            );
        }

        else
        {
            $threadIds = $this->getInlineModIds();

            if (!XenForo_Visitor::getInstance()->hasPermission('forum', 'trashThreads'))
            {
                throw $this->getErrorOrNoPermissionResponseException(false);
            }

            $redirect = $this->getDynamicRedirect();

            if (!$threadIds)
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $redirect
                );
            }

            $viewParams = array(
                'threadIds' => $threadIds,
                'threadCount' => count($threadIds),
                'redirect' => $redirect,
            );
            return $this->responseView('XenForo_ViewPublic_InlineMod_Thread_Delete', 'inline_mod_thread_trash', $viewParams);
        }
    }
}