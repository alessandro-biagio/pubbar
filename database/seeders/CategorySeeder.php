<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'name'        => 'Panini',
                'description' => 'Classici e gourmet',
                'image_path'  => 'categories/beAAtAF5KtohygcemhnH3rrGF7OCUPM5ZQhhQmSk.jpg',
            ],
            [
                'name'        => 'Birre',
                'description' => 'Artigianali e alla spina',
                'image_path'  => 'categories/kiqmUnzlhAWu8fxHzYF5qwqx2zbRb9T80azTq04E.jpg',
            ],
            [
                'name'        => 'Sfiziosità',
                'description' => 'Fritti e stuzzichini',
                'image_path'  => 'categories/RTa3SKroX2y1BNDw35PfGaix6UdP67fvpq8tqk7T.webp',
            ],
        ];

        foreach ($rows as $r) {
            Category::updateOrCreate(
                ['slug' => Str::slug($r['name'])],
                [
                    'name'        => $r['name'],
                    'description' => $r['description'],
                    'image_path'  => $r['image_path'],
                    'is_active'   => true,
                ]
            );
        }
    }
}
