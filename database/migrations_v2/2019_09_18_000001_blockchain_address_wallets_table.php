<?php

use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BlockchainAddressWalletsTable extends Migration
{

    /**
     * @return string
     */
    protected function table(): string
    {
        return (new Wallet())->getTable();
    }

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $table->string('blockchain_address',256)->nullable()->after('decimal_places');
            $table->string('blockchain_pk',256)->nullable()->after('blockchain_address');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $table->dropColumn('blockchain_address');
            $table->dropColumn('blockchain_pk');
        });
    }

}
