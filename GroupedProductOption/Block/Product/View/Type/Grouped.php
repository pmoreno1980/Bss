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
namespace Bss\GroupedProductOption\Block\Product\View\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Bss\GroupedProductOption\Block\Product\View\Type\Configurable;

class Grouped extends \Magento\GroupedProduct\Block\Product\View\Type\Grouped
{
    /**
     * Bss grouped product option helper.
     *
     * @var \Bss\GroupedProductOption\Helper\Data
     */
    private $helperBss;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * Grouped constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Bss\GroupedProductOption\Helper\Data $helperBss
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Bss\GroupedProductOption\Helper\Data $helperBss,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
        $this->jsonEncoder = $jsonEncoder;
        $this->helperBss = $helperBss;
        $this->productRepository = $productRepository;
        $this->localeFormat = $localeFormat;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param $item
     * @return bool|string
     */
    public function renderBlockProduct($item)
    {
        $html = '';
        $product = $this->getProductInfo($item);
        $typeProduct = $product->getTypeId();
        if ($typeProduct == \Bss\GroupedProductOption\Helper\Data::PRODUCT_TYPE_CONFIGURABLE) {
            $block = $this->_addConfigurableBlock($product);
        }

        if (isset($block)) {
            $html .= '<div class="bss-gpo-configurable-info fieldset">'.
                        $block->toHtml().
                     '</div>';
        }

        $customOption = $this->_addCustomOption($product);
        if (isset($customOption)) {
            $html .= '<div class="bss-gpo-custom-option" data-product-id="'.$product->getId().'">'
                    . '<div class="fieldset">'
                    . $customOption->toHtml()
                    . '</div></div>';
        }

        if (isset($html) && $html != '') {
            return $html;
        }

        return false;
    }

    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getJsonConfig()
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $associatedProducts = $this->getAssociatedProducts();
        $config = [];
        foreach ($associatedProducts as $item) {
            $product = $this->getProductInfo($item);
            $productId = $product->getId();

            $tierPrices = [];
            $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
            foreach ($tierPricesList as $tierPrice) {
                $tierPrices[] = $tierPrice['price']->getValue();
            }
            $config[$productId] = [
                'productId'   => $productId,
                'priceFormat' => $this->localeFormat->getPriceFormat(),
                'prices'      => [
                    'oldPrice'   => [
                        'amount'      => $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(),
                        'adjustments' => []
                    ],
                    'basePrice'  => [
                        'amount'     => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount(),
                        'adjustments' => []
                    ],
                    'finalPrice' => [
                        'amount'      => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                        'adjustments' => []
                    ]
                ],
                'idSuffix'    => '_clone',
                'tierPrices'  => $tierPrices
            ];

            $responseObject = $this->dataObjectFactory->create();
            $this->_eventManager->dispatch('catalog_product_view_config', ['response_object' => $responseObject]);
            if (is_array($responseObject->getAdditionalOptions())) {
                foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                    $config[$productId][$option] = $value;
                }
            }
        }
        return $this->jsonEncoder->encode($config);
    }

    /**
     * Add configurable block to layout.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function _addConfigurableBlock($product)
    {
        $layout = $this->_layout->createBlock(Configurable::class)
            ->setProduct($product);

        if (isset($layout)) {
            return $layout;
        }

        return false;
    }

    /**
     * Add custom option block to layout.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function _addCustomOption($product)
    {
        $layout = $this->_layout->getBlock('bss.gpo.product.info.options')->setProduct($product);

        if (isset($layout)) {
            return $layout;
        }

        return false;
    }

    /**
     * @param $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductInfo($item)
    {
        return $this->productRepository->getById($item->getId());
    }

    /**
     * Get grouped info html.
     *
     * @param \Magento\Catalog\Model\Product $item
     * @return string
     */
    public function getProductInfoGpo($item)
    {
        $html = '';
        $product = $this->getProductInfo($item);
        if ($this->helperBss->getConfig('show_link') && $product->getVisibility() != 1) {
            $html .= '<a href = "'.$product->getProductUrl().'" class="bss-gpo-img">';
        }

        $html .= '<img src="'.$this->getProductImage($product).'"  alt='.$product->getName().' />';
        if ($this->helperBss->getConfig('show_link') && $product->getVisibility() != 1) {
            $html .='</a>';
        }

        return $html;
    }

    /**
     * Get product image.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool|string
     */
    protected function getProductImage($product)
    {
        $imageSize = 135;
        $productImage = $this->_imageHelper->init(
            $product,
            'category_page_list',
            ['height' => $imageSize, 'width'=> $imageSize]
        )->getUrl();

        if (!$productImage) {
            return false;
        }

        return $productImage;
    }

    /**
     * Get Bss grouped product option helper.
     *
     * @return \Bss\GroupedProductOption\Helper\Data
     */
    public function getBssHelper()
    {
        return $this->helperBss;
    }
}
