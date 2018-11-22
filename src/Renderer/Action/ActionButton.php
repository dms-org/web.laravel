<?php

namespace Dms\Web\Laravel\Renderer\Action;

use Dms\Core\Model\ITypedObject;

/**
 * The action button class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionButton
{
    /**
     * @var bool
     */
    protected $isPost;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    private $class;

    /**
     * @var callable
     */
    private $urlCallback;

    /**
     * @var callable
     */
    private $objectSupportedCallback;

    /**
     * @var bool
     */
    private $disabled;

    /**
     * RowActionButton constructor.
     *
     * @param bool          $isPost
     * @param string        $name
     * @param string        $label
     * @param string        $class
     * @param callable      $urlCallback
     * @param callable|null $objectSupportedCallback
     * @param bool          $disabled
     */
    public function __construct(
        bool $isPost,
        string $name,
        string $label,
        string $class,
        callable $urlCallback,
        callable $objectSupportedCallback = null,
        bool $disabled = false
    ) {
        $this->isPost                  = $isPost;
        $this->name                    = $name;
        $this->label                   = $label;
        $this->class                   = $class;
        $this->urlCallback             = $urlCallback;
        $this->objectSupportedCallback = $objectSupportedCallback;
        $this->disabled                = $disabled;
    }

    /**
     * @return boolean
     */
    public function isPost(): bool
    {
        return $this->isPost;
    }

    /**
     * @param string $objectId
     *
     * @return string
     */
    public function getUrl(string $objectId): string
    {
        return call_user_func($this->urlCallback, $objectId);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function hasObjectSupportedCallback(): bool
    {
        return $this->objectSupportedCallback !== null;
    }

    /**
     * @param ITypedObject $object
     *
     * @return bool
     */
    public function isSupported(ITypedObject $object): bool
    {
        return $this->objectSupportedCallback
            ? call_user_func($this->objectSupportedCallback, $object)
            : true;
    }

    /**
     * @return boolean
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }
}