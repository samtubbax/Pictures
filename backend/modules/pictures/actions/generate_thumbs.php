<?php

/**
 * BackendPicturesGenerateThumbs
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendPicturesGenerateThumbs extends BackendBaseAction
{
	/**
	 * Execute the action
	 *
	 * @return void
	 */
	public function execute()
	{
		// Generate thumbnails for all the DB pictures
		$pictures = BackendModel::getDB()->getColumn('SELECT filename FROM pictures');
		foreach($pictures as $pic)
		{
			if(SpoonFile::exists(FRONTEND_FILES_PATH . '/pictures/source/' . $pic)) BackendPicturesModel::generateThumbs($pic);
		}

		BackendPicturesModel::cleanupPictures();
	}
}