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
 * Administration of preferences, main file
 *
 * @copyright    http://www.XOOPS.org/
 * @copyright    http://www.impresscms.org/ The ImpressCMS Project
 * @license        http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package        System
 * @subpackage    Preferences
 */

use ImpressCMS\Core\DataFilter;
use ImpressCMS\Core\Extensions\Editors\EditorsRegistry;

if (!is_object(icms::$user)
	|| !is_object($icmsModule)
	|| !icms::$user->isAdmin($icmsModule->mid)
) {
	exit("Access Denied");
}
if (isset($_POST)) {
	$post_vars = filter_input_array(INPUT_POST);
	if (is_array($post_vars)) {
		extract($post_vars);
	}
}
$icmsAdminTpl = new icms_view_Tpl();
$op = (isset($_GET['op']))
	? trim(filter_input(INPUT_GET, 'op'))
	: ((isset($_POST['op']))
		? trim(filter_input(INPUT_POST, 'op'))
		: 'list');

if (isset($_GET['confcat_id'])) {
	$confcat_id = (int) $_GET['confcat_id'];
}

switch ($op) {
	default:
	case 'list':
		/*
	 * Allow easely change the order of Preferences.
	 * $order = 1; Alphabetically order;
	 * $order = 0; Weight order;
	 *
	 * @todo: Create a preference option to set this value and improve the way to change the order.
	 */
		$order = 1;
		$confcat_handler = icms::handler('icms_config_category');
		$confcats = $confcat_handler->getObjects();
		$catcount = count($confcats);
		$ccats = array();
		$i = 0;
		foreach ($confcats as $confcat) {
			$ccats[$i]['id'] = $confcat->confcat_id;
			$ccats[$i]['name'] = constant($confcat->confcat_name);
			$column[] = constant($confcat->confcat_name);
			$i++;
		}
		if ($order == 1) {
			array_multisort($column, SORT_ASC, $ccats);
		}

		icms_cp_header();
		echo '<div class="CPbigTitle" style="background-image: url(' . ICMS_MODULES_URL . '/system/admin/preferences/images/preferences_big.png)">' . _MD_AM_SITEPREF . '</div><br /><div class="CPindexOptions" align="center"><div class="cpicon cpicon-preferences" align="center">';
		foreach ($ccats as $confcat) {
			echo '<a href="admin.php?fct=preferences&amp;op=show&amp;confcat_id=' . $confcat['id'] . '" title="' . _EDIT . ' ' . $confcat['name'] . '"><img alt="icon" src="' . ICMS_MODULES_URL . '/system/images/preferences/' . $confcat['id'] . '.png"><div> ' . $confcat['name'] . '</div></a>';
		}
		echo '</div></div>';
		icms_cp_footer();
		break;

	case 'show':
		if (empty($confcat_id)) {
			$confcat_id = 1;
		}
		$confcat_handler = icms::handler('icms_config_category');
		$confcat = &$confcat_handler->get($confcat_id);
		if (!is_object($confcat)) {
			redirect_header('admin.php?fct=preferences', 1);
		}
		global $icmsConfigUser;
		$form = new icms_form_Theme(constant($confcat->confcat_name), 'pref_form', 'admin.php?fct=preferences', 'post', true);
		$config_handler = icms::handler('icms_config');
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('conf_modid', 0));
		$criteria->add(new icms_db_criteria_Item('conf_catid', $confcat_id));
		$config = $config_handler->getConfigs($criteria);
		$confcount = count($config);
		for ($i = 0; $i < $confcount; $i++) {
			$title = (!defined($config[$i]->conf_desc) || constant($config[$i]->conf_desc) == '') ? constant($config[$i]->conf_title) : '<span>' . constant($config[$i]->conf_title) . '</span> <span data-toggle="tooltip" data-html="true" title="' . constant($config[$i]->conf_desc) . '"><span class="glyphicon glyphicon-info-sign"></span></span>';
			switch ($config[$i]->conf_formtype) {
				case 'textsarea' :
					if ($config[$i]->conf_valuetype == 'array') {
						// this is exceptional.. only when value type is array, need a smarter way for this
						$ele = ($config[$i]->conf_value != '')
							? new icms_form_elements_Textarea($title, $config[$i]->conf_name, DataFilter::htmlSpecialChars(implode('|', $config[$i]->getConfValueForOutput())), 5, 50)
							: new icms_form_elements_Textarea($title, $config[$i]->conf_name, '', 5, 50);
					} else {
						$ele = new icms_form_elements_Textarea($title, $config[$i]->conf_name, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()));
					}
					break;

				case 'textarea' :
					if ($config[$i]->conf_valuetype == 'array') {
						// this is exceptional.. only when value type is array, need a smarter way for this
						$ele = ($config[$i]->conf_value != '')
							? new icms_form_elements_Textarea($title, $config[$i]->conf_name, DataFilter::htmlSpecialChars(implode('|', $config[$i]->getConfValueForOutput())), 5, 50)
							: new icms_form_elements_Textarea($title, $config[$i]->conf_name, '', 5, 50);
					} else {
						$ele = new icms_form_elements_Dhtmltextarea($title, $config[$i]->conf_name, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()));
					}
					break;

				case 'autotasksystem':
					$handler = icms_getModuleHandler('autotasks', 'system');
					$options = $handler->getSystemHandlersList(true);
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput(), 1, false);
					foreach ($options as $k => $v) {
						$ele->addOption($k, $v);
					}
					unset($handler, $options, $option);
					break;

				case 'select' :
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					$options = $config_handler->getConfigOptions(new icms_db_criteria_Item('conf_id', $config[$i]->conf_id));
					$opcount = count($options);
					for ($j = 0; $j < $opcount; $j++) {
						$optval = defined($options[$j]->confop_value) ? constant($options[$j]->confop_value) : $options[$j]->confop_value;
						$optkey = defined($options[$j]->confop_name) ? constant($options[$j]->confop_name) : $options[$j]->confop_name;
						$ele->addOption($optval, $optkey);
					}
					break;

				case 'select_multi' :
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput(), 5, true);
					$options = $config_handler->getConfigOptions(new icms_db_criteria_Item('conf_id', $config[$i]->conf_id));
					$opcount = count($options);
					for ($j = 0; $j < $opcount; $j++) {
						$optval = defined($options[$j]->confop_value)
							? constant($options[$j]->confop_value)
							: $options[$j]->confop_value;
						$optkey = defined($options[$j]->confop_name)
							? constant($options[$j]->confop_name)
							: $options[$j]->confop_name;
						$ele->addOption($optval, $optkey);
					}
					break;

				case 'yesno' :
					$ele = new icms_form_elements_Radioyn($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput(), _YES, _NO);
					break;

				case 'theme' :
				case 'theme_multi' :
				case 'theme_admin' :
					$ele = ($config[$i]->conf_formtype != 'theme_multi')
						? new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput())
						: new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput(), 5, true);
					$dirlist = ($config[$i]->conf_formtype != 'theme_admin')
						? icms_view_theme_Factory::getThemesList()
						: icms_view_theme_Factory::getAdminThemesList();
					if (!empty($dirlist)) {
						asort($dirlist);
						$ele->addOptionArray($dirlist);
					}
					$form->addElement(new icms_form_elements_Hidden('_old_theme', $config[$i]->getConfValueForOutput()));
					break;

				case 'editor' :
				case 'editor_source' :
					$type = explode('_', $config[$i]->conf_formtype);
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					$type = array_pop($type);
					if ($type == 'editor') {
						$type = '';
					}

					/**
					 * @var EditorsRegistry $editorRegistry
					 */
					$editorRegistry = icms::getInstance()->get('\\' . EditorsRegistry::class);

					$dirlist = $editorRegistry->getList($type);
					if (!empty($dirlist)) {
						$ele->addOptionArray($dirlist);
					}
					unset($type);
					break;

				case 'editor_multi' :
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput(), 5, true);
					/**
					 * @var EditorsRegistry $editorRegistry
					 */
					$editorRegistry = icms::getInstance()->get('\\' . EditorsRegistry::class);

					$dirlist = $editorRegistry->getList('content');
					if (!empty($dirlist)) {
						asort($dirlist);
						$ele->addOptionArray($dirlist);
					}
					break;

				case 'select_font' :
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					$dirlist = icms_core_Filesystem::getFileList(ICMS_LIBRARIES_PATH . '/icms/form/elements/captcha/fonts/', '', array('ttf'));
					if (!empty($dirlist)) {
						asort($dirlist);
						$ele->addOptionArray($dirlist);
					}
					break;

				case 'select_plugin' :
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput(), 8, true);
					$dirlist = icms_core_Filesystem::getDirList(ICMS_PLUGINS_PATH . '/textsanitizer/');
					if (!empty($dirlist)) {
						asort($dirlist);
						$ele->addOptionArray($dirlist);
					}
					break;

				case 'tplset' :
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					$tplset_handler = icms::handler('icms_view_template_set');
					$tplsetlist = $tplset_handler->getList();
					asort($tplsetlist);
					foreach ($tplsetlist as $key => $name) {
						$ele->addOption($key, $name);
					}
					// old theme value is used to determine whether to update cache or not. kind of dirty way
					$form->addElement(new icms_form_elements_Hidden('_old_theme', $config[$i]->getConfValueForOutput()));
					break;

				case 'timezone' :
					$ele = new icms_form_elements_select_Timezone($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					break;

				case 'language' :
					$ele = new icms_form_elements_select_Lang($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					break;

				case 'startpage' :
					$member_handler = icms::handler('icms_member');
					$grps = $member_handler->getGroupList();

					$value = $config[$i]->getConfValueForOutput();
					if (!is_array($value)) {
						$value = array();
						foreach ($grps as $k => $v) {
							$value[$k] = $config[$i]->getConfValueForOutput();
						}
					}

					$moduleslist = array_filter(
						icms_module_Handler::getActive(),
						function ($item) {
							return $item != 'system';
						}
					);
					$moduleslist = array_combine($moduleslist, $moduleslist);
					$moduleslist['--'] = _MD_AM_NONE;

					//Adding support to select custom links to be the start page
					$page_handler = icms::handler('icms_data_page');
					$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('page_status', 1));
					$criteria->add(new icms_db_criteria_Item('page_url', '%*', 'NOT LIKE'));
					$pagelist = $page_handler->getList($criteria);

					$list = $moduleslist + $pagelist;
					asort($list);

					$ele = new icms_form_elements_Tray($title, '<br />');
					$hv = '';
					foreach ($grps as $k => $v) {
						if (!isset($value[$k])) {
							$value[$k] = '--';
						}
						$f = new icms_form_elements_Select('<b>' . $v . ':</b>', $config[$i]->conf_name . '[' . $k . ']', $value[$k]);
						$f->addOptionArray($list);
						$ele->addElement($f);
						unset($f);
					}
					break;

				case 'group' :
					$ele = new icms_form_elements_select_Group($title, $config[$i]->conf_name, true, $config[$i]->getConfValueForOutput(), 1, false);
					break;

				case 'group_multi' :
					$ele = new icms_form_elements_select_Group($title, $config[$i]->conf_name, true, $config[$i]->getConfValueForOutput(), 5, true);
					break;

				case 'user' :
					$ele = new icms_form_elements_select_User($title, $config[$i]->conf_name, false, $config[$i]->getConfValueForOutput(), 1, false);
					break;

				case 'user_multi' :
					$ele = new icms_form_elements_select_User($title, $config[$i]->conf_name, false, $config[$i]->getConfValueForOutput(), 5, true);
					break;

				case 'module_cache' :
					$module_handler = icms::handler('icms_module');
					$modules = $module_handler->getObjects(new icms_db_criteria_Item('hasmain', 1), true);
					$currrent_val = $config[$i]->getConfValueForOutput();
					$cache_options = array('0' => _NOCACHE, '30' => sprintf(_SECONDS, 30), '60' => _MINUTE, '300' => sprintf(_MINUTES, 5), '1800' => sprintf(_MINUTES, 30), '3600' => _HOUR, '18000' => sprintf(_HOURS, 5), '86400' => _DAY, '259200' => sprintf(_DAYS, 3), '604800' => _WEEK);
					if (count($modules) > 0) {
						$ele = new icms_form_elements_Tray($title, '<br />');
						foreach (array_keys($modules) as $mid) {
							$c_val = isset($currrent_val[$mid]) ? (int)$currrent_val[$mid] : null;
							$selform = new icms_form_elements_Select($modules[$mid]->name, $config[$i]->conf_name . "[$mid]", $c_val);
							$selform->addOptionArray($cache_options);
							$ele->addElement($selform);
							unset($selform);
						}
					} else {
						$ele = new icms_form_elements_Label($title, _MD_AM_NOMODULE);
					}
					break;

				case 'site_cache' :
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					$ele->addOptionArray(array('0' => _NOCACHE, '30' => sprintf(_SECONDS, 30), '60' => _MINUTE, '300' => sprintf(_MINUTES, 5), '1800' => sprintf(_MINUTES, 30), '3600' => _HOUR, '18000' => sprintf(_HOURS, 5), '86400' => _DAY, '259200' => sprintf(_DAYS, 3), '604800' => _WEEK));
					break;

				case 'password' :
					$ele = new icms_form_elements_Password($title, $config[$i]->conf_name, 50, 255, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()), false, ($icmsConfigUser['pass_level'] ? 'password_adv' : ''));
					break;

				case 'color' :
					$ele = new icms_form_elements_Colorpicker($title, $config[$i]->conf_name, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()));
					break;

				case 'hidden' :
					$ele = new icms_form_elements_Hidden($config[$i]->conf_name, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()));
					break;

				case 'select_pages' :
					$content_handler = &icms_getModuleHandler('content', 'content');
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					$ele->addOptionArray($content_handler->getContentList());
					break;

				# Added by Fábio Egas in XTXM version
				case 'select_image' :
					$ele = new icms_form_elements_select_Image($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					break;

				case 'select_paginati' :
					if (file_exists(ICMS_LIBRARIES_PATH . '/paginationstyles/paginationstyles.php')) {
						include ICMS_LIBRARIES_PATH . '/paginationstyles/paginationstyles.php';
						$st = &$styles;
						$arr = array();
						foreach ($st as $style) {
							$arr[$style['fcss']] = $style['name'];
						}
						$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
						$ele->addOptionArray($arr);
					}
					break;

				case 'select_geshi' :
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					$reflector = new ReflectionClass("\\GeSHi");
					$dir = dirname(
							$reflector->getFileName()
						) . DIRECTORY_SEPARATOR . 'geshi' . DIRECTORY_SEPARATOR;
					$dirlist = str_replace('.php', '',
						icms_core_Filesystem::getFileList($dir, '', array('php'))
					);
					if (!empty($dirlist)) {
						asort($dirlist);
						$ele->addOptionArray($dirlist);
					}
					break;

				case 'textbox' :
				default :
					$ele = new icms_form_elements_Text($title, $config[$i]->conf_name, 50, 255, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()));
					break;
			}
			$hidden = new icms_form_elements_Hidden('conf_ids[]', $config[$i]->conf_id);
			$form->addElement($ele);
			$form->addElement($hidden);
			unset($ele, $hidden);
		}
		$form->addElement(new icms_form_elements_Hidden('op', 'save'));
		$form->addElement(new icms_form_elements_Button('', 'button', _GO, 'submit'));
		icms_cp_header();
		echo '<div class="CPbigTitle" style="background-image: url(' . ICMS_MODULES_URL . '/system/images/preferences/' . $confcat->confcat_id . '.png)"><a href="admin.php?fct=preferences">' . _MD_AM_PREFMAIN . '</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;' . constant($confcat->confcat_name) . '<br /><br /></div><br />';
		$form->display();
		icms_cp_footer();
		break;

	case 'showmod':
		$config_handler = icms::handler('icms_config');
		$mod = isset($_GET['mod']) ? (int)$_GET['mod'] : 0;
		if (empty($mod)) {
			header('Location: admin.php?fct=preferences');
			exit();
		}
		$config = $config_handler->getConfigs(new icms_db_criteria_Item('conf_modid', $mod));
		$count = count($config);
		if ($count < 1) {
			redirect_header('admin.php?fct=preferences', 1);
		}
		$form = new icms_form_Theme(_MD_AM_MODCONFIG, 'pref_form', 'admin.php?fct=preferences', 'post', true);
		$module_handler = icms::handler('icms_module');
		$module = &$module_handler->get($mod);
		icms_loadLanguageFile($module->dirname, 'modinfo');
		// if has comments feature, need comment lang file
		if ($module->hascomments == 1) {
			icms_loadLanguageFile('core', 'comment');
		}
		// if has notification feature, need notification lang file
		if ($module->hasnotification == 1) {
			icms_loadLanguageFile('core', 'notification');
		}

		$modname = $module->name;
		if ($module->getInfo('adminindex')) {
			$form->addElement(new icms_form_elements_Hidden('redirect', ICMS_MODULES_URL . '/' . $module->dirname . '/' . $module->getInfo('adminindex')));
		}
		for ($i = 0; $i < $count; $i++) {
			if (!defined($config[$i]->conf_desc)) {
				$title = $config[$i]->conf_title;
			} elseif (!constant($config[$i]->conf_desc)) {
				$title = constant($config[$i]->conf_title);
			} else {
				$title = '<span>' . constant($config[$i]->conf_title) . '</span> <span data-toggle="tooltip" data-html="true" title="' . constant($config[$i]->conf_desc) . '"><span class="glyphicon glyphicon-info-sign"></span></span>';
			}
			switch ($config[$i]->conf_formtype) {
				case 'textsarea' :
					if ($config[$i]->conf_valuetype == 'array') {
						// this is exceptional.. only when value type is arrayneed a smarter way for this
						$ele = ($config[$i]->conf_value != '') ? new icms_form_elements_Textarea($title, $config[$i]->conf_name, DataFilter::htmlSpecialChars(implode('|', $config[$i]->getConfValueForOutput())), 5, 50) : new icms_form_elements_Textarea($title, $config[$i]->conf_name, '', 5, 50);
					} else {
						$ele = new icms_form_elements_Textarea($title, $config[$i]->conf_name, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()), 5, 50);
					}
					break;

				case 'textarea' :
					if ($config[$i]->conf_valuetype == 'array') {
						// this is exceptional.. only when value type is array need a smarter way for this
						$ele = ($config[$i]->conf_value != '') ? new icms_form_elements_Textarea($title, $config[$i]->conf_name, DataFilter::htmlSpecialChars(implode('|', $config[$i]->getConfValueForOutput())), 5, 50) : new icms_form_elements_Textarea($title, $config[$i]->conf_name, '', 5, 50);
					} else {
						$ele = new icms_form_elements_Dhtmltextarea($title, $config[$i]->conf_name, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()), 5, 50);
					}
					break;

				case 'select' :
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());

					$options = &$config_handler->getConfigOptions(new icms_db_criteria_Item('conf_id', $config[$i]->conf_id));
					$opcount = count($options);
					for ($j = 0; $j < $opcount; $j++) {
						$optval = defined($options[$j]->confop_value) ? constant($options[$j]->confop_value) : $options[$j]->confop_value;
						$optkey = defined($options[$j]->confop_name) ? constant($options[$j]->confop_name) : $options[$j]->confop_name;
						$ele->addOption($optval, $optkey);
					}
					break;

				case 'select_multi' :
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput(), 5, true);
					$options = &$config_handler->getConfigOptions(new icms_db_criteria_Item('conf_id', $config[$i]->conf_id));
					$opcount = count($options);
					for ($j = 0; $j < $opcount; $j++) {
						$optval = defined($options[$j]->confop_value) ? constant($options[$j]->confop_value) : $options[$j]->confop_value;
						$optkey = defined($options[$j]->confop_name) ? constant($options[$j]->confop_name) : $options[$j]->confop_name;
						$ele->addOption($optval, $optkey);
					}
					break;

				case 'yesno' :
					$ele = new icms_form_elements_Radioyn($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput(), _YES, _NO);
					break;

				case 'group' :
					$ele = new icms_form_elements_select_Group($title, $config[$i]->conf_name, true, $config[$i]->getConfValueForOutput(), 1, false);
					break;

				case 'group_multi' :
					$ele = new icms_form_elements_select_Group($title, $config[$i]->conf_name, true, $config[$i]->getConfValueForOutput(), 5, true);
					break;

				case 'user' :
					$ele = new icms_form_elements_select_User($title, $config[$i]->conf_name, false, $config[$i]->getConfValueForOutput(), 1, false);
					break;

				case 'user_multi' :
					$ele = new icms_form_elements_select_User($title, $config[$i]->conf_name, false, $config[$i]->getConfValueForOutput(), 5, true);
					break;

				case 'password' :
					$ele = new icms_form_elements_Password($title, $config[$i]->conf_name, 50, 255, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()));
					break;

				case 'color' :
					$ele = new icms_form_elements_Colorpicker($title, $config[$i]->conf_name, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()));
					break;

				case 'hidden' :
					$ele = new icms_form_elements_Hidden($config[$i]->conf_name, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()));
					break;

				case 'select_pages' :
					$content_handler = &icms_getModuleHandler('content', 'content');
					$ele = new icms_form_elements_Select($title, $config[$i]->conf_name, $config[$i]->getConfValueForOutput());
					$ele->addOptionArray($content_handler->getContentList());
					break;

				case 'textbox' :
				default :
					$ele = new icms_form_elements_Text($title, $config[$i]->conf_name, 50, 255, DataFilter::htmlSpecialChars($config[$i]->getConfValueForOutput()));
					break;
			}
			$hidden = new icms_form_elements_Hidden('conf_ids[]', $config[$i]->conf_id);
			$form->addElement($ele);
			$form->addElement($hidden);
			unset($ele, $hidden);
		}
		$form->addElement(new icms_form_elements_Hidden('op', 'save'));
		$form->addElement(new icms_form_elements_Button('', 'button', _GO, 'submit'));
		icms_cp_header();
		if ($module->getInfo('hasAdmin') == true) {
			$modlink = '<a href="' . ICMS_MODULES_URL . '/' . $module->dirname . '/' . $module->getInfo('adminindex') . '">' . $modname . '</a>';
		} else {
			$modlink = $modname;
		}
		$iconbig = $module->getInfo('iconbig');
		if (isset($iconbig) && $iconbig == false) {
			echo '<div class="CPbigTitle" style="background-image: url(' . ICMS_MODULES_URL . '/system/admin/preferences/images/preferences_big.png);">' . $modlink . ' &raquo; ' . _PREFERENCES . '</div>';

		}
		if (isset($iconbig) && $iconbig == true) {
			echo '<div class="CPbigTitle" style="background-image: url(' . ICMS_MODULES_URL . '/' . $module->dirname . '/' . $iconbig . ')">' . $modlink . ' &raquo; ' . _PREFERENCES . '</div>';
		}
		$form->display();
		icms_cp_footer();
		break;

	case 'save':
		if (!icms::$security->check()) {
			redirect_header('admin.php?fct=preferences', 3, implode('<br />', icms::$security->getErrors()));
		}
		$xoopsTpl = new icms_view_Tpl();
		$count = count($conf_ids);
		$tpl_updated = false;
		$theme_updated = false;
		$startmod_updated = false;
		$lang_updated = false;
		$encryption_updated = false;
		$purifier_style_updated = false;
		$saved_config_items = array();
		$config_handler = icms::handler('icms_config');
		if ($count > 0) {
			for ($i = 0; $i < $count; $i++) {
				$config = &$config_handler->getConfig($conf_ids[$i]);
				$new_value = &${$config->conf_name};
				$old_value = $config->conf_value;
				icms::$preload->triggerEvent('savingSystemAdminPreferencesItem', array((int)$config->conf_catid, $config->conf_name, $config->conf_value));

				if (is_array($new_value) || $new_value != $config->conf_value) {
					// if language has been changed
					if (!$lang_updated && $config->conf_catid == icms_config_Handler::CATEGORY_MAIN && $config->conf_name == 'language') {
						$icmsConfig['language'] = ${$config->conf_name};
						$lang_updated = true;
					}
					// if default theme has been changed
					if (!$theme_updated && $config->conf_catid == icms_config_Handler::CATEGORY_MAIN && $config->conf_name == 'theme_set') {
						$member_handler = icms::handler('icms_member');
						$member_handler->updateUsersByField('theme', ${$config->conf_name});
						$theme_updated = true;
					}
					// if password encryption has been changed
					if (!$encryption_updated && $config->conf_catid == icms_config_Handler::CATEGORY_USER && $config->conf_name == 'enc_type') {
						if ($icmsConfig['closesite'] !== 1) {
							$member_handler = icms::handler('icms_member');
							$member_handler->updateUsersByField('pass_expired', 1);
							$encryption_updated = true;
						} else {
							redirect_header('admin.php?fct=preferences', 2, _MD_AM_UNABLEENCCLOSED);
						}
					}

					if (!$purifier_style_updated
						&& $config->conf_catid == icms_config_Handler::CATEGORY_PURIFIER
						&& $config->conf_name == 'purifier_Filter_ExtractStyleBlocks'
					) {
						if ($config->purifier_Filter_ExtractStyleBlocks == 1) {
							$purifier_style_updated = true;
						}
					}

					// if default template set has been changed
					if (!$tpl_updated && $config->conf_catid == icms_config_Handler::CATEGORY_MAIN && $config->conf_name == 'template_set') {
						// clear cached/compiled files and regenerate them if default theme has been changed
						if ($icmsConfig['template_set'] != ${$config->conf_name}) {
							$newtplset = ${$config->conf_name};
							// clear all compiled and cachedfiles
							$xoopsTpl->clear_compiled_tpl();
							// generate compiled files for the new theme
							// block files only for now..
							$tplfile_handler = icms::handler('icms_view_template_file');
							$dtemplates = &$tplfile_handler->find('default', 'block');
							$dcount = count($dtemplates);

							// need to do this to pass to icms_view_Tpl::template_touch function
							$GLOBALS['icmsConfig']['template_set'] = $newtplset;

							for ($i = 0; $i < $dcount; $i++) {
								$found = &$tplfile_handler->find($newtplset, 'block', $dtemplates[$i]->tpl_refid, null);
								if (count($found) > 0) {
									// template for the new theme found, compile it
									icms_view_Tpl::template_touch($found[0]->tpl_id);
								} else {
									// not found, so compile 'default' template file
									icms_view_Tpl::template_touch($dtemplates[$i]->tpl_id);
								}
							}
						}
						$tpl_updated = true;
					}

					// add read permission for the start module to all groups
					if (!$startmod_updated && $new_value != '--' && $config->conf_catid == icms_config_Handler::CATEGORY_MAIN && $config->conf_name == 'startpage') {
						$moduleperm_handler = icms::handler('icms_member_groupperm');
						$module_handler = icms::handler('icms_module');

						foreach ($new_value as $k => $v) {
							$arr = explode('-', $v);
							if (count($arr) > 1) {
								$mid = $arr[0];
								$module = &$module_handler->get($mid);
								if ($arr[0] == 1 && $arr[1] > 0) {
									//Set read permission to the content page for the selected group
									if (!$moduleperm_handler->checkRight('content_read', $arr[1], $k)) {
										$moduleperm_handler->addRight('content_read', $arr[1], $k);
									}
								}
							} else {
								$module = &$module_handler->getByDirname($v);
							}
							if (is_object($module)) {
								if (!$moduleperm_handler->checkRight('module_read', $module->mid, $k)) {
									$moduleperm_handler->addRight('module_read', $module->mid, $k);
								}
							}
						}
						$startmod_updated = true;
					}

					$config->setConfValueForInput($new_value);
					$config_handler->insertConfig($config);
				}
				unset($new_value);

				if (!isset($saved_config_items[$config->conf_catid])) {
					$saved_config_items[$config->conf_catid] = array();
				}
				$saved_config_items[$config->conf_catid][$config->conf_name] = array($old_value, $config->conf_value);

			}
		}

		icms::$preload->triggerEvent('afterSaveSystemAdminPreferencesItems', $saved_config_items);
		unset($saved_config_items);

		if (!empty($use_mysession) && $icmsConfig['use_mysession'] == 0 && $session_name != '') {
			setcookie($session_name, session_id(), time() + (60 * (int)$session_expire), '/', '', 0);
		}

		// Clean cached files, may take long time
		// Use register_shutdown_function to keep running after connection closes so that cleaning cached files can be finished
		// Cache management should be performed on a separate page
		register_shutdown_function(array(&$xoopsTpl, 'clear_all_cache'));

		// If language is changed, leave the admin menu file to be regenerated upon next request,
		// otherwise regenerate admin menu file for now
		if (!$lang_updated) {
			// regenerate admin menu file
			register_shutdown_function('xoops_module_write_admin_menu', impresscms_get_adminmenu());
		} else {
			$redirect = ICMS_URL . '/admin.php';
		}

		if (isset($redirect) && $redirect != '') {
			redirect_header($redirect, 2, _MD_AM_DBUPDATED);
		} else {
			redirect_header('admin.php?fct=preferences', 2, _MD_AM_DBUPDATED);
		}
}
