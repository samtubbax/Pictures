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
		$this->dataGrid = new BackendDataGridDB(BackendPicturesModel::QRY_BROWSE_ALBUMS);
		$this->dataGrid->setColumnURL('title', BackendModel::createURLForAction('edit') . '&amp;id=[id]');
		$this->dataGrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit') . '&amp;id=[id]');
	}

	/**
	 * Parse the datagrid
	 */
	public function parse()
	{
		if($this->dataGrid->getContent() != null) $this->tpl->assign('dataGrid', $this->dataGrid->getContent());
	}
}