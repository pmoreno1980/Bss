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
 * @package    Bss_ProductQuestions
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductCustomTabs\Ui\DataProvider\Product;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Bss\ProductCustomTabs\Model\ResourceModel\Tab\CollectionFactory;

class CustomTabs extends AbstractDataProvider
{
    /**
     * CollectionFactory
     *
     * @var CollectionFactory $collectionFactory
     */
    protected $collectionFactory;

    /**
     * RequestInterface
     *
     * @var RequestInterface $request
     */
    protected $request;

    /**
     * UrlInterface
     *
     * @var UrlInterface $urlBuilder
     */
    protected $urlBuilder;

    /**
     * Questions constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get Data
     *
     * @return array
     */
    public function getData()
    {
        /**
         * Set storeId to update url.
         */
        $storeId = (int) $this->request->getParam('store') ? (int) $this->request->getParam('store') : 0;
        $configData = $this->getConfigData();
        $configData['update_url'] = $this->urlBuilder->getUrl(
            'mui/index/render',
            [
                'store' => $storeId
            ]
        );
        $this->setConfigData($configData);
        $items = $this->getCollection()->toArray();
        return $items;
    }

    /**
     * Get Collection
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        $storeId = (int) $this->request->getParam('store') ? (int) $this->request->getParam('store') : 0;
        if (!isset($this->collection)) {
            $this->collection = $this->collectionFactory->create()->filterTabByStore($storeId);
        }
        return $this->collection;
    }
}
