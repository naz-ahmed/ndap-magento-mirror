<?php

abstract class AccurateTax_Advanced_Model_Advanced_Abstract extends AccurateTax_Advanced_Model_Abstract {
	protected static $_error = false;
	protected $_request = null;
	
	protected function getATRequest($address) {
	    $country = $address->getCountry();
	    if ( $country == 'US' ) {    
	        $region = $address->getRegionId();
	        if ( $region == '' || is_null( $region ) ) {
	            return false;
	        }
	        $postcode = $address->getPostcode();
	        if ( $postcode == '' || is_null( $postcode ) ) {
	            return false;
	        }
	        $table = Mage::getSingleton('core/resource')->getTableName('tax_calculation_rate');
            $table2 = Mage::getSingleton('core/resource')->getTableName('tax_calculation');
            $groupid = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $customer_tax_class = Mage::getModel('customer/group')->getTaxClassId($groupid);
	        $obj = Mage::getConfig()->getResourceModel()->getReadConnection()->getConfig();
    	    $host = $obj["host"];
        	$username = $obj["username"];
        	$password = $obj["password"];
        	$dbname = $obj["dbname"];
        	$params = array ('host'     => $host,
        		'username' => $username,
        		'password' => $password,
        		'dbname'   => $dbname);
        	$db = Zend_Db::factory('PDO_MYSQL', $params);
            if ( stripos( $postcode, '-' ) !== false ) {
                $zip = explode( '-', $postcode );
                $postcode = $zip['0'];
            }
            $query1 = "SELECT * FROM $table a JOIN $table2 b on a.tax_calculation_rate_id=b.tax_calculation_rate_id AND customer_tax_class_id=" .(int)$customer_tax_class ." WHERE tax_country_id='" .$country ."' and tax_region_id='" .$region ."' AND tax_postcode='" .$postcode ."'";
            $result1 = $db->query( $query1 );
            $rows1 = $result1->fetchAll();
            if ( count( $rows1 ) > 0 ) {
                if ( $rows1['0']['checkAT'] == '1' ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $query2 = "SELECT * FROM $table a JOIN $table2 b on a.tax_calculation_rate_id=b.tax_calculation_rate_id AND customer_tax_class_id=" .(int)$customer_tax_class ." WHERE tax_country_id='" .$country ."' and tax_region_id='" .$region ."' AND zip_is_range=1 AND '" .$postcode ."' BETWEEN zip_from AND zip_to";
                $result2 = $db->query( $query2 );
                $rows2 = $result2->fetchAll();
                if ( count( $rows2 ) > 0 ) {
                    if ( $rows2['0']['checkAT'] == '1' ) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
	                $query3 = "SELECT * FROM $table a JOIN $table2 b on a.tax_calculation_rate_id=b.tax_calculation_rate_id AND customer_tax_class_id=" .(int)$customer_tax_class ."  WHERE tax_country_id='" .$country ."' and tax_region_id='" .$region ."' AND tax_postcode='*'";
	                $result3 = $db->query( $query3 );
	            	$rows3 = $result3->fetchAll();
	            	if ( count( $rows3 ) > 0 ) {
	            	    if ( $rows3['0']['checkAT'] == '1' ) {
	            	        return true;
	            	    } else {
	            	        return false;
	            	    }
	            	}
	            }
	        }
	    } else {
	        return false;
	    }
	}
	
	protected function _addGeneralInfo($object) {
		$storeId = $object->getStoreId();
		$this->_addCustomer($object);
	}
	
	protected function _addCustomer($object) {
		$customer = Mage::getModel('customer/customer')->load($object->getCustomerId());
		
		if($customer->getId()) {
			$taxClass = Mage::getModel('tax/class')->load($customer->getTaxClassId())->getTaxClass();
		} else {
			$address = $object->getBillingAddress();
			$name = $address->getFirstname() . ' ' . $address->getLastname() . ' (Guest)';
		}
	}
	
	protected function _setOriginAddress($store=null) {		
		$country = Mage::getStoreConfig('shipping/origin/country_id', $store);
		$zip = Mage::getStoreConfig('shipping/origin/postcode', $store);
		$regionId = Mage::getStoreConfig('shipping/origin/region_id', $store);
		$state = Mage::getModel('directory/region')->load($regionId)->getCode();
		$city = Mage::getStoreConfig('shipping/origin/city', $store);
		$street = Mage::getStoreConfig('shipping/origin/street', $store);
		$address = $this->_newAddress($street, '', $city, $state, $zip, $country);
		//$this->_request->setOrigAddress($address);
		return $address;
	}
	
	protected function _setDestinationAddress($address) {
		$street = $address->getStreet();
		$street1 = isset($street[0]) ? $street[0] : null;
		$street2 = isset($street[1]) ? $street[1] : null;
		$city = $address->getCity();
		$zip = $address->getPostcode();
		$state = Mage::getModel('directory/region')->load($address->getRegionId())->getCode(); 
		$country = $address->getCountry();
		 
		if(($city && $state) || $zip) {
			$address = $this->_newAddress($street1, $street2, $city, $state, $zip, $country);
			return $this->_request->setShippingAddress($address);
		} else {
			return false;
		}
	}
	
	protected function _newAddress($line1, $line2, $city, $state, $zip, $country='USA') {
		$address = array(
		    'line1'=>$line1,
		    'line2'=>$line2,
		    'city'=>$city,
		    'state'=>$state,
		    'zip'=>$zip,
		    'country'=>$country
		);
        return $address;
	}
	
	protected function _isProductCalculated($item) {
		try {
			if($item->isChildrenCalculated() && !$item->getParentItem()) {
				return true;
			}
			if(!$item->isChildrenCalculated() && $item->getParentItem()) {
				return true;
			}
		} catch(Exception $e) { }
		return false;
	}
	
}
