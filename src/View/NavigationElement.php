<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\View;

/**
 * The navigation element
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NavigationElement
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string[]
     */
    protected $requiredPermissions;

    /**
     * NavigationElement constructor.
     *
     * @param string   $label
     * @param string   $url
     * @param string[] $requiredPermissionNames
     */
    public function __construct(string $label, string $url, array $requiredPermissionNames = [])
    {
        $this->label               = $label;
        $this->url                 = $url;
        $this->requiredPermissions = $requiredPermissionNames;
    }

    /**
     * @return string
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * @param array $usersPermissionNames
     *
     * @return bool
     */
    public function shouldDisplay(array $usersPermissionNames) : bool
    {
        return count(array_diff($this->requiredPermissions, $usersPermissionNames)) === 0;
    }
}