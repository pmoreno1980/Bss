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
 * @package    Bss_SimpledetailconfigurableCustomize
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\SimpledetailconfigurableCustomize\Plugin\Checkout;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel as CoreAbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class ConfigProviderPlugin
 *
 * @package Bss\SimpledetailconfigurableCustomize\Plugin\Checkout
 */
class ConfigProviderPlugin extends CoreAbstractModel
{
    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var
     */
    protected $checkoutSession;

    /**
     * ConfigProviderPlugin constructor.
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param ArrayManager $arrayManager
     * @param Json $json
     * @param CheckoutSession $checkoutSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        ArrayManager $arrayManager,
        Json $json,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->arrayManager = $arrayManager;
        $this->json = $json;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterGetConfig(DefaultConfigProvider $subject, array $result)
    {
        $items = $result['totalsData']['items'];
        $itemsNew = [];
        foreach ($items as $item) {
            $quoteId = $item['item_id'];
            $quote = $quote = $this->checkoutSession->getQuote();
            $product = $quote->getItemById($quoteId)->getProduct();
            //If is configurable => modify option
            if ($this->configurableChecker($product)) {
                $childSimple = $quote->getItemById($quoteId)->getOptionByCode('simple_product');
                $product = $childSimple->getProduct();
                $leadTime = $product->getLeadTime();
                if (!$leadTime) {
                    $leadTime = '';
                }
                $attr = $product->getResource()->getAttribute('fulfillment_type');
                $itemFul = '';
                if ($attr->usesSource()) {
                    $itemFul = $attr->getSource()
                        ->getOptionText($product->getData('fulfillment_type'));
                }
                $optionsPath = $this->arrayManager->findPath('options', $item, null);
                if ($optionsPath) {
                    $optionsData = $this->arrayManager->get($optionsPath, $item);
                    if ($optionsData) {
                        $optionsData = $this->json->unserialize($optionsData);
                        if ($leadTime) {
                            $optionsData[] = [
                                "value" => $leadTime,
                                "label" => "Lead Time"
                            ];
                        }
                        if ($itemFul != false && $itemFul != '') {
                            $optionsData[] = [
                                "value" => $itemFul,
                                "label" => "Fulfillment Type"
                            ];
                        }
                        $optionsData = $this->json->serialize($optionsData);
                        $item = $this->arrayManager->replace($optionsPath, $item, $optionsData);
                    }
                }
                $itemsNew[] = $item;
            } else {
                $itemsNew[] = $item;
            }
        }
        $result['totalsData']['items'] = $itemsNew;
        return $result;
    }

    /**
     * Check if product is configurable type
     *
     * @param Product $product
     * @return bool
     */
    protected function configurableChecker($product)
    {
        $productType = $product->getTypeId();
        if ($productType == 'configurable') {
            return true;
        } else {
            return false;
        }
    }
}
