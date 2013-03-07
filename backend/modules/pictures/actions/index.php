<?php

/**
 * BackendPicturesIndex
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendPicturesIndex extends BackendBaseActionIndex
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadDataGrid();
		$this->parse();
		$this->display();
	}

	/**
	 * Load the datagrid
	 */
	public function loadDataGrid()
	{
		$this->dataGrid = new BackendDataGridDB(BackendPicturesModel::QRY_BROWSE_ALBUMS, BackendLanguage::getInterfaceLanguage());
		$this->dataGrid->setColumnURL('title', BackendModel::createURLForAction('edit') . '&amp;id=[id]');
		$this->dataGrid->setColumnFunction(array('BackendPicturesIndex', 'parseType'), array('[template]'), 'template');
		$this->dataGrid->enableSequenceByDragAndDrop();
		$this->dataGrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit') . '&amp;id=[id]');
	}

	/**
	 * Parse the datagrid
	 */
	public function parse()
	{
		if($this->dataGrid->getContent() != null) $this->tpl->assign('dataGrid', $this->dataGrid->getContent());
	}

	public static function parseType($var)
	{
		switch($var)
		{
			case 'default':
				return BL::lbl('Default');
				break;
			case 'alt':
				return BL::lbl('Alternative');
			case 'slideshow':
				return BL::lbl('Slideshow');

		}
	}
}