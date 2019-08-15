<?php

namespace SM\ElectronicJournal\Model;

use Magento\Framework\Model\AbstractModel;
use SM\ElectronicJournal\Api\Data\ElectronicJournalInterface;
use Magento\Framework\DataObject\IdentityInterface;
/**
 * Class ElectronicJournal
 * @package SM\ElectronicJournal\Model
 */
class ElectronicJournal extends AbstractModel
    implements ElectronicJournalInterface, IdentityInterface
{

    const CACHE_TAG = 'sm_electronic_journal';

    protected function _construct()
    {
        $this->_init('SM\ElectronicJournal\Model\ResourceModel\ElectronicJournal');
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
