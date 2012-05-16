<?php
    Class ATAddressRequest {
        private $serviceURL = '';
        private $licensekey;
        private $checksum;
        public $message;
        public $type;
        
        public function __construct( $url, $license, $checksum ) {
            $this->serviceURL = $url;
            $this->licensekey = $license;
            $this->checksum = $checksum;
        }
        
        public function getAddresses($address) {
            $xml = $this->genXML($address);
            $response = $this->sendRequest($xml);
            $this->response = $this->parseResponse($response);
            if ( $this->response !== false ) {
                return $this->response;
            } else {
                return false;
            }
        }
        
        private function parseResponse( $response ) {
            $AddressResponse = new SimpleXMLElement( $response );

            $this->type = (string)$AddressResponse->message->type;
            $this->message = (string)$AddressResponse->message->msg;
            if ( !isset( $AddressResponse->auth ) ) {
                return false;
            } else {
                $parsed = array();
                $parsed['left'] = (int)$AddressResponse->callsleft; 
                $parsed['addresses'] = array();
                foreach( $AddressResponse->addresses->address as $a ) {
                    $address['line1'] = (string)$a->addressline1;
                    $address['line2'] = (string)$a->addressline2;
                    $address['city'] = (string)$a->city;
                    $address['state'] = (string)$a->state;
                    $address['zipcode'] = (string)$a->zipcode;
                    $address['zip4'] = (string)$a->zip4;
                    array_push( $parsed['addresses'], $address );
                }
                return $parsed;
            }
        }
        
        private function genXML($address) {
         
            $xml = '<?xml version="1.0"?>';
            $xml .= '<AddressRequest>';
            $xml .= '<auth>';
            $xml .= '<licensekey>' .utf8_encode( $this->licensekey ) .'</licensekey>';
            $xml .= '<checksum>' .utf8_encode( $this->checksum ) .'</checksum>';
            $xml .= '</auth>';
            $xml .= '<address>';
            $xml .= ( !empty( $address['line1']) ? '<addressline1>' .utf8_encode( $address['line1'] ) .'</addressline1>' : '' );
            $xml .= ( !empty( $address['line2'] ) ? '<addressline2>' .utf8_encode( $address['line2'] ) .'</addressline2>' : '' );
            $xml .= ( !empty( $address['city'] ) ? '<city>' .utf8_encode( $address['city'] ) .'</city>' : '' );
            $xml .= ( !empty( $address['state'] ) ? '<state>' .utf8_encode( $address['state'] ) .'</state>' : '' );
            $xml .= ( !empty( $address['zip'] ) ? '<zipcode>' .utf8_encode( $address['zip'] ) .'</zipcode>' : '' );
            $xml .= '</address>';
            $xml .= '</AddressRequest>';
            return $xml;
        }
        
        private function sendRequest($xml) {
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
            curl_setopt($ch, CURLOPT_POSTFIELDS, rawurlencode($xml) );
       
            $res = curl_exec( $ch );
            return $res;
        }
    }
?>
