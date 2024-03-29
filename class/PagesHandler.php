<?php
/**
 * Administration of Symlinks
 *
 * @copyright    http://www.impresscms.org/ The ImpressCMS Project
 * @license    LICENSE.txt
 */

use ImpressCMS\Core\Models\AbstractExtendedHandler;

/**
 * Symlinks handler
 *
 * @package     ImpressCMS\Modules\System\Class\Pages
 */
class mod_system_PagesHandler extends icms_data_page_Handler
{

	/** */
	private $modules_name;

	/**
	 * Constructor
	 *
	 * @param $db
	 */
	public function __construct(&$db)
	{
		AbstractExtendedHandler::__construct($db, 'pages', 'page_id', 'page_title', '', 'system');
		$this->table = $db->prefix('icmspage');
	}

	/**
	 * Get an array of installed modules
	 *
	 * @param boolean $full
	 * @return    array
	 */
	public function getModulesArray($full = false)
	{
		if (empty($this->modules_name)) {
			$icms_module_handler = icms::handler('icms_module');
			$installed_modules = $icms_module_handler->getObjects();
			foreach ($installed_modules as $module) {
				$this->modules_name[$module->mid]['name'] = $module->name;
				$this->modules_name[$module->mid]['dirname'] = $module->dirname;
			}
		}
		$rtn = $this->modules_name;

		if (!$full) {
			foreach ($this->modules_name as $key => $module) {
				$rtn[$key] = $module['name'];
			}
		}

		return $rtn;
	}

	/**
	 * Change the status of the symlink in the db
	 *
	 * @param $page_id
	 * @return    boolean    FALSE if failed, TRUE if successful
	 */
	public function changeStatus($page_id)
	{
		$page = $this->get($page_id);
		$page->page_status = !$page->page_status;
		return $this->insert($page, true);
	}
}
