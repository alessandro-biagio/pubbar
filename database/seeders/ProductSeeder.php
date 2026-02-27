<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $panini = Category::where('slug','panini')->first();
        $birre  = Category::where('slug','birre')->first();
        $sfizi  = Category::where('slug','sfiziosita')->first();

        $add = function ($category, $name, $price, $desc = null) {
            if (!$category) return null;

            return Product::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'category_id' => $category->id,
                    'name'        => $name,
                    'description' => $desc,
                    'price'       => $price,     // default per quando non ci sono varianti
                    'is_available'=> true,
                ]
            );
        };

        // Panini (prezzo singolo)
        $add($panini, 'DANDI', 6.00, '(Allergeni 1,4,5) Cotto - Fontina - Maionese - Pomodoro');
        $add($panini, 'Veggie Grigliato', 7.50, 'Verdure grigliate, hummus');
        $add($panini, 'Gourmet Speck & Brie', 8.00, 'Speck, brie, rucola, miele');

        // Birre (prezzo base placeholder; le varianti faranno fede)
        $pils = $add($birre, 'Lauterbacher Brotzeit (Pils)', 0, 'Equilibrata tra malto e luppolo');
        $ipa  = $add($birre, 'IPA Artigianale', 0, 'Agrumata, amaro deciso');

        // Nuove birre
        $sloeb = Product::updateOrCreate(
            ['slug' => Str::slug('SloebER Rossa')],
            [
                'category_id' => $birre->id,
                'name'        => 'SLOEBER ROSSA',
                'description' => 'BELGA - ROSSA DOPPIO MALTO - GR. 7,5% Birra dal color ambrato limpido con una schiuma sottile. Rilascia aromi leggermente fruttati e delicati. Al gusto si avvertono delle note dolci che si esauriscono in un retrogusto amaro.',
                'price'       => 0,
                'is_available'=> true,
                'image_path'  => 'products/sloeb-rossa.jpg', // link gestito via storage:link
            ]
        );

        $hobg = Product::updateOrCreate(
            ['slug' => Str::slug('Hobgoblin IPA Spillata con Carboazoto')],
            [
                'category_id' => $birre->id,
                'name'        => 'HOBGOBLIN IPA SPILLATA CON CARBOAZOTO',
                'description' => 'INGLESE - BIONDA AMBRATA - GR. 5,3% La Hobgoblin IPA rimane fedele alle sue tradizioni ancestrali. Collisione di luppoli del vecchio e nuovo Mondo, aroma tropicale e amarezza succosa unica.',
                'price'       => 0,
                'is_available'=> true,
                'image_path'  => 'products/hobgoblin-ipa.jpg',
            ]
        );

        $gentse = Product::updateOrCreate(
            ['slug' => Str::slug('Gentse Strop Stagionale')],
            [
                'category_id' => $birre->id,
                'name'        => 'GENTSE STROP (Stagionale)',
                'description' => 'BELGA - BIONDA DOPPIO MALTO - GR 6,9% Birra Belga bionda doppio malto ad alta fermentazione, rifermentata in bottiglia. Aroma leggermente fruttato e delicatamente aromatizza.',
                'price'       => 0,
                'is_available'=> true,
                'image_path'  => 'products/gentse-strop.png',
            ]
        );


        // Sfiziosità (prezzo singolo)
        $add($sfizi, 'Patatine Rustiche', 4.50, 'Doppia cottura, croccanti');
        $add($sfizi, 'Olive all’Ascolana', 6.00, 'Ripiene e dorate');
        $add($sfizi, 'Anelli di Cipolla', 5.50, 'Dorati e leggeri');

        // Salviamo gli id birre per il seeder delle varianti
        if ($pils)   config(['seed.ids.pils'   => $pils->id]);
        if ($ipa)    config(['seed.ids.ipa'    => $ipa->id]);
        if ($sloeb)  config(['seed.ids.sloeb'  => $sloeb->id]);
        if ($hobg)   config(['seed.ids.hobg'   => $hobg->id]);
        if ($gentse) config(['seed.ids.gentse' => $gentse->id]);

    }
}
