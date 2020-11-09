<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncrypter;

class EncryptCookies extends BaseEncrypter
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    public function isDisabled($name)
    {
        if (\Str::startsWith($name, 'file-download')) {
            return true;
        }

        return parent::isDisabled($name);
    }


}
