<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\File;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FileCollaborator>
 */
class FileCollaboratorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_id' => File::factory(),
            'user_id' => User::factory(),
            'role' => $this->faker->randomElement(['viewer', 'commenter', 'editor']),
            'revoked_at' => null,
        ];
    }

    public function viewer()
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'viewer',
        ]);
    }

    public function commenter()
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'commenter',
        ]);
    }

    public function editor()
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'editor',
        ]);
    }

    public function revoked()
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => now(),
        ]);
    }
}
