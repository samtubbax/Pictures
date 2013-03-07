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
			$item = $db->getRecord('SELECT i.*, d.title FROM pictures_albums AS i 
									INNER JOIN pictures_albums_data AS d ON d.album_id = i.id AND d.language = ?
									WHERE i.id = ?', array(FRONTEND_LANGUAGE, $id));

			if($item['template'] == 'default') $this->loadTemplate();
			else
			{
				$this->loadTemplate(FRONTEND_MODULES_PATH . '/pictures/layout/widgets/show_' . $item['template'] . '.tpl');
			}

			$item['pictures'] = (array) $db->getRecords('SELECT i.*, d.title FROM pictures AS i
														INNER JOIN pictures_data AS d ON d.picture_id = i.id
														WHERE i.album_id = ? AND d.language = ?
														ORDER BY i.sequence', array($id, FRONTEND_LANGUAGE));
		}
		$this->tpl->assign('widgetPictures', $item);
	}
}