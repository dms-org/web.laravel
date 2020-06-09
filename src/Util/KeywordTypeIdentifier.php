<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Util;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Str;

/**
 * The keyword type identifier class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class KeywordTypeIdentifier
{
    /**
     * @var string[]
     */
    protected $dangerStrings;

    /**
     * @var string[]
     */
    protected $successStrings;

    /**
     * @var string[]
     */
    protected $infoStrings;

    /**
     * @var string[]
     */
    protected $primaryStrings;

    /**
     * @var string[]
     */
    protected $overridesMap;

    /**
     * KeywordTypeIdentifier constructor.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->dangerStrings  = $config->get('dms.keywords.danger', []);
        $this->successStrings = $config->get('dms.keywords.success', []);
        $this->infoStrings    = $config->get('dms.keywords.info', []);
        $this->primaryStrings = $config->get('dms.keywords.primary', []);
        $this->overridesMap   = $config->get('dms.keywords.overrides', []);
    }


    /**
     * Gets the keyword type from the given string.
     *
     * Returns one of ("danger", "success", "info") or the default if unknown
     *
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public static function getClass(string $name, string $default = 'default') : string
    {
        return app(__CLASS__)->getTypeFromName($name, $default);
    }

    /**
     * Gets the keyword type from the given string.
     *
     * Returns one of ("danger", "success", "info") or the default if unknown
     *
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public function getTypeFromName(string $name, string $default = 'default') : string
    {
        if (isset($this->overridesMap[$name])) {
            return $this->overridesMap[$name];
        }

        if (Str::contains($name, $this->dangerStrings)) {
            return 'danger';
        }

        if (Str::contains($name, $this->successStrings)) {
            return 'success';
        }

        if (Str::contains($name, $this->infoStrings)) {
            return 'info';
        }

        if (Str::contains($name, $this->primaryStrings)) {
            return 'primary';
        }

        return $default;
    }
}
