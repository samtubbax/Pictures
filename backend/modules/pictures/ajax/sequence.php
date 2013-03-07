<?php

/**
 * BackendPicturesAjaxSequence
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendPicturesAjaxSequence extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		// get parameters
		$newIdSequence = trim(SpoonFilter::getPostValue('new_id_sequence', null, '', 'string'));

		// list id
		$ids = (array) explode(',', rtrim($newIdSequence, ','));

		// loop id's and set new sequence
		foreach($ids as $i => $id)
		{
			// build item
			$item['id'] = (int) $id;

			// change sequence
			$item['sequence'] = $i + 1;

			// update sequence
			BackendPicturesModel::updateAlbum($item);
		}

		// success output
		$this->output(self::OK, null, 'sequence updated');
	}
}