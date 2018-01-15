<?php

use Illuminate\Database\Seeder;

class ChatterTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        // CREATE THE CATEGORIES

        \DB::table('chatter_categories')->delete();

        \DB::table('chatter_categories')->insert([
            0 => [
                'id'         => 1,
                'parent_id'  => null,
                'order'      => 1,
                'name'       => '基础理论',
                'color'      => '#3498DB',
                'slug'       => '',
                'created_at' => null,
                'updated_at' => null,
            ],
            1 => [
                'id'         => 2,
                'parent_id'  => null,
                'order'      => 2,
                'name'       => '应用技术',
                'color'      => '#2ECC71',
                'slug'       => '',
                'created_at' => null,
                'updated_at' => null,
            ],
            2 => [
                'id'         => 3,
                'parent_id'  => null,
                'order'      => 3,
                'name'       => '谈天说地',
                'color'      => '#9B59B6',
                'slug'       => '',
                'created_at' => null,
                'updated_at' => null,
            ],
            3 => [
                'id'         => 4,
                'parent_id'  => null,
                'order'      => 4,
                'name'       => '杂七杂八',
                'color'      => '#E67E22',
                'slug'       => 'random',
                'created_at' => null,
                'updated_at' => null,
            ]
        ]);
    }
}
