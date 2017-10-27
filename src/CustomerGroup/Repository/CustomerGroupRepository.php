<?php declare(strict_types=1);

namespace Shopware\CustomerGroup\Repository;

use Shopware\Api\Read\BasicReaderInterface;
use Shopware\Api\Read\DetailReaderInterface;
use Shopware\Api\RepositoryInterface;
use Shopware\Api\Search\AggregationResult;
use Shopware\Api\Search\Criteria;
use Shopware\Api\Search\SearcherInterface;
use Shopware\Api\Search\UuidSearchResult;
use Shopware\Api\Write\GenericWrittenEvent;
use Shopware\Api\Write\WriterInterface;
use Shopware\Context\Struct\TranslationContext;
use Shopware\CustomerGroup\Event\CustomerGroupBasicLoadedEvent;
use Shopware\CustomerGroup\Event\CustomerGroupDetailLoadedEvent;
use Shopware\CustomerGroup\Event\CustomerGroupWrittenEvent;
use Shopware\CustomerGroup\Searcher\CustomerGroupSearchResult;
use Shopware\CustomerGroup\Struct\CustomerGroupBasicCollection;
use Shopware\CustomerGroup\Struct\CustomerGroupDetailCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CustomerGroupRepository implements RepositoryInterface
{
    /**
     * @var DetailReaderInterface
     */
    protected $detailReader;

    /**
     * @var BasicReaderInterface
     */
    private $basicReader;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var SearcherInterface
     */
    private $searcher;

    /**
     * @var WriterInterface
     */
    private $writer;

    public function __construct(
        DetailReaderInterface $detailReader,
        BasicReaderInterface $basicReader,
        EventDispatcherInterface $eventDispatcher,
        SearcherInterface $searcher,
        WriterInterface $writer
    ) {
        $this->detailReader = $detailReader;
        $this->basicReader = $basicReader;
        $this->eventDispatcher = $eventDispatcher;
        $this->searcher = $searcher;
        $this->writer = $writer;
    }

    public function readBasic(array $uuids, TranslationContext $context): CustomerGroupBasicCollection
    {
        if (empty($uuids)) {
            return new CustomerGroupBasicCollection();
        }

        /** @var CustomerGroupBasicCollection $collection */
        $collection = $this->basicReader->readBasic($uuids, $context);

        $this->eventDispatcher->dispatch(
            CustomerGroupBasicLoadedEvent::NAME,
            new CustomerGroupBasicLoadedEvent($collection, $context)
        );

        return $collection;
    }

    public function readDetail(array $uuids, TranslationContext $context): CustomerGroupDetailCollection
    {
        if (empty($uuids)) {
            return new CustomerGroupDetailCollection();
        }

        /** @var CustomerGroupDetailCollection $collection */
        $collection = $this->detailReader->readDetail($uuids, $context);

        $this->eventDispatcher->dispatch(
            CustomerGroupDetailLoadedEvent::NAME,
            new CustomerGroupDetailLoadedEvent($collection, $context)
        );

        return $collection;
    }

    public function search(Criteria $criteria, TranslationContext $context): CustomerGroupSearchResult
    {
        /** @var CustomerGroupSearchResult $result */
        $result = $this->searcher->search($criteria, $context);

        $this->eventDispatcher->dispatch(
            CustomerGroupBasicLoadedEvent::NAME,
            new CustomerGroupBasicLoadedEvent($result, $context)
        );

        return $result;
    }

    public function searchUuids(Criteria $criteria, TranslationContext $context): UuidSearchResult
    {
        return $this->searcher->searchUuids($criteria, $context);
    }

    public function aggregate(Criteria $criteria, TranslationContext $context): AggregationResult
    {
        $result = $this->searcher->aggregate($criteria, $context);

        return $result;
    }

    public function getEntityName(): string
    {
        return 'customer_group';
    }

    public function update(array $data, TranslationContext $context): CustomerGroupWrittenEvent
    {
        $event = $this->writer->update($data, $context);

        $container = new GenericWrittenEvent($event, $context);
        $this->eventDispatcher->dispatch($container::NAME, $container);

        return $event;
    }

    public function upsert(array $data, TranslationContext $context): CustomerGroupWrittenEvent
    {
        $event = $this->writer->upsert($data, $context);

        $container = new GenericWrittenEvent($event, $context);
        $this->eventDispatcher->dispatch($container::NAME, $container);

        return $event;
    }

    public function create(array $data, TranslationContext $context): CustomerGroupWrittenEvent
    {
        $event = $this->writer->create($data, $context);

        $container = new GenericWrittenEvent($event, $context);
        $this->eventDispatcher->dispatch($container::NAME, $container);

        return $event;
    }
}
