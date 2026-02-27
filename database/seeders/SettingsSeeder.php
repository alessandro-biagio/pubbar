<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder {
    public function run(): void {
        Setting::updateOrCreate(['key'=>'kitchen.capacity.default.panini'],     ['value'=>'30']);
        Setting::updateOrCreate(['key'=>'kitchen.capacity.default.sfiziosita'], ['value'=>'30']);
    }
}
