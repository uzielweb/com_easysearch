<?php
/**
 * @package     Easy Search Lite
 * @subpackage  Modules
 * @copyright   Copyright (C) Hiro Nozu. All rights reserved.
 * @license     GNU/GPL
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * @package		Easy Search
 * @subpackage	Modules
 */
class HTML_modules
{

    /**
    * Replace content to display it alright
    * @param string $value
    * @param string $keyword
    */
    function replaceContent( $value, $keyword )
    {
        $value = strip_tags($value);
        $value = eregi_replace("({$keyword})", '<span style="font-weight: bold;">\\1</span>', $value);
        return $value;
    }

    /**
     * Writes a list of the defined modules
     *
     * @param array $rows
     * @param string $client
     * @param string $keyword
     * @param array $lists
     */
	function view( &$rows, &$client, $keyword, &$lists )
	{
		$user =& JFactory::getUser();

		JHTML::_('behavior.tooltip');
?>
        <script type="text/javascript"><!--
        function ExecuteTask( id, task ) {
            document.adminForm.cid.value = id;
            submitbutton(task);
            return false;
        }
        // --></script>
        <form action="index.php" method="post" name="adminForm">
        <!-- <form action="index.php" method="get" name="adminForm"> -->

			<table>
			<tr>
				<td align="left" width="100%">
					<input type="text" name="keyword" id="keyword" value="<?php echo $lists['keyword'];?>" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="this.form.submit();"><?php echo JText::_( 'Search' ); ?></button>
					<button onclick="document.getElementById('keyword').value='';this.form.submit();"><?php echo JText::_( 'Clear' ); ?></button>
				</td>
				<td nowrap="nowrap">
					<?php
/*
					echo $lists['assigned'];
					echo $lists['position'];
					echo $lists['type'];
*/
                    echo $lists['state'];
					?>
				</td>
			</tr>
			</table>

			<table class="adminlist" cellspacing="1">
			<thead>
			<tr>
<!--
				<th width="20">
					<?php // echo JText::_( 'NUM' ); ?>
				</th>
-->
                <th class="title">
                    <?php echo JHTML::_('grid.sort', 'Title', 'title', @$lists['order_Dir'], @$lists['order'] ); ?>
                </th>
                <th class="content">
                    <?php echo JHTML::_('grid.sort', 'Content', 'content', @$lists['order_Dir'], @$lists['order'] ); ?>
                </th>
				<th nowrap="nowrap" width="7%">
					<?php echo JHTML::_('grid.sort', 'Published', 'published', @$lists['order_Dir'], @$lists['order'] ); ?>
				</th>
				<th nowrap="nowrap" width="10%">
					<?php echo JHTML::_('grid.sort',   'Type', 'type', @$lists['order_Dir'], @$lists['order'] ); ?>
				</th>
<!--
				<th nowrap="nowrap" width="1%">
					<?php // echo JHTML::_('grid.sort',   'ID', 'id', @$lists['order_Dir'], @$lists['order'] ); ?>
				</th>
-->
 			</tr>
			</thead>
			<tbody>
<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {

			    $row = &$rows[$i];

			    $content = HTML_modules::replaceContent( $row->content, $keyword );

?>
				<tr class="<?php echo "row$k"; ?>">
<!--
					<td align="right">
						<?php // echo $i + 1; ?>
					</td>
-->
					<td nowrap width="20%">
<?php
					if (  JTable::isCheckedOut($user->get ('id'), $row->checked_out ) ) {
						echo $row->title;
                        $published = '';
					} else {
                        //    @todo Backup the data before redirecting to the edit screen
					    switch ($row->type) {
					        case 1: // module
                                $link      = JRoute::_( 'index.php?option=com_modules&task=module.edit&id='. $row->id );
                                $published = JHTML::_('grid.published', $row, $i, 'tick.png', 'publish_x.png', 'modules_' );
?>
                        <span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit Module' );?>::<?php echo $row->title; ?>">
<?php
                                break;
                            case 2: // article
                                $link = JRoute::_( 'index.php?option=com_content&task=article.edit&id='. $row->id );
                                $published = JHTML::_('grid.published', $row, $i, 'tick.png', 'publish_x.png', 'content_' );
?>
                        <span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit Article' );?>::<?php echo $row->title; ?>">
<?php
                                break;
                            case 3: // category
                                $link = JRoute::_( 'index.php?option=com_categories&task=category.edit&id=' . $row->id . '&extension='. $row->scope );
                                $published = JHTML::_('grid.published', $row, $i, 'tick.png', 'publish_x.png', 'categories_' );
?>
                        <span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit Category' );?>::<?php echo $row->title; ?>">
<?php
                                break;
                            case 4: // menu
                                $link      = JRoute::_( 'index.php?option=com_menus&task=item.edit&id=' . $row->id);
                                $published = JHTML::_('grid.published', $row, $i, 'tick.png', 'publish_x.png', 'menu_' );

                                $aParams = explode("\n", $row->content);
                                $content = '';
                                foreach ($aParams as $param) {
                                    $aParam = explode("=", $param);
                                    if ($aParam[0] != 'page_title') continue;
                                    if (eregi($keyword, $aParam[1])) {
                                        $content = 'Matched with "Page Title" at "Parameters - System" pane.<br />'
                                                 . '<p style="padding-left: 12px;">' . HTML_modules::replaceContent( $aParam[1], $keyword ) . '</p>';
                                        break;
                                    }
                                }
?>
                        <span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit Menu' );?>::<?php echo $row->title; ?>">
<?php
                                break;
                            case 6: // component
                                    $link      = JRoute::_(str_replace('{$id}', $row->id, $row->scope));
                                    $published = null;
                                break;
                            case 99: // custom
                                    $link      = $row->link;
                                    $published = null;
                                break;
}

                        $published = ereg_replace('listItemTask\(\'cb[0-9]+\'', "ExecuteTask('{$row->id}'", $published);
?>
						<a href="<?php echo $link; ?>"><?php echo HTML_modules::replaceContent( $row->title, $keyword ); ?></a></span>
<?php
					}
?>
					</td>
                    <td>
                        <?php echo $content; ?>
                    </td>
                    <td align="center">
                        <?php echo $published; ?>
                    </td>
					<td>
						<?php echo $row->type_name; ?>
					</td>
<!-- 
					<td>
						<?php // echo $row->id; ?>
					</td>
-->
				</tr>
<?php
				$k = 1 - $k;
			}
?>
			</tbody>
			</table>

            <p style="text-align: center;margin-bottom: 0;"><a target="_blank" href="http://easysearch.forjoomla.net/" />Easy Search</a><br />2009 Hiro Nozu - All rights reserved.</p>  <p>Adaptado para Joomla 2.5 por Uziel Almeida Oliveira- Ponto Mega
    <br />Setembro de 2012</p>

		<input type="hidden" name="option" value="com_easysearch" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="cid" value="" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		<?php
	}


