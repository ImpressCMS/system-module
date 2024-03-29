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
 * Administration of template sets, main file
 *
 * @copyright    http://www.impresscms.org/ The ImpressCMS Project
 * @license        LICENSE.txt
 * @package        System
 * @subpackage    Templates
 * @todo        Extract HTML and CSS to a template
 */

/* set get and post filters before including admin_header, if not strings */

use ImpressCMS\Core\DataFilter;

$filter_get = array(
	'html' => 'html',
);

$filter_post = array(
	'html' => 'html',
);

/* set default values for variables, $op and $fct are handled in the header */

/** common header for the admin functions */
include 'admin_header.php';
$tplset_handler = $icms_admin_handler;

if ($op == '') {
	$op = 'list';
}

if ($op == 'edittpl_go') {
	if (isset($previewtpl)) {
		$op = 'previewtpl';
	}
}

$icmsAdminTpl = new icms_view_Tpl();
switch ($op) {


	case 'listtpl':
		if ($tplset == '') {
			redirect_header('admin.php?fct=tplsets', 1);
		}
		if ($moddir == '') {
			redirect_header('admin.php?fct=tplsets', 1);
		}
		/* tplset is taken from the $_GET variable and should be encoded before output */
		$tplset_enc = filter_var($tplset, FILTER_SANITIZE_ENCODED);
		icms_cp_header();
		$module_handler = icms::handler('icms_module');
		$module = & $module_handler->getByDirname($moddir);
		$modname = $module->name;
		echo '<div class="CPbigTitle" style="background-image: url('
			. ICMS_MODULES_URL . '/system/admin/tplsets/images/tplsets_big.png)">'
			. '<a href="admin.php?fct=tplsets">' . _MD_TPLMAIN
			.'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'
			. $tplset_enc . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'
			. $modname . '<br /><br /></div><br />';

		echo '<form action="admin.php" method="post" enctype="multipart/form-data">'
			. '<table width="100%" class="outer" cellspacing="1">'
			. '<tr><th width="40%">' . _MD_FILENAME . '</th><th>' . _MD_LASTMOD . '</th>';
		if ($tplset != 'default') {
			echo '<th>' . _MD_LASTIMP . '</th><th colspan="2">' . _MD_TPLSET_ACTIONS . '</th></tr>';
		} else {
			echo '<th>' . _MD_TPLSET_ACTIONS . '</th></tr>';
		}
		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		// get files that are already installed
		$templates = $tpltpl_handler->find($tplset, 'module', null, $moddir);
		$inst_files = array();
		$tcount = count($templates);
		for ($i = 0; $i < $tcount; $i++) {
			if ($i % 2 == 0) {
				$class = 'even';
			} else {
				$class = 'odd';
			}
			$last_modified = $templates[$i]->tpl_lastmodified;
			$last_imported = $templates[$i]->tpl_lastimported;
			$last_imported_f = ($last_imported > 0)? formatTimestamp($last_imported, 'l'):'';
			echo  '<tr class="' . $class . '"><td class="head">'
				. $templates[$i]->tpl_file
				. '<br /><br /><span style="font-weight:normal;">' . $templates[$i]->tpl_desc . '</span></td><td style="vertical-align: middle;">'
				. formatTimestamp($last_modified, 'l') . '</td>';
			$filename = $templates[$i]->tpl_file;
			if ($tplset != 'default') {
				$physical_file = ICMS_THEME_PATH . '/' . $tplset . '/templates/' . $moddir . '/' . $filename;
				if (file_exists($physical_file)) {
					$mtime = filemtime($physical_file);
					if ($last_imported < $mtime) {
						if ($mtime > $last_modified) {
							$bg = '#ff9999';
						} elseif ($mtime > $last_imported) {
							$bg = '#99ff99';
						}
						echo '<td style="background-color:' . $bg . ';">' . $last_imported_f
							. ' [<a href="admin.php?fct=tplsets&amp;tplset=' . $tplset_enc . '&amp;moddir=' . $moddir
							. '&amp;op=importtpl&amp;id=' . $templates[$i]->tpl_id . '">'
							. _MD_IMPORT . '</a>]';
					} else {
						echo '<td style="vertical-align: middle;">' . $last_imported_f;
					}
				} else {
					echo '<td style="vertical-align: middle;">' . $last_imported_f;
				}
				echo '</td><td style="vertical-align: middle;">'
					. '<a href="admin.php?fct=tplsets&amp;op=edittpl&amp;id=' . $templates[$i]->tpl_id . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/edit.png" alt="' . _EDIT . '" title="' . _EDIT . '" /></a>'
					. ' <a href="admin.php?fct=tplsets&amp;op=downloadtpl&amp;id=' . $templates[$i]->tpl_id . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/filesave2.png" alt="' . _MD_DOWNLOAD . '" title="' . _MD_DOWNLOAD . '" /></a>'
					. ' <a href="admin.php?fct=tplsets&amp;op=deletetpl&amp;id=' . $templates[$i]->tpl_id . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/editdelete.png" alt="' . _DELETE . '" title="' . _DELETE . '" /></a>'
					. '</td><td style="vertical-align: middle;" align="' . _GLOBAL_RIGHT . '"><input type="file" name="' . $filename . '" id="' . $filename . '" />'
					. '<input type="hidden" name="xoops_upload_file[]" id="xoops_upload_file[]" value="' . $filename . '" />'
					. '<input type="hidden" name="old_template[' . $filename . ']" value="' . $templates[$i]->tpl_id . '" /></td>';
			} else {
				echo '<td style="vertical-align: middle;"><a href="admin.php?fct=tplsets&amp;op=edittpl&amp;id=' . $templates[$i]->tpl_id . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/viewmag.png" alt="' . _MD_VIEW . '" title="' . _MD_VIEW . '" /></a>&nbsp;<a href="admin.php?fct=tplsets&amp;op=downloadtpl&amp;id=' . $templates[$i]->tpl_id . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/filesave2.png" alt="' . _MD_DOWNLOAD . '" title="' . _MD_DOWNLOAD . '" /></a></td>';
			}
			echo '</tr>' . "\n";
			$inst_files[] = $filename;
		}
		if ($tplset != 'default') {
			// get difference between already installed files and the files under modules directory. which will be recognized as files that are not installed
			$notinst_files = array_diff(icms_core_Filesystem::getFileList(ICMS_MODULES_PATH . '/' . $moddir . '/templates/'), $inst_files);
			foreach ($notinst_files as $nfile) {
				$class = ($class == "even")?"odd":"even";
				if ($nfile != 'index.html') {
					echo  '<tr class="' . $class . '"><td style="background-color:#FFFF99;">' . $nfile . '<br />' . _MD_FILEGENER
						. '</td><td style="background-color:#FFFF99;">&nbsp;</td><td style="background-color:#FFFF99;">';
					$physical_file = ICMS_THEME_PATH . '/' . $tplset . '/templates/' . $moddir . '/' . $nfile;
					if (file_exists($physical_file)) {
						echo '[<a href="admin.php?fct=tplsets&amp;moddir=' . $moddir . '&amp;tplset=' . $tplset_enc . '&amp;op=importtpl&amp;file=' . urlencode($nfile) . '">' . _MD_IMPORT . '</a>]';
					} else {
						echo '&nbsp;';
					}
					echo '</td><td style="background-color:#FFFF99;vertical-align: middle;">'
						. '<a href="admin.php?fct=tplsets&amp;moddir=' . $moddir . '&amp;tplset=' . $tplset_enc
						. '&amp;op=generatetpl&amp;type=module&amp;file=' . urlencode($nfile) . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/filenew2.png" alt="' . _MD_GENERATE . '" title="' . _MD_GENERATE . '" /></a></td>
						<td style="background-color:#FFFF99;vertical-align: middle; text-align:' . _GLOBAL_RIGHT
						. ';"><input type="file" name="' . $nfile . '" id="' . $nfile
						. '" /><input type="hidden" name="xoops_upload_file[]" id="xoops_upload_file[]" value="' . $nfile
						. '" /></td></tr>' . "\n";
				}
			}
		}
		echo '</table><br /><table width="100%" class="outer" cellspacing="1"><tr><th width="40%">' . _MD_FILENAME . '</th><th>' . _MD_LASTMOD . '</th>';
		if ($tplset != 'default') {
			echo '<th>' . _MD_LASTIMP . '</th><th colspan="2">' . _MD_TPLSET_ACTIONS . '</th></tr>';
		} else {
			echo '<th>' . _MD_TPLSET_ACTIONS . '</th></tr>';
		}
		$btemplates = $tpltpl_handler->find($tplset, 'block', null, $moddir);
		$binst_files = array();
		$btcount = count($btemplates);
		for ($j = 0; $j < $btcount; $j++) {
			$last_imported = $btemplates[$j]->tpl_lastimported;
			$last_imported_f = ($last_imported > 0)? formatTimestamp($last_imported, 'l'):'';
			$last_modified = $btemplates[$j]->tpl_lastmodified;
			if ($j % 2 == 0) {
				$class = 'even';
			} else {
				$class = 'odd';
			}
			echo  '<tr class="' . $class . '"><td class="head"><span style="font-weight:bold;">'
				. $btemplates[$j]->tpl_file . '</span><br /><br /><span style="font-weight:normal;">'
				. $btemplates[$j]->tpl_desc . '</span></td><td style="vertical-align: middle;">' . formatTimestamp($last_modified, 'l') . '</td>';
			$filename = $btemplates[$j]->tpl_file;
			$physical_file = ICMS_THEME_PATH . '/' . $tplset . '/templates/' . $moddir . '/blocks/' . $filename;
			if ($tplset != 'default') {
				if (file_exists($physical_file)) {
					$mtime = filemtime($physical_file);
					if ($last_imported < $mtime) {
						if ($mtime > $last_modified) {
							$bg = '#ff9999';
						} elseif ($mtime > $last_imported) {
							$bg = '#99ff99';
						}
						echo '<td style="background-color:' . $bg . ';">' . $last_imported_f . ' <a href="admin.php?fct=tplsets&amp;tplset=' . $tplset . '&amp;op=importtpl&amp;moddir=' . $moddir . '&amp;id=' . $btemplates[$j]->tpl_id . '">' . _MD_IMPORT . '</a>]';
					} else {
						echo '<td>' . $last_imported_f;
					}
				} else {
					echo '<td>' . $last_imported_f;
				}
				echo '</td><td style="vertical-align: middle;">'
					. '<a href="admin.php?fct=tplsets&amp;op=edittpl&amp;id=' . $btemplates[$j]->tpl_id . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/edit.png" alt="' . _EDIT . '" title="' . _EDIT . '" /></a>'
					. ' <a href="admin.php?fct=tplsets&amp;op=downloadtpl&amp;id=' . $btemplates[$j]->tpl_id . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/filesave2.png" alt="' . _MD_DOWNLOAD . '" title="' . _MD_DOWNLOAD . '" /></a>'
					. ' <a href="admin.php?fct=tplsets&amp;op=deletetpl&amp;id=' . $btemplates[$j]->tpl_id . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/editdelete.png" alt="' . _DELETE . '" title="' . _DELETE . '" /></a>'
					. '</td><td style="vertical-align: middle;" align="' . _GLOBAL_RIGHT . '"><input type="file" name="' . $filename . '" id="' . $filename . '" />'
					. '<input type="hidden" name="xoops_upload_file[]" id="xoops_upload_file[]" value="' . $filename . '" />'
					. '<input type="hidden" name="old_template[' . $filename . ']" value="' . $btemplates[$j]->tpl_id . '" /></td>';
			} else {
				echo '<td><a href="admin.php?fct=tplsets&amp;op=edittpl&amp;id=' . $btemplates[$j]->tpl_id . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/viewmag.png" alt="' . _MD_VIEW . '" title="' . _MD_VIEW . '" /></a>&nbsp;
				<a href="admin.php?fct=tplsets&amp;op=downloadtpl&amp;id=' . $btemplates[$j]->tpl_id . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/filesave2.png" alt="' . _MD_DOWNLOAD . '" title="' . _MD_DOWNLOAD . '" /></a></td>';
			}
			echo '</tr>' . "\n";
			$binst_files[] = $filename;
		}
		if ($tplset != 'default') {
			$bnotinst_files = array_diff(icms_core_Filesystem::getFileList(ICMS_MODULES_PATH . '/' . $moddir . '/templates/blocks/'), $binst_files);
			foreach ($bnotinst_files as $nfile) {
				if ($nfile != 'index.html') {
					echo  '<tr style="background-color:#FFFF99;"><td style="background-color:#FFFF99;">' . $nfile
						. '</td><td style="background-color:#FFFF99;">&nbsp;</td><td style="background-color:#FFFF99;">';
					$physical_file = ICMS_THEME_PATH . '/' . $tplset . '/templates/' . $moddir . '/blocks/' . $nfile;
					if (file_exists($physical_file)) {
						echo '[<a href="admin.php?fct=tplsets&amp;moddir=' . $moddir . '&amp;tplset=' . $tplset . '&amp;op=importtpl&amp;file=' . urlencode($nfile) . '">' . _MD_IMPORT . '</a>]';
					} else {
						echo '&nbsp;';
					}
					echo '</td><td style="background-color:#FFFF99;"><a href="admin.php?fct=tplsets&amp;moddir='
						. $moddir . '&amp;tplset=' . $tplset . '&amp;op=generatetpl&amp;type=block&amp;file=' . urlencode($nfile) . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/filenew2.png" alt="' . _MD_GENERATE . '" title="' . _MD_GENERATE . '" /></a></td>
						<td style="background-color:#FFFF99; vertical-align: middle; text-align: ' . _GLOBAL_RIGHT . '">'
						. '<input type="file" name="' . $nfile . '" id="' . $nfile . '" />'
						. '<input type="hidden" name="xoops_upload_file[]" id="xoops_upload_file[]" value="' . $nfile . '" />'
						. '</td></tr>' . "\n";
				}
			}
		}
		echo '</table>';
		if ($tplset != 'default') {
			echo '<div style="text-align: ' . _GLOBAL_RIGHT . '; margin-top: 5px;">'
				. '<input type="hidden" name="fct" value="tplsets" />'
				. '<input type="hidden" name="op" value="update" />' . icms::$security->getTokenHTML()
				. '<input type="hidden" name="moddir" value="' . $moddir . '" />'
				. '<input type="hidden" name="tplset" value="' . $tplset_enc . '" />'
				. '<input type="submit" value="' . _MD_UPLOAD . '" /></div></form>';
		}
		icms_cp_footer();
		break;

	case 'edittpl':
		if ($id <= 0) {
			redirect_header('admin.php?fct=tplsets', 1);
		}
		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		$tplfile = &$tpltpl_handler->get($id, true);
		if (is_object($tplfile)) {
			$tplset = $tplfile->tpl_tplset;
			$tform = array(
				'tpl_tplset' => $tplset,
				'tpl_id' => $id,
				'tpl_file' => $tplfile->tpl_file,
				'tpl_desc' => $tplfile->tpl_desc,
				'tpl_lastmodified' => $tplfile->tpl_lastmodified,
				'tpl_source' => $tplfile->getVar('tpl_source', 'E'),
				'tpl_module' => $tplfile->tpl_module);
			include_once ICMS_MODULES_PATH . '/system/admin/tplsets/tplform.php';
			icms_cp_header();
			echo '<a href="admin.php?fct=tplsets">' . _MD_TPLMAIN . '</a>'
				. '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'
				. '<a href="./admin.php?fct=tplsets&amp;op=listtpl&amp;moddir=' . $tplfile->tpl_module . '&amp;tplset=' . $tplset . '">'
				. $tplset . '</a>'
				. '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'
				. $tform['tpl_module'] . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'
				. _MD_EDITTEMPLATE . '<br /><br />';
			$form->display();
			icms_cp_footer();
			exit();
		} else {
			$err[] = sprintf(_MD_TPLSET_TEMPLATE_NOTEXIST, $id);
		}
		icms_cp_header();
		icms_core_Message::error($err);
		echo '<br /><a href="admin.php?fct=tplsets">' . _MD_AM_BTOTADMIN . '</a>';
		icms_cp_footer();
		break;

	case 'edittpl_go':
		if ($id <= 0 || !icms::$security->check()) {
			redirect_header('admin.php?fct=tplsets', 3, implode('<br />', icms::$security->getErrors()));
		}
		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		$tplfile = &$tpltpl_handler->get($id, true);
		$err = array();
		if (!is_object($tplfile)) {
			$err[] = sprintf(_MD_TPLSET_TEMPLATE_NOTEXIST, $id);
		} else {
			if ($tplfile->tpl_tplset != 'default') {
				$tplfile->tpl_source = $html;
				$tplfile->tpl_lastmodified = time();

				if (!$tpltpl_handler->insert($tplfile)) {
					$err[] = sprintf(_MD_TPLSET_INSERT_FAILED, $tplfile->tpl_file);
				} else {
					$xoopsTpl = new icms_view_Tpl();
					if ($xoopsTpl->is_cached('db:' . $tplfile->tpl_file)) {
						if (!$xoopsTpl->clear_cache('db:' . $tplfile->tpl_file)) {
						}
					}
					if ($tplfile->tpl_tplset == $icmsConfig['template_set']) {
						icms_view_Tpl::template_touch($id);
					}
				}
			} else {
				$err[] = _MD_TPLSET_DEFAULT_NOEDIT;
			}
		}

		if (count($err) == 0) {
			if (!empty($moddir)) {
				redirect_header('admin.php?fct=tplsets&amp;op=edittpl&amp;id=' . $tplfile->tpl_id, 2, _MD_AM_DBUPDATED);
			} elseif (isset($redirect)) {
				redirect_header('admin.php?fct=tplsets&amp;tplset=' . $tplfile->tpl_tplset . '&amp;op=' . trim($redirect), 2, _MD_AM_DBUPDATED);
			} else {
				redirect_header('admin.php?fct=tplsets', 2, _MD_AM_DBUPDATED);
			}
		}
		icms_cp_header();
		icms_core_Message::error($err);
		echo '<br /><a href="admin.php?fct=tplsets">' . _MD_AM_BTOTADMIN . '</a>';
		icms_cp_footer();
		break;

	case 'deletetpl':
		icms_cp_header();
		icms_core_Message::confirm(array('id' => $id, 'op' => 'deletetpl_go', 'fct' => 'tplsets'), 'admin.php', _MD_RUSUREDELTPL, _YES);
		icms_cp_footer();
		break;

	case 'deletetpl_go':
		if ($id <= 0 || !icms::$security->check()) {
			redirect_header('admin.php?fct=tplsets', 1, implode('<br />', icms::$security->getErrors()));
		}
		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		$tplfile = &$tpltpl_handler->get($id);
		$err = array();
		if (!is_object($tplfile)) {
			$err[] = sprintf(_MD_TPLSET_TEMPLATE_NOTEXIST, $id);
		} else {
			if ($tplfile->tpl_tplset != 'default') {
				if (!$tpltpl_handler->delete($tplfile)) {
					$err[] = sprintf(_MD_TPLSET_DELETE_FAIL, $tplfile->tpl_file);
				} else {
					// need to compile default template
					if ($tplfile->tpl_tplset == $icmsConfig['template_set']) {
						$defaulttpl = & $tpltpl_handler->find('default', $tplfile->tpl_type, $tplfile->tpl_refid, null, $tplfile->tpl_file);
						if (count($defaulttpl) > 0) {
							icms_view_Tpl::template_touch($defaulttpl[0]->tpl_id);
						}
					}
				}
			} else {
				$err[] = _MD_TPLSET_DEFAULT_NODELETE;
			}
		}

		if (count($err) == 0) {
			redirect_header('admin.php?fct=tplsets&amp;op=listtpl&amp;moddir='
				. $tplfile->tpl_module . '&amp;tplset='
				. urlencode($tplfile->tpl_tplset), 2, _MD_AM_DBUPDATED);
		}
		icms_cp_header();
		icms_core_Message::error($err);
		echo '<br /><a href="admin.php?fct=tplsets">' . _MD_AM_BTOTADMIN . '</a>';
		icms_cp_footer();
		break;

	case 'delete':
		icms_cp_header();
		icms_core_Message::confirm(array('tplset' => $tplset, 'op' => 'delete_go', 'fct' => 'tplsets'), 'admin.php', _MD_RUSUREDELTH, _YES);
		icms_cp_footer();
		break;

	case 'delete_go':
		if (!icms::$security->check()) {
			redirect_header('admin.php?fct=tplsets', 1, implode('<br />', icms::$security->getErrors()));
		}
		$msgs = array();
		if ($tplset != 'default' && $tplset != $icmsConfig['template_set']) {
			/**
			 * @var icms_view_template_file_Handler $tpltpl_handler
			 */
			$tpltpl_handler = &icms::handler('icms_view_template_file');
			$templates = &$tpltpl_handler->getObjects(new icms_db_criteria_Item('tpl_tplset', $tplset));
			$tcount = count($templates);
			if ($tcount > 0) {
				$msgs[] = _MD_TPLSET_DELETING;
				for ($i = 0; $i < $tcount; $i++) {
					if (!$tpltpl_handler->delete($templates[$i])) {
						$msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">'
							. _ERROR . ': ' . sprintf(_MD_TPLSET_DELETE_FAIL, '<strong>' . $templates[$i]->tpl_file . '</strong>')
							. 'ID: ' . '<strong>' . $templates[$i]->tpl_id . '</strong></span>';
					} else {
						$msgs[] = '&nbsp;&nbsp;' . sprintf(_MD_TPLSET_DELETE_OK, '<strong>' . $templates[$i]->tpl_file . '</strong>')
						. 'ID: <strong>' . $templates[$i]->tpl_id . '</strong>';
					}
				}
			}
			$tplsets = & $tplset_handler->getObjects(new icms_db_criteria_Item('tplset_name', $tplset));
			if (count($tplsets) > 0 && is_object($tplsets[0])) {
				$msgs[] = _MD_TPLSET_DELETING_DATA;
				if (!$tplset_handler->delete($tplsets[0])) {
					$msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">'
						. _ERROR . ': ' . sprintf(_MD_TPLSET_DELETE_FAIL, $tplset)
						. '</span>';
				} else {
					$msgs[] = '&nbsp;&nbsp;' . sprintf(_MD_TPLSET_DELETE_OK, $tplset);
				}
			}
		} else {
			$msgs[] = '<span style="color:#ff0000;">'
				. _ERROR . ': ' . _MD_TPLSET_DEFAULT_NODELETE
				. '</span>';
		}
		icms_cp_header();
		echo '<code>' . implode("<br />", $msgs) . '</code><br />';
		echo '<br /><a href="admin.php?fct=tplsets">' . _MD_AM_BTOTADMIN . '</a>';
		icms_cp_footer();
		break;

	case 'clone':
		$form = new icms_form_Theme(_MD_CLONETHEME, 'template_form', 'admin.php', 'post', true);
		$form->addElement(new icms_form_elements_Label(_MD_THEMENAME, $tplset));
		$form->addElement(new icms_form_elements_Text(_MD_NEWNAME, 'newtheme', 30, 50), true);
		$form->addElement(new icms_form_elements_Hidden('tplset', $tplset));
		$form->addElement(new icms_form_elements_Hidden('op', 'clone_go'));
		$form->addElement(new icms_form_elements_Hidden('fct', 'tplsets'));
		$form->addElement(new icms_form_elements_Button('', 'tpl_button', _SUBMIT, 'submit'));
		icms_cp_header();
		echo '<div class="CPbigTitle" style="background-image: url('
			. ICMS_MODULES_URL . '/system/admin/tplsets/images/tplsets_big.png)"><a href="admin.php?fct=tplsets">'
			. _MD_TPLMAIN . '</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'
			. _MD_CLONETHEME . '<br /><br /></div><br />';
		$form->display();
		icms_cp_footer();
		break;

	case 'clone_go':
		if (!icms::$security->check()) {
			redirect_header('admin.php?fct=tplsets', 1, implode('<br />', icms::$security->getErrors()));
		}

		$msgs = array();
		$tplset = trim($tplset);
		$newtheme = trim($newtheme);
		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		if ($tplset == $newtheme) {
			icms_core_Message::error(_MD_TPLSET_UNIQUE_NAME);
		} elseif ($tpltpl_handler->getCount(new icms_db_criteria_Item('tpl_tplset', $newtheme)) > 0) {
			icms_core_Message::error(sprintf(_MD_TPLSET_EXISTS, '<strong>' . $newtheme . '</strong>'));
		} else {
			$tplsetobj = &$tplset_handler->create();
			$tplsetobj->tplset_name = $newtheme;
			$tplsetobj->tplset_created = time();
			if (!$tplset_handler->insert($tplsetobj)) {
				$msgs[] = '<span style="color:#ff0000;">' . _ERROR . ': ' . sprintf(_MD_TPLSET_CREATE_FAILED, '<strong>' . $newtheme . '</strong>') . '</span><br />';
			} else {
				$tplsetid = $tplsetobj->tplset_id;
				$templates = &$tpltpl_handler->getObjects(new icms_db_criteria_Item('tpl_tplset', $tplset), false);
				$tcount = count($templates);
				if ($tcount > 0) {
					$msgs[] = _MD_TPLSET_COPYING;
					for ($i = 0; $i < $tcount; $i++) {
						$newtpl = &$templates[$i]->xoopsClone();
						$newtpl->tpl_tplset = $newtheme;
						$newtpl->tpl_id = 0;
						$newtpl->tpl_lastimported = 0;
						$newtpl->tpl_lastmodified = time();
						if (!$tpltpl_handler->insert($newtpl)) {
							$msgs[] = '&nbsp;<span style="color:#ff0000;">' . _ERROR . ': ' . sprintf(_MD_TPLSET_COPY_FAILED, '<strong>' . $templates[$i]->tpl_file . '</strong>')
								. 'ID: <strong>' . $templates[$i]->tpl_id . '</strong>' . '</span>';
						} else {
							$msgs[] = '&nbsp;' . sprintf(_MD_TPLSET_COPY_OK, '<strong>' . $templates[$i]->tpl_file . '</strong>')
							. ' ID: <strong>' . $newtpl->tpl_id . '</strong>';
						}
						unset($newtpl);
					}
					$msgs[] = sprintf(_MD_TPLSET_CREATE_OK, '<strong>' . htmlspecialchars($newtheme, ENT_QUOTES, _CHARSET) . '</strong>')
					. ' (ID: <strong>' . $tplsetid . '</strong>)<br />';
				} else {
					$msgs[] = '<span style="color:#ff0000;">' . _ERROR . ': ' . sprintf(_MD_TPLSET_FILE_NOTEXIST, $theme) . '</span>';
				}
			}
		}
		icms_cp_header();
		echo '<code>' . implode("<br />", $msgs) . '</code><br />';
		echo '<br /><a href="admin.php?fct=tplsets">' . _MD_AM_BTOTADMIN . '</a>';
		icms_cp_footer();
		break;

	case 'viewdefault':
		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		$tplfile = &$tpltpl_handler->get($id);
		$default = &$tpltpl_handler->find('default', $tplfile->tpl_type, $tplfile->tpl_refid, null, $tplfile->tpl_file);
		echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>";
		echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . _LANGCODE . '" lang="' . _LANGCODE
			. '"><head><meta http-equiv="content-type" content="text/html; charset=' . _CHARSET
			. '" /><meta http-equiv="content-language" content="' . _LANGCODE
			. '" /><title>' . htmlspecialchars($icmsConfig['sitename']) . ' Administration' . '</title>'
			. '<link rel="stylesheet" type="text/css" media="all" href="'
			. ICMS_URL . '/icms' . ((defined('_ADM_USE_RTL') && _ADM_USE_RTL) ? '_rtl' : '') . '.css" />'
			. '<link rel="stylesheet" type="text/css" media="all" href="'
			. ICMS_MODULES_URL . ' /system/style' . ((defined('_ADM_USE_RTL') && _ADM_USE_RTL)?'_rtl':'') . '.css" />'
			. '</head><body>';

		if (is_object($default[0])) {
			$tpltpl_handler->loadSource($default[0]);
			$last_modified = $default[0]->tpl_lastmodified;
			$last_imported = $default[0]->tpl_lastimported;
			if ($default[0]->tpl_type == 'block') {
				$path = ICMS_MODULES_PATH . '/' . $default[0]->tpl_module . '/blocks/' . $default[0]->tpl_file;
			} else {
				$path = ICMS_MODULES_PATH . '/' . $default[0]->tpl_module . '/' . $default[0]->tpl_file;
			}
			$colorchange = '';
			if (!file_exists($path)) {
				$filemodified_date = _MD_NOFILE;
				$lastimported_date = _MD_NOFILE;
			} else {
				$tpl_modified = filemtime($path);
				$filemodified_date = formatTimestamp($tpl_modified, 'l');
				if ($tpl_modified > $last_imported) {
					$colorchange = ' bgcolor="#ffCC99"';
				}
				$lastimported_date = formatTimestamp($last_imported, 'l');
			}
			$form = new icms_form_Theme(_MD_VIEWDEFAULT, 'template_form', 'admin.php');
			$form->addElement(new icms_form_elements_Textarea(_MD_FILEHTML, 'html', $default[0]->tpl_source, 25));
			$form->display();
		} else {
			echo _MD_TPLSET_FILE_NOTEXIST;
		}
		echo '<div style="text-align:center;">[<a href="#" onclick="window.close();">' . _CLOSE . '</a>]</div></body></html>';
		break;

	case 'downloadtpl':
		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		$tpl = &$tpltpl_handler->get((int)($id), true);
		if (is_object($tpl)) {
			$output = $tpl->tpl_source;
			strlen($output);
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			header('Content-Type: application/force-download');
			if (preg_match("/MSIE 5.5/", $_SERVER['HTTP_USER_AGENT'])) {
				header('Content-Disposition: filename=' . $tpl->tpl_file);
			} else {
				header('Content-Disposition: attachment; filename=' . $tpl->tpl_file);
			}
			header('Content-length: ' . strlen($output));
			echo $output;
		}
		break;

	case 'download':
		if (isset($tplset)) {
			if (false !== extension_loaded('zlib')) {
				if ($method == 'tar') {
					if (@function_exists('gzencode')) {
						$downloader = new icms_file_TarDownloader();
					}
				} else {
					if (@function_exists('gzcompress')) {
						$downloader = new icms_file_ZipDownloader();
					}
				}
				$tplsetobj = &$tplset_handler->getByName($tplset);
				$xml = "<" . "?xml version=\"1.0\"?" . ">\r\n<tplset>\r\n  <name>" . $tplset . "</name>\r\n  <dateCreated>" . $tplsetobj->tplset_created . "</dateCreated>\r\n  <credits>\r\n" . $tplsetobj->tplset_credits . "\r\n  </credits>\r\n  <generator>" . ICMS_VERSION_NAME . "</generator>\r\n  <templates>";
				/**
				 * @var icms_view_template_file_Handler $tpltpl_handler
				 */
				$tpltpl_handler = &icms::handler('icms_view_template_file');
				$files = &$tpltpl_handler->getObjects(new icms_db_criteria_Item('tpl_tplset', $tplset), false);
				$fcount = count($files);
				if ($fcount > 0) {
					for ($i = 0; $i < $fcount; $i++) {
						if ($files[$i]->tpl_type == 'block') {
							$path = $tplset . '/templates/' . $files[$i]->tpl_module . '/blocks/' . $files[$i]->tpl_file;
							$xml .= "\r\n    <template name=\"" . $files[$i]->tpl_file . "\">\r\n      <module>" . $files[$i]->tpl_module . "</module>\r\n      <type>block</type>\r\n      <lastModified>" . $files[$i]->tpl_lastmodified . "</lastModified>\r\n    </template>";
						} elseif ($files[$i]->tpl_type == 'module') {
							$path = $tplset . '/templates/' . $files[$i]->tpl_module . '/' . $files[$i]->tpl_file;
							$xml .= "\r\n    <template name=\"" . $files[$i]->tpl_file . "\">\r\n      <module>" . $files[$i]->tpl_module . "</module>\r\n      <type>module</type>\r\n      <lastModified>" . $files[$i]->tpl_lastmodified . "</lastModified>\r\n    </template>";
						}
						$downloader->addFileData($files[$i]->tpl_source, $path, $files[$i]->tpl_lastmodified);
					}

					$xml .= "\r\n  </templates>";

				}
				//$xml .= "\r\n  </images>
				$xml .= "\r\n</tplset>";
				$downloader->addFileData($xml, $tplset . '/tplset.xml', time());
				echo $downloader->download($tplset, true);
			} else {
				icms_cp_header();
				icms_core_Message::error(_MD_NOZLIB);
				icms_cp_footer();
			}
		}
		break;

	case 'generatetpl':
		icms_cp_header();
		icms_core_Message::confirm(array('tplset' => $tplset, 'moddir' => $moddir, 'file' => $file, 'type' => $type, 'op' => 'generatetpl_go', 'fct' => 'tplsets'), 'admin.php', _MD_PLZGENERATE, _MD_GENERATE);
		icms_cp_footer();
		break;

	case 'generatetpl_go':
		if (!icms::$security->check()) {
			redirect_header('admin.php?fct=tplsets', 3, implode('<br />', icms::$security->getErrors()));
		}
		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		$tplfile = &$tpltpl_handler->find('default', $type, null, $moddir, $file, true);
		if (count($tplfile) > 0) {
			$newtpl = &$tplfile[0]->xoopsClone();
			$newtpl->tpl_id = 0;
			$newtpl->tpl_tplset = $tplset;
			$newtpl->tpl_lastmodified = time();
			$newtpl->tpl_lastimported = 0;
			if (!$tpltpl_handler->insert($newtpl)) {
				$err = _ERROR . ': ' . sprintf(_MD_TPLSET_INSERT_FAILED, '<strong>' . $tplfile[0]->tpl_file . '</strong>');
			} else {
				if ($tplset == $icmsConfig['template_set']) {

					icms_view_Tpl::template_touch($newtpl->tpl_id);
				}
			}
		} else {
			$err = _MD_TPLSET_FILE_NOTEXIST;
		}
		if (!isset($err)) {
			redirect_header('admin.php?fct=tplsets&amp;op=listtpl&amp;moddir=' . $newtpl->tpl_module . '&amp;tplset=' . urlencode($newtpl->tpl_tplset), 2, _MD_AM_DBUPDATED);
		}
		icms_cp_header();
		icms_core_Message::error($err);
		echo '<br /><a href="admin.php?fct=tplsets">' . _MD_AM_BTOTADMIN . '</a>';
		icms_cp_footer();
		break;

	case 'generatemod':
		icms_cp_header();
		icms_core_Message::confirm(array('tplset' => $tplset, 'op' => 'generatemod_go', 'fct' => 'tplsets', 'moddir' => $moddir), 'admin.php', _MD_PLZGENERATE, _MD_GENERATE);
		icms_cp_footer();
		break;

	case 'generatemod_go':
		if (!icms::$security->check()) {
			redirect_header('admin.php?fct=tplsets', 3, implode('<br />', icms::$security->getErrors()));
		}

		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		icms_cp_header();
		echo '<code>';
		$tplfiles = &$tpltpl_handler->find('default', 'module', null, $moddir, null, true);
		$fcount = count($tplfiles);
		if ($fcount > 0) {
			echo sprintf(_MD_TPLSET_INSTALLING . $tplset) . '...<br />';
			for ($i = 0; $i < $fcount; $i++) {
				$newtpl = &$tplfiles[$i]->xoopsClone();
				$newtpl->tpl_id = 0;
				$newtpl->tpl_tplset = $tplset;
				$newtpl->tpl_lastmodified = time();
				$newtpl->tpl_lastimported = 0;
				if (!$tpltpl_handler->insert($newtpl)) {
					echo '&nbsp;&nbsp;<span style="color:#ff0000;">'
					. _ERROR . ': ' . sprintf(_MD_TPLSET_INSERT_FAILED, '<strong>' . $file . '</strong>') . '</span><br />';
				} else {
					if ($tplset == $icmsConfig['template_set']) {
						icms_view_Tpl::template_touch($newtpl->tpl_id);
					}
					echo '&nbsp;&nbsp;' . sprintf(_MD_TPLSET_INSERT_OK, '<strong>' . $tplfiles[$i]->tpl_file . '</strong>') . '<br />';
				}
			}
			flush();
			unset($newtpl);
		}
		unset($files);
		$tplfiles = & $tpltpl_handler->find('default', 'block', null, $moddir, null, true);
		$fcount = count($tplfiles);
		if ($fcount > 0) {
			echo '&nbsp;&nbsp;' . _MD_TPLSET_INSTALLING_BLOCKS . '...<br />';
			for ($i = 0; $i < $fcount; $i++) {
				$newtpl = & $tplfiles[$i]->xoopsClone();
				$newtpl->tpl_id = 0;
				$newtpl->tpl_tplset = $tplset;
				$newtpl->tpl_lastmodified = time();
				$newtpl->tpl_lastimported = 0;
				if (!$tpltpl_handler->insert($newtpl)) {
					echo '&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#ff0000;">'
						. _ERROR . ': ' . sprintf(_MD_TPLSET_BLOCK_INSERT_FAILED, '<strong>' . $tplfiles[$i]->tpl_file . '</strong>')
						. '</span><br />';
					echo $newtpl->getHtmlErrors();
				} else {
					if ($tplset == $icmsConfig['template_set']) {
						icms_view_Tpl::template_touch($newtpl->tpl_id);
					}
					echo '&nbsp;&nbsp;&nbsp;&nbsp;' . sprintf(_MD_TPLSET_BLOCK_INSERT_OK, '<strong>' . $tplfiles[$i]->tpl_file . '</strong>') . '<br />';
				}
			}
			flush();
			unset($newtpl);
		}
		echo '<br />' . sprintf(_MD_TPLSET_TEMPLATE_ADDED, '<strong>' . $tplset . '</strong>')
			. '<br /></code><br /><a href="admin.php?fct=tplsets">' . _MD_AM_BTOTADMIN . '</a>';
		icms_cp_footer();
		break;

	case 'uploadtar_go':
		if (!icms::$security->check()) {
			redirect_header('admin.php?fct=tplsets', 3, implode('<br />', icms::$security->getErrors()));
		}
		$uploader = new icms_file_MediaUploadHandler(ICMS_UPLOAD_PATH, array(
			'application/x-gzip',
			'application/gzip',
			'application/gzip-compressed',
			'application/x-gzip-compressed',
			'application/x-tar',
			'application/x-tar-compressed',
			'application/octet-stream'
			),
			1000000);
		$uploader->setPrefix('tmp');
		icms_cp_header();
		echo '<code>';
		if ($uploader->fetchMedia($xoops_upload_file[0])) {
			if (!$uploader->upload()) {
				icms_core_Message::error($uploader->getErrors());
			} else {
				$tar = new Tar();
				$tar->openTar($uploader->getSavedDestination());
				@unlink($uploader->getSavedDestination());
				$themefound = false;
				foreach ($tar->files as $id => $info) {
					$infoarr = explode('/', str_replace("\\", '/', $info['name']));
					if (!isset($tplset_name)) {
						$tplset_name = trim($infoarr[0]);
					} else {
						$tplset_name = trim($tplset_name);
						if ($tplset_name == '') {
							$tplset_name = trim($infoarr[0]);
						}
					}
					if ($tplset_name != '') {
						break;
					}
				}

				if ($tplset_name == '') {
					echo '<span style="color:#ff0000;">' . _ERROR . ': ' . _MD_TPLSET_NAME_NOT_BLANK . '</span><br />';
				} elseif (preg_match('/[' . preg_quote('\/:*?"<>|', '/') . ']/', $tplset_name)) {
					echo '<span style="color:#ff0000;">' . _ERROR . ': ' . _MD_TPLSET_INVALID_NAME . '</span><br />';
				} else {
					if ($tplset_handler->getCount(new icms_db_criteria_Item('tplset_name', $tplset_name)) > 0) {
						echo '<span style="color:#ff0000;">' . _ERROR . ': ' . sprintf(_MD_TPLSET_EXISTS, '<strong>' . htmlspecialchars($tplset_name, ENT_QUOTES, _CHARSET) . '</strong>') . '</span><br />';
					} else {
						$tplset = & $tplset_handler->create();
						$tplset->tplset_name = $tplset_name;
						$tplset->tplset_created = time();
						if (!$tplset_handler->insert($tplset)) {
							echo '<span style="color:#ff0000;">' . _ERROR . ': ' . sprintf(_MD_TPLSET_CREATE_FAILED, '<strong>' . htmlspecialchars($tplset_name, ENT_QUOTES, _CHARSET) . '</strong>') . '</span><br />';
						} else {
							$tplsetid = $tplset->tplset_id;
							echo sprintf(_MD_TPLSET_CREATE_OK, '<strong>' . htmlspecialchars($tplset_name, ENT_QUOTES, _CHARSET) . '</strong>')
								. '(ID: <strong>' . $tplsetid . '</strong>)</span><br />';
							/**
							 * @var icms_view_template_file_Handler $tpltpl_handler
							 */
							$tpltpl_handler = icms::handler('icms_view_template_file');
							$themeimages = array();
							foreach ($tar->files as $id => $info) {
								$infoarr = explode('/', str_replace("\\", '/', $info['name']));
								if (isset($infoarr[3]) && trim($infoarr[3]) == 'blocks') {
									$default = &$tpltpl_handler->find('default', 'block', null, trim($infoarr[2]), trim($infoarr[4]));
								} elseif ((!isset($infoarr[4]) || trim($infoarr[4]) == '') && $infoarr[1] == 'templates') {
									$default = &$tpltpl_handler->find('default', 'module', null, trim($infoarr[2]), trim($infoarr[3]));
								} elseif ($infoarr[1] == "templates" && $infoarr[2] == "system" && $infoarr[3] == "admin") {
									$file = $infoarr[3];
									for ($i = 4; $i < count($infoarr); $i++) {
										$file .= "/" . $infoarr[$i];
									}
									$default = & $tpltpl_handler->find('default', 'module', null, trim($infoarr[2]), $file);
									unset($file);
								} elseif (isset($infoarr[3]) && trim($infoarr[3]) == 'images') {
									$infoarr[2] = trim($infoarr[2]);
									if (preg_match("/(.*)\.(gif|jpg|jpeg|png)$/i", $infoarr[2], $match)) {
										$themeimages[] = array('name' => $infoarr[2], 'content' => $info['file']);
									}
								}

								if (isset($default) && count($default) > 0) {
									$newtpl = & $default[0]->xoopsClone();
									$newtpl->tpl_id = 0;
									$newtpl->tpl_tplset = $tplset_name;
									$newtpl->setVar('tpl_source', $info['file'], true);
									$newtpl->tpl_lastmodified = time();
									if (!$tpltpl_handler->insert($newtpl)) {
										echo '&nbsp;&nbsp;<span style="color:#ff0000;">' . _ERROR . ': ' . sprintf(_MD_TPLSET_INSERT_FAILED, '<strong>' . $info['name'] . '</strong>') . '</span><br />';
									} else {
										echo '&nbsp;&nbsp;' . sprintf(_MD_TPLSET_INSERT_OK, '<strong>' . $info['name'] . '</strong>') . '<br />';
									}
									unset($default);
								} else {
									if (strrpos($info["name"], "tplset.xml") === false) {
										echo '&nbsp;&nbsp;<span style="color:#ff0000;">' . _ERROR . ': ' . sprintf(_MD_TPLSET_NOT_FOUND, '<strong>' . $info['name'] . '</strong>') . '</span><br />';
									}
								}
								unset($info);
							}

							$icount = count($themeimages);
							if ($icount > 0) {
								$imageset_handler = icms::handler('icms_image_set');
								$imgset = & $imageset_handler->create();
								$imgset->imgset_name = $tplset_name;
								$imgset->imgset_refid = 0;
								if (!$imageset_handler->insert($imgset)) {
									echo '&nbsp;&nbsp;<span style="color:#ff0000;">' . _ERROR . ': ' . _MD_TPLSET_IMGSET_CREATE_FAILED . '</span><br />';
								} else {
									$newimgsetid = $imgset->imgset_id;
									echo '&nbsp;&nbsp;' . sprintf(_MD_TPLSET_IMGSET_CREATED, '<strong>' . htmlspecialchars($tplset_name, ENT_QUOTES, _CHARSET) . '</strong>')
										. '(ID: <strong>' . $newimgsetid . '</strong>)<br />';
									if (!$imageset_handler->linktplset($newimgsetid, $tplset_name)) {
										echo '&nbsp;&nbsp;<span style="color:#ff0000;">' . _ERROR . ': ' . sprintf(_MD_TPLSET_IMGSET_LINK_FAILED, '<strong>' . htmlspecialchars($tplset_name, ENT_QUOTES, _CHARSET) . '</strong>') . '</span><br />';
									}
								}
							}
						}
					}
				}
			}
		} else {
			$err = implode('<br />', $uploader->getErrors(false));
			echo $err;
		}
		echo '</code><br /><a href="admin.php?fct=tplsets">' . _MD_AM_BTOTADMIN . '</a>';
		icms_cp_footer();
		break;

	case 'previewtpl':
		if (!icms::$security->check()) {
			redirect_header('admin.php?fct=tplsets', 3, implode('<br />', icms::$security->getErrors()));
		}


		$html = DataFilter::stripSlashesGPC($html);
		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		$tplfile = &$tpltpl_handler->get($id, true);
		$xoopsTpl = new icms_view_Tpl();

		if (is_object($tplfile)) {
			$dummylayout = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
				. '<html><head><meta http-equiv="content-type" content="text/html; charset=' . _CHARSET
				. '" /><meta http-equiv="content-language" content="' . _LANGCODE
				. '" /><title>' . $icmsConfig['sitename'] . '</title>'
				. '<link rel="stylesheet" type="text/css" media="screen" href="' . ICMS_URL . '/icms'
				. ((defined('_ADM_USE_RTL') && _ADM_USE_RTL)
					?'_rtl'
					:'')
				. '.css" /><link rel="stylesheet" type="text/css" media="screen" href="'
				. xoops_getcss($icmsConfig['theme_set']) . '" />';

			$css = & $tpltpl_handler->find($icmsConfig['template_set'], 'css', 0, null, null, true);
			$csscount = count($css);

			for ($i = 0; $i < $csscount; $i++) {
				$dummylayout .= "\n" . $css[$i]->tpl_source;
			}

			$dummylayout .= "\n" . '</style></head><body><div id="xo-canvas"><{$content}></div></body></html>';
			if ($tplfile->tpl_type == 'block') {

				$block = new icms_view_block_Object($tplfile->tpl_refid);
				$xoopsTpl->assign('block', $block->buildBlock());
			}

			$dummytpl = '_dummytpl_' . time() . '.html';
			$fp = fopen(ICMS_CACHE_PATH . '/' . $dummytpl, 'w');
			fwrite($fp, $html);
			fclose($fp);
			$xoopsTpl->assign('content', $xoopsTpl->fetch('file:' . ICMS_CACHE_PATH . '/' . $dummytpl));
			$xoopsTpl->clear_compiled_tpl('file:' . ICMS_CACHE_PATH . '/' . $dummytpl);
			unlink(ICMS_CACHE_PATH . '/' . $dummytpl);
			$dummyfile = '_dummy_' . time() . '.html';
			$fp = fopen(ICMS_CACHE_PATH . '/' . $dummyfile, 'w');
			fwrite($fp, $dummylayout);
			fclose($fp);
			$tplset = $tplfile->tpl_tplset;
			$tform = array('tpl_tplset' => $tplset, 'tpl_id' => $id, 'tpl_file' => $tplfile->tpl_file, 'tpl_desc' => $tplfile->tpl_desc, 'tpl_lastmodified' => $tplfile->tpl_lastmodified, 'tpl_source' => htmlspecialchars($html, ENT_QUOTES, _CHARSET), 'tpl_module' => $moddir);
			include_once ICMS_MODULES_PATH . '/system/admin/tplsets/tplform.php';
			icms_cp_header();
			echo '<a href="admin.php?fct=tplsets">' . _MD_TPLMAIN . '</a>'
				. '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'
				. '<a href="./admin.php?fct=tplsets&amp;op=listtpl&amp;moddir=' . $moddir
				. '&amp;tplset=' . urlencode($tplset) . '">' . htmlspecialchars($tplset, ENT_QUOTES, _CHARSET) . '</a>'
				. '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;' . _MD_EDITTEMPLATE
				. '<br /><br />';
			$form->display();
			icms_cp_footer();
			echo '<script type="text/javascript">
			<!--//
			preview_window = openWithSelfMain("", "popup", 680, 450, TRUE);
			preview_window.document.clear();
			';
			$lines = preg_split("/(\r\n|\r|\n)( *)/", $xoopsTpl->fetch('file:' . ICMS_CACHE_PATH . '/' . $dummyfile));
			$xoopsTpl->clear_compiled_tpl('file:' . ICMS_CACHE_PATH . '/' . $dummyfile);
			unlink(ICMS_CACHE_PATH . '/' . $dummyfile);
			foreach ($lines as $line) {
				echo 'preview_window.document.writeln("' . str_replace('"', '\"', $line) . '");';
			}
			echo '
			preview_window.focus();
			preview_window.document.close();
			//-->
			</script>';
		}
		break;

	case 'update':
		if (!icms::$security->check()) {
			redirect_header('admin.php?fct=tplsets', 3, implode('<br />', icms::$security->getErrors()));
		}
		$uploader = new icms_file_MediaUploadHandler(ICMS_UPLOAD_PATH, array('text/html', 'application/x-cdf'), 200000);
		$uploader->setPrefix('tmp');
		$msg = array();
		foreach ($xoops_upload_file as $upload_file) {
			// '.' is converted to '_' when upload
			$upload_file2 = str_replace('.', '_', $upload_file);
			if ($uploader->fetchMedia($upload_file2)) {
				if (!$uploader->upload()) {
					$msg[] = $uploader->getErrors();
				} else {
					/**
					 * @var icms_view_template_file_Handler $tpltpl_handler
					 */
					$tpltpl_handler = &icms::handler('icms_view_template_file');
					if (!isset($old_template[$upload_file])) {
						$tplfile = &$tpltpl_handler->find('default', null, null, $moddir, $upload_file);
						if (count($tplfile) > 0) {
							$tpl = &$tplfile[0]->xoopsClone();
							$tpl->tpl_id = 0;
							$tpl->tpl_tplset = $tplset;
						} else {
							$msg[] = sprintf(_MD_TPLSET_FILE_UNNECESSARY, '<strong>' . $upload_file . '</strong>');
							continue;
						}
					} else {
						$tpl = & $tpltpl_handler->get($old_template[$upload_file]);
					}
					$tpl->tpl_lastmodified = time();
					$fp = @fopen($uploader->getSavedDestination(), 'r');
					$fsource = @fread($fp, filesize($uploader->getSavedDestination()));
					@fclose($fp);
					$tpl->setVar('tpl_source', $fsource, true);
					@unlink($uploader->getSavedDestination());
					if (!$tpltpl_handler->insert($tpl)) {
						$msg[] = sprintf(_MD_TPLSET_INSERT_FAILED, $upload_file);
					} else {
						$msg[] = sprintf(_MD_TPLSET_UPDATED, '<strong>' . $upload_file . '</strong>');
						if ($tplset == $icmsConfig['template_set']) {

							if (icms_view_Tpl::template_touch($tpl->tpl_id)) {
								$msg[] = sprintf(_MD_TPLSET_COMPILED, '<strong>' . $upload_file . '</strong>');
							}
						}
					}
				}
			} else {
				if ($uploader->getMediaName() == '') {
					continue;
				} else {
					$msg[] = $uploader->getErrors();
				}
			}
		}

		icms_cp_header();
		echo '<code>' . implode('<br />', $msg) . '<br />';

		echo '</code><br /><a href="admin.php?fct=tplsets&amp;op=listtpl&amp;tplset=' . urlencode($tplset)
			. '&amp;moddir=' . $moddir . '">' . _MD_AM_BTOTADMIN . '</a>';
		icms_cp_footer();
		break;

	case 'importtpl':
		icms_cp_header();
		if (!empty($id)) {
			icms_core_Message::confirm(array('tplset' => $tplset, 'moddir' => $moddir, 'id' => $id, 'op' => 'importtpl_go', 'fct' => 'tplsets'), 'admin.php', _MD_RUSUREIMPT, _MD_IMPORT);
		} elseif (isset($file)) {
			icms_core_Message::confirm(array('tplset' => $tplset, 'moddir' => $moddir, 'file' => $file, 'op' => 'importtpl_go', 'fct' => 'tplsets'), 'admin.php', _MD_RUSUREIMPT, _MD_IMPORT);
		}
		icms_cp_footer();
		break;

	case 'importtpl_go':
		if (!icms::$security->check()) {
			redirect_header('admin.php?fct=tplsets', 3, implode('<br />', icms::$security->getErrors()));
		}
		/**
		 * @var icms_view_template_file_Handler $tpltpl_handler
		 */
		$tpltpl_handler = &icms::handler('icms_view_template_file');
		$tplfile = '';
		if (!empty($id)) {
			$tplfile = &$tpltpl_handler->get($id, true);
		} else {
			$tplfiles = &$tpltpl_handler->find('default', null, null, null, trim($file), true);
			$tplfile = (count($tplfiles) > 0) ? $tplfiles[0] : '';
		}

		$error = true;
		if (is_object($tplfile)) {
			switch ($tplfile->tpl_type) {
				case 'module':
					$filepath = ICMS_THEME_PATH . '/' . $tplset . '/templates/' . $tplfile->tpl_module . '/' . $tplfile->tpl_file;
					break;
				case 'block':
					$filepath = ICMS_THEME_PATH . '/' . $tplset . '/templates/' . $tplfile->tpl_module . '/blocks/' . $tplfile->tpl_file;
					break;
				default:
					break;
			}

			if (file_exists($filepath)) {
				if (false !== $fp = fopen($filepath, 'r')) {
					$filesource = fread($fp, filesize($filepath));
					fclose($fp);
					$tplfile->setVar('tpl_source', $filesource, true);
					$tplfile->tpl_tplset = $tplset;
					$tplfile->tpl_lastmodified = time();
					$tplfile->tpl_lastimported = time();
					if (!$tpltpl_handler->insert($tplfile)) {
					} else {
						$error = false;
					}
				}
			}
		}

		if (false !== $error) {
			icms_cp_header();
			icms_core_Message::error(_MD_TPLSET_IMPORT_FAILED . ' ' . $filepath);
			echo '<br /><a href="admin.php?fct=tplsets&amp;op=listtpl&amp;tplset=' . urlencode($tplset) . '&amp;moddir=' . $moddir . '">' . _MD_AM_BTOTADMIN . '</a>';
			icms_cp_footer();
			exit();
		}
		redirect_header('admin.php?fct=tplsets&amp;op=listtpl&amp;moddir=' . $tplfile->tpl_module . '&amp;tplset=' . urlencode($tplfile->tpl_tplset), 2, _MD_AM_DBUPDATED);
		break;

	case 'list':
	default:
	$tplsets = &$tplset_handler->getObjects();
	icms_cp_header();
	echo '<div class="CPbigTitle" style="background-image: url(' . ICMS_MODULES_URL
		. '/system/admin/tplsets/images/tplsets_big.png)">' . _MD_TPLMAIN
		. '</div><br />';
	$installed = array();
	/**
	 * @var icms_view_template_file_Handler $tpltpl_handler
	 */
	$tpltpl_handler = &icms::handler('icms_view_template_file');
	$installed_mods = $tpltpl_handler->getModuleTplCount('default');
	$tcount = count($tplsets);
	if ($tcount == 1) {
		icms_core_Message::warning(_MD_TPLSET_CREATE_OWN, "", true);
	}
	echo '<table width="100%" cellspacing="1" class="outer"><tr align="center"><th width="25%">'
		. _MD_THMSETNAME . '</th><th>' . _MD_CREATED . '</th><th>' . _MD_TEMPLATES
		. '</th><th>' . _MD_TPLSET_ACTIONS . '</th><th>' . _MD_TPLSET_STATUS . '</th></tr>';
	$class = 'even';
		for ($i = 0; $i < $tcount; $i++) {
			$tplsetname = $tplsets[$i]->tplset_name;
			$installed_themes[] = $tplsetname;
			$class = ($class == 'even')?'odd':'even';
			echo '<tr class="' . $class . '" align="center"><td  style="vertical-align: middle;" class="head">'
				. $tplsetname . '<br /><br /><span style="font-weight:normal;">'
				. $tplsets[$i]->tplset_desc . '</span></td><td style="vertical-align: middle;">'
				. formatTimestamp($tplsets[$i]->tplset_created, 's')
				. '</td><td align="' . _GLOBAL_LEFT . '"><ul>';
			$tplstats = $tpltpl_handler->getModuleTplCount($tplsetname);
			if (count($tplstats) > 0) {
				$module_handler = icms::handler('icms_module');
				echo '<ul>';
				foreach ($tplstats as $moddir => $filecount) {
					$module = & $module_handler->getByDirname($moddir);
					if (is_object($module)) {
						if ($installed_mods[$moddir] > $filecount) {
							$filecount = '<span style="color:#ff0000;">' . $filecount . '</span>';
						}
						echo '<li>' . $module->name
							. ' [<a href="admin.php?fct=tplsets&amp;op=listtpl&amp;tplset=' . $tplsetname
							. '&amp;moddir=' . $moddir . '">' . _LIST . '</a> (<strong>'
							. icms_conv_nr2local($filecount) . '</strong>)]</li>';
					}
					unset($module);
				}
				$not_installed = array_diff(array_keys($installed_mods), array_keys($tplstats));
			} else {
				$not_installed = & array_keys($installed_mods);
			}
			foreach ($not_installed as $ni) {
				$module = & $module_handler->getByDirname($ni);
				echo '<li>' . $module->name
					. ' <a href="admin.php?fct=tplsets&amp;op=listtpl&amp;tplset=' . $tplsetname
					. '&amp;moddir=' . $ni . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/view_choose.png" alt="' . _LIST . '" title="' . _LIST . '" /></a> (<span style="color:#ff0000; font-weight: bold;">0</span>)'
					. ' <a href="admin.php?fct=tplsets&amp;op=generatemod&amp;tplset=' . $tplsetname
					. '&amp;moddir=' . $ni . '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/filenew2.png" alt="' . _MD_GENERATE . '" title="' . _MD_GENERATE . '" /></a></li>';
			}
			echo '</ul></td><td style="vertical-align: middle;">'
				. '<a href="admin.php?fct=tplsets&amp;op=download&amp;method=tar&amp;tplset=' . $tplsetname
				. '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/filesave2.png" alt="' . _MD_DOWNLOAD . '" title="' . _MD_DOWNLOAD . '" /></a>&nbsp;<a href="admin.php?fct=tplsets&amp;op=clone&amp;tplset=' . $tplsetname
				. '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/editcopy.png" alt="' . _CLONE . '" title="' . _CLONE . '" /></a>';
			if ($tplsetname != 'default' && $tplsetname != $icmsConfig['template_set']) {
				echo '&nbsp;<a href="admin.php?fct=tplsets&amp;op=delete&amp;tplset=' . $tplsetname
					. '"><img src="' . ICMS_IMAGES_SET_URL . '/actions/editdelete.png" alt="' . _DELETE . '" title="' . _DELETE . '" /></a>';
			}
			echo '</td>';
			if ($tplsetname == $icmsConfig['template_set']) {
				echo '<td style="vertical-align: middle;"><img src="' . ICMS_IMAGES_SET_URL . '/actions/button_ok.png" alt="' . _MD_DEFAULTTHEME . '" title="' . _MD_DEFAULTTHEME . '" /></td>';
			} else {
				echo '<td>&nbsp;</td>';
			}
			echo '</tr>';
		}
		echo '</table><br />';

		$form = new icms_form_Theme(_MD_UPLOADTAR, 'tplupload_form', 'admin.php', 'post', true);
		$form->setExtra('enctype="multipart/form-data"');
		$form->addElement(new icms_form_elements_File(_MD_CHOOSETAR . '<br /><span style="color:#ff0000;">' . _MD_ONLYTAR . '</span>', 'tpl_upload', 1000000));
		$form->addElement(new icms_form_elements_Text(_MD_NTHEMENAME . '<br /><span style="font-weight:normal;">' . _MD_ENTERTH . '</span>', 'tplset_name', 20, 50));
		$form->addElement(new icms_form_elements_Hidden('op', 'uploadtar_go'));
		$form->addElement(new icms_form_elements_Hidden('fct', 'tplsets'));
		$form->addElement(new icms_form_elements_Button('', 'upload_button', _MD_UPLOAD, 'submit'));
		$form->display();
		icms_cp_footer();
		break;
}
