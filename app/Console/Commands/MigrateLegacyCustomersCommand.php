<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Supplier;
use App\Enums\AccountType;

class MigrateLegacyCustomersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:legacy-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates customers and suppliers from the legacy database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Customers and Suppliers migration...');

        \Illuminate\Database\Eloquent\Model::unguard();

        $this->migrateCustomers();
        $this->migrateSuppliers();

        \Illuminate\Database\Eloquent\Model::reguard();

        $this->info('Customers and Suppliers migration completed successfully.');
    }

    private function migrateCustomers()
    {
        $this->info('Migrating Customers...');
        $legacyCustomers = DB::connection('legacy')->table('store_customer')->get();
        $bar = $this->output->createProgressBar(count($legacyCustomers));
        $bar->start();

        foreach ($legacyCustomers as $oldCust) {
            Customer::updateOrCreate(
                ['id' => $oldCust->id],
                [
                    'billing_name' => $oldCust->title ?: 'Ismeretlen Vásárló',
                    'billing_zip' => $oldCust->address_zip ?: '',
                    'billing_city' => $oldCust->address_city ?: '',
                    'billing_street' => $oldCust->address_street ?: '',
                    'billing_streetnumber' => '', // Régiben egyben volt
                    
                    'postal_name' => $oldCust->title ?: 'Ismeretlen Vásárló',
                    'postal_zip' => $oldCust->post_zip ?: ($oldCust->address_zip ?: ''),
                    'postal_city' => $oldCust->post_city ?: ($oldCust->address_city ?: ''),
                    'postal_street' => $oldCust->post_street ?: ($oldCust->address_street ?: ''),
                    
                    'taxnumber' => $oldCust->tax ?: '',
                    'contact_name' => $oldCust->contact_name ?: '',
                    'contact_email' => $oldCust->contact_email ?: '',
                    'contact_phone' => $oldCust->contact_phone ?: '',
                    
                    // Alapból beállítunk egy típust, ha a Customer model igényli
                    'account_type' => AccountType::Consumer->value, 
                    
                    // user_id and partner_details_id set to null if possible, 
                    // if strictly required we have to map it, but old store_customer had no user_id!
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function migrateSuppliers()
    {
        $this->info('Migrating Suppliers...');
        $legacySuppliers = DB::connection('legacy')->table('store_supplier')->get();
        $bar = $this->output->createProgressBar(count($legacySuppliers));
        $bar->start();

        foreach ($legacySuppliers as $oldSup) {
            Supplier::updateOrCreate(
                ['id' => $oldSup->id],
                [
                    'name' => $oldSup->title ?: 'Ismeretlen Beszállító',
                    'zip' => $oldSup->address_zip ?: '',
                    'city' => $oldSup->address_city ?: '',
                    'street' => $oldSup->address_street ?: '',
                    'streetnumber' => '',
                    
                    'taxnum' => $oldSup->tax ?: '',
                    'contact_name' => $oldSup->contact_name ?: '',
                    'email' => $oldSup->contact_email ?: '',
                    'phone' => $oldSup->contact_phone ?: '',
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
