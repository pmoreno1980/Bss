<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductCustomTabs
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductCustomTabs\Controller\Adminhtml\Tab;

use Magento\Backend\App\Action;
use Bss\ProductCustomTabs\Model;
use Magento\Backend\Model\SessionFactory;

class Save extends \Magento\Backend\App\Action
{

    /**
     * Model Tab Factory
     *
     * @var Model\TabFactory $modelTabFactory
     */
    protected $modelTabFactory;

    /**
     * Model Rule Factory
     *
     * @var Model\RuleFactory $modelRuleFactory
     */
    protected $modelRuleFactory;

    /**
     * Model Rule Factory
     *
     * @var Model\RuleFactory $modelRuleFactory
     */
    protected $sessionFactory;

    /**
     * Core Registry
     *
     * @var \Magento\Framework\Registry $coreRegistry
     */
    protected $coreRegistry;

    /**
     * Store Manager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * Helper Json
     *
     * @var \Magento\Framework\Json\Helper\Data $helperJson
     */
    protected $helperJson;

    /**
     * Save constructor.
     *
     * @param Model\TabFactory $modelTabFactory
     * @param Model\RuleFactory $modelRuleFactory
     * @param Action\Context $context
     * @param SessionFactory $sessionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $helperJson
     */
    public function __construct(
        Model\TabFactory $modelTabFactory,
        Model\RuleFactory $modelRuleFactory,
        Action\Context $context,
        SessionFactory $sessionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $helperJson
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->modelTabFactory = $modelTabFactory;
        $this->modelRuleFactory = $modelRuleFactory;
        $this->sessionFactory= $sessionFactory;
        $this->helperJson = $helperJson;
    }

