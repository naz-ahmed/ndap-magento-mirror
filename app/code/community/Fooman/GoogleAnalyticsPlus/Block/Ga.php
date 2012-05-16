<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mage
 * @package     Mage_GoogleAnalytics
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Analytics block
 *
 * @category   Fooman
 * @package    Fooman_GoogleAnalyticsPlus
 * @author     Magento Core Team <core@magentocommerce.com>
 * @author     Fooman, Kristof Ringleff <kristof@fooman.co.nz>
 */
class  Fooman_GoogleAnalyticsPlus_Block_Ga extends Mage_GoogleAnalytics_Block_Ga
{

    /**
     * Return REQUEST_URI for current page
     * Magento default analytics reports can include the same page as
     * /checkout/onepage/index/ and   /checkout/onepage/
     * filter out index/ here
     *
     * @return string
     */
    public function getPageName() {
        if (!$this->hasData('page_name')) {
            $pageName = Mage::getSingleton('core/url')->escape($_SERVER['REQUEST_URI']);
            $pageName = rtrim(str_replace('index/','',$pageName), '/'); 
            $this->setPageName($pageName);
        }
        return $this->getData('page_name');
    }
   
    /**
     * Prepare and return block's html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $secure = Mage::app()->getStore()->isCurrentlySecure() ? 'true' : 'false';
        $handles = $this->getLayout()->getUpdate()->getHandles();
        
        if (in_array('checkout_onepage_success', $handles) || in_array('checkout_multishipping_success', $handles))
            $success = true;
        else {
            $success = false;
        }
        
        if (version_compare(Mage::getVersion(), '1.4.1.1')  > 0 && version_compare(Mage::getVersion(), '1.7.0.0')  < 0) {
            //Mage 1.4.2 +
            $new = true;
            if (!Mage::helper('googleanalytics')->isGoogleAnalyticsAvailable()) {
                return '';
            }
            $accountId = Mage::getStoreConfig(Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACCOUNT);
        } else {
            //Mage Enterprise, Mage 1.4.1.1 and below
            $new = false;
            if (!Mage::getStoreConfigFlag('google/analytics/active')) {
                return '';
            }
            $accountId = $this->getAccount();
        }
        $accountId2 = Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('accountnumber2');
        
        $html = '
<!-- BEGIN GOOGLE ANALYTICS CODE -->
<script type="text/javascript">
//<![CDATA[
            (function() {
                var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;';
                if ($secure == 'true') {
                    $html .= 'ga.src = \'https://ssl.google-analytics.com/ga.js\';';
                } else {
                    $html .= 'ga.src = \'http://www.google-analytics.com/ga.js\';';
                }
                $html .= '
                var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
            })();
            var _gaq = _gaq || [];
'
  . $this->_getPageTrackingCode($accountId,$accountId2)
  . ($new?$this->_getOrdersTrackingCode($accountId2):'')
  . $this->_getAjaxPageTracking($accountId2) . '
//]]>
</script>
'
 . ($new?'':$this->_getQuoteOrdersHtml($accountId2))
 . ($success?$this->_getCustomerVars($accountId2):'').'
<!-- END GOOGLE ANALYTICS CODE -->
';
        return $html;
    }

    /**
     *  Transaction on Mage 1.4.1.1 and below
     *  duplicate for secondary tracker
     */
    protected function _getQuoteOrdersHtml ($accountId2 = false)
    {
        $html = "\n".parent::getQuoteOrdersHtml();
        if ($accountId2) {
            $html .= str_replace('_gaq.push(["_', '_gaq.push(["t2._', $html);
        }
        return $html;
    }

    /**
     *  Transaction on Mage 1.4.2 +
     *  duplicate for secondary tracker
     */
    protected function _getOrdersTrackingCode ($accountId2 = false)
    {
        $html = "\n".parent::_getOrdersTrackingCode();
        if ($accountId2) {
            $html .= str_replace('_gaq.push([\'_', '_gaq.push([\'t2._', $html);
        }
        return $html;
    }

