<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent\Tests\Unit\Field;

use Contenir\Extension\Api\Form\FieldType;
use Contenir\Extension\Consent\Field\HeaderField;
use Contenir\Extension\Consent\Tests\TestAsset\Settings\ArraySettings;
use Contenir\Extension\Consent\Tests\TestAsset\Settings\SettingsContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class HeaderFieldTest extends TestCase
{
    /**
     * @return array<string, array{mixed, list<string>}>
     */
    public static function values(): array
    {
        $problem = ['Enter a header name: letters, digits and dashes, such as CF-IPCountry.'];

        return [
            "Cloudflare's"   => ['CF-IPCountry', []],
            "CloudFront's"   => ['CloudFront-Viewer-Country', []],
            'blank'          => ['', []],
            'spaces around'  => ['  CF-IPCountry ', []],
            'not text'       => [3, []],
            'a space inside' => ['CF IPCountry', $problem],
            'a colon'        => ['CF-IPCountry: AU', $problem],
            'a new line'     => ["CF-IPCountry\nX", $problem],
        ];
    }

    #[Test]
    #[DataProvider('values')]
    public function acceptsOnlyAHeaderName(mixed $value, array $problems): void
    {
        static::assertSame($problems, (new HeaderField())->validate($value));
    }

    #[Test]
    public function isATextBoxWithNoChoicesOrComponent(): void
    {
        $field = new HeaderField();

        static::assertSame(
            [FieldType::Text, null, []],
            [$field->control(), $field->component(), $field->options(new SettingsContext(new ArraySettings()))],
        );
    }

    #[Test]
    public function storesTheNameTrimmed(): void
    {
        $field = new HeaderField();

        static::assertSame(['CF-IPCountry', ''], [$field->normalise('  CF-IPCountry '), $field->normalise(null)]);
    }
}
