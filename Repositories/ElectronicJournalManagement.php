<?php

namespace SM\ElectronicJournal\Repositories;

use Exception;
use Magento\Framework\DataObject;
use SM\Core\Api\Data\ElectronicJournal;
use SM\XRetail\Helper\Data;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use Magento\Framework\App\RequestInterface;
use SM\XRetail\Helper\DataConfig;
use Magento\Store\Model\StoreManagerInterface;
use SM\ElectronicJournal\Model\ResourceModel\ElectronicJournal\CollectionFactory;
use SM\ElectronicJournal\Model\ElectronicJournalFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class ElectronicJournalManagement
 * @package SM\ElectronicJournal\Repositories
 */
class ElectronicJournalManagement extends ServiceAbstract
{

    /**
     * @var CollectionFactory
     */
    protected $electronicJournalCollectionFactory;

    /**
     * @var ElectronicJournalFactory
     */
    protected $electronicJournalFactory;

    /**
     * @var Data
     */
    private $retailHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * ElectronicJournalManagement constructor.
     * @param RequestInterface $requestInterface
     * @param DataConfig $dataConfig
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $electronicJournalCollectionFactory
     * @param ElectronicJournalFactory $electronicJournalFactory
     * @param Data $retailHelper
     * @param ResourceConnection $resource
     */
    public function __construct(
        RequestInterface $requestInterface,
        DataConfig $dataConfig,
        StoreManagerInterface $storeManager,
        CollectionFactory $electronicJournalCollectionFactory,
        ElectronicJournalFactory $electronicJournalFactory,
        Data $retailHelper,
        ResourceConnection $resource
    )
    {
        $this->electronicJournalFactory           = $electronicJournalFactory;
        $this->electronicJournalCollectionFactory = $electronicJournalCollectionFactory;
        $this->retailHelper                       = $retailHelper;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getElectronicJournalData()
    {
        return $this->load($this->getSearchCriteria())->getOutput();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function save()
    {
        $data = $this->getRequestData();
        /** @var \SM\ElectronicJournal\Model\ElectronicJournal $electronicJournal */
        try {
            $tableName = $this->resource->getTableName('sm_electronic_journal');
            return $this->connection->insertMultiple($tableName, $data->getData('electronicJournal'));
        } catch (\Exception $e) {
            //Error
        }
    }

    /**
     * @throws Exception
     */
    public function delete()
    {
        $data = $this->getRequestData();
        if ($id = $data->getData('id')) {
            /** @var \SM\Sales\Model\Feedback $feedback */
            $electronicJournal = $this->electronicJournalFactory->create();
            $electronicJournal->load($id);
            if (!$electronicJournal->getId()) {
                throw new \Exception("Can not find electronic journal data");
            } else {
                $electronicJournal->delete();
            }
        } else {
            throw new \Exception("Please define id");
        }
    }

    /**
     * @param DataObject $searchCriteria
     * @return \SM\Core\Api\SearchResult
     * @throws Exception
     */
    public function load(DataObject $searchCriteria)
    {
        if (is_null($searchCriteria) || !$searchCriteria) {
            $searchCriteria = $this->getSearchCriteria();
        }

        $collection = $this->getElectronicJournalCollection($searchCriteria);

        $items = [];
        if ($collection->getLastPageNumber() >= $searchCriteria->getData('currentPage')) {
            $storeId = $searchCriteria->getData('storeId');
            if (is_null($storeId)) {
                $outletId = is_null($searchCriteria->getData("outletId"))
                    ? $searchCriteria->getData("outlet_id")
                    : $searchCriteria->getData(
                        "outletId");
                $storeId = $this->retailHelper->getStoreByOutletId($outletId);
            }

            foreach ($collection as $item) {
                $i = new ElectronicJournal($item->getData());
                $i['created_at'] = $this->retailHelper->convertTimeDBUsingTimeZone($i->getData('created_at'), $storeId);
                $items[] = $i;
            }
        }

        return $this->getSearchResult()
            ->setSearchCriteria($searchCriteria)
            ->setItems($items)
            ->setTotalCount($collection->getSize())
            ->setLastPageNumber($collection->getLastPageNumber());
    }

    /**
     * @param DataObject $searchCriteria
     * @return \SM\ElectronicJournal\Model\ResourceModel\ElectronicJournal\Collection
     * @throws Exception
     */
    public function getElectronicJournalCollection(DataObject $searchCriteria)
    {
        $storeId = $searchCriteria->getData('storeId');
        if (is_null($storeId)) {
            $outletId = is_null($searchCriteria->getData("outletId"))
                ? $searchCriteria->getData("outlet_id")
                : $searchCriteria->getData(
                    "outletId");
            $storeId  = $this->retailHelper->getStoreByOutletId($outletId);
        }
        /** @var \SM\ElectronicJournal\Model\ResourceModel\ElectronicJournal\Collection $collection */
        $collection = $this->electronicJournalCollectionFactory->create();
        $outletId    = $searchCriteria->getData('outletId');
        if (is_null($outletId)) {
            throw new Exception("Please define outletId when pull electronic journal");
        }

        $collection->addFieldToFilter('outlet_id', $outletId);

        $registerId    = $searchCriteria->getData('registerId');
        if (is_null($registerId)) {
            throw new Exception("Please define registerId when pull electronic journal");
        }

        $collection->addFieldToFilter('register_id', $registerId);

        if ($dateFrom = $searchCriteria->getData('dateFrom')) {
            $dateFromUTC = $this->retailHelper->convertTimeDBUsingTimeZoneToUTC($dateFrom, $storeId);
            $collection->getSelect()
                ->where('created_at >= ?', $dateFromUTC);
        }
        if ($dateTo = $searchCriteria->getData('dateTo')) {
            $dateToUTC = $this->retailHelper->convertTimeDBUsingTimeZoneToUTC($dateTo, $storeId);
            $collection->getSelect()
                ->where('created_at <= ?', $dateToUTC);
        }

        if ($searchCriteria->getData('ids')) {
            $collection->addFieldToFilter('id', ['in' => explode(",", $searchCriteria->getData('ids'))]);
        }

        if (!is_null($searchCriteria->getData('searchEventType'))
            && $searchCriteria->getData('searchEventType') !== 'null'
            && $searchCriteria->getData('searchEventType') !== 'all'
            && $searchCriteria->getData('searchEventType') !== '') {
            $collection->addFieldToFilter('event_type', ['in' => explode(",", $searchCriteria->getData('searchEventType'))]);
        }

        if (!is_null($searchCriteria->getData('searchEmployee'))
            && $searchCriteria->getData('searchEmployee') !== 'null'
            && $searchCriteria->getData('searchEmployee') !== 'all'
            && $searchCriteria->getData('searchEmployee') !== '') {
            $collection->addFieldToFilter('employee_id', ['in' => explode(",", $searchCriteria->getData('searchEmployee'))]);
        }

        if ($searchString = $searchCriteria->getData('searchString')) {
            $fieldSearch      = ['event_type', 'message', 'employee_username'];
            $fieldSearchValue = [
                ['like' => '%' . $searchString . '%'],
                ['like' => '%' . $searchString . '%'],
                ['like' => '%' . $searchString . '%']
            ];
            $collection->addFieldToFilter($fieldSearch, $fieldSearchValue);
        }
        if (is_nan($searchCriteria->getData('pageSize'))) {
            $collection->setPageSize(
                DataConfig::PAGE_SIZE_LOAD_DATA
            );
        } else {
            $collection->setPageSize(
                $searchCriteria->getData('pageSize')
            );
        }
        if (is_nan($searchCriteria->getData('currentPage'))) {
            $collection->setCurPage(1);
        } else {
            $collection->setCurPage($searchCriteria->getData('currentPage'));
        }
        return $collection;
    }

    /**
     * @param $electronicJournalCollectionFactory
     * @return $this
     */
    public function setElectronicJournalCollectionFactory($electronicJournalCollectionFactory)
    {
        $this->electronicJournalCollectionFactory = $electronicJournalCollectionFactory;
        return $this;
    }

}
