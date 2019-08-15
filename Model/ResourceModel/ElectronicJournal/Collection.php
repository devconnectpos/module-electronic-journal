<?php

namespace SM\ElectronicJournal\Model\ResourceModel\ElectronicJournal;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package SM\ElectronicJournal\Model\ResourceModel\ElectronicJournal
 */
class Collection extends AbstractCollection
{

    protected function _construct()
    {
        $this->_init('SM\ElectronicJournal\Model\ElectronicJournal', 'SM\ElectronicJournal\Model\ResourceModel\ElectronicJournal');
    }
}
