<?php
/**
 * @package     Easy Search Lite
 * @subpackage  Modules
 * @copyright   Copyright (C) Hiro Nozu. All rights reserved.
 * @license     GNU/GPL
 */
class TOOLBAR_modules {
    function _DEFAULT()
    {
        JToolBarHelper::title( JText::_( 'Easy Search Lite' ) . '<small> : <small>Find what you wanna edit!</small></small>', 'module.png' );
	}
}
