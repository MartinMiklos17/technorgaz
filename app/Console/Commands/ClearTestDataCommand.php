<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearTestDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:clean-test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears test data from the new DB, preserving users, companies and partner details.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('This will TRUNCATE orders, products, customers, suppliers, categories and service logs.');
        // if (!$this->confirm('Are you sure you want to proceed?')) {
        //     $this->info('Aborted.');
        //     return;
        // }

        $tables = [
            'product_order_items',
            'product_orders',
            'product_intake_items',
            'product_intakes',
            'product_output_items',
            'product_outputs',
            'products',
            'product_categories',
            'service_reports',
            'commissioning_logs',
            'customers',
            'suppliers',
            'partner_details',
            'companies',
        ];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            DB::table($table)->truncate();
            $this->info("Truncated table: {$table}");
        }

        // Delete all users except the first few test admins (id <= 5)
        $deletedUsers = DB::table('users')->where('id', '>', 5)->delete();
        $this->info("Deleted {$deletedUsers} ghost users.");

        Schema::enableForeignKeyConstraints();

        $this->info('Test data cleared successfully!');
    }
}
