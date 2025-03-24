<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;

class PostSeeder extends Seeder
{
    public function run()
    {
        Post::create([
            'title' => 'First Post',
            'text' => 'example text 1'
        ]);

        Post::create([
            'title' => 'Second Post',
            'text' => 'example text 2'
        ]);
    }
}