    /**
     * Get Array All Store Ids
     *
     * @return array
     */
    protected function getAllStoreIds()
    {
        $arrStoreIds = [];
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $arrStoreIds[] = $store->getId();
        }
        return $arrStoreIds;
    }

    /**
     * Get Attribute Ids
     *
     * @param string $stringAttributes
     * @return bool|string
     */
    protected function getAttributeIds($stringAttributes)
    {
        $arrAttributes = $this->helperJson->jsonDecode($stringAttributes);
        $result = '';
        if (!empty($arrAttributes)) {
            foreach (array_keys($arrAttributes) as $key) {
                $result .= $key.'&';
            }
        }
        $result = substr($result, 0, -1);
        return $result;
    }

    /**
     * Save Tab Edit
     *
     * @param array $data
     * @param int $idTab
     * @param int $isDefault
     * @throws \Exception
     */
    protected function saveEditTabAndRule($data, $idTab, $isDefault = 1)
    {
        $modelTab = $this->modelTabFactory->create();
        $modelRule = $this->modelRuleFactory->create();
        $modelTab->load($idTab);
        $resourceRule = $modelRule->getResource();
        $idRule = $resourceRule->getRuleByTabId($idTab);
        if ($idRule) {
            $modelRule->load($idRule);
        }
        $this->coreRegistry->unregister('bss_pct_customer_group_update_data');
        $this->coreRegistry->register('bss_pct_customer_group_update_data', $data);
        $modelTab->setStatus($data['status']);
        $modelTab->setTitle($data['title']);
        $modelTab->setContent($data['content']);
        $modelTab->setPosition($data['position_tab']);
        $modelTab->setVisibleCustomerPurchased($data['visible_customer_purchased']);
        if ($isDefault == 0) {
            $modelTab->setData('is_default', $isDefault);
        }
        if (isset($data['attributes'])) {
            $modelTab->setAttributeIds($data['attributes']);
        }
        $modelTab->setShowRelatedProduct($data['show_related_products']);
        $modelTab->setShowUpSellProduct($data['show_up_sell_products']);
        $modelTab->setShowCrossSellProduct($data['show_cross_sell_products']);
        $modelTab->save();
        $data["tab_id"] = $modelTab->getId();
        $this->coreRegistry->unregister('bss_pct_rule_update_data');
        $this->coreRegistry->register('bss_pct_rule_update_data', $data);
        $modelRule->loadPost($data);
        $modelRule->save();
    }

    /**
     * Save Tab and Save Rule return id Tab
     * @param array $data
     * @param int $parentId
     * @return int
     * @throws \Exception
     */
    protected function saveTabAndRule($data, $parentId = 0)
    {
        $modelTab = $this->modelTabFactory->create();
        $modelRule = $this->modelRuleFactory->create();

        $this->coreRegistry->unregister('bss_pct_customer_group_update_data');
        $this->coreRegistry->register('bss_pct_customer_group_update_data', $data);
        $modelTab->setStatus($data['status']);
        $modelTab->setTitle($data['title']);
        $modelTab->setContent($data['content']);
        $modelTab->setPosition($data['position_tab']);
        $modelTab->setVisibleCustomerPurchased($data['visible_customer_purchased']);
        if ($parentId != 0) {
            $modelTab->setData('parent_id', $parentId);
        }
        $modelTab->setStoreId($data['store_id']);
        if (isset($data['attributes'])) {
            $modelTab->setAttributeIds($data['attributes']);
        }
        $modelTab->setShowRelatedProduct($data['show_related_products']);
        $modelTab->setShowUpSellProduct($data['show_up_sell_products']);
        $modelTab->setShowCrossSellProduct($data['show_cross_sell_products']);
        $modelTab->save();

        $data["tab_id"] = $modelTab->getId();
        $this->coreRegistry->unregister('bss_pct_rule_update_data');
        $this->coreRegistry->register('bss_pct_rule_update_data', $data);
        $modelRule->loadPost($data);
        $modelRule->save();
        return $modelTab->getId();
    }

    /**
     * Handle function save edit tab
     *
     * @param array $data
     * @param int $idTab
     *
     * @throws \Exception
     */
    protected function handleSaveEditTab($data, $idTab)
    {
        if ($data['store_id'] == 'default' || $data['store_id'] == '0') {
            $this->saveEditTabAndRule($data, $idTab);
            $resourceTab = $this->modelTabFactory->create()->getResource();
            $arrTabChildNotDefaultIds = $resourceTab->getTabChildNotIsDefault($idTab);
            foreach ($arrTabChildNotDefaultIds as $tabChildNotDefaultId) {
                $this->saveEditTabAndRule($data, $tabChildNotDefaultId);
            }
        } else {
            $isDefault = 0;
            $this->saveEditTabAndRule($data, $idTab, $isDefault);
        }
    }

    /**
     * Handle function save new tab
     *
     * @param array $data
     * @return int
     *
     * @throws \Exception
     */
    protected function handleNewSaveTab($data)
    {
        $parentId = $this->saveTabAndRule($data);
        $idUrlSaveAndContinue = $parentId;

        if ($data['store_id'] == 0 || $data['store_id'] == 'default') {
            $arrStoreIds = $this->getAllStoreIds();
            foreach ($arrStoreIds as $storeId) {
                $data['store_id'] = $storeId;
                $this->saveTabAndRule($data, $parentId);
            }
        }
        return $idUrlSaveAndContinue;
    }

    /**
     * Save Action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $data['attributes'] = $this->getAttributeIds($data['attributes']);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $storeReturn = $data['store_id'];
        if ($data) {
            try {
                $idTab = $this->getRequest()->getParam('id');
                if (isset($data['rule'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    unset($data['rule']);
                }
                if ($idTab) {
                    $idUrlSaveAndContinue = $idTab;
                    $this->handleSaveEditTab($data, $idTab);
                } else {
                    $idUrlSaveAndContinue = $this->handleNewSaveTab($data);
                }
                $this->messageManager->addSuccessMessage(__('The tab has been saved.'));
                $warningMessage = $this->coreRegistry->registry('bss_warning_message');
                if ($warningMessage) {
                    $this->messageManager->addErrorMessage(__('No product assigned for tab. Please check rule again!'));
                }
                $this->sessionFactory->create()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'id' => $idUrlSaveAndContinue,
                        'store' => $storeReturn,
                        '_current' => true
                    ]);
                }
                return $resultRedirect->setPath('*/*/', [
                    'store' => $storeReturn
                ]);
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', [
                'id' => $this->getRequest()->getParam('id'),
                'store' => $storeReturn
            ]);
        }
        return $resultRedirect->setPath('*/*/edit', [
            'id' => $this->getRequest()->getParam('id'),
            'store' => $storeReturn
        ]);
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_ProductCustomTabs::save");
    }
}
