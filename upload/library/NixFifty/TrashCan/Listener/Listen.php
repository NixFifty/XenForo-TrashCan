<?php

/**
 * NixFifty/TrashCan/Listener/Listen
 *
 * @package    NixFifty: Move to Trash Can
 * @author     NixFifty / Aakif Nazir
 * @copyright  2014 Aakif Nazir
 */

class NixFifty_TrashCan_Listener_Listen
{
	public static function loadClassController($class, &$extend)
	{
		if ($class == 'XenForo_ControllerPublic_Thread')
		{
			$extend[] = 'NixFifty_TrashCan_ControllerPublic_Thread';
		}

        if ($class == 'XenForo_ControllerPublic_InlineMod_Thread')
        {
            $extend[] = 'NixFifty_TrashCan_ControllerPublic_InlineMod_Thread';
        }
	}

    public static function loadClassModel($class, &$extend)
    {
        if ($class == 'XenForo_Model_InlineMod_Thread')
        {
            $extend[] = 'NixFifty_TrashCan_Model_InlineMod_Thread';
        }
    }
}
