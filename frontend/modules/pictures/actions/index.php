<?php

/**
 * FrontendPicturesIndex
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class FrontendPicturesIndex extends FrontendBaseBlock
{
	/**
	 * The pagination array
	 * It will hold all needed parameters, some of them need initialization.
	 *
	 * @var	array
	 */
	protected $pagination = array('limit' => 20, 'offset' => 0, 'requested_page' => 1, 'num_items' => null, 'num_pages' => null);

	/**
	 * Execute the action
	 *
	 * @return void
	 */
	public function execute()
	{
		$this->loadTemplate();
		$this->parse();
	}

	/**
	 * Parse the thing
	 *
	 * @return void
	 */
	protected function parse()
	{
		// requested page
		$requestedPage = $this->URL->getParameter('page', 'int', 1);

		// set URL and limit
		$this->pagination['url'] = FrontendNavigation::getURLForBlock('pictures');

		// populate count fields in pagination
		$this->pagination['num_items'] = FrontendPicturesModel::getAllCount();
		$this->pagination['num_pages'] = (int) ceil($this->pagination['num_items'] / $this->pagination['limit']);

		// num pages is always equal to at least 1
		if($this->pagination['num_pages'] == 0) $this->pagination['num_pages'] = 1;

		// redirect if the request page doesn't exist
		if($requestedPage > $this->pagination['num_pages'] || $requestedPage < 1) $this->redirect(FrontendNavigation::getURL(404));

		// populate calculated fields in pagination
		$this->pagination['requested_page'] = $requestedPage;
		$this->pagination['offset'] = ($this->pagination['requested_page'] * $this->pagination['limit']) - $this->pagination['limit'];

		// get articles
		$this->items = FrontendPicturesModel::getAll($this->pagination['offset'], $this->pagination['limit']);

		// assign articles
		$this->tpl->assign('items', $this->items);

		// parse the pagination
		$this->parsePagination();
	}
}