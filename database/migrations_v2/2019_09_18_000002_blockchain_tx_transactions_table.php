<?php

use Bavix\Wallet\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BlockchainTxTransactionsTable extends Migration
{

    /**
     * @return string
     */
    protected function table(): string
    {
        return (new Transaction())->getTable();
    }

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $table->string('blockchain_tx',256)->nullable()->after('uuid');
        });

    }

    /**
     * @return void
     */
    public function down(): void
    {

        Schema::table($this->table(), function (Blueprint $table) {
            $table->dropColumn('blockchain_tx');
        });
    }

}
