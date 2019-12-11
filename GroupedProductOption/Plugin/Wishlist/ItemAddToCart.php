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
 * @package    Bss_GroupedProductOption
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GroupedProductOption\Plugin\Wishlist;

class ItemAddToCart
{

    /**
     * Registry model.
     *
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Resolver.
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $resolver;

    /**
     * Product repository.
     *
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $productRepository;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $resolver
    ) {
        $this->registry = $registry;
        $this->resolver = $resolver;
        $this->productRepository = $productRepository;
    }
    public function aroundAddToCart(
        \Magento\Wishlist\Model\Item $subject,
        $proceed,
        $cart,
        $delete = false
    ) {
        $product = $subject->getProduct();
        if($product->getTypeId() === 'grouped') {
            $params = $subject->getBuyRequest()->getData();
            if (!isset($params['bss-gpo'])) {
                $flag = false;
                foreach ($product->getTypeInstance()->getAssociatedProducts($product) as $child) {
                    if ($child->getRequiredOptions()) {
                        $flag = true;
                        break;
                    }
                }
                if ($flag) {
                    throw new \Magento\Framework\Exception\LocalizedException(__("Please specify product's required option(s)."));
                }
            } else {

                foreach ($params['super_group'] as $id => $qty) {
                    if (!isset($qty) || $qty <= 0 || $qty == '') {
                        continue;
                    }
                    $productChild = $this->productRepository->getById($id);
                    $paramsChild = [];
                    $paramsChild['uenc'] = $params['uenc'];
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
                            ['locale' => $this->resolver->getLocale()]
                        );
                        $paramsChild['qty'] = $filter->filter($paramsChild['qty']);
                    }

                    if ($productChild->getTypeId() == 'configurable') {
                        $paramsChild['super_attribute'] = $params['super_attribute'][$id];
                    } else {
                        $paramsChild['super_product_config'] = [
                            'product_type' => $product->getTypeId(),
                            'product_id' => $params['product']
                        ];
                    }

                    /**
                     * Check product availability
                     */
                    if (!$productChild) {
                        return $this->goBack();
                    }
                    $this->registry->unregister('bss-gpo-group-add');
                    $this->registry->register('bss-gpo-group-add', $id);
                    $cart->addProduct($productChild, $paramsChild);
                }

                if ($delete) {
                    $subject->delete();
                }
                return true;
            }
        }
        return $proceed($cart, $delete);
    }
}
