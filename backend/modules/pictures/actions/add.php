<?php

/**
 * BackendPicturesAdd
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendPicturesAdd extends BackendBaseActionAdd
{
	/**
	 * Uploaded images
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
		parent::execute();

		foreach(BackendLanguage::getActiveLanguages() as $language)
		{
			$this->languages[$language] = array('language' => $language);
		}

		$this->loadForm();
		$this->validateForm();
		$this->parse();
		$this->tpl->assign('languages', $this->languages);
		$this->display();

	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('add');

		foreach(BackendLanguage::getActiveLanguages() as $language)
		{
			$this->languages[$language]['formElements']['txtTitle'] = $this->frm->addText('title_' . $language);
			$this->languages[$language]['formElements']['txtText'] = $this->frm->addEditor('text_' . $language);
		}
		$this->frm->addDropdown('template', BackendPicturesModel::getTemplatesForDropdown());

	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			// validate fields
			foreach(BackendLanguage::getActiveLanguages() as $language)
			{
				$this->frm->getField('title_' . $language)->isFilled(BL::err('TitleIsRequired'));
			}


			if($this->frm->isCorrect())
			{
				// build item
				$item['template'] = $this->frm->getField('template')->getValue();

				// insert the item
				$id = BackendPicturesModel::insertAlbum($item);

				foreach(BackendLanguage::getActiveLanguages() as $language)
				{
					try
					{

						BackendModel::getDB()->insert('locale', array('user_id' => 0,
							'language' => $language,
							'application' => 'backend',
							'module' => 'pages',
							'type' => 'lbl',
							'name' => 'PicturesWidget' . $id,
							'value' => $this->frm->getField('title_' . $language)->getValue(),
							'edited_on' => BackendModel::getUTCDate()));

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
					$language['album_id'] = $id;
					$language['title'] = $this->frm->getField('title_' . $language['language'])->getValue();
					$language['url'] = BackendPicturesModel::getURL($language['title']);
					$language['text'] = $this->frm->getField('text_' . $language['language'])->getValue();
				}
				BackendPicturesModel::insertAlbumData($id, $this->languages);

				$this->redirect(BackendModel::createURLForAction('edit') . '&id=' . $id . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $id);
			}
		}
	}
}