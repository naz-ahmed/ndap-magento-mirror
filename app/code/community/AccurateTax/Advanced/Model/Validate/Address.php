<?php
    class AccurateTax_Advanced_Model_Validate_Address extends AccurateTax_Advanced_Model_Abstract {
        
        protected $_magentoAddress = null;
        
        protected $_requestAddress = null;
        protected $_storeId = null;
        protected $_responseAddress = null;
        private $_cache = array();
        
        public function __construct() {
		    $addresses = Mage::getSingleton('advanced/session')->getAddresses();
		    if(is_array($addresses)) {
			    $this->_cache = $addresses;
		    }
		    parent::__construct();
	    }
	    
	    
	    public function __destruct() {
		    Mage::getSingleton('advanced/session')->setAddresses($this->_cache);
		
		    if(method_exists(get_parent_class(), '__destruct')) {
			    parent::__destruct();
		    }		
	    }
	    
	    public function setAddress (Mage_Customer_Model_Address_Abstract $address) {
		    $this->_storeId = $address->getQuote()->getStore()->getId();
		    $this->_magentoAddress = $address;
		    return $this->_ATSetupAddress();
	    }
	    
	    protected function _ATSetupAddress () {
		    if (!$this->_requestAddress) {
			    $this->_requestAddress = array();
		    }
		    $this->_requestAddress['line1'] = $this->_magentoAddress->getStreet(1);
		    $this->_requestAddress['line2'] = $this->_magentoAddress->getStreet(2);
		    $this->_requestAddress['city'] = $this->_magentoAddress->getCity();
		    $this->_requestAddress['state'] = $this->_magentoAddress->getRegionCode();

		    $this->_requestAddress['zip'] = $this->_magentoAddress->getPostcode();

		    return $this;
	    }
	    
	    protected function _ATSaveAddress () {
	        if ( count( $this->_responseAddress ) == 1 ) {
		        $region = Mage::getModel('directory/region')->loadByCode($this->_responseAddress['0']['state'], $this->_magentoAddress->getCountryId());

		        $this->_magentoAddress->setStreet(array( $this->_responseAddress['0']['line1'], $this->_responseAddress['0']['line2']))
				        ->setCity($this->_responseAddress['0']['city'])
				        ->setRegionId($region->getId())
				        ->setPostcode($this->_responseAddress['0']['zipcode'] .'-' .$this->_responseAddress['0']['zip4'])
				        ->setPlus4($this->responseAddress['0']['zip4'])
				        ->save()
				        ->setAddressNormalized(true);
            }
		    return $this;
	    }
	    
	    protected function _isValidCountry($countryCode) {
		    if ( $countryCode == 'US' ) {
		        return true;
		    }
            return false;
	    }
	    
	    function ValidateAddress($address) {
	        $country = $address->getCountry();
	        if ( $country == 'US' ) {    
	            $config = Mage::getSingleton('advanced/config');
	            if ( (boolean)$config->getAgree() === false ) {
	                return false;
	            }
	            if ( (boolean)$config->getOnlyScrubTaxable() === false ) {
	                return true;
	            }
	            $region = $address->getRegionId();
	            $postcode = $address->getPostcode();
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
                    if ( (int)strlen( $zip['1'] ) === 4 ) {
                        return false;
                    }
                    $postcode = $zip['0'];
                }
                $table = Mage::getSingleton('core/resource')->getTableName('tax_calculation_rate');
                $query1 = "SELECT * FROM " .$table ." WHERE tax_country_id='" .$country ."' and tax_region_id=" .$region ." AND tax_postcode='" .$postcode ."'";
                $result1 = $db->query( $query1 );
                $rows1 = $result1->fetchAll();
                if ( count( $rows1 ) > 0 ) {
                    if ( $rows1['0']['checkAT'] == '1' ) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    $query2 = "SELECT * FROM " .$table ." WHERE tax_country_id='" .$country ."' and tax_region_id=" .$region ." AND zip_is_range=1 AND '" .$postcode ."' BETWEEN zip_from AND zip_to";
                    $result2 = $db->query( $query2 );
                    $rows2 = $result2->fetchAll();
                    if ( count( $rows2 ) > 0 ) {
                        if ( $rows2['0']['checkAT'] == '1' ) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
	                    $query3 = "SELECT * FROM " .$table ." WHERE tax_country_id='" .$country ."' and tax_region_id=" .$region ." AND tax_postcode='*'";
	                    $result3 = $db->query( $query3 );
	                	$rows3 = $result3->fetchAll();
	                	if ( count( $rows3 ) > 0 ) {
	                	    if ( $rows3['0']['checkAT'] == '1' ) {
	                	        return true;
	                	    } else {
	                	        return false;
	                	    }
	                	} else {
	                	    return false;
	                	}
	                }
	            }
	        } else {
	            return false;
	        }
	    }
	    
	    
	    public function validate() {
	        $errors = array();
	        $config = Mage::getSingleton('advanced/config');
	        
	        if($this->_isValidCountry($this->_magentoAddress->getCountry()) == false) {
			    return true;
		    }
		    if (!$this->_magentoAddress) {
    		    $errors[] = $this->__('An address must be set before validation.');
    		    return $errors;
		    }
		    
		    if($this->_magentoAddress->getAddressNormalized()===true ) {
		        return true;
		    }
		    if ( $config->getOnlyScrubTaxable() == 1 ) {
		        if ( $this->validateAddress($this->_magentoAddress) === false ) {
		            return true;
		        }
		    }
		    
		    //check cache
		    $key = hash('md4', print_r($this->_requestAddress, true));
		    
		    
		    if (array_key_exists($key, $this->_cache)) {
			    $resp = unserialize($this->_cache[$key]);			
		    } else {
			    $at = new ATAddressRequest( $config->getURL(), $config->getLicenseKey(), $config->getChecksum() );
			    $resp = $at->getAddresses($this->_requestAddress);	
			    $this->_log($at, $resp, $this->_storeId);
			    if ( $resp !== false ) {
			        $this->_cache[$key] = serialize($resp);
			    }
		    }
		    if ( isset( $resp ) ) {
		        if ( $resp !== false ) {
		            if ( count( $resp['addresses'] ) > 0 ) {
		                $this->_responseAddress = $resp['addresses'];
		                $this->_ATSaveAddress();
                        $this->_magentoAddress->setAddressValidated(true);
                        return true;
		            } else {
		                $this->_magentoAddress->setATNoMatches(true);
		            }
		            if ( $config->getAllowInvalid == 1 ) {
		                return true;
		            } else {
		                return false;
		            }
		        } else {
		            if ( isset( $at ) ) {
		                if ( $at->message == 'No Addresses Found' ) {
		                    if ( $config->getAllowInvalid() == 1 ) {
		                        return true;
		                    } else {
                                $errors[] = $this->__($at->message);
                            }
		                } else {
		                    return true;
                        }
                    } else {
                        $errors[] = $this->__('Error encountered while validating address with AccurateTax');
                    }
                    return $errors;
		        }
		    }
	    }
    
    }
?>
