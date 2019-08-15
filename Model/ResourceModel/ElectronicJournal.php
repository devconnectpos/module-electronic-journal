<?php

namespace SM\ElectronicJournal\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
/**
 * Class ElectronicJournal
 * @package SM\ElectronicJournal\Model\ResourceModel
 */
class ElectronicJournal extends AbstractDb
{

    protected function _construct()
    {
        $this->_init('sm_electronic_journal', 'id');
    }
}
