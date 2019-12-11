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

namespace Bss\GroupedProductOption\Framework\HTTP\Adapter;

use Bss\GroupedProductOption\Model\File\Transfer\Adapter\HttpFactory;

class FileTransferFactory
{
    /**
     * @var \Bss\GroupedProductOption\Model\File\Transfer\Adapter\Http
     */
    private $http;

    /**
     * FileTransferFactory constructor.
     * @param HttpFactory $http
     */
    public function __construct(
        HttpFactory $http
    ) {
        $this->http = $http;
    }

    /**
     * Create HTTP adapter
     *
     * @param array $options
     * @return mixed
     */
    public function create(array $options = [])
    {
        return $this->http->create()->setOptions($options);
    }
}
