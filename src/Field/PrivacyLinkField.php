<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent\Field;

use Contenir\Consent\Service;
use Contenir\Extension\Api\Context\ExtensionContextInterface;
use Contenir\Extension\Api\Field\FieldTypeInterface;
use Contenir\Extension\Api\Form\FieldType;
use Override;

use function is_string;
use function trim;

/**
 * A field holding the link to the site's privacy policy: consent:privacy-link.
 * It must be a path on the site (/privacy) or an http(s) address, so the
 * banner never links anywhere a visitor can't safely follow.
 *
 * @api
 */
final class PrivacyLinkField implements FieldTypeInterface
{
    public const string NAME = 'privacy-link';

    private static function text(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    #[Override]
    public function component(): ?string
    {
        return null;
    }

    #[Override]
    public function control(): FieldType
    {
        return FieldType::Text;
    }

    #[Override]
    public function normalise(mixed $value): mixed
    {
        return self::text($value);
    }

    #[Override]
    public function options(ExtensionContextInterface $context): array
    {
        return [];
    }

    #[Override]
    public function validate(mixed $value): array
    {
        $link = self::text($value);
        if ('' === $link || Service::link($link)) {
            return [];
        }

        return ['Enter a page on this site, such as /privacy, or an address starting with https://.'];
    }
}
