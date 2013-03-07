<?php

/**
 * BackendPicturesEdit
 * This is the edit-action, it will display a form to create a new item
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendPicturesEdit extends BackendBaseActionEdit
{
	/**
	 * Images
	 *
	 * @var array
	 */
	public $imageData;

	/**
	 * The language data
	 *
	 * @var array
	 */
	private $languages;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		$this->images = array();

		foreach(BackendLanguage::getActiveLanguages() as $language)
		{
			$this->languages[$language] = array('language' => $language);
		}

		// does the item exists
		if($this->id !== null && BackendPicturesModel::existsAlbum($this->id))
		{
			parent::execute();
			$this->getData();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}
		// no item found, throw an exception, because somebody is fucking with our URL
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Get the data
	 */
	private function getData()
	{
		$this->record = (array) BackendPicturesModel::getAlbum($this->id);
		foreach($this->record['images'] as $i => &$image)
		{
			$image['index'] = $i + 1;
		}

		// no item found, throw an exceptions, because somebody is fucking with our URL
		if(empty($this->record)) $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('edit');
		// need this so multipart encding is set
		$this->frm->addImage('preview');
		$this->frm->addDropdown('template', BackendPicturesModel::getTemplatesForDropdown(), $this->record['template']);
		foreach(BackendLanguage::getActiveLanguages() as $language)
		{
			$this->languages[$language]['formElements']['txtTitle'] = $this->frm->addText('title_' . $language, $this->record['languages'][$language]['title']);
			$this->languages[$language]['formElements']['txtText'] = $this->frm->addEditor('text_' . $language, $this->record['languages'][$language]['text']);
		}

		$this->imageData = $this->record['images'];
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		$settings = BackendModel::getModuleSettings();

		foreach($this->imageData as &$image)
		{
			if(!isset($image['error'])) $image['error'] = '';
		}

		usort($this->imageData, array('BackendPicturesModel', 'sortImageData'));

		$fakeImage = array();
		$fakeImage['sequence'] = 0;
		$fakeImage['id'] = 0;
		$fakeImage['filename'] = '';
		foreach(BL::getActiveLanguages() as $language)
		{
			$fakeImage['title_' . $language] = '';
		}
		$fakeImage['index'] = 0;
		$fakeImage['error'] = '';

		$this->imageData[] = $fakeImage;

		$imageDatagrid = new BackendDataGridArray($this->imageData);
		$hiddenCollumns = array('filename', 'index');
		foreach(BL::getActiveLanguages() as $language)
		{
			$hiddenCollumns[] = 'title_' . $language;
		}
		$imageDatagrid->setColumnsHidden($hiddenCollumns);
		$imageDatagrid->setHeaderLabels(array('error' => ''));
		$imageDatagrid->addColumn('preview', '', '<img src="' . FRONTEND_FILES_URL . '/pictures/100x/[filename]" width="100" />');

		$language = BL::getWorkingLanguage();
		$titleFields = '';
		foreach(BL::getActiveLanguages() as $language)
		{
			$titleFields .= '<span data-title-' . $language . '="[title_' . $language . ']" class="titlePreview">[title_' . $language . ']</span><input data-language="' . $language . '" type="text" value="[title_' . $language . ']" disabled="disabled" style="display:none" class="titleField" name="image_title_' . $language . '_[index]" id="image_title_' . $language . '_[index]" placeholder="' . ucfirst(BL::lbl('Title')) . ' ' . $language . '" />';

		}
		$imageDatagrid->addColumn('titleField', ucfirst(BL::lbl('Title')), $titleFields);
		$imageDatagrid->addColumn('input', ucfirst(BL::lbl('File')), 	'<input class="fileField" style="display:none" type="file" name="image_upload_[index]" id="image_upload_[index]" />
																		<input class="imageField" type="hidden" value="[filename]" name="image_[index]" id="image_[index]" />
																		<input type="hidden" class="sequenceField" value="[sequence]" name="sequence_[index]" id="sequence_[index]" />
																		<input type="hidden" class="idField" value="[id]" name="id_[index]" id="id_[index]" />');

		$imageDatagrid->addColumn('edit', null, BL::lbl('Edit'), null, '#');
		$imageDatagrid->addColumn('delete', null, BL::lbl('Delete'), null, '#');
		$imageDatagrid->enableSequenceByDragAndDrop();
		$this->tpl->assign('imageDatagrid', $imageDatagrid->getContent());

		// assign to template
		$this->tpl->assign('item', $this->record);
		$this->tpl->assign('languages', $this->languages);
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			// reset image data
			$this->imageData = array();
			$albumImages = array();

			// validate fields
			foreach(BackendLanguage::getActiveLanguages() as $language)
			{
				$this->frm->getField('title_' . $language)->isFilled(BL::err('TitleIsRequired'));
			}

			if($this->frm->getField('preview')->isFilled())
			{
				// image extension and mime type
				$this->frm->getField('preview')->isAllowedExtension(array('jpg', 'png', 'gif', 'jpeg'), BL::err('JPGGIFAndPNGOnly'));
				$this->frm->getField('preview')->isAllowedMimeType(array('image/jpg', 'image/png', 'image/gif', 'image/jpeg'), BL::err('JPGGIFAndPNGOnly'));
			}

			// get newly uploaded files
			foreach($_FILES as $fileName => $fileValue)
			{
				if(substr_count($fileName, 'image_upload_') > 0 && $fileValue['name'] != '')
				{
					$i = (int) substr($fileName, strlen('image_upload_'));
					if($i != 0)
					{
						unset($_POST['image_' . $i]);

						$this->frm->addFile('image_upload_' . $i);
						if($this->frm->getField('image_upload_' . $i)->isAllowedExtension(array('jpg', 'png', 'gif', 'jpeg'), BL::err('JPGGIFAndPNGOnly')))
						{
							$filenameImg = preg_replace('/[^a-z0-9 -]/i', '', $this->frm->getField('title_en')->getValue());
							$filenameImg = SpoonFilter::urlise($filenameImg) . '_' . time() . '_' . $i . '.' . $this->frm->getField('image_upload_' . $i)->getExtension();

							$this->frm->getField('image_upload_' . $i)->moveFile(FRONTEND_FILES_PATH . '/pictures/source/' . $filenameImg);
							$this->imageData[$i]['filename'] = $filenameImg;
							$this->frm->addHidden('image_' . $i, $filenameImg);

							BackendPicturesModel::generateThumbs($filenameImg);
						}
						else
						{
							$this->imageData[$i]['error'] = BL::err('JPGGIFAndPNGOnly');
						}

						$this->imageData[$i]['index'] = $i;
					}
				}
			}

			$idsUsed = array();
			// get already uploaded images
			foreach($_POST as $postName => $postValue)
			{
				if(substr_count($postName, 'image') > 0 && $postValue != '' && substr_count($postName, 'image_title') == 0)
				{
					$i = (int) substr($postName, strlen('image_'));
					if($i != 0)
					{
						$savedImage = array();
						$this->imageData[$i]['filename'] = SpoonFilter::getPostValue('image_' . $i, null, null);
						$this->imageData[$i]['index'] = $i;
					}
				}
				if(substr_count($postName, 'id_') > 0)
				{
					$idsUsed[] = $postValue;
				}
			}

			// save the correct images
			foreach($this->imageData as &$image)
			{
				$image['languages'] = array();
				foreach(BL::getActiveLanguages() as $language)
				{
					$imageTitle = SpoonFilter::getPostValue('image_title_' . $language . '_' . $image['index'], null, null);
					if($imageTitle != null) $image['languages'][$language]['title'] = $imageTitle;
					$image['title_' . $language] = $imageTitle;
				}

				if(isset($image['filename']))
				{
					$filenameImg = $image['filename'];
				}


				$sequence = SpoonFilter::getPostValue('sequence_' . $image['index'], null, null);
				$image['sequence'] = $sequence;
				$pictureId = SpoonFilter::getPostValue('id_' . $image['index'], null, null);

				if($image['filename'] != '')
				{
					if($pictureId == null || $pictureId == 0)
					{
						$albumImage = array('filename' => $filenameImg,
											'sequence' => $sequence,
											'album_id' => $this->id,
											'languages' => $image['languages']);

						foreach(BL::getActiveLanguages() as $language)
						{
							if(!isset($albumImage['languages'][$language])) $albumImage['languages'][$language]['title'] = '';
						}
						$pictureId = BackendPicturesModel::insertPicture($albumImage, $this->id);
						$idsUsed[] = $pictureId;
					}
					else
					{
						$albumImage = array('id' => $pictureId,
											'sequence' => $sequence,
											'album_id' => $this->id,
											'languages' => $image['languages']);

						BackendPicturesModel::updatePicture($albumImage);
					}
				}

				unset($image['languages']);
			}

			BackendPicturesModel::deleteUnusedPictures($idsUsed, $this->id);

			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['template'] = $this->frm->getField('template')->getValue();

				if($this->frm->getField('preview')->isFilled())
				{
					$item['preview'] = 'thumbnail_' . time() . '_' . $this->id . '.' .  $this->frm->getField('preview')->getExtension();
					$this->frm->getField('preview')->moveFile(FRONTEND_FILES_PATH . '/pictures/source/' . $item['preview']);
					BackendPicturesModel::generateThumbs($item['preview']);
				}
				else $item['preview'] = $this->record['preview'];


				// insert the item
				BackendPicturesModel::updateAlbum($item);

				foreach(BackendLanguage::getActiveLanguages() as $language)
				{
					try
					{
						BackendModel::getDB()->update('locale', array('value' => $this->frm->getField('title_' . $language)->getValue()), 'language = ? AND application = "backend" AND name = ?', array($language, 'PicturesWidget' . $this->id));
						BackendLocaleModel::buildCache($language, 'backend');
					}
					catch(PDOException $e)
					{
						if(substr_count($e->getMessage(), 'Duplicate entry') == 0) throw $e;
					}
				}

				foreach($this->languages as &$language)
				{
					unset($language['formElements']);
					$language['album_id'] = $this->id;
					$language['title'] = $this->frm->getField('title_' . $language['language'])->getValue();
					$language['url'] = BackendPicturesModel::getURL($language['title'], $this->id);
					$language['text'] = $this->frm->getField('text_' . $language['language'])->getValue();
				}
				BackendPicturesModel::insertAlbumData($this->id, $this->languages);

				// redirect
				$this->redirect(BackendModel::createURLForAction('index') . '&report=edited&var=' . urlencode($this->frm->getField('title_' . BackendLanguage::getInterfaceLanguage())->getValue()) . '&highlight=row-' . $this->id);
			}
			else
			{
				$record = (array) BackendPicturesModel::getAlbum($this->id);
				foreach($record['images'] as $i => &$image)
				{
					$image['index'] = $i + 1;
				}
				$this->imageData = $record['images'];
			}
		}
	}
}
