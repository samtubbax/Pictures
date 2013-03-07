<?php

/**
 * BackendPicturesModel
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendPicturesModel
{

	const QRY_BROWSE_ALBUMS = 'SELECT i.id, d.title, i.sequence, i.template FROM pictures_albums AS i
								INNER JOIN pictures_albums_data AS d ON d.album_id = i.id
								WHERE d.language = ? ORDER BY sequence';

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
			if(!SpoonFile::exists(FRONTEND_FILES_PATH . '/pictures/' . $folder . '/' . $filename)) $thumb->parseToFile(FRONTEND_FILES_PATH . '/pictures/' . $folder . '/' . $filename);
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

		$imagesQuery = 'SELECT i.id, i.sequence, i.filename, ';
		$i = 0;
		foreach(BackendLanguage::getActiveLanguages() as $language)
		{
			$i++;
			$imagesQuery .= 'd' . $i . '.title AS title_' . $language . ', ';
		}
		$i = 0;

		$imagesQuery = substr($imagesQuery, 0, -2) . ' FROM pictures AS i ';

		foreach(BackendLanguage::getActiveLanguages() as $language)
		{
			$i++;
			$imagesQuery .= 'LEFT OUTER JOIN pictures_data AS d' . $i . ' ON d' . $i . '.picture_id = i.id AND d' . $i . '.language = "' . $language . '" ';
		}
		$imagesQuery .= 'WHERE album_id = ? ORDER BY sequence';
		$data['images'] = (array) $db->getRecords($imagesQuery, $id);

		$data['languages'] = (array) $db->getRecords('SELECT title, text, language FROM pictures_albums_data WHERE album_id = ?', $id, 'language');

		return $data;
	}

	/**
	 * Get all the albums for a dropdown
	 *
	 * @return array
	 */
	public static function getAlbumsForDropdown()
	{
		return BackendModel::getDB()->getPairs('SELECT i.id, d.title FROM pictures_albums AS i
												INNER JOIN pictures_albums_data AS d ON d.album_id = i.id AND d.language = ?', BackendLanguage::getInterfaceLanguage());
	}

	/**
	 * Get the templates for a dropdown
	 *
	 * @return array
	 */
	public static function getTemplatesForDropdown()
	{
		// @remark When adding new templates Add them here + in frontend layout folder as show_<name-of-template>.tpl
		return array(
			'default' => BL::lbl('Default'),
			'lightbox' => BL::lbl('Lightbox'),
			'slideshow' => BL::lbl('Slideshow')
		);
	}

	/**
	 * Get URL for an album
	 *
	 * @param string $base
	 * @param int[optional] $id
	 * @return string
	 */
	public static function getURL($base, $id = null)
	{
		$URL = SpoonFilter::urlise($base);

		// get db
		$db = BackendModel::getDB();

		// new item
		if($id === null)
		{
			// already exists
			if((bool) $db->getVar(
				'SELECT 1
				 FROM pictures_albums_data AS i
				 WHERE i.language = ? AND i.url = ?
				 LIMIT 1',
				array(BL::getWorkingLanguage(), $base)))
			{
				$URL = BackendModel::addNumber($base);
				return self::getURL($base);
			}
		}

		// current category should be excluded
		else
		{
			// already exists
			if((bool) $db->getVar(
				'SELECT 1
				 FROM pictures_albums_data AS i
				 WHERE i.language = ? AND i.url = ? AND i.album_id != ?
				 LIMIT 1',
				array(BL::getWorkingLanguage(), $URL, $id)))
			{

				$URL = BackendModel::addNumber($URL);
				return self::getURL($URL, $id);
			}
		}

		return $URL;
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
		$extra['label'] = 'PicturesWidget' . $id;
		$extra['action'] = 'show';
		$extra['data'] = serialize(array('albumId' => $id, 'edit_url' => BackendModel::createURLForAction('edit', 'pictures', null, array('id' => $id))));
		$extra['hidden'] = 'N';
		$extra['sequence'] = '1600' . $id;

		// insert extra
		BackendModel::getDB(true)->insert('modules_extras', $extra);

		return $id;
	}

	/**
	 * Insert album data
	 *
	 * @param int $albumId
	 * @param array $values
	 * @return void
	 */
	public static function insertAlbumData($albumId, $values)
	{
		BackendModel::getDB(true)->delete('pictures_albums_data', 'album_id = ?', $albumId);
		BackendModel::getDB(true)->insert('pictures_albums_data', $values);
	}

	/**
	 * Insert a picture
	 *
	 * @param array $values
	 * @param int $albumId
	 * @return void
	 */
	public static function insertPicture($values, $albumId)
	{
		$db = BackendModel::getDB(true);

		$languageData = $values['languages'];
		unset($values['languages']);
		$pictureId = $db->insert('pictures', $values);

		foreach($languageData as $language => $data)
		{
			$data['language'] = $language;
			$data['picture_id'] = $pictureId;
			$db->insert('pictures_data', $data);
		}

		return $pictureId;
	}

	/**
	 * Update a  picture
	 *
	 * @param array $values
	 * @return void
	 */
	public static function updatePicture($values)
	{
		$db = BackendModel::getDB(true);

		$languageData = $values['languages'];
		unset($values['languages']);

		$db->update('pictures', $values, 'id = ?', $values['id']);

		foreach($languageData as $language => $data)
		{
			$db->delete('pictures_data', 'picture_id = ? AND language = ?', array($values['id'], $language));
			$data['picture_id'] = $values['id'];
			$data['language'] = $language;
			$db->insert('pictures_data', $data);
		}
	}

	/**
	 * Delete pictures
	 *
	 * @param array $ids
	 * @return void
	 */
	public static function deleteUnusedPictures($ids, $albumId)
	{
		if(empty($ids)) return;
		$unusedPicIds = BackendModel::getDB()->getColumn('SELECT id FROM pictures WHERE id NOT IN (' . implode(',', $ids) . ') AND album_id = ?', $albumId);
		if(empty($unusedPicIds)) return;
		BackendModel::getDB(true)->delete('pictures', 'id IN (' . implode(',', $unusedPicIds) . ')');
		BackendModel::getDB(true)->delete('pictures_data', 'picture_id IN (' . implode(',', $unusedPicIds) . ')');
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
		foreach($values as $value)
		{
			$languageData = $value['languages'];
			unset($value['languages']);
			$pictureId = $db->insert('pictures', $value);

			foreach($languageData as $language => $data)
			{
				$data['language'] = $language;
				$data['picture_id'] = $pictureId;
				$db->insert('pictures_data', $data);
			}
		}

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
		$extra['label'] = 'album' . $values['id'];
		$extra['data'] = serialize(array('albumId' => $values['id']));
		$extra['id'] = BackendModel::getDB()->getVar('SELECT id FROM modules_extras WHERE data = ?', array($extra['data']));

		BackendModel::getDB(true)->update('modules_extras', $extra, 'id = ?', array($extra['id']));

		BackendModel::getDB(true)->update('pictures_albums', $values, 'id = ?', $values['id']);
	}
}