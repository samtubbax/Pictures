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
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		$this->images = array();

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
		foreach($this->record['images'] as $i=>&$image)
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
		$this->frm->addText('title', $this->record['title'], null, 'inputText title', 'inputTextError title');
		// need this so multipart encding is set
		$this->frm->addFile('redHerring');

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
		$fakeImage['filename'] = '';
		$fakeImage['title'] = '';
		$fakeImage['url'] = '';
		$fakeImage['index'] = 0;
		$fakeImage['error'] = '';

		$this->imageData[] = $fakeImage;

		$imageDatagrid = new BackendDataGridArray($this->imageData);
		$imageDatagrid->setColumnsHidden(array('filename', 'index', 'title', 'url'));
		$imageDatagrid->setHeaderLabels(array('error' => ''));
		$imageDatagrid->addColumn('preview', '', '<img src="' . FRONTEND_FILES_URL . '/pictures/100x/[filename]" width="100" />');
		$imageDatagrid->addColumn('titleField', ucfirst(BL::lbl('Title')), '<input type="text" value="[title]" class="titleField" name="image_title_[index]" id="image_title_[index]"');
		$imageDatagrid->addColumn('urlField', ucfirst(BL::lbl('Link')), '<input type="text" value="[url]" class="urlField" name="image_url_[index]" id="image_url_[index]"');
		$imageDatagrid->addColumn('input', '', '<input class="fileField" style="display:none" type="file" name="image_upload_[index]" id="image_upload_[index]" />
												<input class="imageField" type="hidden" value="[filename]" name="image_[index]" id="image_[index]" />
												<input type="hidden" class="sequenceField" value="[sequence]" name="sequence_[index]" id="sequence_[index]" />');
		$imageDatagrid->addColumn('delete', null, BL::lbl('Delete'), null, '#');
		$imageDatagrid->setColumnsSequence(array('preview', 'titleField', 'input', 'urlField', 'error', 'delete'));
		$imageDatagrid->enableSequenceByDragAndDrop();
		$this->tpl->assign('imageDatagrid', $imageDatagrid->getContent());

		// assign to template
		$this->tpl->assign('item', $this->record);
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			// validate fields
			$this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));

			// reset image data
			$this->imageData = array();

			// get newly uploaded files
			foreach($_FILES as $fileName => $fileValue)
			{
				if(substr_count($fileName, 'image_upload_') > 0 && $fileValue['name'] != '')
				{
					$i = (int) substr($fileName, strlen('image_upload_'));

					unset($_POST['image_' . $i]);

					$submittedImage = array();

					$this->frm->addFile('image_upload_' . $i);
					$submittedImage['filename'] = '';

					if($this->frm->getField('image_upload_' . $i)->isAllowedExtension(array('jpg', 'png', 'gif', 'jpeg'), BL::err('JPGGIFAndPNGOnly')))
					{
						$filenameImg = SpoonFilter::urlise($this->frm->getField('title')->getValue()) . '_' . time() . '_' . $i . '.' . $this->frm->getField('image_upload_' . $i)->getExtension();
						$this->frm->getField('image_upload_' . $i)->moveFile(FRONTEND_FILES_PATH . '/pictures/source/' . $filenameImg);
						$submittedImage['filename'] = $filenameImg;
						$this->frm->addHidden('image_' . $i, $filenameImg);
						BackendPicturesModel::generateThumbs($filenameImg);
					}
					else
					{
						$submittedImage['error'] = BL::err('JPGGIFAndPNGOnly');
					}

					$submittedImage['index'] = $i;

					$this->imageData[] = $submittedImage;
				}
			}

			// get already uploaded images
			foreach($_POST as $postName => $postValue)
			{
				if(substr_count($postName, 'image') > 0 && $postValue != '')
				{
					$i = (int) substr($postName, 6);

					if($i != 0)
					{
						$savedImage = array();
						$savedImage['filename'] = SpoonFilter::getPostValue('image_' . $i, null, null);
						$savedImage['index'] = $i;
						$this->imageData[] = $savedImage;
					}
				}
			}

			// Validate images
			foreach($this->imageData as &$image)
			{
				$image['sequence'] = SpoonFilter::getPostValue('sequence_' . $image['index'], null, null);
				$image['title'] = SpoonFilter::getPostValue('image_title_' . $image['index'], null, null);
				$image['url'] = SpoonFilter::getPostValue('image_url_' . $image['index'], null, null);

				if($image['title'] == null) $image['error'] = BL::err('TitleIsRequired');
			}

			// save the correct images
			foreach($this->imageData as &$image)
			{
				if(isset($image['filename']))
				{
					$filenameImg = $image['filename'];
				}
				else
				{
					$filenameImg = SpoonFilter::urlise($project['title']) . '_' . time() . '_' . $image['index'] . '.' . $this->frm->getField('image_upload_' . $image['index'])->getExtension();
					$this->frm->getField('image_' . $image['index'])->moveFile(FRONTEND_FILES_PATH . '/projects/images/source/' . $filenameImg);
				}

				$sequence = SpoonFilter::getPostValue('sequence_' . $image['index'], null, null);
				if($image['filename'] != '')
				{
					$albumImages[] = array('filename' => $filenameImg,
											'sequence' => $sequence,
											'title' => $image['title'],
											'url' => $image['url'],
											'album_id' => $this->id);
				}
			}
			BackendPicturesModel::insertPictures($albumImages, $this->id);



			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['title'] = $this->frm->getField('title')->getValue();

				// insert the item
				BackendPicturesModel::updateAlbum($item);

				foreach(BackendLanguage::getActiveLanguages() as $language)
				{
					BackendLocaleModel::insert(array('user_id' => 0,
												'language' => $language,
												'application' => 'backend',
												'module' => 'pages',
												'type' => 'lbl',
												'name' => SpoonFilter::toCamelCase($item['title']),
												'value' => $item['title'],
												'edited_on' => BackendModel::getUTCDate()));
				}

				// redirect
				$this->redirect(BackendModel::createURLForAction('index') . '&report=edited&var=' . urlencode($item['title']) . '&highlight=row-' . $this->id);
			}
		}
	}
}
