<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportAttachedDevices extends Command
{
    protected $signature = 'legacy:import-attached-devices';
    protected $description = 'A régi product_product_id táblából betölti a csatolt készülékeket JSON tömbként az új DB products táblába.';

    public function handle(): int
    {
        $this->info('Csatolt készülék kapcsolatok importálása indul...');

        $legacy = DB::connection('legacy');

        // Régi kapcsolatok lekérése
        $rows = $legacy->table('product_product_id')->get();

        // Átmeneti tároló: product_id => [attached_ids...]
        $map = [];

        foreach ($rows as $r) {
            $map[$r->p_id][] = $r->c_id;
        }

        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($map as $productId => $attachedList) {
                DB::table('products')
                    ->where('id', $productId)
                    ->update([
                        'attached_device_id' => json_encode($attachedList),
                        'updated_at' => now(),
                    ]);

                $count++;
            }

            DB::commit();
            $this->info("Import kész! Összesen {$count} termékhez lettek kitöltve a kapcsolatok JSON tömbként.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Hiba történt: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
