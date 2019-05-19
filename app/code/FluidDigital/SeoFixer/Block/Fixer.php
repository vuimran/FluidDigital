<?php

namespace FluidDigital\SeoFixer\Block;


use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\VariableFactory;
use Magento\Store\Model\StoreRepository;


class Fixer extends Template
{

    protected $_pageFactory;
    protected $_page;
    protected $_storeManager;
    protected $_varFactory;
    protected $_urlInterface;
    protected $_storeRepository;

    public function __construct(Context $context,
                                PageFactory $pageFactory,
                                Page $page,
                                StoreManagerInterface $storeManager,
                                VariableFactory $varFactory,
                                UrlInterface $urlInterface,
                                StoreRepository $storeRepository
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_page = $page;
        $this->_storeManager = $storeManager;
        $this->_varFactory = $varFactory;
        $this->_urlInterface = $urlInterface;
        $this->_storeRepository = $storeRepository;
        parent::__construct($context);
    }


    public function loadCMSPage()
    {

        $pageData = $this->_page->getData();


            // process only if page is showing on multiple stores.
        if (count($pageData['store_id']) > 1) {
            foreach ($pageData['store_id'] as $storeId) {

                //check current store in the list
                //and populate link tag
                if ($storeId == $this->getStoreId()) {
                    $store_language = $this->getVariableValue('store_language', $storeId);

                    if (!empty($store_language)) {// store_language must be set to
                        $link_tag = '';
                        $link_tag .= '<link rel="alternate" hreflang="';
                        $link_tag .= $store_language;
                        $link_tag .= '" href="' . $this->getStoreBaseUrl() . $pageData['identifier'] . '" />';
                        echo $link_tag;

                    }


                }

            }


        } else {
            // if all stores option is selected
            if($pageData['store_id'][0] == 0 ){
                $stores  = $this->_storeRepository->getList();
                foreach ($stores as $store) {

                    //check current store in the list
                    //and populate link tag
                    if ($store->getStoreId() == $this->getStoreId()) {
                        $store_language = $this->getVariableValue('store_language', $this->getStoreId());

                        if (!empty($store_language)) {
                            $link_tag = '';
                            $link_tag .= '<link rel="alternate" hreflang="';
                            $link_tag .= $store_language;
                            $link_tag .= '" href="' . $this->getStoreBaseUrl() . $pageData['identifier'] . '" />';
                            echo $link_tag;

                        }


                    }

                }


            }





        }

    }


    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function getVariableValue($variable, $store_id = null)
    {
        $var = $this->_varFactory->create();

        if ($store_id != null) {
            $var->setStoreId($store_id);
        }
        $var->loadByCode($variable);

        return $var->getValue('text');
    }


    public function getStoreBaseUrl()
    {

        return $this->_storeManager->getStore()->getBaseUrl();
    }

    //getCurrentPageUrl() can be used instead of using concatenation of get getStoreBaseUrl() and pageIdentifier
    public function getCurrentPageUrl()
    {

        return $this->_urlInterface->getCurrentUrl();

    }
}