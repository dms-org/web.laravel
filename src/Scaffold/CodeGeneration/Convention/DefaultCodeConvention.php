<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration\Convention;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DefaultCodeConvention extends CodeConvention
{
    /**
     * @param string $propertyName
     *
     * @return string
     */
    public function getPersistenceColumnName(string $propertyName) : string
    {
        return \Str::snake($propertyName);
    }

    /**
     * @param string $propertyName
     *
     * @return string
     */
    public function getCmsFieldName(string $propertyName) : string
    {
        return \Str::snake($propertyName);
    }

    /**
     * @param string $propertyName
     *
     * @return string
     */
    public function getCmsFieldLabel(string $propertyName) : string
    {
        return ucwords(str_replace('_', ' ', \Str::snake($propertyName)));
    }
}