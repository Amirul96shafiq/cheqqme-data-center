<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix existing clients with incorrect phone number formats
        $clients = \App\Models\Client::all();

        foreach ($clients as $client) {
            $currentNumber = $client->pic_contact_number;

            // Skip if already in correct format
            if (preg_match('/^\+6[025]\d{8,9}$/', $currentNumber)) {
                continue;
            }

            // Generate a new proper phone number based on random country
            $country = rand(1, 3);

            switch ($country) {
                case 1: // Malaysia
                    $prefix = '60';
                    $areaCode = rand(1, 9);
                    if ($areaCode == 1) {
                        $mobilePrefix = rand(0, 9);
                        $newNumber = '+' . $prefix . '1' . $mobilePrefix . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
                    } else {
                        $newNumber = '+' . $prefix . $areaCode . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
                    }
                    break;

                case 2: // Indonesia
                    $prefix = '62';
                    $areaCode = rand(2, 8);
                    if ($areaCode == 6) {
                        $subArea = rand(1, 9);
                        $newNumber = '+' . $prefix . $areaCode . $subArea . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                    } else {
                        $newNumber = '+' . $prefix . $areaCode . rand(1, 9) . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
                    }
                    break;

                case 3: // Singapore
                    $prefix = '65';
                    $areaCode = rand(3, 9);
                    if ($areaCode == 9) {
                        $newNumber = '+' . $prefix . '9' . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
                    } else {
                        $newNumber = '+' . $prefix . $areaCode . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
                    }
                    break;

                default:
                    $newNumber = '+601' . rand(0, 9) . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            }

            $client->pic_contact_number = $newNumber;
            $client->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
