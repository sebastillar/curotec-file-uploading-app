<?php

namespace Tests\Unit\Services\Traits;

use App\Models\User;

trait WithTestUser
{
    protected User $testUser;

    protected function setUpTestUser(): void
    {
        $this->testUser = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        $this->actingAs($this->testUser);
    }
}
