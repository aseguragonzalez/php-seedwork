<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Entities;

use Seedwork\Domain\AggregateRoot;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Entities\Transaction;
use Tests\Fixtures\BankAccount\Domain\Entities\TransactionId;
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
     * @param array<\Seedwork\Domain\DomainEvent> $domainEvents
     */
    public function __construct(
        BankAccountId $id,
        private AccountBalance $balance,
        private array $transactions = [],
        array $domainEvents = []
    ) {
        parent::__construct($id, $domainEvents);
    }

    public static function create(BankAccountId $id, ?AccountBalance $initialBalance = null): self
    {
        $balance = $initialBalance ?? AccountBalance::zero();

        return new self($id, $balance, []);
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
        $transactionId = TransactionId::generate();
        $transaction = new Transaction(
            $transactionId,
            TransactionType::WITHDRAWAL,
            $amount,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            null
        );
        $event = new MoneyWithdrawn($this->id, $amount, $transactionId);

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
        $transactionId = TransactionId::generate();
        $transaction = new Transaction(
            $transactionId,
            TransactionType::DEPOSIT,
            $amount,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            null
        );
        $event = new MoneyDeposited($this->id, $amount, $transactionId);

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
        $transactionId = TransactionId::generate();
        $transaction = new Transaction(
            $transactionId,
            TransactionType::TRANSFER_OUT,
            $amount,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            $toAccountId
        );
        $event = new MoneyTransferredOut($this->id, $toAccountId, $amount, $transactionId);

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
        $transactionId = TransactionId::generate();
        $transaction = new Transaction(
            $transactionId,
            TransactionType::TRANSFER_IN,
            $amount,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            $fromAccountId
        );
        $event = new MoneyTransferredIn($this->id, $fromAccountId, $amount, $transactionId);

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
