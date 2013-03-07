<?php

/**
 * FrontendPicturesModel
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class FrontendPicturesModel
{
	/**
	 * Get an album
	 *
	 * @return void
	 */
	public static function get($url)
	{
		$db = FrontendModel::getDB();
		$data = (array)$db->getRecord('SELECT i.*, d.* FROM pictures_albums AS i
													INNER JOIN pictures_albums_data AS d ON d.album_id = i.id AND d.language = ?
													WHERE d.url = ?', array(FRONTEND_LANGUAGE, $url));

		if(empty($data)) return array();

		$data['pictures'] = $db->getRecords('SELECT i.*, d.title FROM pictures AS i
											INNER JOIN pictures_data AS d ON d.picture_id = i.id AND d.language = ?
											WHERE i.album_id = ? ORDER BY sequence', array(FRONTEND_LANGUAGE, $data['id']));
		foreach($data['pictures'] as &$picture)
		{
			$picture['full_url'] = FRONTEND_FILES_URL . '/pictures/source/' . $picture['filename'];
			$picture['thumbnail_full_url'] = FRONTEND_FILES_URL . '/pictures/100x/' . $picture['filename'];
		}

		return $data;
	}

	/**
	 * Get ALL the albums (or a chunk)
	 *
	 * @param int[optional] $offset
	 * @param int[optional] $limit
	 * @return array
	 */
	public static function getAll($offset = 0, $limit = 20)
	{
		$ids = FrontendModel::getDB()->getColumn('SELECT i.id FROM pictures_albums AS i WHERE i.template = "default" ORDER BY sequence LIMIT ? , ?', array($offset, $limit));
		return self::getMultiple($ids);
	}

	/**
	 * Get all the albums (Count)
	 *
	 * @return int
	 */
	public static function getAllCount()
	{
		return ((int) FrontendModel::getDB()->getVar('SELECT COUNT(id) FROM pictures_albums WHERE template = "default" OR template = "video"'));
	}

	/**
	 * Get multiple albums
	 *
	 * @param array $ids
	 * @return array
	 */
	public static function getMultiple($ids, $addAlts = false)
	{
		if(empty($ids)) return array();


		$albums = (array) FrontendModel::getDB()->getRecords('SELECT i.album_id AS id, i.title, i.url, p.filename, a.preview FROM pictures_albums_data AS i
										INNER JOIN pictures AS p ON p.album_id = i.album_id
										INNER JOIN pictures_albums AS a ON a.id = i.album_id
										WHERE i.language = ? AND i.album_id IN (' . implode(',', $ids) . ') GROUP BY i.album_id ORDER BY a.sequence', FRONTEND_LANGUAGE);

		$i = 1;

		foreach($albums as &$album)
		{
			$album['preview'] = FRONTEND_FILES_URL . '/pictures/100x/' . $album['preview'];
			$album['full_url'] = FrontendNavigation::getURLForBlock('pictures', 'detail') . '/' . $album['url'];
			$i++;
		}

		return $albums;
	}
}