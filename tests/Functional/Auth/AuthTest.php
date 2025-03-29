<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\FunctionalTestCase;

class AuthTest extends FunctionalTestCase
{
    public function testUserCanAuthenticateAndReceiveToken(): void
    {
        $this->assertNotEmpty($this->getJwtToken());
    }
}
