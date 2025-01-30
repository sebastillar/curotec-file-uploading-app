<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extensions = ['pdf', 'doc', 'docx', 'jpg', 'png'];
        $extension = $this->faker->randomElement($extensions);

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'png' => 'image/png'
        ];

        return [
            'name' => $this->faker->words(3, true) . '.' . $extension,
            'extension' => $extension,
            'mime_type' => $mimeTypes[$extension],
            'size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'created_at' => $this->faker->dateTimeBetween('-1 month'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at']);
            }
        ];
    }
}