    protected function _getCustomerVars ($accountId2 = false)
    {
        //TODO: check if we hit the 5 custom var maximum when using with first touch tracking
        //set customer variable for the current session c=1
        //set returning customer variable onwards for this visitor rc=1
        return '
<script type="text/javascript">
//<![CDATA[
    _gaq.push(["_setCustomVar", 5, "c", "1", 2]);
    _gaq.push(["_setCustomVar", 5, "rc", "1", 1]);
    '.($accountId2?'
    _gaq.push(["t2._setCustomVar", 5, "c", "1", 2]);
    _gaq.push(["t2._setCustomVar", 5, "rc", "1", 1]);
':'').'
//]]>
</script>
';
    }


    protected function _getPageTrackingCode ($accountId, $accountId2 = false)
    {
        //url to track
        $optPageURL = trim($this->getPageName());
        if ($optPageURL && preg_match('/^\/.*/i', $optPageURL)) {
            $optPageURL = "{$this->jsQuoteEscape($optPageURL)}";
        }

        //main profile tracking including optional first touch tracking
        $html = '
            _gaq.push(["_setAccount", "' . $this->jsQuoteEscape($accountId) . '"]';
        if ($domainName = Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('domainname')) {
            $html .=' ,["_setDomainName","' . $domainName . '"]';
        }
        if($anonymise = Mage::getStoreConfigFlag('google/analyticsplus/anonymise')) {
            $html .=', ["_gat._anonymizeIp"]';
        }
        if(Mage::getStoreConfigFlag('google/analyticsplus/firstouch')) {
            $html .=');
            asyncDistilledFirstTouch(_gaq);
            _gaq.push(["_trackPageview","' . $optPageURL . '"]';
        } else {
            $html .=', ["_trackPageview","' . $optPageURL . '"]';
        }

        if(Mage::getStoreConfigFlag('google/analyticsplus/trackpageloadtime')) {
            $html .=', ["_trackPageLoadTime"]';
        }        
        $html .=');';
        
        //track to alternative profile (optional)
        if($accountId2){
            $html .= '
            _gaq.push(["t2._setAccount", "' . $this->jsQuoteEscape($accountId2) . '"]';
            if ($domainName2 = Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('domainname2')) {
                $html .=' ,["t2._setDomainName","' . $domainName2 . '"]';
            }
            if($anonymise){
                //anonymise requires the synchronous tracker object so likely not needed on this one
                //$html .=', ["t2._anonymizeIp"]';
            }
            $html .=', ["t2._trackPageview","' . $optPageURL . '"]';
            
            if(Mage::getStoreConfigFlag('google/analyticsplus/trackpageloadtime')) {
                $html .=', ["_trackPageLoadTime"]';
            }        
            $html .=');';            
        }

        return $html;
    }

    /**
     * return code to track AJAX requests
     *
     * @param int $accountId2
     * @return string
     */
    private function _getAjaxPageTracking($accountId2 = false) {
    return '

            if(Ajax.Responders){
                Ajax.Responders.register({
                  onComplete: function(response){
                    if(!response.url.include("progress")){
                        if(response.url.include("saveOrder")){
                            _gaq.push(["_trackPageview", "'.$this->getPageName().'"+ "/opc-review-placeOrderClicked"]);'
                            .($accountId2?'
                            _gaq.push(["t2._trackPageview", "'.$this->getPageName().'"+ "/opc-review-placeOrderClicked"]);':'').'
                        }else if(accordion.currentSection){
                            _gaq.push(["_trackPageview", "'.$this->getPageName().'/"+ accordion.currentSection]);'
                            .($accountId2?'
                            _gaq.push(["t2._trackPageview", "'.$this->getPageName().'/"+ accordion.currentSection]);':'').'
                        }
                    }
                  }
                });
            }
';
    }

}