	/**
	* Writes the edit form for new and existing module
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param JTableCategory The category object
	* @param array <p>The modules of the left side.  The array elements are in the form
	* <var>$leftorder[<i>order</i>] = <i>label</i></var>
	* where <i>order</i> is the module order from the db table and <i>label</i> is a
	* text label associciated with the order.</p>
	* @param array See notes for leftorder
	* @param array An array of select lists
	* @param object Parameters
	*/
	function edit( &$model, &$row, &$orders2, &$lists, &$params, $client )
	{
		JRequest::setVar( 'hidemainmenu', 1 );

		// clean item data
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'content' );

		// Check for component metadata.xml file
		//$path = JApplicationHelper::getPath( 'mod'.$client->id.'_xml', $row->module );
		//$params = new JParameter( $row->params, $path );
		$document =& JFactory::getDocument();

		JHTML::_('behavior.combobox');

		jimport('joomla.html.pane');
		$pane =& JPane::getInstance('sliders');
		$editor 	=& JFactory::getEditor();

		JHTML::_('behavior.tooltip');
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if ( ( pressbutton == 'save' || pressbutton == 'apply' ) && ( document.adminForm.title.value == "" ) ) {
				alert("<?php echo JText::_( 'Module must have a title', true ); ?>");
			} else {
				<?php
				if ($row->module == '' || $row->module == 'mod_custom') {
					echo $editor->save( 'content' );
				}
				?>
				submitform(pressbutton);
			}
		}
		<!--
		var originalOrder 	= '<?php echo $row->ordering;?>';
		var originalPos 	= '<?php echo $row->position;?>';
		var orders 			= new Array();	// array in the format [key,value,text]
		<?php	$i = 0;
		foreach ($orders2 as $k=>$items) {
			foreach ($items as $v) {
				echo "\n	orders[".$i++."] = new Array( \"$k\",\"$v->value\",\"$v->text\" );";
			}
		}
		?>
		//-->
		</script>
		<form action="index.php" method="post" name="adminForm">
		<div class="col width-50">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Details' ); ?></legend>

				<table class="admintable" cellspacing="1">
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_( 'Module Type' ); ?>:
						</td>
						<td>
							<strong>
								<?php echo JText::_($row->module); ?>
							</strong>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_( 'Title' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="title" id="title" size="35" value="<?php echo $row->title; ?>" />
						</td>
					</tr>
					<tr>
						<td width="100" class="key">
							<?php echo JText::_( 'Show title' ); ?>:
						</td>
						<td>
							<?php echo $lists['showtitle']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_( 'Published' ); ?>:
						</td>
						<td>
							<?php echo $lists['published']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<label for="position" class="hasTip" title="<?php echo JText::_('MODULE_POSITION_TIP_TITLE', true); ?>::<?php echo JText::_('MODULE_POSITION_TIP_TEXT', true); ?>">
								<?php echo JText::_( 'Position' ); ?>:
							</label>
						</td>
						<td>
							<input type="text" id="position" class="combobox" name="position" value="<?php echo $row->position; ?>" />
							<ul id="combobox-position" style="display:none;"><?php
							$positions = $model->getPositions();
							for ($i=0,$n=count($positions);$i<$n;$i++) {
								echo '<li>',$positions[$i],'</li>';
							}
							?></ul>
						</td>
					</tr>
					<tr>
						<td valign="top"  class="key">
							<label for="ordering">
								<?php echo JText::_( 'Order' ); ?>:
							</label>
						</td>
						<td>
							<script language="javascript" type="text/javascript">
							<!--
							writeDynaList( 'class="inputbox" name="ordering" id="ordering" size="1"', orders, originalPos, originalPos, originalOrder );
							//-->
							</script>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<label for="access">
								<?php echo JText::_( 'Access Level' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['access']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_( 'ID' ); ?>:
						</td>
						<td>
							<?php echo $row->id; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_( 'Description' ); ?>:
						</td>
						<td>
							<?php echo JText::_($row->description); ?>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Menu Assignment' ); ?></legend>
				<script type="text/javascript">
					function allselections() {
						var e = document.getElementById('selections');
							e.disabled = true;
						var i = 0;
						var n = e.options.length;
						for (i = 0; i < n; i++) {
							e.options[i].disabled = true;
							e.options[i].selected = true;
						}
					}
					function disableselections() {
						var e = document.getElementById('selections');
							e.disabled = true;
						var i = 0;
						var n = e.options.length;
						for (i = 0; i < n; i++) {
							e.options[i].disabled = true;
							e.options[i].selected = false;
						}
					}
					function enableselections() {
						var e = document.getElementById('selections');
							e.disabled = false;
						var i = 0;
						var n = e.options.length;
						for (i = 0; i < n; i++) {
							e.options[i].disabled = false;
						}
					}
				</script>
				<table class="admintable" cellspacing="1">
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_( 'Menus' ); ?>:
						</td>
						<td>
						<?php if ($row->client_id != 1) : ?>
							<?php if ($row->pages == 'all') { ?>
							<label for="menus-all"><input id="menus-all" type="radio" name="menus" value="all" onclick="allselections();" checked="checked" /><?php echo JText::_( 'All' ); ?></label>
							<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" /><?php echo JText::_( 'None' ); ?></label>
							<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" /><?php echo JText::_( 'Select From List' ); ?></label>
							<?php } elseif ($row->pages == 'none') { ?>
							<label for="menus-all"><input id="menus-all" type="radio" name="menus" value="all" onclick="allselections();" /><?php echo JText::_( 'All' ); ?></label>
							<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" checked="checked" /><?php echo JText::_( 'None' ); ?></label>
							<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" /><?php echo JText::_( 'Select From List' ); ?></label>
							<?php } else { ?>
							<label for="menus-all"><input id="menus-all" type="radio" name="menus" value="all" onclick="allselections();" /><?php echo JText::_( 'All' ); ?></label>
							<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" /><?php echo JText::_( 'None' ); ?></label>
							<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" checked="checked" /><?php echo JText::_( 'Select From List' ); ?></label>
							<?php } ?>
						<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_( 'Menu Selection' ); ?>:
						</td>
						<td>
							<?php echo $lists['selections']; ?>
						</td>
					</tr>
				</table>
				<?php if ($row->client_id != 1) : ?>
					<?php if ($row->pages == 'all') { ?>
					<script type="text/javascript">allselections();</script>
					<?php } elseif ($row->pages == 'none') { ?>
					<script type="text/javascript">disableselections();</script>
					<?php } else { ?>
					<?php } ?>
				<?php endif; ?>
			</fieldset>
		</div>

		<div class="col width-50">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Parameters' ); ?></legend>

				<?php
					echo $pane->startPane("menu-pane");
					echo $pane->startPanel(JText :: _('Module Parameters'), "param-page");
					$p = $params;
					if($params = $p->render('params')) :
						echo $params;
					else :
						echo "<div style=\"text-align: center; padding: 5px; \">".JText::_('There are no parameters for this item')."</div>";
					endif;
					echo $pane->endPanel();

					if ($p->getNumParams('advanced')) {
						echo $pane->startPanel(JText :: _('Advanced Parameters'), "advanced-page");
						if($params = $p->render('params', 'advanced')) :
							echo $params;
						else :
							echo "<div  style=\"text-align: center; padding: 5px; \">".JText::_('There are no advanced parameters for this item')."</div>";
						endif;
						echo $pane->endPanel();
					}

					if ($p->getNumParams('legacy')) {
						echo $pane->startPanel(JText :: _('Legacy Parameters'), "legacy-page");
						if($params = $p->render('params', 'legacy')) :
							echo $params;
						else :
							echo "<div  style=\"text-align: center; padding: 5px; \">".JText::_('There are no legacy parameters for this item')."</div>";
						endif;
						echo $pane->endPanel();
					}
					echo $pane->endPane();
				?>
			</fieldset>
		</div>
		<div class="clr"></div>

		<?php
		if ( !$row->module || $row->module == 'custom' || $row->module == 'mod_custom' ) {
			?>
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Custom Output' ); ?></legend>

				<?php
				// parameters : areaname, content, width, height, cols, rows
				echo $editor->display( 'content', $row->content, '100%', '400', '60', '20', array('pagebreak', 'readmore') ) ;
				?>

			</fieldset>
			<?php
		}
		?>

		<input type="hidden" name="option" value="com_modules" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="original" value="<?php echo $row->ordering; ?>" />
		<input type="hidden" name="module" value="<?php echo $row->module; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="client" value="<?php echo $client->id ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
	<?php
	}
}