<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\File;
use App\Models\FileVersion;
class FileVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create versions for files that don't have any
        File::doesntHave('versions')->each(function ($file) {
            // Create 1-3 versions for each file
            $versionCount = rand(1, 3);

            for ($i = 1; $i <= $versionCount; $i++) {
                FileVersion::factory()->create([
                    'file_id' => $file->id,
                    'version_number' => $i,
                    'path' => "files/{$file->id}/version_{$i}.{$file->extension}",
                    'created_at' => $file->created_at->addDays($i - 1),
                    'updated_at' => $file->created_at->addDays($i - 1)
                ]);
            }
        });
    }
}
