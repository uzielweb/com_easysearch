<?php
/**
 * @package     Easy Search Lite
 * @subpackage  Modules
 * @copyright   Copyright (C) Hiro Nozu. All rights reserved.
 * @license		GNU/GPL
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );

//JSubMenuHelper::addEntry(JText::_('Search'), 'index.php?option=com_easysearch');
//JSubMenuHelper::addEntry(JText::_('Setting'), 'index.php?option=com_easysearch&task=setting');

class EasySearchController extends JController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
        parent::__construct( $config );
        // Register Extra tasks
        $this->registerTask( 'modules_publish',         'publish' );
        $this->registerTask( 'modules_unpublish',       'publish' );
        $this->registerTask( 'content_publish',         'publish' );
        $this->registerTask( 'content_unpublish',       'publish' );
        $this->registerTask( 'categories_publish',      'publish' );
        $this->registerTask( 'categories_unpublish',    'publish' );
        $this->registerTask( 'menu_publish',            'publish' );
        $this->registerTask( 'menu_unpublish',          'publish' );
	}

	/**
	 * Compiles a list of installed or defined modules
	 */
	function view()
	{
		global $app;


		// Initialize some variables
		$db		 =& JFactory::getDBO();
		$client	 =& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$option	 =  'com_easysearch';

        $filter_order     = $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'title', 'cmd' );
        $filter_order_Dir = $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', '', 'word' );

        $filter_state = $app->getUserStateFromRequest( $option.'filter_state', 'filter_state', '', 'word' );

		$keyword = $app->getUserStateFromRequest( $option.'keyword', 'keyword', '', 'string' );
		$keyword = JString::strtolower( $keyword );

/*
		$limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$limitstart	= $app->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
*/

		// state filter
        $lists['state'] = JHTML::_('grid.state',  $filter_state );

        if ($keyword != '') {

//            if (ereg('^"(.+)"$', $keyword, $match)) {
//                $sql_keyword = '%' . $match[1] . '%';
//            }else{
//                $sql_keyword = '%' . implode('%', explode(' ', $keyword)) . '%';
//            }
            $sql_keyword = "%{$keyword}%";

            $where_modules[] = 'LOWER(m.title) LIKE ' . $db->Quote($sql_keyword);
            $where_modules[] = 'LOWER(m.content) LIKE ' . $db->Quote($sql_keyword);

            $where_content[] = 'LOWER(c.title) LIKE ' . $db->Quote($sql_keyword);
            $where_content[] = 'LOWER(c.introtext) LIKE ' . $db->Quote($sql_keyword);
            $where_content[] = 'LOWER(c.fulltext) LIKE ' . $db->Quote($sql_keyword);


            $where_categories[] = 'LOWER(cat.title) LIKE ' . $db->Quote($sql_keyword);
            $where_categories[] = 'LOWER(cat.description) LIKE ' . $db->Quote($sql_keyword);

            $where_menu[] = 'LOWER(menu.title) LIKE ' . $db->Quote($sql_keyword);
            $where_menu[] = 'LOWER(menu.params) LIKE ' . $db->Quote("%page_title=%{$keyword}%");

            $where_modules    = ' WHERE (' . implode( ' OR ', $where_modules ) . ')';
            $where_content    = ' WHERE (' . implode( ' OR ', $where_content ) . ')';

            $where_categories = ' WHERE (' . implode( ' OR ', $where_categories ) . ')';
            $where_menu       = ' WHERE (' . implode( ' OR ', $where_menu ) . ')';

            if ( $filter_state ) {
                if ( $filter_state == 'P' ) {
                    $where_modules    .= ' AND m.published = 1';
                    $where_content    .= ' AND cat.state = 1';

                     $where_menu       .= ' AND menu.published = 1';
                } else if ($filter_state == 'U' ) {
                    $where_modules    .= ' AND m.published = 0';
                    $where_content    .= ' AND cat.state = 0';

                    $where_menu       .= ' AND menu.published = 0';
                }
            }


            // Join all the SQL
            $query = 'SELECT m.id, m.published, m.checked_out, m.title, m.content, CONCAT(\'Module (\', m.module, \')\') AS type_name, 1 AS type, \'\' AS scope'
                   . ' FROM #__modules AS m'
                   . $where_modules
                   . ' UNION SELECT c.id, c.state AS published, c.checked_out, c.title, c.introtext AS content, \'Article\' AS type_name, 2 AS type, \'\' AS scope'
                   . ' FROM #__content AS c'
                   . $where_content
                  . ' UNION SELECT cat.id, cat.published, cat.checked_out, cat.title, cat.description AS content, \'Category\' AS type_name, 3 AS type, cat.extension AS scope'
                   . ' FROM #__categories AS cat'
                   . $where_categories
                   . ' UNION SELECT menu.id, menu.published, menu.checked_out, menu.title AS title, menu.params AS content, CONCAT(\'Menu (\', menu.menutype, \', \', menu.type , \')\')  AS type_name, 4 AS type, menu.menutype AS scope'
                   . ' FROM #__menu AS menu'
                   . $where_menu
                   . ' ORDER BY '. $filter_order .' '. $filter_order_Dir;
        }else{
            $query = 'SELECT NULL LIMIT 0';
        }
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		if ($db->getErrorNum()) {
			echo $db->stderr();
			return false;
		}

        switch ($keyword) {
            case 'copyright':
            case 'joomla':
            case 'free':
            case 'software':
                $tmpClass =& new StdClass();
                $tmpClass->id = '';
                $tmpClass->published = null;
                $tmpClass->checked_out = '';
                $tmpClass->title = 'Joomla! Copyright information';
                $tmpClass->content = 'Extensions &raquo; Module Manager &raquo; Select "mod_footer" at "- Select Type -"';
                $tmpClass->type = 99;
                $tmpClass->type_name = '';
                $tmpClass->link = 'index.php?option=com_modules&task=module.edit&id=33';
                $rows[] = $tmpClass;
                break;
        }

		// table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']     = $filter_order;

        // keyword filter
		$lists['keyword'] = htmlspecialchars($keyword);

		require_once( JApplicationHelper::getPath( 'admin_html' ) );
        HTML_modules::view( $rows, $client, $keyword, $lists );
	}


	/**
	* Publishes or Unpublishes one or more modules
	*/
	function publish()
	{
        global $app;


		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		// Initialize some variables
		$db 	=& JFactory::getDBO();
		$user 	=& JFactory::getUser();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect( 'index.php?option=com_easysearch' );

		$cache = & JFactory::getCache();
		$cache->clean( 'com_content' );

		$cid  = JRequest::getVar( 'cid', '', 'post', 'integer' );
		$task = $this->getTask();

		if (empty( $cid )) {
			return JError::raiseWarning( 500, 'No items selected' );
		}

		$tmp_task = explode('_', $task);
        $table    = "#__{$tmp_task[0]}";
        $field    = ($tmp_task[0] == 'content') ? 'state' : 'published';
        $publish  = ($tmp_task[1] == 'publish') ? '1' : '0';

        $query = "UPDATE {$table}"
		. " SET {$field} = {$publish}"
		. " WHERE id = {$cid}"
		. ' AND ( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ) )'
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getErrorMsg() );
		}
	}
}
