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
 * @package    BSS_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductCustomTabs\Plugin\Tab\Grid;

use \Magento\Framework\App\Request\Http;

class Collection
{

    /**
     * @var Http $request
     */
    protected $request;

    /**
     * Collection constructor.
     * @param Http $request
     */
    public function __construct(Http $request)
    {
        $this->request = $request;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetItems(
        $subject,
        $result
    ) {
        $items = $result;
        $controller = $this->request->getFullActionName();
        if ($controller == 'mui_export_gridToCsv' ||
            $controller == 'mui_export_gridToXml'
        ) {
            $storeId = $this->request->getParam('store') ? $this->request->getParam('store') : 0;
            foreach ($items as $key => $tab) {
                if ($tab->getData('store_id') != $storeId) {
                    $subject->removeItemByKey($key);
                }

            }
        }
        return $items;
    }
}
