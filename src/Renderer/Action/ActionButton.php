<?php

namespace Dms\Web\Laravel\Renderer\Action;

use Dms\Core\Table\ITableRow;

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
     * @var callable
     */
    private $urlCallback;

    /**
     * RowActionButton constructor.
     *
     * @param bool     $isPost
     * @param string   $name
     * @param string   $label
     * @param callable $urlCallback
     */
    public function __construct(bool $isPost, string $name, string $label, callable $urlCallback)
    {
        $this->isPost      = $isPost;
        $this->name        = $name;
        $this->label       = $label;
        $this->urlCallback = $urlCallback;
    }

    /**
     * @return boolean
     */
    public function isPost() : bool
    {
        return $this->isPost;
    }

    /**
     * @param string $objectId
     *
     * @return string
     */
    public function getUrl(string $objectId) : string
    {
        return call_user_func($this->urlCallback, $objectId);
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel() : string
    {
        return $this->label;
    }
}