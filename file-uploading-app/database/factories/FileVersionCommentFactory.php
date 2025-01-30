<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FileVersion;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FileVersionComment>
 */
class FileVersionCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_version_id' => FileVersion::factory(),
            'comment' => $this->faker->paragraph(),
            'created_at' => function (array $attributes) {
                $fileVersion = FileVersion::find($attributes['file_version_id']);
                return $this->faker->dateTimeBetween($fileVersion->created_at);
            },
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at']);
            }
        ];
    }

    /**
     * State for review comments
     */
    public function review()
    {
        return $this->state(function () {
            return [
                'comment' => 'Please review the changes made in this version.'
            ];
        });
    }

    /**
     * State for approval comments
     */
    public function approval()
    {
        return $this->state(function () {
            return [
                'comment' => 'Changes approved. Ready for next phase.'
            ];
        });
    }
}
