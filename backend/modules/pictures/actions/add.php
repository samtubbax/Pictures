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
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadForm();
		$this->validateForm();
		$this->parse();
		$this->display();
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('add');
		$this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');
		$this->frm->addText('tagline', null, null);

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

			if($this->frm->isCorrect())
			{
				// build item
				$item['title'] = $this->frm->getField('title')->getValue();

				foreach(BackendLanguage::getActiveLanguages() as $language)
				{
					try
					{

						BackendModel::getDB()->insert('locale', array('user_id' => 0,
												'language' => $language,
												'application' => 'backend',
												'module' => 'pages',
												'type' => 'lbl',
												'name' => SpoonFilter::toCamelCase($item['title']),
												'value' => $item['title'],
												'edited_on' => BackendModel::getUTCDate()));


						BackendLocaleModel::buildCache($language, 'backend');


					}
					catch(PDOException $e)
					{
						if(substr_count($e->getMessage(), 'Duplicate entry') == 0) throw $e;
					}

				}


				// insert the item
				$id = BackendPicturesModel::insertAlbum($item);

				$this->redirect(BackendModel::createURLForAction('edit') . '&id=' . $id . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $id);
			}
		}
	}
}