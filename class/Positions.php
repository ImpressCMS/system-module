<?php
/**
 * Blocks position admin classes
 *
 * @copyright    The ImpressCMS Project <http://www.impresscms.org/>
 * @license    LICENSE.txt
 * @since    ImpressCMS 1.2
 * @author    Rodrigo Pereira Lima (AKA TheRplima) <therplima@impresscms.org>
 * @author    Gustavo Pilla (aka nekro) <nekro@impresscms.org>
 * @author    modified by UnderDog <underdog@impresscms.org>
 */

/**
 * System positions Class
 *
 * @copyright    The ImpressCMS Project <http://www.impresscms.org/>
 * @license    LICENSE.txt
 * @since    ImpressCMS 1.2
 * @author    Gustavo Pilla (aka nekro) <nekro@impresscms.org>
 * @package     ImpressCMS\Modules\System\Class\BlockPositions
 */
class mod_system_Positions extends icms_view_block_position_Object
{

	/**
	 * Constructor
	 *
	 * @param icms_view_block_position_Handler $handler
	 */
	public function __construct(&$handler)
	{
		parent::__construct($handler);

		$this->hideFieldFromForm('id');
		$this->hideFieldFromForm('block_default');
		$this->hideFieldFromForm('block_type');
	}

	/**
	 * Get Custom Title
	 *
	 * @return string
	 */
	public function getCustomTitle()
	{
		$rtn = defined($this->title) ? constant($this->title) : $this->title;
		return $rtn;
	}

	/**
	 * getDeleteItemLink
	 *
	 * Overwrited Method
	 *
	 * @param string $onlyUrl
	 * @param boolean $withimage
	 * @param boolean $userSide
	 * @return string
	 */
	public function getEditItemLink($onlyUrl = false, $withimage = true, $userSide = false)
	{
		if ($this->block_default == 1) {
			return "";
		}
		return parent::getEditItemLink($onlyUrl, $withimage, $userSide);
	}

	/**
	 * getDeleteItemLink
	 *
	 * Overwrited Method
	 *
	 * @param string $onlyUrl
	 * @param boolean $withimage
	 * @param boolean $userSide
	 * @return string
	 */
	public function getDeleteItemLink($onlyUrl = false, $withimage = true, $userSide = false)
	{
		if ($this->block_default == 1) {
			return "";
		}
		return parent::getDeleteItemLink($onlyUrl, $withimage, $userSide);
	}
}
