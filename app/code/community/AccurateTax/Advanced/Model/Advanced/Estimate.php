<?php
class AccurateTax_Advanced_Model_Advanced_Estimate extends AccurateTax_Advanced_Model_Advanced_Abstract {
	protected $subTotal = 0.00;
	protected $_rates = array();
	protected $_rateInfo = array();
	const CACHE_TIME = 90;

	public function __construct() {
		$rates = Mage::getSingleton('advanced/session')->getRates();
		$rateInfo = Mage::getSingleton('advanced/session')->getRateInfo();
		if(is_array($rates)) {
			foreach($rates as $key=>$rate) {
				if($rate['timestamp'] < strtotime('-' . self::CACHE_TIME . ' minutes')) {
					unset($rates[$key]);
				}
			}
			$this->_rates = $rates;
		}
		if (is_array($rateInfo ) ) {
		    foreach($rateInfo as $key=>$info ) {
		        if($info['timestamp'] < strtotime('-' . self::CACHE_TIME . ' minutes')) {
					unset($rateInfo[$key]);
				}
		    }
		    $this->_rateInfo = $rateInfo;
		}

		if(method_exists(get_parent_class(), '__construct')) {
			parent::__construct();
		}
	}

	public function __destruct() {
		Mage::getSingleton('advanced/session')->setRates($this->_rates);
		Mage::getSingleton('advanced/session')->setRateInfo($this->_rateInfo);

		if(method_exists(get_parent_class(), '__destruct')) {
			parent::__destruct();
		}

	}
	 
	public function getItemRate($item) {
		if($this->_isProductCalculated($item)) {
			return 0;
		} else {
			$cacheKey = $this->_getRates($item);
			return array_key_exists($cacheKey, $this->_rates) ? $this->_rates[$cacheKey]['rate'] : 0;
		}
	}
	 
	public function getItemTax($item) {
		if($this->_isProductCalculated($item)) {
			$tax = 0;
			foreach($item->getChildren() as $child) {
				$tax += $this->getItemTax($child);
			}
			return $tax;
		} else {
			$cacheKey = $this->_getRates($item);
			return array_key_exists($cacheKey, $this->_rates) ? $this->_rates[$cacheKey]['tax'] : 0;
		}
	}
	
	public function getItemTaxShipping($item, $part) {
	    if($this->_isProductCalculated($item)) {
			foreach($item->getChildren() as $child) {
				return $this->getItemTaxShipping($child, $part);
			}
		} else {
			$cacheKey = $this->_getRates($item);
			if ( $part == 'alone' ) {
			    return array_key_exists($cacheKey, $this->_rates) ? $this->_rates[$cacheKey]['taxshippinghandling'] : false;
			} else if ( $part == 'handling' ) {
    			return array_key_exists($cacheKey, $this->_rates) ? $this->_rates[$cacheKey]['taxshippingalone'] : false;
            }
		}
	}
	
	public function getZipRateInfo($zip) {
	    $cacheKey = $this->_genRequestKey($zip);
        return (array_key_exists( $cacheKey, $this->_rateInfo ) ? $this->_rateInfo[$cacheKey] :  false);
	}
	 
