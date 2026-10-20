<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent\Tests\Unit\Handler;

use Contenir\Extension\Api\Event\Outcome;
use Contenir\Extension\Api\Form\Field;
use Contenir\Extension\Api\Form\FieldType;
use Contenir\Extension\Api\Form\Form;
use Contenir\Extension\Api\Handler\HandlerResult;
use Contenir\Extension\Api\Handler\Screen;
use Contenir\Extension\Consent\Handler\AskEveryoneAgain;
use Contenir\Extension\Consent\Tests\TestAsset\Settings\ArraySettings;
use Contenir\Extension\Consent\Tests\TestAsset\Settings\SettingsContext;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class AskEveryoneAgainTest extends TestCase
{
    /**
     * @return array<string, array{mixed, int}>
     */
    public static function revisions(): array
    {
        return [
            'none yet'       => [0, 1],
            'a number'       => [4, 5],
            'text'           => ['7', 8],
            'negative'       => [-3, 1],
            'negative text'  => ['-3', 1],
            'something else' => [[2], 1],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function ticked(): array
    {
        return [
            '1'    => ['1'],
            'on'   => ['on'],
            'yes'  => ['yes'],
            'true' => ['true'],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>|null}>
     */
    public static function unconfirmed(): array
    {
        return [
            'nothing posted' => [null],
            'not ticked'     => [[]],
            'ticked with no' => [['confirm' => '0']],
            'not text'       => [['confirm' => true]],
        ];
    }

    #[Test]
    #[DataProvider('unconfirmed')]
    public function asksAgainWithoutTheTick(?array $posted): void
    {
        $settings = new ArraySettings();

        static::assertEquals(
            [
                HandlerResult::screen($this->screen())->withMessage(
                    'warning',
                    'Tick "Ask every visitor again" to confirm.',
                ),
                [],
            ],
            [$this->handle($this->post($posted), $settings), $settings->saved],
        );
    }

    #[Test]
    public function asksTheEditorToConfirmFirst(): void
    {
        static::assertEquals(HandlerResult::screen($this->screen()), $this->handle(new ServerRequest('GET', '/')));
    }

    #[Test]
    #[DataProvider('ticked')]
    public function raisesTheRevisionOnceConfirmed(string $tick): void
    {
        $settings = new ArraySettings(['revision' => 2]);

        static::assertEquals(
            [
                HandlerResult::back()->withMessage('success', 'Every visitor will be asked again (revision 3).'),
                ['revision' => 3],
            ],
            [$this->handle($this->post(['confirm' => $tick]), $settings), $settings->saved],
        );
    }

    #[Test]
    public function readsTheMethodWhateverItsCase(): void
    {
        $settings = new ArraySettings();

        $this->handle((new ServerRequest('post', '/'))->withParsedBody(['confirm' => '1']), $settings);

        static::assertSame(['revision' => 1], $settings->saved);
    }

    #[Test]
    #[DataProvider('revisions')]
    public function readsTheStoredRevisionAsAWholeNumber(mixed $stored, int $raised): void
    {
        $settings = new ArraySettings(['revision' => $stored]);

        $this->handle($this->post(['confirm' => '1']), $settings);

        static::assertSame(['revision' => $raised], $settings->saved);
    }

    #[Test]
    public function saysTheRevisionCouldNotBeSavedWhenTheHostSaysNothing(): void
    {
        $settings = new ArraySettings([], Outcome::Failed);

        static::assertEquals(
            HandlerResult::back()->withMessage('danger', 'The revision could not be saved.'),
            $this->handle($this->post(['confirm' => '1']), $settings),
        );
    }

    #[Test]
    public function saysWhyTheRevisionCouldNotBeSaved(): void
    {
        $settings = new ArraySettings([], Outcome::Stopped, 'Settings are locked.');

        static::assertEquals(
            HandlerResult::back()->withMessage('danger', 'Settings are locked.'),
            $this->handle($this->post(['confirm' => '1']), $settings),
        );
    }

    private function handle(ServerRequest $request, ArraySettings $settings = new ArraySettings()): HandlerResult
    {
        return (new AskEveryoneAgain())->handle($request, new SettingsContext($settings));
    }

    /**
     * @param array<string, mixed>|null $posted
     */
    private function post(?array $posted): ServerRequest
    {
        return (new ServerRequest('POST', '/'))->withParsedBody($posted);
    }

    private function screen(): Screen
    {
        return new Screen(
            title: 'Ask everyone again',
            intro: 'Every visitor sees the cookie banner again on their next visit, with a line saying the site has '
                . 'changed how it uses cookies. Until they choose again, only what the site needs runs for those '
                . 'who are asked first. Do this after adding a service that needs consent.',
            form: new Form([
                new Field(
                    name: 'confirm',
                    label: 'Ask every visitor again',
                    type: FieldType::Checkbox,
                    description: "Their earlier choices stop counting. This can't be undone.",
                    required: true,
                ),
            ]),
        );
    }
}
