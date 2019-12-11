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

namespace Bss\GroupedProductOption\Model\File\Transfer\Adapter;

class Http extends \Zend_File_Transfer_Adapter_Http
{
    /**
     * Registry model.
     *
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Bss\GroupedProductOption\Model\Validate\File\Upload
     */
    private $upload;

    /**
     * Constructor for Http File Transfers
     *
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\GroupedProductOption\Model\Validate\File\Upload $upload
     * @param array $options OPTIONAL Options to set
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Bss\GroupedProductOption\Model\Validate\File\Upload $upload,
        $options = []
    ) {
        $this->registry = $registry;
        $this->upload = $upload;
        parent::__construct($options);
    }

    protected function _prepareFiles()
    {
        $this->_files = [];
        $productId = $this->registry->registry('bss-gpo-group-add');
        if ($productId && $this->registry->registry('bss-gpo-group')) {
            $params = $this->upload->getFiles();
            $name = 'bss-gpo-option-' . $productId;
            $result = [];
            if (isset($params[$name])) {
                foreach ($params[$name] as $key => $value) {
                    foreach ($value as $resultKey => $resultValue) {
                        $result[$resultKey][$key] = $resultValue;
                    }
                }
                foreach ($result as $form => $content) {
                    if (is_array($content['name'])) {
                        foreach ($content as $param => $file) {
                            foreach ($file as $number => $target) {
                                $this->_files[$form . '_' . $number . '_'][$param]      = $target;
                                $this->_files[$form]['multifiles'][$number] = $form . '_' . $number . '_';
                            }
                        }

                        $this->_files[$form]['name'] = $form;
                        foreach ($this->_files[$form]['multifiles'] as $key => $value) {
                            $this->_files[$value]['options']   = $this->_options;
                            $this->_files[$value]['validated'] = false;
                            $this->_files[$value]['received']  = false;
                            $this->_files[$value]['filtered']  = false;

                            $mimetype = $this->_detectMimeType($this->_files[$value]);
                            $this->_files[$value]['type'] = $mimetype;

                            $filesize = $this->_detectFileSize($this->_files[$value]);
                            $this->_files[$value]['size'] = $filesize;
                        }
                    } else {
                        $this->_files[$form]              = $content;
                        $this->_files[$form]['options']   = $this->_options;
                        $this->_files[$form]['validated'] = false;
                        $this->_files[$form]['received']  = false;
                        $this->_files[$form]['filtered']  = false;

                        $mimetype = $this->_detectMimeType($this->_files[$form]);
                        $this->_files[$form]['type'] = $mimetype;

                        $filesize = $this->_detectFileSize($this->_files[$form]);
                        $this->_files[$form]['size'] = $filesize;
                    }
                }

                return $this;
            }
        } else {
            return parent::_prepareFiles();
        }
    }
}
