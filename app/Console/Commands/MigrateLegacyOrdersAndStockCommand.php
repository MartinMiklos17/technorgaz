<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ProductOrder;
use App\Models\ProductOrderItem;
use App\Models\ProductIntake;
use App\Models\ProductIntakeItem;
use App\Models\ProductOutput;
use App\Models\ProductOutputItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Customer;

class MigrateLegacyOrdersAndStockCommand extends Command
{
    protected $signature = 'migrate:legacy-orders-and-stock';
    protected $description = 'Migrate legacy orders, intakes and outputs from store_product_stock and store_sale.';

    public function handle()
    {
        $this->warn('This script is ready to run, as per your request.');
        
        $this->info('Starting Orders and Stock Migration...');

        \Illuminate\Database\Eloquent\Model::unguard();

        $this->migrateOrders();
        $this->migrateIntakes();
        $this->migrateOutputs();

        \Illuminate\Database\Eloquent\Model::reguard();

        $this->info('Orders and Stock Migration completed.');
    }

    private function migrateOrders()
    {
        $this->info('Migrating Orders...');
        $legacyOrders = DB::connection('legacy')->table('orders')->get();

        $bar = $this->output->createProgressBar(count($legacyOrders));
        $bar->start();

        foreach ($legacyOrders as $oldOrder) {
            $newOrder = ProductOrder::updateOrCreate(
                ['id' => $oldOrder->o_id],
                [
                    'note' => $oldOrder->o_comment ?: '',
                    'order_date' => $this->validateDate($oldOrder->o_date),
                    'is_sent' => $oldOrder->o_status >= 2,
                    'total_net_amount' => 0,
                    'total_gross_amount' => 0,
                    'total_vat_amount' => 0,
                    'total_quantity' => 0,
                ]
            );

            ProductOrderItem::where('product_order_id', $newOrder->id)->delete();

            // Decode serialized order data
            $cartData = null;
            if (!empty($oldOrder->o_data)) {
                $decoded = base64_decode($oldOrder->o_data);
                if ($decoded !== false) {
                    $cartData = @unserialize($decoded);
                    if ($cartData === false && $decoded !== 'b:0;') {
                        $cartData = json_decode($decoded, true);
                    }
                }
            }
            if (!$cartData && !empty($oldOrder->o_data)) {
                 $cartData = json_decode($oldOrder->o_data, true);
            }

            if (is_array($cartData)) {
                foreach ($cartData as $item) {
                    // Adapt to the specific array keys used in the legacy serialized object
                    if (is_array($item) || is_object($item)) {
                        $itemArray = (array)$item;
                        $quantity = $itemArray['qty'] ?? ($itemArray['quantity'] ?? 1);
                        $price = $itemArray['price'] ?? 0;
                        $productId = $itemArray['id'] ?? ($itemArray['p_id'] ?? null);
                        
                        // Prevent invalid foreign keys
                        if ($productId && Product::where('id', $productId)->exists()) {
                            ProductOrderItem::create([
                                'product_order_id' => $newOrder->id,
                                'product_id' => $productId,
                                'quantity' => $quantity,
                                'net_unit_price' => $price,
                                'net_total_price' => $price * $quantity,
                                'gross_unit_price' => $price * 1.27,
                                'gross_total_price' => ($price * 1.27) * $quantity,
                                'vat_amount' => ($price * 0.27) * $quantity,
                                'item_number' => $itemArray['item_number'] ?? ($itemArray['p_item'] ?? ''),
                                'vat_percent' => 27,
                            ]);
                        }
                    }
                }
            }

            $newOrder->updateTotals();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function migrateIntakes()
    {
        $this->info('Migrating Intakes...');
        
        // Group intakes by supplier and date
        $intakes = DB::connection('legacy')->table('store_product_stock')
            ->whereNotNull('supplier')
            ->where('supplier', '>', 0)
            ->get()
            ->groupBy(function($item) {
                return $item->supplier . '_' . substr($item->date, 0, 10);
            });

        $bar = $this->output->createProgressBar(count($intakes));
        $bar->start();

        foreach ($intakes as $key => $items) {
            $first = $items->first();
            
            // Ellenőrizzük, hogy létezik-e a beszállító az új rendszerben
            if (!Supplier::where('id', $first->supplier)->exists()) {
                $bar->advance();
                continue;
            }

            $intake = ProductIntake::firstOrCreate(
                [
                    'supplier_id' => $first->supplier,
                    'date' => $this->validateDate(substr($first->date, 0, 10)),
                ],
                [
                    'note' => $first->comment ?: '',
                ]
            );

            ProductIntakeItem::where('product_intake_id', $intake->id)->delete();

            foreach ($items as $item) {
                if ($item->product && Product::where('id', $item->product)->exists()) {
                    ProductIntakeItem::create([
                        'product_intake_id' => $intake->id,
                        'product_id' => $item->product,
                        'quantity' => abs($item->quantity),
                        'unit_price' => $item->price,
                    ]);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function migrateOutputs()
    {
        $this->info('Migrating Outputs...');
        
        // Outputs are linked to store_sale which holds the payment info and global comment
        $sales = DB::connection('legacy')->table('store_sale')->get();

        $bar = $this->output->createProgressBar(count($sales));
        $bar->start();

        foreach ($sales as $sale) {
            
            $items = DB::connection('legacy')->table('store_product_stock')
                ->where('sale_id', $sale->id)
                ->get();
            
            if ($items->isEmpty()) {
                $bar->advance();
                continue;
            }

            $firstItem = $items->first();
            $customer = $firstItem->customer ?: null;

            // Ellenőrizzük, hogy létezik-e a vevő az új rendszerben
            if ($customer && !Customer::where('id', $customer)->exists()) {
                $customer = null;
            }

            $output = ProductOutput::updateOrCreate(
                [
                    'id' => $sale->id
                ],
                [
                    'customer_id' => $customer,
                    'date' => $this->validateDate($sale->date),
                    'note' => $sale->comment ?: '',
                    'payment_method' => $sale->payment_mode ?: 'cash',
                ]
            );

            ProductOutputItem::where('product_output_id', $output->id)->delete();

            foreach ($items as $item) {
                if ($item->price < 0) {
                    $this->warn('Negative price detected:');
                    $this->info(json_encode($item));
                }

                if ($item->product && Product::where('id', $item->product)->exists()) {
                    ProductOutputItem::create([
                        'product_output_id' => $output->id,
                        'product_id' => $item->product,
                        'quantity' => abs($item->quantity),
                        'price_type' => 'custom',
                        'selected_price' => $item->price_sum,
                        'discount' => isset($item->discount) ? $item->discount : 0,
                        'is_vat_included' => false, // legacy generally calculated vat on top, review if needed
                        'warranty' => !empty($item->quarantee_serial_number),
                        'spare_part_returned' => false,
                        'serial_number' => $item->quarantee_serial_number ?: null,
                    ]);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function validateDate($date)
    {
        if (empty($date) || str_contains($date, '-0001') || str_contains($date, '0000-00-00')) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->toDateTimeString();
        } catch (\Exception $e) {
            return null;
        }
    }
}
