<?php
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
/**
 * Form for setting group options
 *
 * @copyright    http://www.XOOPS.org/
 * @copyright    http://www.impresscms.org/ The ImpressCMS Project
 * @license        http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package        Administration
 * @author        Kazumi Ono (AKA onokazu) http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/
 * @author        modified by UnderDog <underdog@impresscms.org>
 */

use Imponeer\Database\Criteria\CriteriaCompo;
use Imponeer\Database\Criteria\CriteriaItem;
use Imponeer\Database\Criteria\Enum\ComparisionOperator;

$name_text = new icms_form_elements_Text(_NAME, "name", 30, 50, $name_value);
$desc_text = new icms_form_elements_Textarea(_AM_DESCRIPTION, "desc", $desc_value);

$s_cat_checkbox = new icms_form_elements_Checkbox(_AM_SYSTEMRIGHTS, "system_catids[]", $s_cat_value);

include_once ICMS_MODULES_PATH . '/system/constants.php';
$admin_dir = ICMS_MODULES_PATH . '/system/admin/';
$dirlist = icms_core_Filesystem::getDirList($admin_dir);

/* changes to only allow permission admins you already have */
$gperm_handler = icms::handler('icms_member_groupperm');
$groups = icms::$user->getGroups();

foreach ($dirlist as $file) {
	if (file_exists(ICMS_MODULES_PATH . '/system/admin/' . $file . '/icms_version.php')) {
		icms_loadLanguageFile('system', $file, true);
		include ICMS_MODULES_PATH . '/system/admin/' . $file . '/icms_version.php';
	}
	if (!empty($modversion['category'])
		&& (count(array_intersect($groups, $gperm_handler->getGroupIds('system_admin', $modversion['category']))) > 0
			|| in_array(ICMS_GROUP_ADMIN, $groups))
	) {
		$s_cat_checkbox->addOption($modversion['category'], $modversion['name']);
	}
	unset($modversion);
}
unset($dirlist);

$a_mod_checkbox = new icms_form_elements_Checkbox(_AM_ACTIVERIGHTS, "admin_mids[]", $a_mod_value);

$module_handler = icms::handler('icms_module');
$criteria = new CriteriaCompo(new CriteriaItem('hasadmin', 1));
$criteria->add(new CriteriaItem('isactive', 1));
$criteria->add(new CriteriaItem('dirname', 'system', ComparisionOperator::NOT_EQUAL_TO));

/* criteria added to see if the active user can admin the module, do not filter for administrator group  (module_admin)*/
if (!in_array(ICMS_GROUP_ADMIN, $groups)) {
	$a_mod = $gperm_handler->getItemIds('module_admin', $groups);
	$criteria->add(new CriteriaItem('mid', '(' . implode(',', $a_mod) . ')', 'IN'));
}
$a_mod_checkbox->addOptionArray($module_handler->getList($criteria));

$r_mod_checkbox = new icms_form_elements_Checkbox(_AM_ACCESSRIGHTS, "read_mids[]", $r_mod_value);
$criteria = new CriteriaCompo(new CriteriaItem('hasmain', 1));
$criteria->add(new CriteriaItem('isactive', 1));

/* criteria added to see if the active user can access the module, do not filter for administrator group  (module_read)*/
if (!in_array(ICMS_GROUP_ADMIN, $groups)) {
	$r_mod = $gperm_handler->getItemIds('module_read', $groups);
	$criteria->add(new CriteriaItem('mid', '(' . implode(',', $r_mod) . ')', 'IN'));
}
$r_mod_checkbox->addOptionArray($module_handler->getList($criteria));

$criteria = new CriteriaCompo(new CriteriaItem('isactive', 1));

$debug_mod_checkbox = new icms_form_elements_Checkbox(_AM_DEBUG_PERM, "enabledebug_mids[]", $debug_mod_value);
$criteria = new CriteriaCompo(new CriteriaItem('isactive', 1));

