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

namespace Bss\GroupedProductOption\Plugin\Model\Product\Option\Type\File;

class ValidatorFile extends \Magento\Catalog\Model\Product\Option\Type\File\ValidatorFile
{
    /**
     * @var \Bss\GroupedProductOption\Framework\HTTP\Adapter\FileTransferFactory
     */
    private $httpBssGpoFactory;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory
     */
    protected $httpFactory;

    /**
     * Registry model.
     *
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * ValidatorFile constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory
     * @param \Magento\Framework\Validator\File\IsImage $isImageValidator
     * @param \Bss\GroupedProductOption\Framework\HTTP\Adapter\FileTransferFactory $httpBssGpoFactory
     * @param \Magento\Framework\Registry $registry
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\Framework\Validator\File\IsImage $isImageValidator,
        \Bss\GroupedProductOption\Framework\HTTP\Adapter\FileTransferFactory $httpBssGpoFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->httpFactory = $httpFactory;
        $this->httpBssGpoFactory = $httpBssGpoFactory;
        $this->registry = $registry;
        parent::__construct(
            $scopeConfig,
            $filesystem,
            $fileSize,
            $httpFactory,
            $isImageValidator
        );
    }

    public function beforeValidate($subject, $processingParams, $option)
    {
        if ($this->registry->registry('bss-gpo-group')) {
            $subject->httpFactory = $this->httpBssGpoFactory;
        }
        return [$processingParams, $option];
    }
}
