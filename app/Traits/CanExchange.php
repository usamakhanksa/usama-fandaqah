<?php

namespace App\Traits;

use App\Interfaces\Wallet;
use App\Transfer;
use App\Objects\Bring;
use App\Services\CommonService;
use App\Services\ExchangeService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

trait CanExchange
{

    /**
     * @inheritDoc
     */
    public function exchange(Wallet $to, int $amount, ?array $meta = null): Transfer
    {
        $wallet = app(WalletService::class)
            ->getWallet($this);

        app(CommonService::class)
            ->verifyWithdraw($wallet, $amount);

        return $this->forceExchange($to, $amount, $meta);
    }

    /**
     * @inheritDoc
     */
    public function safeExchange(Wallet $to, int $amount, ?array $meta = null): ?Transfer
    {
        try {
            return $this->exchange($to, $amount, $meta);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function forceExchange(Wallet $to, int $amount, ?array $meta = null): Transfer
    {
        /**
         * @var Wallet $from
         */
        $from = app(WalletService::class)->getWallet($this);

        return DB::transaction(static function() use ($from, $to, $amount, $meta) {
            $rate = app(ExchangeService::class)->rate($from, $to);
            $fee = app(WalletService::class)->fee($to, $amount);

            $withdraw = app(CommonService::class)
                ->forceWithdraw($from, $amount + $fee, $meta);

            $deposit = app(CommonService::class)
                ->deposit($to, $amount * $rate, $meta);

            $transfers = app(CommonService::class)->multiBrings([
                (new Bring())
                    ->setStatus(Transfer::STATUS_EXCHANGE)
                    ->setDeposit($deposit)
                    ->setWithdraw($withdraw)
                    ->setFrom($from)
                    ->setTo($to)
            ]);

            return current($transfers);
        });
    }

}
