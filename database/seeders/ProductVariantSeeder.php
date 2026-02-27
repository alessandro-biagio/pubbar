<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductVariant;
use App\Models\Product;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        // Recuperiamo gli id (da config se settati dal ProductSeeder o da slug)
        $pilsId   = config('seed.ids.pils')   ?? Product::where('slug','lauterbacher-brotzeit-pils')->value('id');
        $ipaId    = config('seed.ids.ipa')    ?? Product::where('slug','ipa-artigianale')->value('id');
        $sloebId  = config('seed.ids.sloeb')  ?? Product::where('slug','sloebER-rossa')->value('id');
        $hobgId   = config('seed.ids.hobg')   ?? Product::where('slug','hobgoblin-ipa-spillata-con-carboazoto')->value('id');
        $gentseId = config('seed.ids.gentse') ?? Product::where('slug','gentse-strop-stagionale')->value('id');

        $add = function ($productId, $name, $price, $ml = null) {
            if (!$productId) return;
            ProductVariant::updateOrCreate(
                ['product_id' => $productId, 'name' => $name],
                [
                    'volume_ml'   => $ml,
                    'price'       => $price,
                    'is_available'=> true,
                ]
            );
        };

        // Lauterbacher: “Bicchiere 0,2” e “Bicchiere 0,4”
        $add($pilsId, 'Bicchiere 0,2', 3.00, 200);
        $add($pilsId, 'Bicchiere 0,4', 5.00, 400);

        // IPA: bottiglia 0,33 e 0,50
        $add($ipaId, 'Bottiglia 0,33', 6.00, 330);
        $add($ipaId, 'Bottiglia 0,50', 7.50, 500);

        // DANDI panino: due basi
        $dandi = Product::where('slug','dandi')->first();
        if ($dandi) {
            $add($dandi->id, 'Panfocaccia', 6.00, null);
            $add($dandi->id, 'Piadina artigianale', 7.00, null);
        }

        // Nuove birre
        // SLOEBER ROSSA: Bicchiere 0,33
        $add($sloebId, 'Bicchiere 0,33', 5.00, 330);

        // HOBGOBLIN IPA: Pinta
        $add($hobgId, 'Pinta', 5.00, 568); // Pinta UK ≈ 568 ml

        // GENTSE STROP: 0,33
        $add($gentseId, '0,33', 5.00, 330);
    }
}
