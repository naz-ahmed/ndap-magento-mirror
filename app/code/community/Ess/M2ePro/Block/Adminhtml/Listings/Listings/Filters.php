<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Listings_Filters extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingsListingsFilters');
        //------------------------------

        $this->setTemplate('M2ePro/listings/filters.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $maxRecordsQuantity = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/autocomplete/', 'max_records_quantity');
        //-------------------------------
        
        //-------------------------------
        $this->selectedSellingFormatTemplate = (int)$this->getRequest()->getParam('filter_selling_format_template');
        $sellingFormatTemplatesModel = Mage::getModel('M2ePro/SellingFormatTemplates');

        if ($sellingFormatTemplatesModel->getCollection()->getSize() < $maxRecordsQuantity) {
            $this->sellingFormatTemplatesDropDown = true;
            $tempData = $sellingFormatTemplatesModel->getCollection()->setOrder('title', 'ASC');
            $sellingFormatTemplates = array();

            foreach ($tempData as $item) {
                $sellingFormatTemplates[$item->getId()] = Mage::helper('M2ePro')->escapeHtml($item->getTitle());
            }
            $this->sellingFormatTemplates = $sellingFormatTemplates;
        } else {
            $this->sellingFormatTemplatesDropDown = false;
            $this->sellingFormatTemplates = array();

            if ($this->selectedSellingFormatTemplate > 0) {
                $this->selectedSellingFormatTemplateValue = $sellingFormatTemplatesModel->load($this->selectedSellingFormatTemplate)->getTitle();
            } else {
                $this->selectedSellingFormatTemplateValue = '';
            }
        }

        $this->sellingFormatTemplateUrl = $this->makeCutUrlForTemplate('filter_selling_format_template');
        //-------------------------------

        //-------------------------------
        $this->selectedDescriptionTemplate = (int)$this->getRequest()->getParam('filter_description_template');
        $descriptionTemplatesModel = Mage::getModel('M2ePro/DescriptionsTemplates');

        if ($descriptionTemplatesModel->getCollection()->getSize() < $maxRecordsQuantity) {
            $this->descriptionsTemplatesDropDown = true;
            $tempData = $descriptionTemplatesModel->getCollection()->setOrder('title', 'ASC');
            $descriptionsTemplates = array();

            foreach ($tempData as $item) {
                $descriptionsTemplates[$item->getId()] = Mage::helper('M2ePro')->escapeHtml($item->getTitle());
            }
            $this->descriptionsTemplates = $descriptionsTemplates;
        } else {
            $this->descriptionsTemplatesDropDown = false;
            $this->descriptionsTemplates = array();

            if ($this->selectedDescriptionTemplate > 0) {
                $this->selectedDescriptionTemplateValue = $descriptionTemplatesModel->load($this->selectedDescriptionTemplate)->getTitle();
            } else {
                $this->selectedDescriptionTemplateValue = '';
            }
        }

        $this->descriptionTemplateUrl = $this->makeCutUrlForTemplate('filter_description_template');
        //-------------------------------

        //-------------------------------
        $this->selectedListingTemplate = (int)$this->getRequest()->getParam('filter_listing_template');
        $listingTemplatesModel = Mage::getModel('M2ePro/ListingsTemplates');

        if ($listingTemplatesModel->getCollection()->getSize() < $maxRecordsQuantity) {
            $this->listingsTemplatesDropDown = true;
            $tempData = $listingTemplatesModel->getCollection()->setOrder('title', 'ASC');
            $listingsTemplates = array();

            foreach ($tempData as $item) {
                $listingsTemplates[$item->getId()] = Mage::helper('M2ePro')->escapeHtml($item->getTitle());
            }
            $this->listingsTemplates = $listingsTemplates;
        } else {
            $this->listingsTemplatesDropDown = false;
            $this->listingsTemplates = array();

            if ($this->selectedListingTemplate > 0) {
                $this->selectedListingTemplateValue = $listingTemplatesModel->load($this->selectedListingTemplate)->getTitle();
            } else {
                $this->selectedListingTemplateValue = '';
            }
        }

        $this->listingTemplateUrl = $this->makeCutUrlForTemplate('filter_listing_template');
        //-------------------------------

        //-------------------------------
        $this->selectedSynchronizationTemplate = (int)$this->getRequest()->getParam('filter_synchronization_template');
        $synchronizationTemplatesModel = Mage::getModel('M2ePro/SynchronizationsTemplates');

        if ($synchronizationTemplatesModel->getCollection()->getSize() < $maxRecordsQuantity) {
            $this->synchronizationsTemplatesDropDown = true;
            $tempData = $synchronizationTemplatesModel->getCollection()->setOrder('title', 'ASC');
            $synchronizationsTemplates = array();

            foreach ($tempData as $item) {
                $synchronizationsTemplates[$item->getId()] = Mage::helper('M2ePro')->escapeHtml($item->getTitle());
            }
            $this->synchronizationsTemplates = $synchronizationsTemplates;
        } else {
            $this->synchronizationsTemplatesDropDown = false;
            $this->synchronizationsTemplates = array();

            if ($this->selectedSynchronizationTemplate > 0) {
                $this->selectedSynchronizationTemplateValue = $synchronizationTemplatesModel->load($this->selectedSynchronizationTemplate)->getTitle();
            } else {
                $this->selectedSynchronizationTemplateValue = '';
            }
        }

        $this->synchronizationTemplateUrl = $this->makeCutUrlForTemplate('filter_synchronization_template');
        //-------------------------------
        
        return parent::_beforeToHtml();
    }

    protected function makeCutUrlForTemplate($templateUrlParamName)
    {
        $paramsFilters = array(
            'filter_selling_format_template',
            'filter_description_template',
            'filter_listing_template',
            'filter_synchronization_template'
        );

        $params = array();
        foreach ($paramsFilters as $value) {
            if ($value != $templateUrlParamName) {
                $params[$value] = $this->getRequest()->getParam($value);
            }
        }

        return $this->getUrl('*/*/*',$params);
    }
}