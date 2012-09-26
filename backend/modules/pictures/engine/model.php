<?php

/**
 * BackendPicturesModel
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendPicturesModel
{
	const QRY_BROWSE_ALBUMS = 'SELECT id, title FROM pictures_albums';

	/**
	 * Delete unused pictures
	 *
	 * @return void
	 */
	public static function cleanupPictures()
	{
		// get pictures that aren't in the DB & remove them
		$pictures = BackendModel::getDB()->getColumn('SELECT filename FROM pictures');
		$files = SpoonDirectory::getList(FRONTEND_FILES_PATH . '/pictures/source', true, array('.gitignore'));
		$waste = array_diff($files, $pictures);

		foreach($waste as $picture)
		{
			$folders = SpoonDirectory::getList(FRONTEND_FILES_PATH . '/pictures');
			foreach($folders as $folder)
			{
				if(SpoonFile::exists(FRONTEND_FILES_PATH . '/pictures/' . $folder . '/' . $picture)) SpoonFile::delete(FRONTEND_FILES_PATH . '/pictures/' . $folder . '/' . $picture);
			}
		}
	}

	/**
	 * Delete an album
	 *
	 * @param	int $id
	 * @return void
	 */
	public static function deleteAlbum($id)
	{
		$db = BackendModel::getDB(true);
		$db->delete('pictures_albums', 'id = ?', $id);

		self::cleanupPictures();
		$db->delete('pictures', 'album_id = ?', $id);

		$dataLookup = 'a:2:{s:7:"albumId";i:' . $id . '%';

		$extraId = $db->getVar('SELECT id FROM modules_extras WHERE data LIKE ?', array($dataLookup));

		if($extraId != null)
		{
			$db->delete('modules_extras', 'id = ?', $extraId);
			$db->delete('pages_blocks', 'extra_id = ?', $extraId);
		}
	}

	/**
	 * Check if an album exists
	 *
	 * @param	int $id
	 * @return bool
	 */
	public static function existsAlbum($id)
	{
		return (BackendModel::getDB()->getVar('SELECT 1 FROM pictures_albums WHERE id = ?', $id) > 0);
	}

	/**
	 * Generate thumbnails for an image
	 *
	 * @param	string $filename	The filename
	 * @return void
	 */
	public static function generateThumbs($filename)
	{
		$folders = SpoonDirectory::getList(FRONTEND_FILES_PATH . '/pictures', false, array('source'));

		foreach($folders as $folder)
		{
			$size = explode('x', $folder);

			// set Null in stead of ''
			foreach($size as $i => $dim) if($dim == '') $size[$i] = null;

			$thumb = new SpoonThumbnail(FRONTEND_FILES_PATH . '/pictures/source/' . $filename, $size[0], $size[1]);
			$thumb->setAllowEnlargement(true);
			// if both sizes are set ignore Aspect Ratio
			$thumb->setForceOriginalAspectRatio($size[0] == null || $size[1] == null);
			$thumb->parseToFile(FRONTEND_FILES_PATH . '/pictures/' . $folder . '/' . $filename);
		}

	}

	/**
	 * Get an album
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getAlbum($id)
	{
		$db = BackendModel::getDB();

		$data = (array) $db->getRecord('SELECT * FROM pictures_albums WHERE id = ?', $id);
		if(empty($data)) return $data;

		$data['images'] = (array) $db->getRecords('SELECT title, sequence, url, filename FROM pictures WHERE album_id = ? ORDER BY sequence', $id);

		return $data;
	}

	/**
	 * Insert an album
	 *
	 * @param array $values
	 * @return int
	 */
	public static function insertAlbum($values)
	{
		$id = BackendModel::getDB(true)->insert('pictures_albums', $values);

		// build array
		$extra['module'] = 'pictures';
		$extra['type'] = 'widget';
		$extra['label'] = $values['title'];
		$extra['action'] = 'show';
		$extra['data'] = serialize(array('albumId' => $id, 'edit_url' => BackendModel::createURLForAction('edit', 'pictures', null, array('id' => $id))));
		$extra['hidden'] = 'N';
		$extra['sequence'] = '1600' . $id;

		// insert extra
		BackendModel::getDB(true)->insert('modules_extras', $extra);

		return $id;
	}

	/**
	 * Insert pictures
	 *
	 * @param array $values
	 * @param int $albumId
	 * @return void
	 */
	public static function insertPictures($values, $albumId)
	{
		$db = BackendModel::getDB(true);
		$db->delete('pictures', 'album_id = ?', $albumId);
		if(!empty($values)) $db->insert('pictures', $values);
	}

	/**
	 * Helper function for sorting image data
	 *
	 * @param	$a
	 * @param	$b
	 * @return int
	 */
	public static function sortImageData($a, $b)
	{
   		return ($a['sequence'] < $b['sequence']) ? -1 : 1;
	}

	/**
	 * Update an album
	 *
	 * @param array $values
	 * @return void
	 */
	public static function updateAlbum($values)
	{
		// check for an id
		if(!isset($values['id'])) throw new SpoonException('Id not set');

		// build array
		$extra['label'] = $values['title'];
		$extra['data'] = serialize(array('albumId' => $values['id']));
		$extra['id'] = BackendModel::getDB()->getVar('SELECT id FROM modules_extras WHERE data = ?', array($extra['data']));

		BackendModel::getDB(true)->update('modules_extras', $extra, 'id = ?', array($extra['id']));

		BackendModel::getDB(true)->update('pictures_albums', $values, 'id = ?', $values['id']);
	}
}