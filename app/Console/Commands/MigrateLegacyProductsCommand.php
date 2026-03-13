<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ProductCategory;
use App\Models\Product;

class MigrateLegacyProductsCommand extends Command
{
    protected $signature = 'migrate:legacy-products';
    protected $description = 'Migrates categories and products from the legacy database';

    public function handle()
    {
        $this->info('Starting Products and Categories migration...');

        \Illuminate\Database\Eloquent\Model::unguard();

        $defaultUserId = \App\Models\User::where('is_admin', 1)->value('id') ?? (\App\Models\User::first()->id ?? null);
        $defaultCompanyId = \App\Models\Company::first()->id ?? null;

        if (!$defaultUserId || !$defaultCompanyId) {
            $this->error('No users or companies found! Please run the users migration first.');
            return;
        }

        $this->migrateCategories($defaultUserId, $defaultCompanyId);
        $this->migrateProducts($defaultUserId, $defaultCompanyId);

        \Illuminate\Database\Eloquent\Model::reguard();

        $this->info('Products and Categories migration completed successfully.');
    }

    private function migrateCategories($defaultUserId, $defaultCompanyId)
    {
        $this->info('Migrating Categories...');
        // Régiben: content tábla ahol c_type = 'category'
        $legacyCategories = DB::connection('legacy')->table('content')->where('c_type', 'category')->get();
        $bar = $this->output->createProgressBar(count($legacyCategories));
        $bar->start();

        foreach ($legacyCategories as $oldCat) {
            ProductCategory::updateOrCreate(
                ['id' => $oldCat->c_id],
                [
                    'name' => $oldCat->c_title ?: 'Ismeretlen Kategória',
                    'short_description' => property_exists($oldCat, 'c_subtitle') ? ($oldCat->c_subtitle ?: '') : '',
                    'user_id' => $defaultUserId,
                    'company_id' => $defaultCompanyId,
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function migrateProducts($defaultUserId, $defaultCompanyId)
    {
        $this->info('Migrating Products...');
        $legacyProducts = DB::connection('legacy')->table('product')->get();
        $bar = $this->output->createProgressBar(count($legacyProducts));
        $bar->start();

        foreach ($legacyProducts as $oldProd) {
            // Ellenőrizzük, hogy van-e ilyen kategória, különben null (külföldi kulcs hiba elkerülése)
            $catId = isset($oldProd->p_category) && $oldProd->p_category > 0 ? $oldProd->p_category : null;
            if ($catId) {
                $categoryExists = ProductCategory::where('id', $catId)->exists();
                if (!$categoryExists) {
                    $catId = null;
                }
            }

            Product::updateOrCreate(
                ['id' => $oldProd->p_id],
                [
                    'item_number' => isset($oldProd->p_item) ? $oldProd->p_item : '',
                    'inventory' => isset($oldProd->quantity) ? (float)$oldProd->quantity : 0,
                    'name' => isset($oldProd->p_title) ? $oldProd->p_title : 'Névtelen Termék',
                    
                    'purchase_price' => isset($oldProd->p_price_acquisition) ? (float)$oldProd->p_price_acquisition : 0,
                    'consumer_price' => isset($oldProd->p_price1) ? (float)$oldProd->p_price1 : 0,
                    'service_price' => isset($oldProd->p_price2) ? (float)$oldProd->p_price2 : 0,
                    'retail_price' => isset($oldProd->p_price3) ? (float)$oldProd->p_price3 : 0,
                    'wholesale_price' => isset($oldProd->p_price4) ? (float)$oldProd->p_price4 : 0,
                    'handover_price' => isset($oldProd->p_price5) ? (float)$oldProd->p_price5 : 0,
                    'service_partner_price' => isset($oldProd->p_price6) ? (float)$oldProd->p_price6 : 0,
                    
                    'description' => isset($oldProd->p_content) ? $oldProd->p_content : '',
                    'product_category_id' => $catId,
                    'is_active' => isset($oldProd->p_status) ? (bool)$oldProd->p_status : true,
                    //'user_id' => $defaultUserId,
                    //'company_id' => $defaultCompanyId,
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
