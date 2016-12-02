<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold\Domain;

use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;
use Dms\Web\Laravel\Scaffold\Domain\DomainStructure;
use Dms\Web\Laravel\Scaffold\Domain\DomainStructureLoader;
use Dms\Web\Laravel\Tests\Integration\CmsIntegrationTest;
use Dms\Web\Laravel\Tests\Integration\Fixtures\Demo\DemoFixture;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\Simple\Domain\TestEntity;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestDateTimeValueObject;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestFileValueObject;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestValueObject;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestValueObjectWithEnum;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DomainStructureLoaderTest extends CmsIntegrationTest
{
    protected static function getFixture()
    {
        return new DemoFixture();
    }

    public function domains()
    {
        $fixtures = [
            [
                'domain_namespace' => 'Dms\\Web\\Laravel\\Tests\\Integration\\Scaffold\\Fixture\\Simple\\Domain',
                'expected_domain'  => new DomainStructure([
                    new DomainObjectStructure(
                        TestEntity::definition()
                    ),
                ]),
            ],
            [
                'domain_namespace' => 'Dms\\Web\\Laravel\\Tests\\Integration\\Scaffold\\Fixture\\ValueObject\\Domain',
                'expected_domain'  => new DomainStructure([
                    new DomainObjectStructure(TestValueObject::definition()),
                    new DomainObjectStructure(TestDateTimeValueObject::definition()),
                    new DomainObjectStructure(TestFileValueObject::definition()),
                ]),
            ],
        ];

        return $fixtures;
    }

    /**
     * @dataProvider domains
     */
    public function testDomainStructureLoader(string $domainNamespace, DomainStructure $expected)
    {
        /** @var DomainStructureLoader $loader */
        $loader = app(DomainStructureLoader::class);

        $this->assertEquals(
            $expected,
            $loader->loadDomainStructure($domainNamespace)
        );
    }
}