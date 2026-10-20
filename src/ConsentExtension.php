<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent;

use Contenir\Extension\Api\ExtensionInterface;
use Contenir\Extension\Api\Registry;
use Contenir\Extension\Consent\Field\HeaderField;
use Contenir\Extension\Consent\Field\PrivacyLinkField;
use Contenir\Extension\Consent\Handler\AskEveryoneAgain;
use Override;

/**
 * The Consent add-on: a settings section for the cookie banner, field types
 * that check its link and header as they are saved, and a screen that asks
 * every visitor again. Sites draw the banner with a site adapter, such as
 * contenir/consent-laminas-mvc, from the settings this section saves.
 *
 * @api
 */
final class ConsentExtension implements ExtensionInterface
{
    #[Override]
    public function register(Registry $registry): void
    {
        $registry->fieldType(PrivacyLinkField::NAME, PrivacyLinkField::class);
        $registry->fieldType(HeaderField::NAME, HeaderField::class);

        $registry->settingsSection(ConsentSection::define($registry->handle()));

        $registry->handler(AskEveryoneAgain::NAME, AskEveryoneAgain::class);
    }
}
