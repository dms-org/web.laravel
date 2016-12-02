<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold;

use Dms\Common\Structure\FileSystem\PathHelper;
use Dms\Core\Exception\InvalidArgumentException;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NamespaceDirectoryResolver
{
    /**
     * @param string $namespace
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function getDirectoryFor(string $namespace) : string
    {
        $psr4Rules = require base_path('vendor/composer/autoload_psr4.php');
        $psr0Rules = require base_path('vendor/composer/autoload_namespaces.php');

        foreach ($psr4Rules as $ruleNamespace => list($directory)) {
            if (starts_with($namespace, $ruleNamespace)) {
                return PathHelper::combine($directory, substr($namespace, strlen($ruleNamespace)));
            }
        }

        foreach ($psr0Rules as $ruleNamespace => list($directory)) {
            if (starts_with($namespace, $ruleNamespace)) {
                return PathHelper::combine($directory, $namespace);
            }
        }

        throw InvalidArgumentException::format('No directory could be found for namespace \'%s\'', $namespace);
    }
}