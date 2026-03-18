<?php

/**
 * Script to add balance to all outlets/users
 * This script creates commission records for all outlets
 * 
 * Usage: php scripts/add-balance-to-all-users.php [amount]
 * Example: php scripts/add-balance-to-all-users.php 100
 */

use Illuminate\Support\Facades\DB;
use App\Models\Outlet;
use App\Models\OutletBalance;
use App\Models\User;

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 Adding Balance to All Users/Outlets\n";
echo "======================================\n\n";

// Get amount from command line or use default
$defaultAmount = $argv[1] ?? 0;

if ($defaultAmount == 0) {
    echo "❌ Please specify an amount to add\n";
    echo "Usage: php scripts/add-balance-to-all-users.php [amount]\n";
    echo "Example: php scripts/add-balance-to-all-users.php 100\n";
    exit(1);
}

echo "💰 Amount to add: {$defaultAmount} DH\n\n";

// Get all outlets
$outlets = Outlet::with('user', 'balanceRecord')->get();

echo "📊 Found " . $outlets->count() . " outlet(s)\n\n";

$updated = 0;
$created = 0;
$totalAdded = 0;

foreach ($outlets as $outlet) {
    echo "📍 Processing Outlet: {$outlet->code} - {$outlet->name}\n";
    
    $balanceRecord = $outlet->balanceRecord;
    
    if ($balanceRecord) {
        // Update existing balance
        $oldBalance = $balanceRecord->balance;
        $newBalance = $oldBalance + $defaultAmount;
        
        $balanceRecord->balance = $newBalance;
        $balanceRecord->save();
        
        echo "   ✅ Updated: {$oldBalance} DH → {$newBalance} DH\n";
        $updated++;
    } else {
        // Create new balance record
        OutletBalance::create([
            'outlet_id' => $outlet->id,
            'pos_code' => $outlet->code,
            'full_name' => $outlet->name,
            'balance' => $defaultAmount,
            'bonus' => 0,
        ]);
        
        echo "   ✨ Created: {$defaultAmount} DH\n";
        $created++;
    }
    
    $totalAdded += $defaultAmount;
    echo "\n";
}

// Also update users with outlet role
$users = User::where('role', 'outlet')->get();
echo "👥 Found " . $users->count() . " user(s) with outlet role\n\n";

foreach ($users as $user) {
    echo "👤 Processing User: {$user->email}\n";
    
    // Check if user has an outlet
    $outlet = Outlet::where('user_id', $user->id)->first();
    
    if (!$outlet) {
        echo "   ⚠️  No outlet found for this user\n";
    }
    echo "\n";
}

echo "======================================\n";
echo "✅ Summary:\n";
echo "   - Outlets Updated: {$updated}\n";
echo "   - Outlets Created: {$created}\n";
echo "   - Total Added: {$totalAdded} DH\n";
echo "   - Total Outlets: " . $outlets->count() . "\n";
echo "\n";
echo "🎉 Done!\n";
