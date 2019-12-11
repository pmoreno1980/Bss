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
namespace Bss\GroupedProductOption\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class GroupedProductDataProvider extends \Magento\GroupedProduct\Ui\DataProvider\Product\GroupedProductDataProvider
{
    /**
     * Bss grouped option helper.
     *
     * @var \Bss\GroupedProductOption\Helper\Data
     */
    private $helperBss;

    /**
     * Initialize dependencies.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param ConfigInterface $config
     * @param StoreRepositoryInterface $storeRepository
     * @param \Bss\GroupedProductOption\Helper\Data $helperBss
     * @param array $meta
     * @param array $data
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ConfigInterface $config,
        StoreRepositoryInterface $storeRepository,
        \Bss\GroupedProductOption\Helper\Data $helperBss,
        array $meta = [],
        array $data = [],
        array $addFieldStrategies = [],
        array $addFilterStrategies = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $request,
            $config,
            $storeRepository,
            $meta,
            $data,
            $addFieldStrategies,
            $addFilterStrategies
        );

        $this->helperBss = $helperBss;
        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->config = $config;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {
        if (!$this->helperBss->getConfig()) {
            return parent::getData();
        }

        $type = $this->config->getComposableTypes();
        array_push($type, \Bss\GroupedProductOption\Helper\Data::PRODUCT_TYPE_CONFIGURABLE);
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()
            ->addAttributeToFilter(
                'type_id',
                $type
            );
            if ($storeId = $this->request->getParam('current_store_id')) {
                /** @var StoreInterface $store */
                $store = $this->storeRepository->getById($storeId);
                $this->getCollection()->setStore($store);
            }
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }
}
