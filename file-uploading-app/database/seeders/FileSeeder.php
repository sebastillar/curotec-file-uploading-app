<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\File;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        File::factory()
            ->count(10)
            ->create()
            ->each(function ($file) {
                // Create 1-3 versions for each file
                $versionCount = rand(1, 3);
                for ($i = 1; $i <= $versionCount; $i++) {
                    $file->versions()->create([
                        'version_number' => $i,
                        'path' => "files/dummy/{$file->id}/version_{$i}.{$file->extension}",
                        'created_at' => now()->subDays(rand(0, 30)),
                        'updated_at' => now(),
                    ]);
                }
            });
    }
}
