<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\File;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FileVersion>
 */
class FileVersionFactory extends Factory
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
            'version_number' => function (array $attributes) {
                // Get the current highest version number for this file
                $file = File::find($attributes['file_id']);
                return $file->versions()->max('version_number') + 1;
            },
            'path' => function (array $attributes) {
                $file = File::find($attributes['file_id']);
                return "files/{$file->id}/test.{$file->extension}";
            },
            'created_at' => function (array $attributes) {
                $file = File::find($attributes['file_id']);
                return $file->created_at;
            },
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at']);
            }
        ];
    }

    public function initial()
    {
        return $this->state(function (array $attributes) {
            return [
                'version_number' => 1
            ];
        });
    }
}
