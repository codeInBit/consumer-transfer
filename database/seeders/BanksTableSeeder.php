<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;

class BanksTableSeeder extends Seeder
{
    public function run()
    {
        $banks = [
            'First Bank' => '011', 'Gt Bank' => '058', 'Zenith Bank' => '057', 'Diamond Bank' => '063',
            'Wema Bank' => '035', 'Fidelity Bank' => '070', 'Eco Bank' => '050', 'Access Bank' => '044',
            'Stanbic IBTC Bank' => '221', 'FCMB' => '214', 'Sterling Bank' => '232','Unity Bank' => '215',
            'Standard Chartered Bank' => '221', 'Providus Bank' => '101', 'Keystone Bank' => '082',
            'Heritage Bank' => '030', 'United Bank For Africa' => '033'
        ];

        foreach ($banks as $key => $value) {
            Bank::create([
                'bank_name' => $key,
                'bank_code' => $value
            ]);
        }
    }
}

// bank codes gotten from https://bank.codes/api-nigeria-nuban/
