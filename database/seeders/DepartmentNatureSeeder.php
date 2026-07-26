<?php

namespace Database\Seeders;

use App\Models\DepartmentNature;
use Illuminate\Database\Seeder;

class DepartmentNatureSeeder extends Seeder
{
    public function run(): void
    {
        $natures = [
            'Storage and Supplies',
            'Procurements',
            'Maintenance',
            'Human Resource',
        ];

        foreach ($natures as $nature) {
            DepartmentNature::firstOrCreate(['department_nature' => $nature]);
        }
    }
}