<?php

namespace Dms\Web\Laravel\Language;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Language\Message;
use Dms\Core\Language\MessageNotFoundException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * The laravel language provider.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LaravelLanguageProvider implements ILanguageProvider
{
    /**
     * @var TranslatorInterface
     */
    protected $laravelTranslator;

    /**
     * LaravelLanguageProvider constructor.
     *
     * @param TranslatorInterface $laravelTranslator
     */
    public function __construct(TranslatorInterface $laravelTranslator)
    {
        $this->laravelTranslator = $laravelTranslator;
    }

    /**
     * Gets the fully formed message string from the supplied message id
     * and parameters
     *
     * @param Message $message
     *
     * @return string
     * @throws MessageNotFoundException
     */
    public function format(Message $message)
    {
        return $this->laravelTranslator->trans(
                $message->getId(),
                $message->getParameters()
        );
    }

    /**
     * Gets the fully formed message strings from the supplied message ids
     * and parameters
     *
     * @param Message[] $messages
     *
     * @return string[]
     * @throws InvalidArgumentException
     * @throws MessageNotFoundException
     */
    public function formatAll(array $messages)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'messages', $messages, Message::class);

        return array_map([$this, 'format'], $messages);
    }
}