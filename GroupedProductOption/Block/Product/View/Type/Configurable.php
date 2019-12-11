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

namespace Bss\GroupedProductOption\Block\Product\View\Type;

use Magento\Swatches\Block\Product\Renderer\Configurable as ConfigurableSwatches;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\Json\EncoderInterface;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Bss\GroupedProductOption\Helper\Data as GroupedProductOptionHelper;

class Configurable extends ConfigurableSwatches
{
    const GROUPED_CONFIGURABLE_SWATCHES_TEMPLATE = 'product/view/type/configurable/renderer.phtml';

    const GROUPED_CONFIGURABLE_TEMPLATE = 'product/view/type/configurable/configurable.phtml';

    /**
     * @var GroupedProductOptionHelper
     */
    private $gpoHelper;

    /**
     * Configurable constructor.
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param GroupedProductOptionHelper $gpoHelper
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        GroupedProductOptionHelper $gpoHelper
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper
        );
        $this->gpoHelper = $gpoHelper;
    }

    /**
     * Return renderer template wholesale
     *
     * @return string
     */
    public function getRendererTemplate()
    {
        if ($this->getMagentoVersion('2.1.6')) {
            $hasSwatch = $this->isProductHasSwatchAttribute();
        } else {
            $hasSwatch = $this->isProductHasSwatchAttribute;
        }
        if ($hasSwatch) {
            return self::GROUPED_CONFIGURABLE_SWATCHES_TEMPLATE;
        } else {
            return self::GROUPED_CONFIGURABLE_TEMPLATE;
        }
    }

    /**
     * @return string
     */
    public function getJsonGroupedConfigurable()
    {
        $config = [];
        $config['productId'] = $this->getProduct()->getId();
        return $this->jsonEncoder->encode($config);
    }

    /**
     *  Compare magento version
     *
     * @param string $version
     * @return bool
     */
    public function getMagentoVersion($version)
    {
        return $this->gpoHelper->getMagentoVersion($version);
    }
}
