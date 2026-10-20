<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent\Field;

use Contenir\Extension\Api\Context\ExtensionContextInterface;
use Contenir\Extension\Api\Field\FieldTypeInterface;
use Contenir\Extension\Api\Form\FieldType;
use Override;

use function is_string;
use function preg_match;
use function trim;

/**
 * A field holding the name of a request header: consent:header, such as
 * CF-IPCountry. Letters, digits and dashes only.
 *
 * @api
 */
final class HeaderField implements FieldTypeInterface
{
    public const string NAME = 'header';

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
        $header = self::text($value);
        if ('' === $header || 1 === preg_match('/^[A-Za-z0-9-]+$/', $header)) {
            return [];
        }

        return ['Enter a header name: letters, digits and dashes, such as CF-IPCountry.'];
    }
}
