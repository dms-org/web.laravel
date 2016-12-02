<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold;

use Dms\Web\Laravel\Scaffold\NamespaceDirectoryResolver;
use Symfony\Component\Finder\Finder;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ScaffoldCmsTest extends ScaffoldTest
{
    public function scaffoldDomains()
    {
        $fixtures = [];

        foreach (Finder::create()
                     ->in(__DIR__ . '/Fixture')
                     ->depth('== 0')
                     ->directories()
                 as $directory) {
            $fixtures[] = [
                'name'             => $directory->getFilename(),
                'entity_namespace' => __NAMESPACE__ . '\\Fixture\\' . $directory->getFilename() . '\\Domain',
                'domain_path'      => $directory->getRealPath() . '/Domain',
                'cms_path'         => $directory->getRealPath() . '/Cms',
            ];
        }

        return $fixtures;
    }

    /**
     * @dataProvider scaffoldDomains
     */
    public function testScaffold(string $name, string $entityNamespace, string $domainPath, string $cmsPath)
    {
        $tempCmsPath = __DIR__ . '/temp/' . str_random();

        foreach (Finder::create()->files()->in($domainPath) as $file) {
            /** @var \SplFileInfo $file */
            require_once $file->getRealPath();
        }

        $this->app[NamespaceDirectoryResolver::class] = $this->mockNamespaceDirectoryResolver([
            __NAMESPACE__ . '\\Fixture\\' . $name . '\\Domain'               => $domainPath,
            __NAMESPACE__ . '\\Fixture\\' . $name . '\\Cms'                  => $tempCmsPath,
            __NAMESPACE__ . '\\Fixture\\' . $name . '\\Cms\\Modules'         => $tempCmsPath . '/Modules',
            __NAMESPACE__ . '\\Fixture\\' . $name . '\\Cms\\Modules\\Fields' => $tempCmsPath . '/Modules/Fields',
        ]);;

        $this->getConsole()->call('dms:scaffold:cms', [
            'package_name'          => $name,
            'entity_namespace'      => $entityNamespace,
            'output_namespace'      => __NAMESPACE__ . '\\Fixture\\' . $name . '\\Cms',
            'data_source_namespace' => 'Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\\' . $name . '\Persistence\Services',
        ]);

        $this->assertDirectoriesEqual($cmsPath, $tempCmsPath);
    }
}