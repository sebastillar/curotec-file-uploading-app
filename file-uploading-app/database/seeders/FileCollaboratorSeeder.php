<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FileCollaborator;
use App\Models\File;
use App\Models\User;
use Faker\Factory as Faker;
class FileCollaboratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get all files except the ones from the first user (to avoid self-collaboration)
        $files = File::whereNot('author_id', 1)->get();

        // Get users for collaboration (excluding file authors)
        $users = User::all();

        foreach ($files as $file) {
            // Get potential collaborators (excluding the author)
            $potentialCollaborators = $users->where('id', '!=', $file->author_id);

            // Randomly select 1-3 users as collaborators
            $collaboratorCount = rand(1, min(3, $potentialCollaborators->count()));

            $selectedUsers = $potentialCollaborators->random($collaboratorCount);

            foreach ($selectedUsers as $user) {
                FileCollaborator::factory()
                    ->create([
                        'file_id' => $file->id,
                        'user_id' => $user->id,
                        // Random role with higher chance of viewer
                        'role' => $faker->randomElement([
                            'viewer', 'viewer', 'viewer',
                            'commenter', 'commenter',
                            'editor'
                        ])
                    ]);
            }
        }
    }
}
