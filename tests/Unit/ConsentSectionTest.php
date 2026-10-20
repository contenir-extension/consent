<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent\Tests\Unit;

use Contenir\Consent\SettingKeys;
use Contenir\Extension\Api\Form\Field;
use Contenir\Extension\Api\Form\Group;
use Contenir\Extension\Api\Handle;
use Contenir\Extension\Api\Operation\Contribution;
use Contenir\Extension\Api\Settings\SettingsCategory;
use Contenir\Extension\Api\Settings\SettingsSection;
use Contenir\Extension\Consent\ConsentSection;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[TestGroup('unit')]
final class ConsentSectionTest extends TestCase
{
    #[Test]
    public function asksEveryoneFirstInBasicModeByDefault(): void
    {
        static::assertSame(
            [
                'title'               => null,
                'description'         => null,
                'privacy_url'         => null,
                'regional_mode'       => 'opt-in',
                'country_header'      => 'CF-IPCountry',
                'google_consent_mode' => 'basic',
                'revision'            => 0,
            ],
            $this->byName(static fn(Field $field): mixed => $field->default),
        );
    }

    #[Test]
    public function checksTheLinkAndHeaderWithTheAddOnsFieldTypes(): void
    {
        static::assertSame(
            [
                'title'               => 'text',
                'description'         => 'textarea',
                'privacy_url'         => 'consent:privacy-link',
                'regional_mode'       => 'select',
                'country_header'      => 'consent:header',
                'google_consent_mode' => 'select',
                'revision'            => 'hidden',
            ],
            $this->byName(static fn(Field $field): string => $field->type),
        );
    }

    #[Test]
    public function describesEachFieldForEditors(): void
    {
        static::assertSame(
            [
                'title'               => ['Banner heading', 'Leave blank for "We use cookies".'],
                'description'         => [
                    'Banner text',
                    'What the banner tells visitors, as plain text. Leave blank for a short explanation that some '
                        . 'cookies make the site work and others measure visits and advertising.',
                ],
                'privacy_url'         => [
                    'Privacy policy',
                    'The page the banner and preferences link to, such as /privacy. Leave blank for no link.',
                ],
                'regional_mode'       => [
                    'Who is asked first',
                    'Asked visitors run nothing but what the site needs until they choose. Told visitors run '
                        . 'everything at once, and the banner offers them the same choices.',
                ],
                'country_header'      => [
                    'Country header',
                    "The request header naming the visitor's country, when only the EEA, UK and Switzerland are "
                        . 'asked. Cloudflare sends CF-IPCountry. A visitor without one is asked.',
                ],
                'google_consent_mode' => [
                    'Google Consent Mode',
                    "Google's tags always start with every signal denied. Advanced lets them send Google cookieless "
                        . 'pings before visitors choose, which Google models conversions from.',
                ],
                'revision'            => ['Revision', 'Raised by Ask everyone again.'],
            ],
            $this->byName(static fn(Field $field): array => [$field->label, $field->description]),
        );
    }

    #[Test]
    public function givesTheBannerTextRoomToWrite(): void
    {
        static::assertSame(
            ['rows' => 4],
            $this->byName(static fn(Field $field): array => $field->attributes)['description'],
        );
    }

    #[Test]
    public function groupsTheBannerTheVisitorsAndGoogle(): void
    {
        static::assertEquals(
            [
                new Group('banner', 'Banner', ['title', 'description', 'privacy_url', 'revision']),
                new Group('visitors', 'Visitors', ['regional_mode', 'country_header']),
                new Group('google', 'Google', ['google_consent_mode']),
            ],
            $this->section()->form->groups,
        );
    }

    #[Test]
    public function keepsItsKeysInOnePlace(): void
    {
        static::assertSame(ConsentSection::KEYS, $this->byName(static fn(Field $field): ?string => $field->setting));
    }

    #[Test]
    public function linksToTheScreenThatAsksEveryoneAgain(): void
    {
        static::assertEquals(
            [
                new Contribution(
                    region: 'layout-nav',
                    type: 'link',
                    privilege: 'update',
                    label: 'Ask everyone again',
                    icon: 'refresh',
                    url: '/admin/extension/consent/ask-again',
                ),
            ],
            $this->section()->contributions,
        );
    }

    #[Test]
    public function offersEachModeByItsLabel(): void
    {
        $fields = $this->byName(static fn(Field $field): array => $field->options);

        static::assertSame(
            [
                [
                    'by-country' => 'Ask visitors from the EEA, UK and Switzerland; tell everyone else',
                    'notice'     => 'Tell visitors, without asking first',
                    'opt-in'     => 'Ask everyone first',
                ],
                [
                    'basic'    => 'Basic: load Google tags only once visitors allow them',
                    'advanced' => 'Advanced: load Google tags at once, without cookies until visitors allow them',
                ],
            ],
            [$fields['regional_mode'], $fields['google_consent_mode']],
        );
    }

    #[Test]
    public function sitsUnderSiteAsCookieConsent(): void
    {
        $section = $this->section();

        static::assertSame(
            ['consent', 'Cookie Consent', SettingsCategory::Site],
            [$section->name, $section->title, $section->category],
        );
    }

    #[Test]
    public function storesEachValueUnderTheKeyTheSiteAdaptersRead(): void
    {
        static::assertSame(
            [
                'title'               => SettingKeys::TITLE,
                'description'         => SettingKeys::DESCRIPTION,
                'privacy_url'         => SettingKeys::PRIVACY_URL,
                'regional_mode'       => SettingKeys::REGIONAL_MODE,
                'country_header'      => SettingKeys::COUNTRY_HEADER,
                'google_consent_mode' => SettingKeys::GOOGLE_CONSENT_MODE,
                'revision'            => SettingKeys::REVISION,
            ],
            $this->byName(static fn(Field $field): ?string => $field->setting),
        );
    }

    /**
     * @template T
     *
     * @param callable(Field): T $value
     *
     * @return array<string, T>
     */
    private function byName(callable $value): array
    {
        $fields = [];
        foreach ($this->section()->form->fields as $field) {
            $fields[$field->name] = $value($field);
        }

        return $fields;
    }

    private function section(): SettingsSection
    {
        return ConsentSection::define(new Handle('consent'));
    }
}
