<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Entities;

use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\DomainEvent;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Entities\Transaction;
use Examples\BankAccount\Domain\Events\AccountOpened;
use Examples\BankAccount\Domain\Events\MoneyDeposited;
use Examples\BankAccount\Domain\Events\MoneyTransferredIn;
use Examples\BankAccount\Domain\Events\MoneyTransferredOut;
use Examples\BankAccount\Domain\Events\MoneyWithdrawn;
use Examples\BankAccount\Domain\Exceptions\InsufficientFundsException;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;
use Examples\BankAccount\Domain\ValueObjects\Money;
use Examples\BankAccount\Domain\ValueObjects\TransactionType;

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
        $event = AccountOpened::create($id, $balance);

        return new self($id, $balance, domainEvents: [$event]);
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
            [...$this->getDomainEvents(), $event]
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
            [...$this->getDomainEvents(), $event]
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
            [...$this->getDomainEvents(), $event]
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
            [...$this->getDomainEvents(), $event]
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
