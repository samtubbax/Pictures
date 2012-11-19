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
		$db = FrontendModel::getDB();

		// get id from data
		$id = $this->data['albumId'];
		if($id != null)
		{
			$item = $db->getRecord('SELECT * FROM pictures_albums WHERE id = ?', array($id));

			if($item['template'] == 'default') $this->loadTemplate();
			else
			{
				$this->loadTemplate(FRONTEND_MODULES_PATH . '/pictures/layout/widgets/show_' . $item['template'] . '.tpl');
			}

			$item['pictures'] = (array) $db->getRecords('SELECT * FROM pictures WHERE album_id = ? ORDER BY sequence', array($id));

			foreach($item['pictures'] as &$picture)
			{
				$sizes = SpoonDirectory::getList(FRONTEND_FILES_PATH . '/pictures');
				foreach($sizes as $size) $picture['image_' . $size] = FRONTEND_FILES_URL . '/pictures/' . $size . '/' . $picture['filename'];
			}
		}
		$this->tpl->assign('widgetPictures', $item);
	}
}