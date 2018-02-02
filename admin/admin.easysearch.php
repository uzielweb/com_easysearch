<?php
/**
 * @package     Easy Search Lite
 * @subpackage  Modules
 * @copyright   Copyright (C) Hiro Nozu. All rights reserved.
 * @license     GNU/GPL
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Create the controller
$controller	= new EasySearchController( array( 'default_task' => 'view' ));

// Perform the Request task
$controller->execute( JRequest::getCmd('task', 'view') );

// Redirect if set by the controller
$controller->redirect();
