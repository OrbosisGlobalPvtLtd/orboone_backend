<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\DocumentTypeModal::create(['name' => 'Aadhaar Card', 'scope' => 'employee', 'is_mandatory' => true, 'has_expiry' => false]);
        \App\Models\DocumentTypeModal::create(['name' => 'PAN Card', 'scope' => 'employee', 'is_mandatory' => true, 'has_expiry' => false]);
        \App\Models\DocumentTypeModal::create(['name' => 'Passport', 'scope' => 'employee', 'is_mandatory' => false, 'has_expiry' => true]);
        \App\Models\DocumentTypeModal::create(['name' => 'Driving License', 'scope' => 'employee', 'is_mandatory' => false, 'has_expiry' => true]);
        \App\Models\DocumentTypeModal::create(['name' => 'Experience Letter', 'scope' => 'employee', 'is_mandatory' => false, 'has_expiry' => false]);
        \App\Models\DocumentTypeModal::create(['name' => 'Bank Proof', 'scope' => 'employee', 'is_mandatory' => true, 'has_expiry' => false]);
    }
}
