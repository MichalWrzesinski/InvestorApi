<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\Dto\MeDtoOutput;
use App\Dto\ValueInDefaultCurrencyDtoOutput;
use App\Entity\Symbol;
use App\Entity\User;
use App\Entity\UserAsset;
use App\Normalizer\UserAssetNormalizerInterface;
use App\State\Provider\MeProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MeProviderTest extends TestCase
{
    public function testReturnsCorrectData(): void
    {
        $user = $this->createMock(User::class);
        $symbol = $this->createMock(Symbol::class);

        $user->method('getEmail')->willReturn('test@example.com');
        $user->method('getDefaultQuoteSymbol')->willReturn($symbol);
        $symbol->method('getSymbol')->willReturn('USD');

        $asset1 = $this->createMock(UserAsset::class);
        $asset2 = $this->createMock(UserAsset::class);

        $asset1->method('getValueInDefaultCurrency')
            ->willReturn(new ValueInDefaultCurrencyDtoOutput(100.123456, 'USD'));
        $asset2->method('getValueInDefaultCurrency')
            ->willReturn(new ValueInDefaultCurrencyDtoOutput(50.654321, 'USD'));

        $userAssets = [$asset1, $asset2];

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findBy')->willReturn($userAssets);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $userAssetNormalizer = $this->createMock(UserAssetNormalizerInterface::class);
        $userAssetNormalizer->method('normalize')->willReturn(['normalized' => 'asset']);

        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['symbol' => 'USD']);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $provider = new MeProvider(
            $entityManager,
            $userAssetNormalizer,
            $security,
            $normalizer
        );

        $result = $provider->provide($this->createMock(Operation::class));

        $this->assertInstanceOf(MeDtoOutput::class, $result);
        $this->assertSame('test@example.com', $result->email);
        $this->assertSame([['normalized' => 'asset'], ['normalized' => 'asset']], $result->assets);
        $this->assertEquals(['symbol' => 'USD'], $result->defaultQuoteSymbol);
        $this->assertInstanceOf(ValueInDefaultCurrencyDtoOutput::class, $result->totalBalance);
        $this->assertSame(150.777777, $result->totalBalance->value);
        $this->assertSame('USD', $result->totalBalance->quote);
    }

    public function testReturnsNullQuoteSymbolIfNoneSet(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('test@example.com');
        $user->method('getDefaultQuoteSymbol')->willReturn(null);

        $userAssets = [];

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findBy')->willReturn($userAssets);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $userAssetNormalizer = $this->createMock(UserAssetNormalizerInterface::class);
        $userAssetNormalizer->method('normalize')->willReturn([]);

        $normalizer = $this->createMock(NormalizerInterface::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $provider = new MeProvider(
            $entityManager,
            $userAssetNormalizer,
            $security,
            $normalizer
        );

        $result = $provider->provide($this->createMock(Operation::class));

        $this->assertNull($result->defaultQuoteSymbol);
        $this->assertSame('USD', $result->totalBalance->quote);
        $this->assertSame(0.0, $result->totalBalance->value);
    }
}
