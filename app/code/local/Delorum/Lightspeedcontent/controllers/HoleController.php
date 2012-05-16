<?php

class Delorum_Lightspeedcontent_HoleController extends Mage_Core_Controller_Front_Action 
{
	public function IndexAction() 
	{
		//Use me first. Once I work, try using the one on line 22-25
		$content = array(
				//'vafgarage' => 'Hello World!'
				'vafgarage' => $vehicle
					 		);
		
		//Needed when enabling 22-25
		//$this->loadLayout();
		
		//Helps show what block names are being loaded (Might not be needed in this example)
		// foreach($this->getLayout()->getAllBlocks() as $key => $block){
		// 		echo $key;
		// 		echo "<br />";
		// 	}
		
		//Fills hole with real content by calling specific blocks by name
		// $content = array(
		// 				'topMenuLinks' => $this->getLayout()->getBlock('top.links')->toHtml()
		// 		 	);
		
		echo Zend_Json::encode($content);
	}
}