<?php

/**
 * FrontendPicturesWidgetShow
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class FrontendPicturesWidgetShow extends FrontendBaseWidget
{
	/**
	 * Execute the widget
	 *
	 * @return void
	 */
	public function execute()
	{
		parent::execute();

		$db = FrontendModel::getDB();

		$this->loadTemplate();

		// get id from data
		$id = $this->data['albumId'];
		if($id != null)
		{
			$item = $db->getRecord('SELECT * FROM pictures_albums WHERE id = ?', array($id));
			$item['pictures'] = (array) $db->getRecords('SELECT * FROM pictures WHERE album_id = ? ORDER BY sequence', array($id));

			$i = 0;
			foreach($item['pictures'] as &$picture)
			{
				$picture['index'] = $i;
				$sizes = SpoonDirectory::getList(FRONTEND_FILES_PATH . '/pictures');
				foreach($sizes as $size) $picture['image_' . $size] = FRONTEND_FILES_URL . '/pictures/' . $size . '/' . $picture['filename'];
				$i++;
			}
		}
		$this->tpl->assign('widgetPictures', $item);
	}
}