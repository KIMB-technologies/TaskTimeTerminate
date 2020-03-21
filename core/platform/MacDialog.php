<?php

class MacDialog extends Dialog {
	
	public function open() : void {

		/**
		 * ToDo
		 */

		$d = new InTerminalDialog();
		$d->setCategories($this->categories);
		$d->open();
		$this->chCategory = $d->getChosenCategory();	
		$this->chName = $d->getChosenName();
		$this->chTime = $d->getChosenTime();

	}

	public static function checkOSPackages() : void {
	}
}

?>