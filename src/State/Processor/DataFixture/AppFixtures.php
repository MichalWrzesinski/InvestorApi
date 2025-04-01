<?php

declare(strict_types=1);

namespace App\State\Processor\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\LoaderInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly LoaderInterface $loader,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->loader->load(fixturesFiles: [
            __DIR__.'/../../../../fixtures/symbol.yaml',
            __DIR__.'/../../../../fixtures/user.yaml',
            __DIR__.'/../../../../fixtures/user_asset.yaml',
            __DIR__.'/../../../../fixtures/user_asset_operation.yaml',
            __DIR__.'/../../../../fixtures/exchange_rate.yaml',
        ]);
    }
}
