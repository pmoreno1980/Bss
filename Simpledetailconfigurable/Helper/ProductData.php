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
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Helper;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class ProductData
 * @package Bss\Simpledetailconfigurable\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductData extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productInfo;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $productHelper;

    /**
     * @var \Bss\Simpledetailconfigurable\Model\PreselectKeyFactory
     */
    private $preselectKey;

    /**
     * @var \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory
     */
    private $productEnabledModule;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * ProductData constructor.
     *
     * @param \Magento\Catalog\Model\ProductRepository $productInfo
     * @param \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory $productEnabledModule
     * @param \Bss\Simpledetailconfigurable\Model\PreselectKeyFactory $preselectKey
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productInfo,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\Product $productHelper,
        \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory $productEnabledModule,
        \Bss\Simpledetailconfigurable\Model\PreselectKeyFactory $preselectKey,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->productInfo = $productInfo;
        $this->stockRegistry = $stockRegistry;
        $this->imageHelper = $imageHelper;
        $this->productHelper = $productHelper;
        $this->preselectKey = $preselectKey;
        $this->productEnabledModule = $productEnabledModule;
        $this->filterProvider = $filterProvider;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param int $productEntityId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAllData($productEntityId)
    {
        $result = [];
        $product = $this->productInfo->getById($productEntityId);
        $result = $this->getDetailData($product);
        $isAjaxLoad = $this->getEnabledModuleOnProduct($productEntityId)['is_ajax_load'];
        $result['is_ajax_load'] = $isAjaxLoad;
        $result['preselect'] = $this->getSelectingDataWithConfig($productEntityId);
        $this->getDetailStock($result, false);
        $result['url'] = $this->productHelper->getProductUrl($product);
        if ($result['url'] == null) {
            $result['url'] = str_replace(' ', '-', $result['name']) . $this->moduleConfig->getSuffix();
        }

        if ($isAjaxLoad) {
            $this->getDetailPrice($product, $result);
            $result['child'] = [];
            return $result;
        }
        $parentPrice = 0;

        $parentProduct = $product->getTypeInstance()->getChildrenIds($productEntityId);
        foreach ($parentProduct[0] as $simpleProduct) {
            $childProduct = $this->getChildDetail($simpleProduct);
            $result['child'][$simpleProduct] = $childProduct;
            $parentPrice = $childProduct['price']['finalPrice'];
        }
        foreach ($result['child'] as $ri) {
            $parentPrice = ($ri['price']['finalPrice'] < $parentPrice) ? $ri['price']['finalPrice'] : $parentPrice;
        }
        $result['price']['finalPrice'] = $parentPrice;
        return $result;
    }

    /**
     * @param string $childId
     * @return array|bool
     * @throws \Exception
     */
    public function getChildDetail($childId)
    {
        try {
            $child = $this->productInfo->getById($childId);
            $result = $this->getDetailData($child);
            $this->getDetailStock($result);
            $this->getDetailPrice($child, $result);
            return $result;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws \Exception
     */
    public function getDetailData($product)
    {
        $data = [];
        $productDesc = $product->getDescription() ?: '';
        $productShortDesc = $product->getShortDescription() ?: '';
        $data['entity'] = $product->getId();
        $data['sku'] = $product->getSku();
        $data['name'] = $product->getName();
        $data['desc'] = $this->filterProvider->getPageFilter()->filter($productDesc);
        $data['sdesc'] = $this->filterProvider->getPageFilter()->filter($productShortDesc);
        $data['meta_data']['meta_title'] = $product->getMetaTitle();
        $data['meta_data']['meta_keyword'] = $product->getMetaKeyword();
        $data['meta_data']['meta_description'] = $product->getMetaDescription();
        $data['additional_info'] = $this->getAdditionalInfo($product);
        $data['image'] = $this->getGalleryImages($product);
        if (version_compare($this->moduleConfig->getMagentoVersion(), '2.2.0', '<')) {
            $data['video'] = $this->getVideoData($product);
        }
        return $data;
    }

    /**
     * @param array $data
     * @param bool $isChild
     */
    public function getDetailStock(&$data, $isChild = true)
    {
        $childStock = $this->stockRegistry->getStockItem($data['entity']);
        $data['stock_number'] = $childStock->getQty();
        $data['stock_status'] = $childStock->getIsInStock();
        if ($isChild) {
            $data['minqty'] = ($childStock->getUseConfigMinSaleQty()) ? 0 : $childStock->getMinSaleQty();
            $data['maxqty'] = ($childStock->getUseConfigMaxSaleQty()) ? 0 : $childStock->getMaxSaleQty();
            $data['increment'] = ($childStock->getUseConfigQtyIncrements()) ? 0 : $childStock->getQtyIncrements();
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     */
    public function getDetailPrice($product, &$data)
    {
        $data['price']['oldPrice'] = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $data['price']['basePrice'] = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
        $data['price']['finalPrice'] = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $data['price']['tier_price'] = $this->getTierPriceData($product);
    }

    /**
     * @param int $productId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSelectingKey($productId)
    {
        $result = [];
        $product = $this->productInfo->getById($productId);
        $parentProduct = $product->getTypeInstance()->getChildrenIds($productId);
        $parentAttribute = $product->getTypeInstance()->getConfigurableAttributes($product);
        foreach ($parentProduct[0] as $simpleProduct) {
            $child = $this->productInfo->getById($simpleProduct);
            foreach ($parentAttribute as $attrValue) {
                $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                if (!array_key_exists($attrLabel, $child->getAttributes())) {
                    continue;
                }
                $result[$attrValue->getAttributeId()]['label'] = $attrValue->getLabel();
                $childRow = $child->getAttributes()[$attrLabel]->getFrontend()->getValue($child);
                $result[$attrValue->getAttributeId()]['child'][$child->getData($attrLabel)] = $childRow;
            }
        }
        return $result;
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getSelectingData($productId)
    {
        return $this->preselectKey->create()->getCollection()->getArrayData($productId);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getSelectingDataWithConfig($product)
    {
        $result = [];
        $result['data'] = $this->getSelectingData($product);
        if (isset($result['data'])) {
            $result['enabled'] = true;
        } else {
            $result['enabled'] = false;
        }
        return $result;
    }

    /**
     * @param string $productId
     * @return \Bss\Simpledetailconfigurable\Model\ProductEnabledModule
     */
    public function getEnabledModuleOnProduct($productId)
    {
        $resultObject = $this->productEnabledModule->create()->load($productId);
        if (!$resultObject->getProductId()) {
            return $this->productEnabledModule->create()->setData(['enabled' => 1, 'is_ajax_load' => 0]);
        }
        return $resultObject;
    }

    /**
     * @param string $productId
     * @return bool
     */
    public function isAjaxLoad($productId)
    {
        $resultObject = $this->productEnabledModule->create()->load($productId);
        if (!$resultObject->getProductId()) {
            return false;
        }
        return $resultObject->getIsAjaxLoad();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getTierPriceData($product)
    {
        $result = [];
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $baseFinalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        if (isset($tierPricesList) && !empty($tierPricesList)) {
            foreach ($tierPricesList as $tier) {
                $tierData = [];
                $tierData['qty'] = $tier['price_qty'];
                $tierData['final'] = $tier['price']->getValue();
                $tierData['value'] = $tier['price']->getValue();
                $tierData['base'] = $tier['price']->getBaseAmount();
                $tierData['final_discount'] = $tierData['final'] - $finalPrice;
                $tierData['base_discount'] = $tierData['base'] - $baseFinalPrice;
                $tierData['percent'] = (1 - $tierData['base'] / $baseFinalPrice) * 100;
                $result[$tierData['qty']] = $tierData;
            }
        }
        return $result;
    }

    /**
     * @param Product $product
     * @return mixed
     */
    public function getMainImage($product)
    {
        $images = $product->getMediaGallery('images');
        return $images;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGalleryImages($product)
    {
        $images = $product->getMediaGalleryImages();
        $imagesItems = [];
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                $image->setData(
                    'small_image_url',
                    $this->imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->imageHelper->init($product, 'product_page_image_medium')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->imageHelper->init($product, 'product_page_image_large')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $imagesItems[] = [
                    'thumb' => $image->getData('small_image_url'),
                    'img' => $image->getData('medium_image_url'),
                    'full' => $image->getData('large_image_url'),
                    'caption' => ($image->getLabel() ?: $product->getName()),
                    'position' => $image->getPosition(),
                    'isMain' => $product->getImage() == $image->getFile(),
                    'type' => str_replace('external-', '', $image->getMediaType()),
                    'videoUrl' => $image->getVideoUrl(),
                ];
            }
        }
        if (empty($imagesItems) && $this->moduleConfig->getChildImageConfig() === 'placeholder') {
            $imagesItems[] = [
                'thumb' => $this->imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'img' => $this->imageHelper->getDefaultPlaceholderUrl('image'),
                'full' => $this->imageHelper->getDefaultPlaceholderUrl('image'),
                'type' => 'image',
                'videoUrl' => null,
                'caption' => '',
                'position' => '0',
                'isMain' => true,
            ];
        }
        return $imagesItems;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getVideoData($product)
    {
        $mediaGalleryData = [];
        foreach ($product->getMediaGalleryImages() as $mediaGalleryImage) {
            if ($mediaGalleryImage->getMediaType() === 'external-video') {
                $mediaType = 'video';
            } else {
                $mediaType = $mediaGalleryImage->getMediaType();
            }
            $mediaGalleryData[] = [
                'mediaType' => $mediaType,
                'videoUrl' => $mediaGalleryImage->getVideoUrl(),
                'isBase' => $product->getImage() == $mediaGalleryImage->getFile(),
            ];
        }
        return $mediaGalleryData;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAdditionalInfo($product)
    {
        $result = [];
        foreach ($product->getAttributes() as $attrkey => $value) {
            if ($value->getData('is_visible_on_front')) {
                $valueData = $value->getFrontend()->getValue($product);
                if ($valueData != false && $valueData != 'No' && $valueData != 'N/A') {
                    $result[$attrkey]['value'] = $valueData;
                    $result[$attrkey]['label'] = $value->getStoreLabel();
                }
            }
        }
        return $result;
    }

    /**
     * @return \Magento\Catalog\Model\ProductRepository
     */
    public function getProductRepository()
    {
        return $this->productInfo;
    }
}
