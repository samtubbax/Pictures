<?php

/**
 * FrontendPicturesDetail
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class FrontendPicturesDetail extends FrontendBaseBlock
{
	/**
	 * The album
	 *
	 * @var array
	 */
	private $album;

	/**
	 * The pagination array
	 * It will hold all needed parameters, some of them need initialization.
	 *
	 * @var	array
	 */
	protected $pagination = array('limit' => 16, 'offset' => 0, 'requested_page' => 1, 'num_items' => null, 'num_pages' => null);

	/**
	 * Execute the action
	 *
	 * @return void
	 */
	public function execute()
	{
		$this->album = FrontendPicturesModel::get($this->URL->getParameter(1));
		if(empty($this->album)) $this->redirect(FrontendNavigation::getURL(404));

		if($this->album['template'] == 'default') $this->loadTemplate();
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
		$this->pagination['url'] = FrontendNavigation::getURLForBlock('pictures', 'detail') . '/' . $this->album['url'];

		// populate count fields in pagination
		$this->pagination['num_items'] = count($this->album['pictures']);
		$this->pagination['num_pages'] = (int) ceil($this->pagination['num_items'] / $this->pagination['limit']);

		// num pages is always equal to at least 1
		if($this->pagination['num_pages'] == 0) $this->pagination['num_pages'] = 1;

		// redirect if the request page doesn't exist
		if($requestedPage > $this->pagination['num_pages'] || $requestedPage < 1) $this->redirect(FrontendNavigation::getURL(404));

		// populate calculated fields in pagination
		$this->pagination['requested_page'] = $requestedPage;
		$this->pagination['offset'] = ($this->pagination['requested_page'] * $this->pagination['limit']) - $this->pagination['limit'];

		$this->album['pictures'] = array_slice($this->album['pictures'], $this->pagination['offset'], $this->pagination['limit']);

		// parse the pagination
		$this->parsePagination();

		$this->tpl->assign('item', $this->album);
		$this->header->setPageTitle($this->album['title']);
		$this->header->addOpenGraphData('title', $this->album['title']);
		$previewPicture = $this->album['pictures'][0];
		$this->header->addOpenGraphData('image', SITE_URL . $previewPicture['thumbnail_full_url']);
	}
}