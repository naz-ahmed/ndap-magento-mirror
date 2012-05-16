<?php
    Class ATTaxRequest {
        private $serviceURL;
        private $shippingAddress = array();
        private $licensekey;
        private $checksum;
        private $shippingTaxClass = '';
        public $taxRate = 0.00;
        public $error = false;
        public $errors = array();
        public $taxshippinghandling = false;
        public $taxshippingalone = false;
        private $sendClientInfo = false;
        
        public function __construct( $url, $license, $checksum ) {
            $this->serviceURL = $url;
            $this->licensekey = $license;
            $this->checksum = $checksum;
        }
        
        public function setShippingAddress( array $address ) {
            $this->shippingAddress = $address;
            return true;
        }
        
        public function getShippingAddress() {
            return $this->shippingAddress;
        }
        
        public function setSendClientInfo($sendClientInfo=false) {
            $this->sendClientInfo = $sendClientInfo;
        }
        
        
        public function sendTaxRequest() {
            $xml = $this->genXML();
            if ( $xml !== true ) {
                $ch = curl_init( $this->serviceURL );
                if ( empty( $_SERVER['HTTPS'] ) ) {
                    $protocol = 'http';
                } else {
                    if ( $_SERVER['HTTPS'] == 'off' ) {
                        $protocol = 'http';
                    } else {
                        $protocol = 'https';
                    }
                }
                $site = $protocol .'://' .$_SERVER['SERVER_NAME'];
                curl_setopt($ch, CURLOPT_REFERER,  $site);
                curl_setopt($ch, CURLOPT_URL, $this->serviceURL );
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                curl_setopt($ch, CURLOPT_POSTFIELDS, array('data'=>rawurlencode($xml)) );
           
                $res = trim( curl_exec( $ch ) );
                $parsed = $this->parseResponse( $res );
                return $parsed;
            }
        }
        
        private function parseResponse( $xml ) {
            try {
                $TaxResponse = new SimpleXMLElement( trim($xml) );
            } catch( Exception $e ) {
                $this->error = true;
                array_push( $this->errors, $e->getMessage() );
                return false;
            }
            if ( isset( $TaxResponse->errors ) ) {
                $this->error = true;
                foreach( $TaxResponse->errors->error as $e ) {
                    array_push( $this->errors, $e );
                }
            } else {
                if ( isset( $TaxResponse->taxes->tax ) ) {
                    $this->taxRate = (float)$TaxResponse->taxes->tax->rate;
                    
                    $taxShipHand = (string)$TaxResponse->taxes->tax->taxshippinghandling;
                    $taxShipAlone = (string)$TaxResponse->taxes->tax->taxshippingalone;
                    $taxShipHand = trim( $taxShipHand );
                    $taxShipAlone = trim( $taxShipAlone );
                    
                    $this->taxshippinghandling = ($taxShipHand == 'Y' ? true: false );
                    $this->taxshippingalone = ($taxShipAlone == 'Y' ? true : false );
                    return $this->taxRate;
                } else {
                    return false;
                }
            }
            return $this;
        }
        
        public function genXML() {
            if ( isset( $this->shippingAddress['zip'] ) ) {
                if ( stripos( $this->shippingAddress['zip'], '-' ) !== false ) {
                    $zip9 = explode( '-', $this->shippingAddress['zip'] );
                    $zip = trim( $zip9['0'] );
                    $plus4 = trim( $zip9['1'] );
                } else {
                    $fullzip = trim( $this->shippingAddress['zip'] );
                    if ( strlen( $fullzip )  > 5 ) {
                        $zip = substr( $fullzip, 0, 5 );
                        $plus4 = substr( $fullzip, 5, 4 );
                    } else {
                        $zip = $fullzip;
                    }
                }
            } else {
                return true;
            }
            $xml = '<?xml version="1.0"?>';
            $xml .= '<taxrequest>';
            $xml .= '<auth><licensekey>' .$this->licensekey .'</licensekey><checksum>' .$this->checksum .'</checksum></auth>';
            $xml .= '<address>';
            $xml .= '<zipcode>' .$zip .'</zipcode>';
            if ( isset( $plus4 ) ) {
                $xml .= '<zip4>' .$plus4 .'</zip4>';
            }
            $xml .= '</address>';
            if ( $this->sendClientInfo === true ) {
                $xml .= '<clientinfo><![CDATA[ ' ."\n"
                    .'Referer: ' .$_SERVER['HTTP_REFERER'] ."\n"
                    .'User-Agent: ' .$_SERVER['HTTP_USER_AGENT'] ."\n"
                    .'Remote IP: ' .$_SERVER['REMOTE_ADDR'] ."\n"
                .' ]]></clientinfo>';
            }
            $xml .= '</taxrequest>';
            return $xml;
        }
        
        function SSTP_Format( $str ) {
            return substr($str, 0, strpos($str, '.') + 3 + 1);
        }
    }  
?>
