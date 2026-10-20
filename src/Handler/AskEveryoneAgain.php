<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent\Handler;

use Contenir\Extension\Api\Context\ExtensionContextInterface;
use Contenir\Extension\Api\Exception\UnknownSettingException;
use Contenir\Extension\Api\Form\Field;
use Contenir\Extension\Api\Form\FieldType;
use Contenir\Extension\Api\Form\Form;
use Contenir\Extension\Api\Handler\HandlerInterface;
use Contenir\Extension\Api\Handler\HandlerResult;
use Contenir\Extension\Api\Handler\Screen;
use Contenir\Extension\Consent\ConsentSection;
use Override;
use Psr\Http\Message\ServerRequestInterface;

use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function max;
use function sprintf;
use function strtoupper;

/**
 * Ask everyone again: raises the consent revision, so every visitor's stored
 * choice stops counting and the banner asks them again, saying the site has
 * changed how it uses cookies. Use it after adding a service that needs
 * consent. It asks the editor to confirm first, and needs the right to
 * update the section's settings.
 *
 * @api
 */
final class AskEveryoneAgain implements HandlerInterface
{
    public const string NAME = 'ask-again';

    public const string CONFIRM = 'confirm';

    /**
     * Values a ticked checkbox posts.
     */
    private const array TICKED = ['1', 'on', 'yes', 'true'];

    private static function confirmed(ServerRequestInterface $request): bool
    {
        $posted = $request->getParsedBody();

        /** @var mixed $value */
        $value = is_array($posted) ? $posted[self::CONFIRM] ?? null : null;

        return is_string($value) && in_array($value, self::TICKED, strict: true);
    }

    /**
     * The revision as stored, which a hand-edited settings file might hold
     * as text.
     */
    private static function revision(mixed $value): int
    {
        if (is_string($value)) {
            return max(0, (int) $value);
        }

        return is_int($value) ? max(0, $value) : 0;
    }

    private static function screen(): Screen
    {
        return new Screen(
            title: 'Ask everyone again',
            intro: 'Every visitor sees the cookie banner again on their next visit, with a line saying the site has'
                . ' changed how it uses cookies. Until they choose again, only what the site needs runs for those'
                . ' who are asked first. Do this after adding a service that needs consent.',
            form: new Form([
                new Field(
                    name: self::CONFIRM,
                    label: 'Ask every visitor again',
                    type: FieldType::Checkbox,
                    description: 'Their earlier choices stop counting. This can\'t be undone.',
                    required: true,
                ),
            ]),
        );
    }

    /**
     * @throws UnknownSettingException Never: the revision is a declared field.
     */
    #[Override]
    public function handle(ServerRequestInterface $request, ExtensionContextInterface $context): HandlerResult
    {
        if ('POST' !== strtoupper($request->getMethod())) {
            return HandlerResult::screen(self::screen());
        }

        if (! self::confirmed($request)) {
            return HandlerResult::screen(self::screen())->withMessage(
                'warning',
                'Tick "Ask every visitor again" to confirm.',
            );
        }

        $settings = $context->settings();
        $revision = self::revision($settings->get(ConsentSection::REVISION)) + 1;
        $result   = $settings->set([ConsentSection::REVISION => $revision]);

        if (! $result->succeeded()) {
            return HandlerResult::back()->withMessage(
                'danger',
                '' === $result->message ? 'The revision could not be saved.' : $result->message,
            );
        }

        return HandlerResult::back()->withMessage(
            'success',
            sprintf('Every visitor will be asked again (revision %d).', $revision),
        );
    }
}
