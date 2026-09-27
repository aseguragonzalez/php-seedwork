<?php

declare(strict_types=1);

namespace Examples\BankAccount\Infrastructure\Repositories;

use Examples\BankAccount\Domain\Repositories\BankAccountRepository;
use SeedWork\Application\DomainEventBusPublisher;
use SeedWork\Infrastructure\DomainEventPublishingRepository;

final class PublishingBankAccountRepository extends DomainEventPublishingRepository implements BankAccountRepository
{
    public function __construct(
        BankAccountRepository $repository,
        DomainEventBusPublisher $eventBus,
    ) {
        parent::__construct($repository, $eventBus);
    }
}
