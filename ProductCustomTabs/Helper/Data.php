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
namespace Bss\ProductCustomTabs\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\Phrase;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Helper Output
     *
     * @var \Magento\Catalog\Helper\Output $helperOutput
     */
    protected $helperOutput;

    /**
     * Attribute Factory
     *
     * @var AttributeFactory $attributeFactory
     */
    protected $attributeFactory;

    /**
     * Price Currency Interface
     *
     * @var PriceCurrencyInterface $priceCurrency
     */
    protected $priceCurrency;

    /**
     * Store Manager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry $registry
     */
    protected $registry;

    /**
     * Session Factory
     *
     * @var \Magento\Customer\Model\SessionFactory $customerSession
     */
    protected $customerSession;

    /**
     * Order Factory
     *
     * @var \Magento\Sales\Model\OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * Order Items Factory
     *
     * @var \Magento\Sales\Model\Order\ItemFactory $orderItemsFactory
     */
    protected $orderItemsFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemsFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Catalog\Helper\Output $helperOutput,
        AttributeFactory $attributeFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemsFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->helperOutput = $helperOutput;
        $this->attributeFactory = $attributeFactory;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->orderFactory = $orderFactory;
        $this->orderItemsFactory = $orderItemsFactory;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getProductCustomerPurchased()
    {
        $productIds = [];
        if ($this->customerSession->create()->isLoggedIn()) {
            $customerId = $this->customerSession->create()->getId();
            $orderIds = $this->orderFactory->create()->getCollection()
                ->addFieldToFilter(
                    "customer_id",
                    $customerId
                )->getAllIds();
            $productIds = $this->orderItemsFactory->create()->getCollection()->addAttributeToSelect('product_id')
                ->addFieldToFilter(
                    'order_id',
                    [
                        'in' => $orderIds
                    ]
                )->addFieldToFilter(
                    'parent_item_id',
                    [
                        'null' => true
                    ]
                );
            $productIds = $productIds->getData();
        }

        return $productIds;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return mixed
     */
    public function getCustomerSession()
    {
        return $this->customerSession->create();
    }

    /**
     * Load Attribute
     *
     * @param int $attributeId
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected function loadAtribute($attributeId)
    {
        return $this->attributeFactory->create()->load($attributeId);
    }

    /**
     * @param array $arrAttributes
     * @param string $content
     * @return string
     */
    protected function handleGetAttributesContent($arrAttributes, $content = '')
    {
        foreach ($arrAttributes as $attributeId) {
            $modelAttribute = $this->loadAtribute($attributeId);

            $currentProduct = $this->registry->registry('product');
            $value = $modelAttribute->getFrontend()->getValue($currentProduct);
            // return value after check
            $value = $this->returnValue($value, $currentProduct, $modelAttribute);

            if ($value instanceof Phrase || (is_string($value) && $value != '')) {
                $labelAttribute = $modelAttribute->getStoreLabel();
                $valueContent = $this->helperOutput->productAttribute(
                    $currentProduct,
                    $value,
                    $modelAttribute->getAttributeCode()
                );
                if ($labelAttribute != '') {
                    $content .= "<tr>
                                <td style='font-weight: bold'>
                                    $labelAttribute
                                </td>
                                <td>
                                    $valueContent
                                </td>
                              <tr>";
                }
            }
        }
        return $content;
    }

    /**
     * @param mixed $value
     * @param mixed $currentProduct
     * @param mixed $modelAttribute
     * @return mixed
     */
    protected function returnValue($value, $currentProduct, $modelAttribute)
    {
        if (!$currentProduct->hasData($modelAttribute->getAttributeCode())) {
            $value = __('N/A');
        } elseif ($modelAttribute->getFrontendInput() == 'price' && is_string($value)) {
            $value = $this->priceCurrency->convertAndFormat($value);
        } elseif (is_array($value)) {
            $value = implode(',', $value);
        }
        return $value;
    }

    /**
     * @param string $attributeIdsOfTab
     * @return string
     */
    public function getAttributesContent($attributeIdsOfTab)
    {
        $content = '';
        $arrAttributes = explode('&', $attributeIdsOfTab);
        if (is_array($arrAttributes)) {
            $content = $this->handleGetAttributesContent($arrAttributes);
        }
        return $content;
    }
}
