<?php

namespace Dms\Web\Laravel\Util;

use Illuminate\Contracts\Config\Repository;

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
    protected $overridesMap;

    /**
     * KeywordTypeIdentifier constructor.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->dangerStrings  = $config->get('dms::keywords.danger', []);
        $this->successStrings = $config->get('dms::keywords.success', []);
        $this->infoStrings    = $config->get('dms::keywords.info', []);
        $this->overridesMap   = $config->get('dms::keywords.overrides', []);
    }


    /**
     * Gets the keyword type from the given string.
     *
     * Returns one of ("danger", "success", "info") or NULL if unknown
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getTypeFromName($name)
    {
        if (isset($this->overridesMap[$name])) {
            return $this->overridesMap[$name];
        }

        if (str_contains($name, $this->dangerStrings)) {
            return 'danger';
        }

        if (str_contains($name, $this->successStrings)) {
            return 'success';
        }

        if (str_contains($name, $this->infoStrings)) {
            return 'info';
        }

        return null;
    }
}