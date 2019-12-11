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
namespace Bss\ProductCustomTabs\Controller\Adminhtml\Export;

use Bss\ProductCustomTabs\Model\ResourceModel;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList as FileDirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class GridToXml
 *
 * @package Bss\ProductCustomTabs\Controller\Adminhtml\Export
 */
class GridToXml extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var
     */
    protected $varDirectory;

    /**
     * @var File
     */
    protected $io;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var ResourceModel\Tab\CollectionFactory
     */
    protected $tabCollectionFactory;

    /**
     * @var \Magento\Framework\Data\Form\Filter\Escapehtml
     */
    protected $escapehtml;

    /**
     * GridToXml constructor.
     * @param Action\Context $context
     * @param Filter $filter
     * @param Filesystem $filesystem
     * @param RawFactory $resultRawFactory
     * @param Filesystem\Driver\File $file
     * @param File $io
     * @param FileFactory $fileFactory
     * @param ResourceModel\Tab\CollectionFactory $tabCollectionFactory
     * @param \Magento\Framework\Data\Form\Filter\Escapehtml $escapehtml
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        Filesystem $filesystem,
        RawFactory $resultRawFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        File $io,
        FileFactory $fileFactory,
        ResourceModel\Tab\CollectionFactory $tabCollectionFactory,
        \Magento\Framework\Data\Form\Filter\Escapehtml $escapehtml
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->resultRawFactory = $resultRawFactory;
        $this->io = $io;
        $this->fileFactory = $fileFactory;
        $this->tabCollectionFactory = $tabCollectionFactory;
        $this->escapehtml = $escapehtml;
    }
    /**
     * Convert to xml
     *
     * @return mixed
     */
    public function execute()
    {
        try {
            $component = $this->filter->getComponent();
            $params = $this->getRequest()->getParams();
            $selceted  = isset($params['selected']) ? $params['selected'] : 1;

            if (!(int) $selceted) {
                $dataProvider = $component->getContext()->getDataProvider()->getData();
                $data = isset($dataProvider['items']) ? $dataProvider['items'] : [];
            } else {
                $collection = $this->filter->getCollection($this->tabCollectionFactory->create());
                foreach ($collection as $item) {
                    $data[] = $item->getData();
                }
            }

            $this->varDirectory = $this->filesystem
                ->getDirectoryWrite(FileDirectoryList::VAR_DIR);
            $dir = $this->varDirectory->getAbsolutePath('bss/export');
            $this->io->mkdir($dir, 0775);
            $outputFile = $dir . "/Bss_Tab_List" . ".xml";
            $fileName = "Bss_Tab_List" . ".xml";
            // @codingStandardsIgnoreStart
            $fileHandler = fopen($outputFile, 'w');
            $dataConvert = $this->convertData($data);
            $dataXml = $this->convert($dataConvert, $component->getName() . '.xml');
            $this->file->fileWrite($fileHandler, $dataXml);

            fclose($fileHandler);
            // @codingStandardsIgnoreEnd
            $this->fileFactory->create(
                $fileName,
                [
                    'type'  => "filename",
                    'value' => "bss/export/" . $fileName,
                    'rm'    => true,
                ],
                FileDirectoryList::VAR_DIR,
                'text/csv',
                null
            );
            $resultRaw = $this->resultRawFactory->create();
            return $resultRaw;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/index',
            ['_secure'=>$this->getRequest()->isSecure()]
        );
    }

    /**
     * Convert Data
     *
     * @param array $items
     * @return array
     */
    protected function convertData($items)
    {
        $data[0] = [
            'ID',
            'Status',
            'Tab Title',
            'Position',
            'Visible for Purchasing Customers Only'
        ];
        $row = [];
        if (!empty($items)) {
            foreach ($items as $item) {
                $row['ID'] = $item['id'];
                $row['Status'] = $item['status'];
                $row['Tab Title'] = $item['title'];
                $row['Position'] = $item['position'];
                $row['Visible for Purchasing Customers Only'] = $item['visible_customer_purchased'];
                $data[] = $row;
            }
        }
        return $data;
    }

    /**
     * Convert Data to Excel XML Document
     *
     * @param array $data
     * @param string $sheetName
     * @return string
     */
    protected function convert($data, $sheetName = '')
    {
        $xml = $this->getXmlHeader($sheetName);

        foreach ($data as $dataRow) {
            $xml .= $this->getXmlRow($dataRow);
        }
        $xml .= $this->getXmlFooter();

        return $xml;
    }

    /**
     * Retrieve Excel XML Document Header XML Fragment
     *
     * @param string $sheetName
     * @return string
     */
    protected function getXmlHeader($sheetName = '')
    {
        if (empty($sheetName)) {
            $sheetName = 'Sheet 1';
        }

        $sheetName = $this->escapehtml->outputFilter($sheetName);

        $xmlHeader = '<' .
            '?xml version="1.0"?' .
            '><' .
            '?mso-application progid="Excel.Sheet"?' .
            '><Workbook' .
            ' xmlns="urn:schemas-microsoft-com:office:spreadsheet"' .
            ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
            ' xmlns:x="urn:schemas-microsoft-com:office:excel"' .
            ' xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml"' .
            ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' .
            ' xmlns:o="urn:schemas-microsoft-com:office:office"' .
            ' xmlns:html="http://www.w3.org/TR/REC-html40"' .
            ' xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet">' .
            '<OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">' .
            '</OfficeDocumentSettings>' .
            '<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">' .
            '</ExcelWorkbook>' .
            '<Worksheet ss:Name="' .
            $sheetName .
            '">' .
            '<Table>';

        return $xmlHeader;
    }

    /**
     * Retrieve Excel XML Document Footer XML Fragment
     *
     * @return string
     */
    protected function getXmlFooter()
    {
        $xmlFooter = '';

        $xmlFooter .= '</Table></Worksheet></Workbook>';

        return $xmlFooter;
    }

    /**
     * Get a Single XML Row
     *
     * @param array $row
     * @return string
     */
    protected function getXmlRow($row)
    {
        $xmlData = [];
        $xmlData[] = '<Row>';

        foreach ($row as $value) {
            $value = $this->escapehtml->outputFilter($value);
            $dataType = is_numeric($value) && $value[0] !== '+' && $value[0] !== '0' ? 'Number' : 'String';

            /**
             * Security enhancement for CSV data processing by Excel-like applications.
             * @see https://bugzilla.mozilla.org/show_bug.cgi?id=1054702
             *
             * @var $value string|\Magento\Framework\Phrase
             */
            if (!is_string($value)) {
                $value = (string)$value;
            }
            if (isset($value[0]) && in_array($value[0], ['=', '+', '-'])) {
                $value = ' ' . $value;
                $dataType = 'String';
            }

            $value = str_replace("\r\n", '&#10;', $value);
            $value = str_replace("\r", '&#10;', $value);
            $value = str_replace("\n", '&#10;', $value);

            $xmlData[] = '<Cell><Data ss:Type="' . $dataType . '">' . $value . '</Data></Cell>';
        }
        $xmlData[] = '</Row>';

        return join('', $xmlData);
    }
}
