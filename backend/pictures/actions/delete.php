<?php

/**
 * BackendPicturesDelete
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendPicturesDelete extends BackendBaseActionDelete
{
	public function execute()
	{
		// get parameters
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendPicturesModel::existsAlbum($this->id))
		{
			parent::execute();

			// get all data for the item we want to edit
			$this->record = (array) BackendPicturesModel::getAlbum($this->id);

			// delete item
			BackendPicturesModel::deleteAlbum($this->id);

			// album was deleted, so redirect
			$this->redirect(BackendModel::createURLForAction('index') . '&report=deleted&var=' . urlencode($this->record['title']));
		}

		// something went wrong
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}
}