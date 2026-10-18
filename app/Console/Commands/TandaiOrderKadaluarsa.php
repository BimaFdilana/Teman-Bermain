<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class TandaiOrderKadaluarsa extends Command
{
    protected $signature = 'order:kadaluarsa';
    protected $description = 'Tandai order "menunggu pembayaran" yang melewati batas 24 jam sebagai kadaluarsa.';

    public function handle(): int
    {
        $count = Order::where('status', Order::ST_PEMBAYARAN)
            ->where('batas_bayar', '<', now())
            ->update(['status' => Order::ST_KADALUARSA]);

        $this->info("{$count} order ditandai kadaluarsa.");

        return self::SUCCESS;
    }
}
