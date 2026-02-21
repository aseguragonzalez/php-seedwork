<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Entities;

use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\DomainEvent;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Entities\Transaction;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyDeposited;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyTransferredIn;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyTransferredOut;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyWithdrawn;
use Tests\Fixtures\BankAccount\Domain\Exceptions\InsufficientFundsException;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\AccountBalance;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\TransactionType;

/**
 * @extends AggregateRoot<BankAccountId>
 */
final readonly class BankAccount extends AggregateRoot
{
    /**
     * @param array<Transaction> $transactions
     * @param array<DomainEvent> $domainEvents
     */
    private function __construct(
        BankAccountId $id,
        private AccountBalance $balance,
        private array $transactions = [],
        array $domainEvents = []
    ) {
        parent::__construct($id, $domainEvents);
    }

    protected function validate(): void
    {
    }

    public static function create(?BankAccountId $id = null, ?AccountBalance $initialBalance = null): self
    {
        $id = $id ?? BankAccountId::create();
        $balance = $initialBalance ?? AccountBalance::zero();

        return new self($id, $balance);
    }

    /**
     * @param array<Transaction> $transactions
     */
    public static function build(
        BankAccountId $id,
        AccountBalance $balance,
        array $transactions
    ): self {
        return new self($id, $balance, $transactions, domainEvents: []);
    }

    public function withdraw(Money $amount): self
    {
        $this->assertSameCurrency($amount);

        if ($this->balance->amount < $amount->amount) {
            throw InsufficientFundsException::forWithdrawal($this->balance->amount, $amount->amount);
        }

        $newBalance = new AccountBalance(
            $this->balance->amount - $amount->amount,
            $this->balance->currency
        );
        $transaction = Transaction::create(TransactionType::WITHDRAWAL, $amount);
        $event = MoneyWithdrawn::create($amount, $this->id, $transaction->id);

        return new self(
            $this->id,
            $newBalance,
            [...$this->transactions, $transaction],
            [...$this->collectEvents(), $event]
        );
    }

    public function deposit(Money $amount): self
    {
        $this->assertSameCurrency($amount);

        $newBalance = new AccountBalance(
            $this->balance->amount + $amount->amount,
            $this->balance->currency
        );
        $transaction = Transaction::create(TransactionType::DEPOSIT, $amount);
        $event = MoneyDeposited::create($amount, $this->id, $transaction->id);

        return new self(
            $this->id,
            $newBalance,
            [...$this->transactions, $transaction],
            [...$this->collectEvents(), $event]
        );
    }

    public function transferOut(Money $amount, BankAccountId $toAccountId): self
    {
        $this->assertSameCurrency($amount);

        if ($this->balance->amount < $amount->amount) {
            throw InsufficientFundsException::forWithdrawal($this->balance->amount, $amount->amount);
        }

        $newBalance = new AccountBalance(
            $this->balance->amount - $amount->amount,
            $this->balance->currency
        );
        $transaction = Transaction::create(TransactionType::TRANSFER_OUT, $amount);
        $event = MoneyTransferredOut::create($amount, $this->id, $toAccountId, $transaction->id);

        return new self(
            $this->id,
            $newBalance,
            [...$this->transactions, $transaction],
            [...$this->collectEvents(), $event]
        );
    }

    public function transferIn(Money $amount, BankAccountId $fromAccountId): self
    {
        $this->assertSameCurrency($amount);

        $newBalance = new AccountBalance(
            $this->balance->amount + $amount->amount,
            $this->balance->currency
        );
        $transaction = Transaction::create(TransactionType::TRANSFER_IN, $amount);
        $event = MoneyTransferredIn::create($amount, $this->id, $fromAccountId, $transaction->id);

        return new self(
            $this->id,
            $newBalance,
            [...$this->transactions, $transaction],
            [...$this->collectEvents(), $event]
        );
    }

    public function getBalance(): AccountBalance
    {
        return $this->balance;
    }

    /**
     * @return array<Transaction>
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    private function assertSameCurrency(Money $amount): void
    {
        if ($this->balance->currency !== $amount->currency) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Currency mismatch: account uses %s, operation uses %s',
                    $this->balance->currency->value,
                    $amount->currency->value
                )
            );
        }
    }
}
