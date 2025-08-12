<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZimbabweProvincesAndCitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // First, clear existing data to prevent duplicates on re-seeding
        DB::table('zimbabwe_cities')->truncate();
        DB::table('provinces')->truncate();

        $provinces = [
            'Harare Province' => ['Harare' => 'HRE', 'Chitungwiza' => 'CHT', 'Epworth' => 'EPW', 'Ruwa' => 'RUW'],
            'Bulawayo Province' => ['Bulawayo' => 'BYO'],
            'Manicaland' => ['Mutare' => 'MTA', 'Nyanga' => 'NYA', 'Rusape' => 'RSP', 'Chipinge' => 'CPI', 'Buhera' => 'BUH', 'Chimanimani' => 'CMA', 'Vumba' => 'VUM', 'Nyazura' => 'NYZ', 'Mutasa' => 'MTS', 'Makoni' => 'MKO', 'Birchenough Bridge' => 'BIR'],
            'Mashonaland East' => ['Marondera' => 'MRO', 'Murehwa' => 'MUR', 'Domboshava' => 'DOM', 'Goromonzi' => 'GOR', 'Chivhu' => 'CHV', 'Arcturus' => 'ARC', 'Beatrice' => 'BEA', 'Bromley' => 'BRO', 'Kotwa' => 'KOT', 'Macheke' => 'MAC', 'Mount Hampden' => 'MOU', 'Nyamapanda' => 'NYP', 'Shinga' => 'SHI', 'Mutoko' => 'MTK', 'Rushinga' => 'RSH'],
            'Mashonaland West' => ['Chinhoyi' => 'CNY', 'Kariba' => 'KAR', 'Kadoma' => 'KAD', 'Chegutu' => 'CHE', 'Zvimba' => 'ZVI', 'Norton' => 'NRT', 'Alaska' => 'ALA', 'Banket' => 'BNK', 'Chakari' => 'CHA', 'Chirundu' => 'CRU', 'Hurungwe' => 'HUR', 'Karoi' => 'KRO', 'Makonde' => 'MAK', 'Mhangura' => 'MHA', 'Mutorashanga' => 'MTR', 'Mvurwi' => 'MVW', 'Sanyati' => 'SAN', 'Trelawney' => 'TRE', 'Zhombe' => 'ZHO'],
            'Mashonaland Central' => ['Bindura' => 'BID', 'Shamva' => 'SHA', 'Mount Darwin' => 'MDA', 'Guruve' => 'GUR', 'Centenary' => 'CEN', 'Concession' => 'CON', 'Mazowe' => 'MAZ', 'Mukumbura' => 'MUK', 'Muzarabani' => 'MUZ', 'Raffingora' => 'RAF'],
            'Matabeleland North' => ['Lupane' => 'LUP', 'Hwange' => 'HWA', 'Victoria Falls' => 'VIC', 'Binga' => 'BIG', 'Dete' => 'DET', 'Jotsholo' => 'JOT', 'Kamativi' => 'KAM', 'Nkayi' => 'NKA', 'Nyamandlovu' => 'NYM', 'Tsholotsho' => 'TSH', 'Umguza' => 'UMG'],
            'Matabeleland South' => ['Gwanda' => 'GWA', 'Beitbridge' => 'BEI', 'Filabusi' => 'FIL', 'Insiza' => 'INS', 'Maphisa' => 'MAP', 'Plumtree' => 'PLU', 'Bubi' => 'BUB', 'Bulilima' => 'BUL', 'Esigodini' => 'ESI', 'Figtree' => 'FIG', 'Kezi' => 'KEZ', 'Mangwe' => 'MNG', 'Matobo' => 'MAT', 'Umzingwane' => 'UMZ'],
            'Midlands' => ['Gweru' => 'GWE', 'Kwekwe' => 'KWE', 'Zvishavane' => 'ZVA', 'Shurugwi' => 'SHU', 'Mvuma' => 'MVU', 'Gokwe' => 'GOK', 'Chivi' => 'CH', 'Chirumanzu' => 'CHZ', 'Chiwundura' => 'CWD', 'Lalapanzi' => 'LAL', 'Lower Gweru' => 'LGR', 'Mberengwa' => 'MBE', 'Munyati' => 'MUN', 'Redcliff' => 'RED', 'Shangani' => 'SHN', 'Silobela' => 'SIL', 'Somabula' => 'SOM'],
            'Masvingo Province' => ['Masvingo' => 'MSV', 'Chiredzi' => 'CHD', 'Bikita' => 'BIK', 'Gutu' => 'GUT', 'Ngundu' => 'NGU', 'Zaka' => 'ZAK', 'Mwenezi' => 'MWE', 'Triangle' => 'TRI', 'Rutenga' => 'RUT'],
        ];

        foreach ($provinces as $provinceName => $cities) {
            $province = DB::table('provinces')->insertGetId(['name' => $provinceName, 'created_at' => now(), 'updated_at' => now()]);
            foreach ($cities as $cityName => $abbreviation) {
                DB::table('zimbabwe_cities')->insert([
                    'name' => $cityName,
                    'abbreviation' => $abbreviation,
                    'province_id' => $province,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}