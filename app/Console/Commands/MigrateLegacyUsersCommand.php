<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;
use App\Models\PartnerDetails;
use App\Enums\AccountType;
use Illuminate\Support\Str;

class MigrateLegacyUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:legacy-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate users, companies and partner details from legacy DB';

    /**
     * Map old u_type (0-6) into new AccountType Enums.
     * 1=Fogyasztó, stb... Kérés alapján módosítható ha a régi rendszer máshogy számozta.
     */
    private function mapAccountType($uType): ?AccountType
    {
        // Ha üres vagy érvénytelen
        if ($uType === null || $uType === '') {
            return null;
        }

        // Itt beállítható a pontos leképezés:
        // Példa (ellenőrizd, hogy a régi rendszerben a számok ezt takarták-e!):
        // 0 / 1 -> Fogyasztó
        // 2 -> Szervizes
        // 3 -> Kisker
        // ... (vagy használd a lenti kiosztást feltételesen)
        return match ((int)$uType) {
            1 => AccountType::Consumer,
            2 => AccountType::Service,
            3 => AccountType::Retail,
            4 => AccountType::Wholesale,
            5 => AccountType::Handover,
            6 => AccountType::ServicePartner,
            0 => AccountType::Consumer, // Default to consumer for 0
            default => null,
        };
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting User migration...');

        // Ideiglenesen kikapcsoljuk a Mass Assignment védelmet, hogy az 'id' mezőt is átadhassuk
        \Illuminate\Database\Eloquent\Model::unguard();

        // Itt queryzzük a régi DB-t ("technorgaz_eredeti")
        $legacyUsers = DB::connection('legacy')->table('user')->get();

        $bar = $this->output->createProgressBar(count($legacyUsers));
        $bar->start();

        foreach ($legacyUsers as $oldUser) {
            $accountType = $this->mapAccountType($oldUser->u_type);

            // A feltelek (kiemelt kérés) alapján a consumereket (Fogyasztók) NE importáljuk itt
            if ($accountType === AccountType::Consumer) {
                $bar->advance();
                continue;
            }

            // 1. User alap létrehozása (company_id és partner_details_id nélkül)
            $email = $oldUser->u_email ?: ('ismeretlen_' . $oldUser->u_id . '@domain.com');
            // Deduplicate email
            if (User::where('email', $email)->where('id', '!=', $oldUser->u_id)->exists()) {
                $email = 'duplicated_' . $oldUser->u_id . '_' . $email;
            }

            $newUser = User::updateOrCreate(
                ['id' => $oldUser->u_id],
                [
                    'name' => $oldUser->u_name ?: 'Ismeretlen Felhasználó',
                    'email' => $email,
                    'password' => $oldUser->u_hash ?: ($oldUser->u_pass ?: ''),
                    'is_admin' => (bool)$oldUser->u_admin,
                    'account_type' => $this->mapAccountType($oldUser->u_type),
                    'created_at' => $oldUser->u_date,
                    'updated_at' => $oldUser->u_update,
                ]
            );

            // 2. Szükségünk van-e PartnerData-ra? Megnézzük előre
            $hasPartnerData = !empty($oldUser->u_measuring_device) || !empty($oldUser->lat) || !empty($oldUser->u_city);
            
            // 3. Cég létrehozása, ha van cégnév, VAGY ha van PartnerData (mivel partner_details kötelezővé teszi a company_id-t)
            $companyId = null;
            $companyName = $oldUser->u_company ?: ($oldUser->u_name ?: 'Ismeretlen Partner Cég - ' . $oldUser->u_id);

            if (!empty($oldUser->u_company) || $hasPartnerData) {
                $company = Company::firstOrCreate(
                    ['company_name' => $companyName],
                    [
                        'user_id' => $newUser->id, // owner/creator
                        'company_country' => 'HU',
                        'company_zip' => $oldUser->u_zip ?: '',
                        'company_city' => $oldUser->u_city ?: '',
                        'company_address' => $oldUser->u_address ?: '',
                        'company_taxnum' => $oldUser->u_tax ?: '',
                    ]
                );
                $companyId = $company->id;
                
                if (empty($company->user_id)) {
                    $company->update(['user_id' => $newUser->id]);
                }
            }

            // 3. Szerviz partnerek / Partner adatok létrehozása
            $partnerDetailsId = null;
            $hasPartnerData = !empty($oldUser->u_measuring_device) || !empty($oldUser->lat) || !empty($oldUser->u_city);
            
            if ($hasPartnerData) {
                $location = trim(implode(', ', array_filter([$oldUser->u_zip, $oldUser->u_city, $oldUser->u_address])));
                
                $partnerDetails = PartnerDetails::updateOrCreate(
                    ['user_id' => $newUser->id],
                    [
                        'company_id' => $companyId,
                        'location_address' => $location ?: 'Nincs megadva',
                        'latitude' => $oldUser->lat ?: null,
                        'longitude' => $oldUser->lng ?: null,
                        'flue_gas_analyzer_type' => $oldUser->u_measuring_device ?: null,
                        'flue_gas_analyzer_serial_number' => $oldUser->u_measuring_device_sn ?: null,
                        'account_type' => $this->mapAccountType($oldUser->u_type) ? $this->mapAccountType($oldUser->u_type)->value : null,
                    ]
                );
                
                $partnerDetailsId = $partnerDetails->id;
            }

            // 4. User frissítése a most már ismert company_id és partner_details_id értékekkel
            if ($companyId !== null || $partnerDetailsId !== null) {
                $newUser->update([
                    'company_id' => $companyId,
                    'partner_details_id' => $partnerDetailsId
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Legacy users migration compleded successfully.');
        
        \Illuminate\Database\Eloquent\Model::reguard();
    }
}
