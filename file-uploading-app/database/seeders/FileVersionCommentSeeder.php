<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FileVersion;
use App\Models\FileVersionComment;
class FileVersionCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add comments to each file version
        FileVersion::all()->each(function ($fileVersion) {
            // Create 0-3 random comments per version
            $commentCount = rand(0, 3);

            if ($commentCount > 0) {
                // First comment is always a review request
                FileVersionComment::factory()
                    ->review()
                    ->create([
                        'file_version_id' => $fileVersion->id,
                        'created_at' => $fileVersion->created_at->addMinutes(rand(30, 120))
                    ]);

                // If more comments, add them
                if ($commentCount > 1) {
                    // Last comment is approval if there are multiple comments
                    FileVersionComment::factory()
                        ->approval()
                        ->create([
                            'file_version_id' => $fileVersion->id,
                            'created_at' => $fileVersion->created_at->addMinutes(rand(180, 360))
                        ]);
                }

                // Add any remaining random comments
                if ($commentCount > 2) {
                    FileVersionComment::factory()->create([
                        'file_version_id' => $fileVersion->id,
                        'created_at' => $fileVersion->created_at->addMinutes(rand(120, 180))
                    ]);
                }
            }
        });
    }
}
