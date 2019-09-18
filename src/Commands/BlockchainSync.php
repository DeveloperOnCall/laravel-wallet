<?php

namespace Bavix\Wallet\Commands;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\ProxyService;
use Illuminate\Console\Command;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use function config;

/**
 * Class BlockchainSync
 * @package Bavix\Wallet\Commands
 * @codeCoverageIgnore 
 */
class BlockchainSync extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:blockchain_sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds Blockchain Transactions to Wallet';

    /**
     * @return void
     */
    public function handle(): void
    {
        // Make sure we have all the blockchain transactions as wallet transactions
        $this->blockchainSync();
        // Refresh the wallet balances
        app(ProxyService::class)->fresh();
        DB::transaction(function () {
            $wallet = config('wallet.wallet.table');
            DB::table($wallet)->update(['balance' => 0]);

            if (DB::connection() instanceof SQLiteConnection) {
                $this->sqliteUpdate();
            } else {
                $this->multiUpdate();
            }
        });
    }

    /**
     * SQLite
     *
     * @return void
     */
    protected function sqliteUpdate(): void
    {
        Wallet::query()->each(static function (Wallet $wallet) {
            $wallet->refreshBalance();
        });
    }

    /**
     * MySQL/PgSQL
     *
     * @return void
     */
    protected function multiUpdate(): void
    {
        $wallet = config('wallet.wallet.table');
        $trans = config('wallet.transaction.table');
        $availableBalance = DB::table($trans)
            ->select('wallet_id', DB::raw('sum(amount) balance'))
            ->where('confirmed', true)
            ->groupBy('wallet_id');

        $joinClause = static function (JoinClause $join) use ($wallet) {
            $join->on("$wallet.id", '=', 'b.wallet_id');
        };

        DB::table($wallet)
            ->joinSub($availableBalance, 'b', $joinClause, null, null, 'left')
            ->update(['balance' => DB::raw('b.balance')]);
    }

    protected function blockchainSync(): void{
        $wallet = config('wallet.wallet.table');
        $trans = config('wallet.transaction.table');
        $wallets = DB::table($wallet)->get();
        foreach( $wallets as $wallet ){
            $slug = $wallet->slug;
            switch ($wallet->slug){
                case 'bitcoin':
                    $list_url = config('wallet.blockchain_api.BTC.list');
                    $tx_url = config('wallet.blockchain_api.BTC.detail');
                    break;
                case 'bitcoin-cash':
                    $list_url = config('wallet.blockchain_api.BCH.list');
                    $tx_url = config('wallet.blockchain_api.BCH.detail');
                    break;
                case 'etherium':
                    $list_url = config('wallet.blockchain_api.ETH.list');
                    $tx_url = config('wallet.blockchain_api.ETH.detail');
                    break;
                case 'ripple':
                    $list_url = config('wallet.blockchain_api.XRP.list');
                    $tx_url = config('wallet.blockchain_api.XRP.detail');
                    break;

            }
            // Replace the address in the transaction list URL
            $list_url = str_replace('ADDRESS_REPLACE',$wallet->blockchain_address,$list_url);

            $tx_list = file_get_contents($list_url);

            // @todo This may be different for each api
            foreach( $tx_list as $tx ){
                $exists = DB::table($trans)->where('blockchain_tx',$tx)->first();
                if( $exists instanceof Transaction ){ // Not sure if instanceof will work here
                    // we already have this transaction skip to the next
                    break;
                }
                // Replace the transaction in the transaction view URL
                $tx_url = str_replace('TX_REPLACE',$tx,$tx_url);

                $tx_detail = file_get_contents($tx_url); // this also might be different for each blockchain

                $trans_type = $tx_amount < 0 ? Transaction::TYPE_WITHDRAW : Transaction::TYPE_DEPOSIT; // $tx_amount is not correct -- need to get the amount from the transaction
                Transaction::create([
                    // add the blockchain transaction
                    'wallet_id'     => $wallet->id,
                    'type'          => $trans_type,
                    'payable_type'  => "App\Models\User",
                    'payable_id'    => $wallet->holder_id,
                    'confirmed'     => 1,
                ]);

            }

        }

    }

}
