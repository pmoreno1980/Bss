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
namespace Bss\ProductCustomTabs\Block\Adminhtml\Tab\Edit\Tab\Attributes;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class AssignAttributes extends \Magento\Backend\Block\Template implements RendererInterface
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'tab/attribute/assign_attributes.phtml';

    /**
     * Block Grid
     *
     * @var \Bss\ProductCustomTabs\Block\Adminhtml\Tab\Edit\Tab\Attributes
     */
    protected $blockGrid;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Encoder Interface
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Bss\ProductCustomTabs\Block\Adminhtml\Tab\Edit\Tab\Attributes::class
            );
        }
        return $this->blockGrid;
    }

    /**
     * Get Grid Html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * Get Products Json
     *
     * @return string
     */
    public function getProductsJson()
    {

        if ($this->getTab()) {
            $attributes = $this->getTab()->getArrAttributeIds();
            $attributes = array_flip($attributes);
            if (!empty($attributes)) {
                return $this->jsonEncoder->encode($attributes);
            }
        }
        return '{}';
    }

    /**
     * Render element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

    /**
     * Retrieve current Tab
     *
     * @return array|null
     */
    public function getTab()
    {
        return $this->registry->registry('tab');
    }
}
