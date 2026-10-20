<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent\Tests\Unit\Field;

use Contenir\Extension\Api\Form\FieldType;
use Contenir\Extension\Consent\Field\PrivacyLinkField;
use Contenir\Extension\Consent\Tests\TestAsset\Settings\ArraySettings;
use Contenir\Extension\Consent\Tests\TestAsset\Settings\SettingsContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class PrivacyLinkFieldTest extends TestCase
{
    /**
     * @return array<string, array{mixed, list<string>}>
     */
    public static function values(): array
    {
        $problem = ['Enter a page on this site, such as /privacy, or an address starting with https://.'];

        return [
            'a page'          => ['/privacy', []],
            'an address'      => ['https://example.com/privacy', []],
            'blank'           => ['', []],
            'spaces'          => ['  /privacy  ', []],
            'not text'        => [3, []],
            'a script link'   => ['javascript:alert(1)', $problem],
            'another site'    => ['//evil.example', $problem],
            'a relative path' => ['privacy', $problem],
        ];
    }

    #[Test]
    #[DataProvider('values')]
    public function acceptsOnlyAPageOnTheSiteOrAWebAddress(mixed $value, array $problems): void
    {
        static::assertSame($problems, (new PrivacyLinkField())->validate($value));
    }

    #[Test]
    public function isATextBoxWithNoChoicesOrComponent(): void
    {
        $field = new PrivacyLinkField();

        static::assertSame(
            [FieldType::Text, null, []],
            [$field->control(), $field->component(), $field->options(new SettingsContext(new ArraySettings()))],
        );
    }

    #[Test]
    public function storesTheLinkTrimmed(): void
    {
        $field = new PrivacyLinkField();

        static::assertSame(['/privacy', ''], [$field->normalise('  /privacy '), $field->normalise(null)]);
    }
}
