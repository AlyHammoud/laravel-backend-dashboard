<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SiteData;

class SiteDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SiteData::create([
            'site_visits' => 1
        ]);
        // $roles = ['admin', 'user'];

        // collect($roles)->each(function ($role) {
        //     SiteD::create([
        //         'name' => $role
        //     ]);
        // });
    }
}
