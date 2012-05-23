<?php

	protected function _toHtml () {
		$additional = '';
		if ($this->getAddress()->getAddressNormalized()) {
			$notice = 'Your shipping address has been modified during our validation process.  Please confirm the address to the right is accurate.';
			if ($notice) {
				Mage::getSingleton('advanced/session')->addNotice($notice);
				$additional = $this->getMessagesBlock()->getGroupedHtml();
			}
		} else if ( $this->getAddress()->getATNoMatches()) {
		    $notice = 'We were not able to find a match while trying to validate your shipping address.  Please double check your shipping address to the right.';
			if ($notice) {
				Mage::getSingleton('advanced/session')->addNotice($notice);
				$additional = $this->getMessagesBlock()->getGroupedHtml();
			}
		}
		return $additional . parent::_toHtml();
	}
}