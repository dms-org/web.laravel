<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold\Domain;

use Dms\Web\Laravel\Scaffold\Domain\DomainObjectRelation;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectRelationMode;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;
use Dms\Web\Laravel\Scaffold\Domain\DomainStructure;
use Dms\Web\Laravel\Scaffold\Domain\DomainStructureLoader;
use Dms\Web\Laravel\Tests\Integration\CmsIntegrationTest;
use Dms\Web\Laravel\Tests\Integration\Fixtures\Demo\DemoFixture;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\Simple\Domain\TestEntity;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestColourValueObject;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestDateTimeRangeValueObject;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestDateTimeValueObject;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestFileValueObject;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestGeoValueObject;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestMoneyValueObject;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestValueObject;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestValueObjectWithEnum;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestWebValueObject;

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
                    new DomainObjectStructure(TestColourValueObject::definition()),
                    new DomainObjectStructure(TestDateTimeRangeValueObject::definition()),
                    new DomainObjectStructure(TestDateTimeValueObject::definition()),
                    new DomainObjectStructure(TestFileValueObject::definition()),
                    new DomainObjectStructure(TestGeoValueObject::definition()),
                    new DomainObjectStructure(TestMoneyValueObject::definition()),
                    new DomainObjectStructure(TestValueObject::definition()),
                    new DomainObjectStructure(TestWebValueObject::definition()),
                ]),
            ],
            $this->toRelationFixture(),
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

    /**
     * @return array
     */
    private function toRelationFixture():array
    {
        $testEntity = new DomainObjectStructure(\Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ToOneRelation\Domain\TestEntity::definition());
        $testRelatedEntity = new DomainObjectStructure(\Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ToOneRelation\Domain\TestRelatedEntity::definition());

        $relation = new DomainObjectRelation(
            DomainObjectRelationMode::toOne(),
            $testEntity->getDefinition()->getProperty('related'),
            $testRelatedEntity
        );
        $inverse = new DomainObjectRelation(
            DomainObjectRelationMode::toOne(),
            $testRelatedEntity->getDefinition()->getProperty('parent'),
            $testEntity
        );


        $relation->setInverseRelation($inverse);
        $testEntity->addRelation($relation);
        $testRelatedEntity->addRelation($inverse);

        return [
            'domain_namespace' => 'Dms\\Web\\Laravel\\Tests\\Integration\\Scaffold\\Fixture\\ToOneRelation\\Domain',
            'expected_domain'  => new DomainStructure([
                $testEntity,
                $testRelatedEntity,
            ]),
        ];
    }
}