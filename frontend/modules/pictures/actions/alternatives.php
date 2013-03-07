<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the overview-action
 *
 * @author Tijs Verkoyen <tijs@sumocoders.be>
 * @author Davy Hellemans <davy.hellemans@netlash.com>
 */
class FrontendPicturesAlternatives extends FrontendBaseBlock
{
	/**
	 * Execute the extra
	 */
	public function execute()
	{
		parent::execute();
		$this->loadTemplate();
		$this->parse();
	}
	/**
	 * Parse the data into the template
	 */
	private function parse()
	{
		// assign articles
		$this->tpl->assign('items', FrontendPicturesModel::getAlternatives());
	}
}
