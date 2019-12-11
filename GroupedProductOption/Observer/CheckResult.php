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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_GroupedProductOption
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GroupedProductOption\Observer;

use \Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Catalog\Api\ProductRepositoryInterface;

class CheckResult implements ObserverInterface
{
    /**
     * Customer cart
     *
     * @var CustomerCart
     */
    private $cart;

    /**
     * Product repository
     *
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Resolver
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $locale;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $locale,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository
    ) {
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->locale = $locale;
    }

    /**
     * Execute Bss_AjaxCart add grouped product with custom option.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $params = $observer->getRequest()->getParams();
        $product = $observer->getProduct();
        $result = $observer->getResult();
        $messages = $result->getMessages();

        if (!isset($params['bss-gpo'])) {
            $redirect = false;
            $typeInstance = $product->getTypeInstance();
            $associatedProducts = $typeInstance->getAssociatedProducts($product);
            if ($associatedProducts) {
                foreach ($associatedProducts as $associatedProduct) {
                    if ($associatedProduct->getRequiredOptions()) {
                        $redirect = true;
                        break;
                    }
                }
            }

            if ($redirect) {
                $result->setStatus(false);
                $messages[] = [
                    'type' => 'error',
                    'message' => __("Please specify product's required option(s).")
                ];
            }
        } else {
            try {
                foreach ($params['super_group'] as $id => $qty) {
                    if (!isset($qty) || $qty <= 0 || $qty == '') {
                        continue;
                    }
                    $productChild = $this->productRepository->getById($id);
                    $paramsChild = [];
                    $paramsChild['product'] = $id;
                    
                    $paramsChild['selected_configurable_option'] = $params['selected_configurable_option'];
                    if (isset($params['options_'.$id]) && !empty($params['options_'.$id]) > 0) {
                        $paramsChild['options'] = $params['options_'.$id];
                    }
                    
                    if (isset($params['bss-gpo-option-'.$id]) && !empty($params['bss-gpo-option-'.$id]) > 0) {
                        foreach ($params['bss-gpo-option-'.$id] as $name => $value) {
                            $paramsChild[$name] = $value;
                        }
                    }

                    $paramsChild['qty'] = $qty;
                    if (isset($paramsChild['qty'])) {
                        $filter = new \Zend_Filter_LocalizedToNormalized(
                            ['locale' => $this->locale->getLocale()]
                        );
                        $paramsChild['qty'] = $filter->filter($paramsChild['qty']);
                    }
                    $paramsChild['super_product_config'] = [
                        'product_type' => $product->getTypeId(),
                        'product_id' => $params['product']
                    ];

                    /**
                     * Check product availability
                     */
                    if (!$productChild) {
                        $result->setStatus(false);
                        $messages[] = [
                            'type' => 'error',
                            'message' => __("Product $id not exist.")
                        ];
                    }

                    $this->cart->addProduct($productChild, $paramsChild);
                }

                $result['added'] = true;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result->setStatus(false);
                $messages[] = [
                    'type' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        $result->setMessages($messages);
        return $this;
    }
}
