<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLegacyProducts extends Command
{
    /**
     * A parancs neve, amivel terminálból futtatod.
     *
     * php artisan legacy:import-products
     */
    protected $signature = 'legacy:import-products';

    /**
     * A parancs rövid leírása.
     */
    protected $description = 'Termék kategóriák és termékek importálása a technorgaz_eredeti adatbázisból.';

    public function handle(): int
    {
        $this->info('Legacy import indul...');

        $legacy = DB::connection('legacy'); // régi DB (technorgaz_eredeti)
        $current = DB::connection();        // aktuális DB (technorgaz)

        // --- KATEGÓRIA IMPORT -------------------------------------------------
        $this->info('Termék kategóriák importálása...');

        $legacyCategories = $legacy->table('content')
            ->where('c_type', 'category')
            ->get();

        $defaultUserId = \App\Models\User::where('is_admin', 1)->value('id') ?? (\App\Models\User::first()->id ?? null);
        $defaultCompanyId = \App\Models\Company::first()->id ?? null;

        if (!$defaultUserId || !$defaultCompanyId) {
            $this->error('No users or companies found! Please run the users migration first.');
            return self::FAILURE;
        }

        $categoryCount = 0;

        DB::beginTransaction();
        try {
            foreach ($legacyCategories as $cat) {
                DB::table('product_categories')->updateOrInsert(
                    // kulcs – így többször is lefuttatható a parancs
                    ['id' => $cat->c_id],
                    [
                        'name'              => $cat->c_title,
                        'short_description' => $cat->c_desciption ?? null,
                        'user_id'           => $defaultUserId,
                        'company_id'        => $defaultCompanyId,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]
                );

                $categoryCount++;
            }

            DB::commit();
            $this->info("Kategória import kész. Összesen: {$categoryCount} rekord.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Hiba a kategória import során: ' . $e->getMessage());
            return self::FAILURE;
        }

        // --- TERMÉK IMPORT ----------------------------------------------------
        $this->info('Termékek importálása...');

        $legacyProducts = $legacy->table('product')->get();
        $productCount = 0;

        DB::beginTransaction();
        try {
            foreach ($legacyProducts as $p) {

                // low_stock_limit kezelése: negatív értékek ne menjenek az unsigned mezőbe
                $lowStockLimit = $p->quantity_low_stock;

                if ($lowStockLimit === null) {
                    $lowStockLimit = 0;        // maradjon NULL
                } elseif ($lowStockLimit < 0) {
                    $lowStockLimit = 0;        // vagy 0, ha azt szeretnéd
                    // $lowStockLimit = 0;
                }

                DB::table('products')->updateOrInsert(
                    // kulcs – feltételezve, hogy p_item egyedi cikkszám
                    ['item_number' => $p->p_item, 'id' => $p->p_id],
                    [
                        'inventory'                 => (int) $p->quantity,
                        'name'                      => $p->p_title,
                        'purchase_price'            => $p->p_price_acquisition,
                        'consumer_price'            => $p->p_price1,
                        'service_price'             => $p->p_price2,
                        'retail_price'              => $p->p_price3,
                        'wholesale_price'           => $p->p_price4,
                        'handover_price'            => $p->p_price5,
                        'service_partner_price'     => $p->p_price6,
                        'description'               => $p->p_description,
                        'product_category_id'       => $p->p_category,
                        'show_in_webshop'           => (int) $p->p_webshop,
                        'low_stock_limit'           => $lowStockLimit,
                        'is_active'                 => (int) $p->p_status,
                        'show_in_spare_parts_list'  => (int) $p->p_replacement_enabled,
                        'is_main_device'            => (int) $p->p_primary,
                        'created_at'                => now(),
                        'updated_at'                => now(),
                    ]
                );

                $productCount++;
            }

            DB::commit();
            $this->info("Termék import kész. Összesen: {$productCount} rekord.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Hiba a termék import során: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Legacy import sikeresen lefutott.');
        $this->info("Összesen importált kategóriák: {$categoryCount}");
        $this->info("Összesen importált termékek: {$productCount}");

        return self::SUCCESS;
    }
}
