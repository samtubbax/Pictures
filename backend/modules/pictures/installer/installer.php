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

		SpoonDirectory::create(FRONTEND_FILES_PATH . '/pictures');
		SpoonDirectory::create(FRONTEND_FILES_PATH . '/pictures/source');
		SpoonDirectory::create(FRONTEND_FILES_PATH . '/pictures/100x');

		SpoonFile::setContent(FRONTEND_FILES_PATH . '/pictures/source/.gitignore', "*\n!.gitignore");
		SpoonFile::setContent(FRONTEND_FILES_PATH . '/pictures/100x/.gitignore', "*\n!.gitignore");

		// set navigation
		$navigationModulesId = $this->setNavigation(null, 'Modules');
		$this->setNavigation($navigationModulesId, 'Pictures', 'pictures/index', array('pictures/add', 'pictures/edit'));


		$picturesId = $this->insertExtra('pictures', 'block', 'Pictures', null, null, 'N', 6000);
		// get search extra id
		$searchId = (int) $this->getDB()->getVar(
			'SELECT id FROM modules_extras
			 WHERE module = ? AND type = ? AND action = ?',
			array('search', 'widget', 'form')
		);

		// loop languages
		foreach($this->getLanguages() as $language)
		{
			// check if a page for blog already exists in this language
			if(!(bool) $this->getDB()->getVar(
				'SELECT 1
				 FROM pages AS p
				 INNER JOIN pages_blocks AS b ON b.revision_id = p.revision_id
				 WHERE b.extra_id = ? AND p.language = ?
				 LIMIT 1',
				array($picturesId, $language)))
			{
				$this->insertPage(
					array('title' => 'Pictures', 'language' => $language),
					null,
					array('extra_id' => $picturesId, 'position' => 'main'),
					array('extra_id' => $searchId, 'position' => 'top')
				);
			}
		}
	}
}
