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
namespace Bss\ProductCustomTabs\Model\ResourceModel\Tab\Grid;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Api\Search\SearchResultInterface;
use \Bss\ProductCustomTabs\Model\ResourceModel;

class Collection extends ResourceModel\Tab\Collection implements SearchResultInterface
{
    /**
     * Resource Model
     *
     * @var string
     */
    protected $resourceModel;

    /**
     * Event Prefix
     *
     * @var AbstractDb
     */
    protected $eventPrefix;

    /**
     * Model
     *
     * @var string
     */
    protected $model;

    /**
     * Event Object
     *
     * @var string
     */
    protected $eventObject;

    /**
     * Aggregations
     *
     * @var \Magento\Framework\Search\AggregationInterface
     */
    private $aggregations;

    /**
     * Collection constructor.
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $eventPrefix,
        $eventObject,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->eventPrefix = $eventPrefix;
        $this->eventObject = $eventObject;
        $this->model = $model;
    }

    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \Bss\ProductCustomTabs\Model\ResourceModel\Tab::class
        );
    }

    /**
     * Get Aggregations
     * @return \Magento\Framework\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Set Aggregations
     * @param \Magento\Framework\Search\AggregationInterface $aggregations
     * @return void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
