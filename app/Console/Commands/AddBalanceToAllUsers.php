<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Outlet;
use App\Models\OutletBalance;
use App\Models\User;

class AddBalanceToAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:add-balance {amount : The amount to add to all users} {--outlet : Only update outlets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add balance to all users/outlets in the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $amount = (float) $this->argument('amount');
        $outletOnly = $this->option('outlet');

        if ($amount <= 0) {
            $this->error('❌ Amount must be greater than 0');
            return 1;
        }

        $this->info('🚀 Adding Balance to All Users/Outlets');
        $this->info('======================================');
        $this->info('');
        $this->info("💰 Amount to add: {$amount} DH");
        $this->info('');

        // Get all outlets
        $outlets = Outlet::with('user', 'balanceRecord')->get();

        $this->info("📊 Found {$outlets->count()} outlet(s)");
        $this->info('');

        $updated = 0;
        $created = 0;
        $totalAdded = 0;

        $bar = $this->output->createProgressBar($outlets->count());
        $bar->start();

        foreach ($outlets as $outlet) {
            $balanceRecord = $outlet->balanceRecord;

            if ($balanceRecord) {
                // Update existing balance
                $oldBalance = $balanceRecord->balance;
                $newBalance = $oldBalance + $amount;

                $balanceRecord->balance = $newBalance;
                $balanceRecord->save();

                $updated++;
            } else {
                // Create new balance record
                OutletBalance::create([
                    'outlet_id' => $outlet->id,
                    'pos_code' => $outlet->code,
                    'full_name' => $outlet->name,
                    'balance' => $amount,
                    'bonus' => 0,
                ]);

                $created++;
            }

            $totalAdded += $amount;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('======================================');
        $this->info('✅ Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Outlets Updated', $updated],
                ['Outlets Created', $created],
                ['Total Added', "{$totalAdded} DH"],
                ['Total Outlets', $outlets->count()],
            ]
        );
        $this->info('');
        $this->info('🎉 Done!');

        return 0;
    }
}
