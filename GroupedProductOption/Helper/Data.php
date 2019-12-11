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

namespace Bss\GroupedProductOption\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PRODUCT_TYPE_CONFIGURABLE = 'configurable';
    const PRODUCT_TYPE_BUNDLE = 'bundle';
    const PRODUCT_TYPE_GROUPED = 'grouped';

    /**
     * Product drop-down option type.
     */
    const OPTION_TYPE_DROP_DOWN = 'drop_down';

    /**
     * Product multiple option type.
     */
    const OPTION_TYPE_MULTIPLE = 'multiple';

    /**
     * Product radio option type.
     */
    const OPTION_TYPE_RADIO = 'radio';

    /**
     * Product checkbox option type.
     */
    const OPTION_TYPE_CHECKBOX = 'checkbox';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata
    ) {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
    }

    /**
     * Get config values
     *
     * @param string $field
     * @return bool|string
     */
    public function getConfig($field = 'active')
    {
        if (!$this->scopeConfig->getValue(
            'groupedproductoption/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return false;
        }

        $result = $this->scopeConfig->getValue(
            'groupedproductoption/general/'.$field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($result) {
            return $result;
        }

        return false;
    }

    /**
     *  Compare magento version
     *
     * @param string $version
     * @return bool
     */
    public function getMagentoVersion($version)
    {
        $dataVersion = $this->productMetadata->getVersion();
        if (version_compare($dataVersion, $version) >= 0) {
            return true;
        }
        return false;
    }
}