/* criteria added to see where the active user can view the debug mode (enable_debug)
 * administrators do not have explicit entries for this, do not filter
 */
if (!in_array(ICMS_GROUP_ADMIN, $groups)) {
	$debug_mod = $gperm_handler->getItemIds('enable_debug', $groups);
	$criteria->add(new CriteriaItem('mid', '(' . implode(',', $debug_mod) . ')', 'IN'));
}
$debug_mod_checkbox->addOptionArray($module_handler->getList($criteria));

$group_manager_checkbox = new icms_form_elements_Checkbox(_AM_GROUPMANAGER_PERM, "groupmanager_gids[]", $group_manager_value);
$criteria = new CriteriaCompo(new CriteriaItem('isactive', 1));
$groups = $member_handler->getGroups();

foreach ($groups as $group) {
	if ($gperm_handler->checkRight('group_manager', $group->groupid, icms::$user->getGroups())) {
		$group_manager_checkbox->addOption($group->groupid, $group->name);
	}
	}
$icms_block_handler = icms::handler('icms_view_block');
$posarr = $icms_block_handler->getBlockPositions(true);
$block_checkbox = array();
$i = 0;
$groups = icms::$user->getGroups();
foreach ($posarr as $k=>$v) {
	$tit = (defined($posarr[$k]['title']))? constant($posarr[$k]['title']):$posarr[$k]['title'];
	$block_checkbox[$i] = new icms_form_elements_Checkbox('<strong>' . $tit . '</strong><br />', "read_bids[]", $r_block_value);
	$new_blocks_array = array();
	$blocks_array = $icms_block_handler->getAllBlocks("list", $k);

	/* compare to list of blocks the group can read, do not filter for administrator group */
	if (!in_array(ICMS_GROUP_ADMIN, $groups)) {
		$r_blocks = $gperm_handler->getItemIds('block_read', $groups);
		$n_blocks_array = array_intersect_key($blocks_array, array_flip($r_blocks));
	} else {
		$n_blocks_array = $blocks_array;
	}
	foreach ($n_blocks_array as $key=>$value) {
		$new_blocks_array[$key] = "<a href='" . ICMS_MODULES_URL . "/system/admin.php?fct=blocks&amp;op=mod&amp;bid=" . $key . "'>" . $value . " (ID: " . $key . ")</a>";
	}
	$block_checkbox[$i]->addOptionArray($new_blocks_array);
	$i++;
}
$r_block_tray = new icms_form_elements_Tray(_AM_BLOCKRIGHTS, "<br /><br />");
foreach ($block_checkbox as $k=>$v) {
	$r_block_tray->addElement($block_checkbox[$k]);
}

$op_hidden = new icms_form_elements_Hidden("op", $op_value);
$fct_hidden = new icms_form_elements_Hidden("fct", "groups");
$submit_button = new icms_form_elements_Button("", "groupsubmit", $submit_value, "submit");
$form = new icms_form_Theme($form_title, "groupform", "admin.php", "post", true);
$form->addElement($name_text, true);
$form->addElement($desc_text);
$form->addElement($s_cat_checkbox);

if (!isset($g_id) || ($g_id != ICMS_GROUP_ADMIN && $g_id != ICMS_GROUP_ANONYMOUS)) {
	$form->addElement($group_manager_checkbox);
}
$form->addElement($a_mod_checkbox);
$form->addElement($r_mod_checkbox);

if (!isset($g_id) || $g_id != ICMS_GROUP_ADMIN) {
	$form->addElement($debug_mod_checkbox);
}

$form->addElement($r_block_tray);
$form->addElement($op_hidden);
$form->addElement($fct_hidden);
if (!empty($g_id_value)) {
	$g_id_hidden = new icms_form_elements_Hidden("g_id", $g_id_value);
	$form->addElement($g_id_hidden);
}
$form->addElement($submit_button);
$form->setRequired($name_text);
$form->display(); // render() does not output the form, just contains the output