	public function collectTotals($quote) {
		$storeId = Mage::app()->getStore()->getId();
        $shipping = $quote->getShippingAddress();
        if ( $this->getATRequest($shipping) ) {
	        if($quote->hasItems()) {
		        $items = $quote->getAllItems();
		        $unsetTax = 0;
		        $totalTax = 0;
		        
		        foreach($items as $item) {
			        $amount = $this->getItemTax($item);
			        $percent = $this->getItemRate($item);
			        if(!$this->_isProductCalculated($item)) {
				        $unsetTax += $item->getTaxAmount();
				        $totalTax += $amount;
			        }
			        if($amount) {
				        $item->setTaxAmount($amount);
				        $item->setBaseTaxAmount($amount);
				        $item->setTaxPercent($percent);
			        }
		        }
		        if($unsetTax) {
			        $quote->setGrandTotal($quote->getGrandTotal() - $unsetTax);
			        $quote->setBaseGrandTotal($quote->getBaseGrandTotal() - $unsetTax);
		        }
		         
		        if($totalTax) {
			        $quote->setGrandTotal($quote->getGrandTotal() + $totalTax);
			        $quote->setBaseGrandTotal($quote->getBaseGrandTotal() + $totalTax);
		        }
		         
		        foreach ($quote->getAddressesCollection() as $address) {
			        if($address->hasItems()) {
				        $items = $address->getAllItems();
				        $totalTax = 0;
				        $taxableSubTotal = 0.00;
				        $ATRateInfo = $this->getZipRateInfo($address->getPostcode());
				        foreach($items as $item) {
					        $amount = $this->getItemTax($item);
					        $percent = $this->getItemRate($item);
					        if($amount) {
    					        if(!$this->_isProductCalculated($item)) {
    					            $price = ($item->getCustomPrice()!==null) ? $item->getCustomPrice() : $item->getPrice();
	            	                $qty = $item->getQty();
            		                $price_ext = ( $price * $qty ) - $item->getDiscountAmount();
            		                $taxableSubTotal += $price_ext;
            		            }
						        $item->setTaxAmount($amount);
						        $item->setBaseTaxAmount($amount);
						        $item->setTaxPercent($percent);
						        $totalTax += $amount;
					        }
				        }
				        $shipping_amount = $quote->getShippingPrice();
		                $shippingTaxClass = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $storeId);

                        $shippingTax      = 0;
                        $shippingBaseTax  = 0;
                        $taxable_shipping = 0.00;
                        $shippingTax      = 0.00;
                        $shippingBaseTax  = 0.00;

                        if ($shippingTaxClass) {
                            if ( $ATRateInfo['taxshippinghandling'] === true ) {
                                $TaxShipping = true;
                            } else {
                                $TaxShipping = false;
                            }
                            $this->setATTaxShipping( $TaxShipping );
                            if ( $ATRateInfo['taxshippingalone'] === true ) {
                                $TaxOnlyShipping = true;
                            } else {
                                $TaxOnlyShipping = false;                        
                            }
                            $this->setATTaxOnlyShipping( $TaxOnlyShipping );
                            $carriers = array();
                            $carriers = Mage::getStoreConfig('carriers');
                            $handlingFee = 0.00;
                            foreach($carriers as $carrier){
                                $tmp = array_keys($carrier);
                                if(in_array("title",$tmp) && in_array("handling_fee",$tmp)){				                            
                                    $shippingDetails =  Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod();
		                            $shipCar = explode( '_', $shippingDetails );
                                    $curMeth = strtolower( str_replace( ' ', '', $carrier['title'] ) );
                                    if(trim($carrier['handling_fee'])!="" && $carrier['handling_fee'] > 0 && ( $shipCar['0'] == $curMeth ) ){
                                        $handlingFee = doubleval($carrier['handling_fee']);		
                                        break;
                                    }
                                }
                            }
                            
                            if ( $handlingFee > 0 ) {
                                if ( $TaxShipping ) {
                                    if ( $address->getTaxAmount() > 0 ) {
                                        $taxable_shipping = $address->getShippingAmount(); 
                                        $shippingTax = $address->getShippingAmount() * $ATRateInfo['rate'];
                                        $shippingBaseTax = $address->getBaseShippingAmount() *  $ATRateInfo['rate'];
                                    }
                                }
                            } else {
                                if ( $TaxOnlyShipping ) {
                                    if ( $address->getTaxAmount() > 0 ) {
                                        $taxable_shipping = $address->getShippingAmount();
                                        $shippingTax    = $address->getShippingAmount() * $ATRateInfo['rate'];
                                        $shippingBaseTax= $address->getBaseShippingAmount() * $ATRateInfo['rate'];
                                    }
                                }
                            }
                        }
                        $address->setTaxAmount( $address->getTaxAmount() + $shippingTax);
                        $address->setBaseTaxAmount( $address->getBaseTaxAmount() + $shippingBaseTax);
				        $totalTax = round( (($taxableSubTotal + $taxable_shipping) * $ATRateInfo['rate']), 2);

				        $unsetTax = $address->getTaxAmount();
				        if($unsetTax) {
					        $address->setSubtotalInclTax($address->getSubtotal() - $unsetTax);
					        $address->setBaseSubtotalInclTax($address->getBaseSubtotal() - $unsetTax);
					        $address->setGrandTotal($address->getGrandTotal() - $unsetTax);
					        $address->setBaseGrandTotal($address->getBaseGrandTotal() - $unsetTax);
				        }
				        if(isset($totalTax)) {
					        // Update the applied taxes so that the "sales_order_tax" table gets the correct values
					        // and payments apply the correct tax 
					        // (magento tax reports may work correctly after this, but rate column cannot be trusted)
					        $applied = unserialize( $address->getData('applied_taxes') );
					        $atapplied = $applied;
					        $a_key = key( $applied );
					        $atapplied[$a_key]['percent'] = ($ATRateInfo['rate'] * 100 );
					        $atapplied[$a_key]['amount'] = $totalTax;
					        $atapplied[$a_key]['base_amount'] = $totalTax;
                            $atapplied[$a_key]['rates']['0']['percent'] = ($ATRateInfo['rate'] * 100 );
                            $atapplied[$a_key]['rates']['percent'] = ($ATRateInfo['rate'] * 100 );
                            $atapplied[$a_key]['rates']['amount']  = $totalTax;
                            $atapplied[$a_key]['rates']['base_amount'] = $totalTax;
                            $address->setAppliedTaxes($atapplied);


                            // Update all other tax amounts on the address iteself
					        $shipping_tax = round( ($taxable_shipping * $ATRateInfo['rate'] ), 2 );
					        $address->setShippingTaxAmount($shipping_tax);
					        $address->setBaseShippingTaxAmount($shipping_tax);
					        $address->setTaxAmount($totalTax);
					        $address->setBaseTaxAmount($totalTax);
					        $address->setSubtotalInclTax($address->getSubtotal() + $totalTax);
					        $address->setBaseSubtotalInclTax($address->getBaseSubtotal() + $totalTax);
					        $address->setGrandTotal(( $address->getGrandTotal() + $shippingTax ) + $totalTax);
					        $address->setBaseGrandTotal(( $address->getBaseGrandTotal() + $shippingBaseTax ) + $totalTax);
				        }
			        }
		        }	     
	        }
	    }
	}
	 
	protected function _getRates($item) {
		if(self::$_error) {
			return 'error';
		}

		$quote = $item->getQuote();
		$config = Mage::getSingleton('advanced/config');
		$this->_request = new ATTaxRequest( $config->getURL(), $config->getLicenseKey(), $config->getChecksum() );
		if ( $config->getSendClientInfo() == "1" || $config->getSendClientInfo() === true ) {
		    $this->_request->setSendClientInfo(true);
		}
		$this->_setOriginAddress($quote->getStoreId());
		$shipping = ($quote->getShippingAddress()->getPostcode()) ? $quote->getShippingAddress() : $quote->getBillingAddress();
		$this->_setDestinationAddress($shipping);
		$this->_addCustomer($quote);
//		$this->_request->setSubTotal( $this->subTotal );

        $reqShipAddr = $this->_request->getShippingAddress();

		$requestKey = $this->_genRequestKey($reqShipAddr['zip']);
		$cacheKey = $this->_genCacheKey($item->getProductId(), $requestKey);
		
		$isCached = isset($this->_rates[$cacheKey]);
//		$isCached &= count($this->ATRates[$cacheKey]) > 0 ? true : false;
//		$isCached &= (isset($reqShipAddr['zip'] ) ? false : true );

		if ( $isCached === false ) {
			$result = $this->getATRates($quote->getStoreId());
			if ( $result === false ) {
				$quote->setHasError(true);
				Mage::helper('advanced')->addErrorMessage($quote->getStoreId(),$this->_request->errors);
			} else {
				$items = $quote->getAllItems();
		        $unsetTax = 0;
		        $totalTax = 0;
		        $key = '';
		        foreach($items as $linenum=>$item) {
			        $key = $this->_genCacheKey($item->getProductId(), $requestKey);
					if($this->_isProductCalculated($item)) {
			            $price = 0;
		            } else {
			            $price = ($item->getCustomPrice()!==null) ? $item->getCustomPrice() : $item->getPrice();
//			            $price = ($price - $item->getDiscountAmount());
		            }
		            $mag_tax_amount = $item->getTaxAmount();
		            if ( $mag_tax_amount > 0 ) {
		                //$price = $price - $item->getDiscountAmount();
		                $qty = $item->getQty();
		                $price_ext = ( $price * $qty ) - $item->getDiscountAmount();
		                $calc_tax_amount = ($price_ext * $result);
		                $calc_rate = $result * 100;
		            } else {
		                $calc_tax_amount = 0.00;
		                $calc_rate = 0;
		            }
		            $cal_tax_amount = round( $calc_tax_amount, 2);
					$this->_rates[$key] = array(
                			'rate' =>$calc_rate,
                			'tax' =>$calc_tax_amount,
                			'timestamp' =>time()
					);
					$this->_rateInfo[$requestKey] = array(
					    'rate'=>$result,
					    'taxshippinghandling'=>$this->_request->taxshippinghandling,
					    'taxshippingalone'=>$this->_request->taxshippingalone,
					    'zip'=>$reqShipAddr['zip'],
					    'timestamp' =>time()
					);
		        }
			}
		}
		return $cacheKey;
	}
	 
	protected function _genRequestKey($zipcode) {
		return hash('md4', $zipcode);
	}
	 
	protected function _genCacheKey($productId, $requestKey) {
		return hash('md4', $productId . ':' . $requestKey);
	}
	 
	function getATRates($storeid) {
		return $this->_request->sendTaxRequest();
	}
}
