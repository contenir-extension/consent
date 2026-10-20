<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent\Tests\Unit;

use Contenir\Extension\Api\Field\FieldTypeDeclaration;
use Contenir\Extension\Api\Handler\HandlerDeclaration;
use Contenir\Extension\Api\Registration;
use Contenir\Extension\Api\Registry;
use Contenir\Extension\Consent\ConsentExtension;
use Contenir\Extension\Consent\Field\HeaderField;
use Contenir\Extension\Consent\Field\PrivacyLinkField;
use Contenir\Extension\Consent\Handler\AskEveryoneAgain;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function array_map;

#[Group('unit')]
final class ConsentExtensionTest extends TestCase
{
    #[Test]
    public function declaresAFieldTypeForTheLinkAndTheHeader(): void
    {
        static::assertSame(
            ['consent:privacy-link' => PrivacyLinkField::class, 'consent:header' => HeaderField::class],
            array_map(
                static fn(FieldTypeDeclaration $type): string => $type->class,
                $this->registration()->fieldTypes,
            ),
        );
    }

    #[Test]
    public function declaresNoScriptsChecksOrListeners(): void
    {
        $registration = $this->registration();

        static::assertSame(
            [[], [], []],
            [$registration->scripts, $registration->diagnostics, $registration->listeners],
        );
    }

    #[Test]
    public function declaresOneSettingsSection(): void
    {
        static::assertSame(['consent'], array_keys($this->registration()->settingsSections));
    }

    #[Test]
    public function declaresTheScreenThatAsksEveryoneAgain(): void
    {
        static::assertSame(
            ['ask-again' => AskEveryoneAgain::class],
            array_map(
                static fn(HandlerDeclaration $handler): string => $handler->class,
                $this->registration()->handlers,
            ),
        );
    }

    private function registration(): Registration
    {
        $registry = new Registry('consent');
        (new ConsentExtension())->register($registry);

        return $registry->registration();
    }
}
