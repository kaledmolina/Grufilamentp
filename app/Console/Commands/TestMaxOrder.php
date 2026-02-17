<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Orden;
use Illuminate\Support\Facades\DB;

class TestMaxOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:max-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test max order number logic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $maxString = Orden::max('numero_orden');
        $this->info("Max String: " . $maxString);

        $nextString = ($maxString ?? 0) + 1;
        $this->info("Next String Logic: " . $nextString);

        $maxInteger = Orden::selectRaw('MAX(CAST(numero_orden AS UNSIGNED)) as max_orden')->value('max_orden');
        $this->info("Max Integer: " . $maxInteger);

        $nextInteger = ($maxInteger ?? 0) + 1;
        $this->info("Next Integer Logic: " . $nextInteger);
    }
}
