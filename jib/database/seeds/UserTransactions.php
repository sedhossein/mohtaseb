<?php

use App\Utils\Enums\TransactionTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTransactions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_transactions')->insert([
            'user_id' => 9123456789,
            'transaction_type' => TransactionTypeEnum::GiftCode,
            'transaction_reference' => "REFRENCE-123",
            'creditor' => 150000,
            'debtor' => 0,
            'current_balance' => 150000,
        ]);
        DB::table('user_transactions')->insert([
            'user_id' => 9123456789,
            'transaction_type' => TransactionTypeEnum::GiftCode,
            'transaction_reference' => "REFRENCE-123",
            'creditor' => 0,
            'debtor' => 50000,
            'current_balance' => 150000 - 50000,
        ]);
    }
}
