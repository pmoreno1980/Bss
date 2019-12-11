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
namespace Bss\GroupedProductOption\Controller\Cart;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * Bss grouped product option helper.
     *
     * @var \Bss\GroupedProductOption\Helper\Data
     */
    private $helperBss;

    /**
     * Product repository.
     *
     * @var ProductRepositoryInterface
     */
    public $productRepository;

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
     * Escaper.
     *
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * Checkout cart helper.
     *
     * @var \Magento\Checkout\Helper\Cart
     */
    private $cartHelper;

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\GroupedProductOption\Helper\Data $helperBss
     * @param \Magento\Framework\Locale\ResolverInterface $resolver
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry,
        \Bss\GroupedProductOption\Helper\Data $helperBss,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        \Magento\Framework\Escaper $escaper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );

        $this->productRepository = $productRepository;
        $this->helperBss = $helperBss;
        $this->registry = $registry;
        $this->resolver = $resolver;
        $this->escaper = $escaper;
        $this->cartHelper = $cartHelper;
        $this->logger = $logger;
    }

    /**
     * @param null $coreRoute
     * @return Add|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute($coreRoute = null)
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $params = $this->getRequest()->getParams();
        $product = $this->_initProduct();
        if (!$this->helperBss->getConfig() || $product->getTypeId() !=
            \Bss\GroupedProductOption\Helper\Data::PRODUCT_TYPE_GROUPED) {
            return parent::execute($coreRoute);
        }

        // check in category
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
                $url = $product->getUrlModel()->getUrl($product);
                $this->messageManager->addNoticeMessage(__("Please specify product's required option(s)."));
                return $this->goBack($url);
            } else {
                return parent::execute($coreRoute);
            }
        } else {
            // add to cart function
            $this->registry->register('bss-gpo-group', $params['product']);
            try {
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
                    $this->cart->addProduct($productChild, $paramsChild);
                }

                $related = $this->getRequest()->getParam('related_product');
                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }

                $this->cart->save();

                $this->_eventManager->dispatch(
                    'checkout_cart_add_product_complete',
                    ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );

                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if (!$this->cart->getQuote()->getHasError()) {
                        $message = __(
                            'You added %1 to your shopping cart.',
                            $product->getName()
                        );
                        $this->messageManager->addSuccessMessage($message);
                    }
                    return $this->goBack(null, $product);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_checkoutSession->getUseNotice(true)) {
                    $this->messageManager->addNoticeMessage(
                        $this->escaper->escapeHtml($e->getMessage())
                    );
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $this->messageManager->addErrorMessage(
                            $this->escaper->escapeHtml($message)
                        );
                    }
                }

                $url = $this->_checkoutSession->getRedirectUrl(true);

                if (!$url) {
                    $cartUrl = $this->cartHelper->getCartUrl();
                    $url = $this->_redirect->getRedirectUrl($cartUrl);
                }

                return $this->goBack($url);
            } catch (\Exception $e) {
                $this->messageManager
                    ->addExceptionMessage($e, __('We can\'t add this item to your shopping cart right now.'));
                $this->logger->critical($e);
                return $this->goBack();
            }
        }
    }
}
