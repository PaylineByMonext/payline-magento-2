<?php

namespace Monext\Payline\Model\Category\Attribute\Source;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\File\Csv;
use Monext\Payline\Helper\Constants as HelperConstants;

/**
 * This class serve to map Magento Categories to Payline Categories : https://payline.atlassian.net/wiki/spaces/DT/pages/28901389/Codes+Category
 */
class CategoryMapping extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    public function __construct(
        ComponentRegistrar $componentRegistrar,
        Csv $csvReader
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->csvReader = $csvReader;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [];

            $csvFile = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, HelperConstants::MODULE_NAME) . '/fixtures/category_mapping.csv';
            $rows = $this->csvReader->getData($csvFile);

            foreach ($rows as $row) {
                $this->_options[] = ['value' => $row[0], 'label' => __($row[1])];
            }

            array_unshift($this->_options, ['value' => '', 'label' => __('Please select a category mapping...')]);
        }
        return $this->_options;
    }
}
