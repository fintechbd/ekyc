<?php

namespace Fintech\Ekyc\Seeders;

use Fintech\Ekyc\Facades\Ekyc;
use Illuminate\Database\Seeder;

class KycStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = $this->data();

        foreach (array_chunk($data, 200) as $block) {
            set_time_limit(2100);
            foreach ($block as $entry) {
                Ekyc::kycStatus()->create($entry);
            }
        }
    }

    private function data()
    {
        return [];
    }
}
