<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\ShelfItem;
use App\Models\Transaction;
use App\Models\AuditLog;

class AuditLogSeeder extends Seeder
{
    public function run()
    {
        // Common variables
        $url = 'http://cafe-app.com/admin';
        $ipAddress = '127.0.0.1';
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36';
        $users = User::take(4)->pluck('id');

        // Function to create audit log
        $createAuditLog = function ($userId, $event, $type, $id, $oldValues, $newValues, $timestamp) use ($url, $ipAddress, $userAgent) {
            AuditLog::create([
                'user_id' => $userId,
                'event' => $event,
                'auditable_type' => $type,
                'auditable_id' => $id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'url' => $url,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        };

        // Products
        $products = Product::take(7)->get();
        foreach ($products as $product) {
            $T1 = fake()->dateTimeBetween('-1 year', 'now');
            $userId = $users->isEmpty() ? null : $users->random();
            $createAuditLog($userId, 'created', Product::class, $product->id, null, $product->toArray(), $T1);
            for ($i = 0; $i < 4; $i++) {
                $T = fake()->dateTimeBetween($T1, 'now');
                $oldValues = $product->toArray();
                $oldValues['quantity'] = max(0, $product->quantity - 10);
                $createAuditLog($userId, 'updated', Product::class, $product->id, $oldValues, $product->toArray(), $T);
            }
        }

        // ShelfItems
        $shelfItems = ShelfItem::take(7)->get();
        foreach ($shelfItems as $shelfItem) {
            $T1 = fake()->dateTimeBetween('-1 year', 'now');
            $userId = $users->isEmpty() ? null : $users->random();
            $createAuditLog($userId, 'created', ShelfItem::class, $shelfItem->id, null, $shelfItem->toArray(), $T1);
            for ($i = 0; $i < 4; $i++) {
                $T = fake()->dateTimeBetween($T1, 'now');
                $oldValues = $shelfItem->toArray();
                $oldValues['quantity_added'] = max(0, $shelfItem->quantity_added - 5);
                $createAuditLog($userId, 'updated', ShelfItem::class, $shelfItem->id, $oldValues, $shelfItem->toArray(), $T);
            }
        }

        // Transactions
        $transactions = Transaction::take(6)->get();
        foreach ($transactions as $transaction) {
            $T1 = fake()->dateTimeBetween('-1 year', 'now');
            $userId = $users->isEmpty() ? null : $users->random();
            $createAuditLog($userId, 'created', Transaction::class, $transaction->id, null, $transaction->toArray(), $T1);
            for ($i = 0; $i < 4; $i++) {
                $T = fake()->dateTimeBetween($T1, 'now');
                $oldValues = $transaction->toArray();
                $oldValues['quantity'] = max(1, $transaction->quantity - 1);
                $createAuditLog($userId, 'updated', Transaction::class, $transaction->id, $oldValues, $transaction->toArray(), $T);
            }
        }

        // Generate some 'deleted' events
        $deletedProducts = Product::inRandomOrder()->take(3)->get();
        foreach ($deletedProducts as $product) {
            $T = fake()->dateTimeBetween('-1 year', 'now');
            $userId = $users->isEmpty() ? null : $users->random();
            $createAuditLog($userId, 'deleted', Product::class, $product->id, $product->toArray(), null, $T);
        }

        $deletedShelfItems = ShelfItem::inRandomOrder()->take(3)->get();
        foreach ($deletedShelfItems as $shelfItem) {
            $T = fake()->dateTimeBetween('-1 year', 'now');
            $userId = $users->isEmpty() ? null : $users->random();
            $createAuditLog($userId, 'deleted', ShelfItem::class, $shelfItem->id, $shelfItem->toArray(), null, $T);
        }

        $deletedTransactions = Transaction::inRandomOrder()->take(3)->get();
        foreach ($deletedTransactions as $transaction) {
            $T = fake()->dateTimeBetween('-1 year', 'now');
            $userId = $users->isEmpty() ? null : $users->random();
            $createAuditLog($userId, 'deleted', Transaction::class, $transaction->id, $transaction->toArray(), null, $T);
        }
    }
}