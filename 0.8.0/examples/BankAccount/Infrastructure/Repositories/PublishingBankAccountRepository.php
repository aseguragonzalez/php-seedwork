<?php

declare(strict_types=1);

namespace Examples\BankAccount\Infrastructure\Repositories;

use SeedWork\Application\DomainEventBusPublisher;
use SeedWork\Infrastructure\DomainEventPublishingRepository;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;

final class PublishingBankAccountRepository extends DomainEventPublishingRepository implements BankAccountRepository
{
    public function __construct(
        BankAccountRepository $repository,
        DomainEventBusPublisher $eventBus,
    ) {
        parent::__construct($repository, $eventBus);
    }
}
