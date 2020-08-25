<?php
namespace Scandiweb\Multilang\ViewModel;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;


class MetaLangs implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

	protected $_storeManager;
	protected $_page;
	protected $_scopeConfig;

	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Cms\Model\Page $page,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	)
	{
		$this->_page = $page;		
		$this->_storeManager = $storeManager;
		$this->_scopeConfig = $scopeConfig;
	}
	
	public function getLang($store_id = '') {
		return $this->_scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
	}
	
	public function getMetas() {
		$storeIdsFromPage = $this->_page->getStoreId(); //stores ids that current page belong
		$storesMetas = array();
		$pageIdentifier = $this->_page->getIdentifier(); //current cms url key

		if($storeIdsFromPage[0]=="0") { //check if option all store views is checked under page content tab
			$stores = $this->_storeManager->getStores();
			foreach($stores as $store){
				if($this->_storeManager->getStore()->getId()!=$store->getId()){ //check if not current lang
					$localeCode = MetaLangs::getLang($store->getId());
					$storesMetas[] = array('alternateurl'=>$store->getBaseUrl().$pageIdentifier, 'locale'=>$localeCode);
				}	
			}
		} else { 
			foreach($storeIdsFromPage as $storeId) {
				if($this->_storeManager->getStore()->getId()!=$storeId){ //check if not current lang
					$localeCode = MetaLangs::getLang($storeId);
					$storesMetas[] = array('alternateurl'=>$this->_storeManager->getStore($storeId)->getBaseUrl().$pageIdentifier, 'locale'=>$localeCode);
				}
			}
		}

		return $storesMetas;	
	}

}
