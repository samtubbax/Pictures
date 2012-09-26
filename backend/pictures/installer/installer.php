<?php

/**
 * Installer for the pictures module
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class PicturesInstaller extends ModuleInstaller
{
	/**
	 * Install the module
	 */
	public function install()
	{
		// load install.sql
		$this->importSQL(dirname(__FILE__) . '/data/install.sql');

		// add 'location' as a module
		$this->addModule('pictures');

		// import locale
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');

		// module rights
		$this->setModuleRights(1, 'pictures');

		// action rights
		$this->setActionRights(1, 'pictures', 'index');
		$this->setActionRights(1, 'pictures', 'add');
		$this->setActionRights(1, 'pictures', 'edit');
		$this->setActionRights(1, 'pictures', 'delete');
		$this->setActionRights(1, 'pictures', 'download');

		// set navigation
		$navigationModulesId = $this->setNavigation(null, 'Modules');
		$this->setNavigation($navigationModulesId, 'Pictures', 'pictures/index', array('pictures/add', 'pictures/edit'));
	}
}
