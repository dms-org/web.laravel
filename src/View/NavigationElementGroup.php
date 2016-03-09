<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\View;
use Dms\Core\Exception\InvalidArgumentException;

/**
 * The navigation element group.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NavigationElementGroup
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var NavigationElement[]
     */
    protected $elements;

    /**
     * NavigationElementGroup constructor.
     *
     * @param string              $label
     * @param NavigationElement[] $elements
     */
    public function __construct(string $label, array $elements)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'elements', $elements, NavigationElement::class);

        $this->label    = $label;
        $this->elements = $elements;
    }

    /**
     * @return string
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @return NavigationElement[]
     */
    public function getElements() : array
    {
        return $this->elements;
    }

    /**
     * @param array $usersPermissionNames
     *
     * @return bool
     */
    public function shouldDisplay(array $usersPermissionNames) : bool
    {
        foreach ($this->elements as $element) {
            if (!$element->shouldDisplay($usersPermissionNames)) {
                return false;
            }
        }

        return true;
    }
}