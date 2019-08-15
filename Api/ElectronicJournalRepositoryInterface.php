<?php

namespace SM\ElectronicJournal\Api;

use SM\ElectronicJournal\Api\Data\ElectronicJournalInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface ElectronicJournalRepositoryInterface
 * @package SM\ElectronicJournal\Api
 */
interface ElectronicJournalRepositoryInterface
{

    /**
     * @param ElectronicJournalInterface $page
     * @return mixed
     */
    public function save(ElectronicJournalInterface $page);

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id);

    /**
     * @param SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * @param ElectronicJournalInterface $page
     * @return mixed
     */
    public function delete(ElectronicJournalInterface $page);

    /**
     * @param $id
     * @return mixed
     */
    public function deleteById($id);
}
