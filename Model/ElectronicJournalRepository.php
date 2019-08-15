<?php

namespace SM\ElectronicJournal\Model;

use SM\ElectronicJournal\Api\ElectronicJournalRepositoryInterface;
use SM\ElectronicJournal\Api\Data\ElectronicJournalInterface;
use SM\ElectronicJournal\Model\ElectronicJournalFactory;
use SM\ElectronicJournal\Model\ResourceModel\ElectronicJournal\CollectionFactory;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchResultsInterfaceFactory;

/**
 * Class ElectronicJournalRepository
 * @package SM\ElectronicJournal\Model
 */
class ElectronicJournalRepository implements ElectronicJournalRepositoryInterface
{
    /**
     * @var \SM\ElectronicJournal\Model\ElectronicJournalFactory
     */
    protected $objectFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * ElectronicJournalRepository constructor.
     * @param \SM\ElectronicJournal\Model\ElectronicJournalFactory $objectFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ElectronicJournalFactory $objectFactory,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    )
    {
        $this->objectFactory        = $objectFactory;
        $this->collectionFactory    = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param ElectronicJournalInterface $object
     * @return mixed|ElectronicJournalInterface
     * @throws CouldNotSaveException
     */
    public function save(ElectronicJournalInterface $object)
    {
        try {
            $object->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $object;
    }

    /**
     * @param $id
     * @return mixed|ElectronicJournal
     * @throws NoSuchEntityException
     */
    public function getById($id) {
        $object = $this->objectFactory->create();
        $object->load($id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }

        return $object;
    }

    /**
     * @param ElectronicJournalInterface $object
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(ElectronicJournalInterface $object)
    {
        try {
            $object->delete();
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * @param $id
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return \Magento\Framework\Api\SearchResultsInterface|mixed
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->getCollectionFactory($criteria);

        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($objects);

        return $searchResults;
    }

    /**
     * @param $criteria
     *
     * @return mixed
     */
    private function getCollectionFactory($criteria)
    {
        $collection = $this->collectionFactory->create();

        foreach ($criteria->getFilterGroups() as $filterGroup) {
            list($fields, $conditions) = $this->prepareFilterCollection($filterGroup);
            $collection = $this->addFilterCollection($collection, $fields, $conditions);
        }

        $collection = $this->addSortCollection($collection, $criteria);
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        return $collection;
    }

    /**
     * @param $collection
     * @param $criteria
     *
     * @return mixed
     */
    private function addSortCollection($collection, $criteria) {
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var sortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        return $collection;
    }

    /**
     * @param $filterGroup
     *
     * @return array
     */
    private function prepareFilterCollection($filterGroup) {
        $fields     = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition    = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[]     = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        return [$fields, $conditions];
    }

    /**
     * @param $collection
     * @param $fields
     * @param $conditions
     *
     * @return mixed
     */
    private function addFilterCollection($collection, $fields, $conditions) {
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
        return $collection;
    }
}
