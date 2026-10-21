<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent;

use Contenir\Consent\ConsentConfig;
use Contenir\Consent\GoogleConsentMode;
use Contenir\Consent\RegionalMode;
use Contenir\Consent\SettingKeys;
use Contenir\Consent\Texts;
use Contenir\Extension\Api\Form\Field;
use Contenir\Extension\Api\Form\FieldType;
use Contenir\Extension\Api\Form\Form;
use Contenir\Extension\Api\Form\Group;
use Contenir\Extension\Api\Handle;
use Contenir\Extension\Api\Operation\Contribution;
use Contenir\Extension\Api\Settings\SettingsCategory;
use Contenir\Extension\Api\Settings\SettingsSection;
use Contenir\Extension\Consent\Field\HeaderField;
use Contenir\Extension\Consent\Field\PrivacyLinkField;
use Contenir\Extension\Consent\Handler\AskEveryoneAgain;

/**
 * The add-on's settings section, Cookie Consent under Site. Each field
 * stores its value under the key contenir/contenir-consent reads, so the site
 * adapters read the same settings the CMS saves.
 *
 * @api
 */
final class ConsentSection
{
    public const string NAME = 'consent';

    public const string TITLE = 'title';

    public const string DESCRIPTION = 'description';

    public const string PRIVACY_URL = 'privacy_url';

    public const string REGIONAL_MODE = 'regional_mode';

    public const string COUNTRY_HEADER = 'country_header';

    public const string GOOGLE_CONSENT_MODE = 'google_consent_mode';

    public const string REVISION = 'revision';

    /**
     * The settings key each field stores its value under.
     */
    public const array KEYS = [
        self::TITLE               => SettingKeys::TITLE,
        self::DESCRIPTION         => SettingKeys::DESCRIPTION,
        self::PRIVACY_URL         => SettingKeys::PRIVACY_URL,
        self::REGIONAL_MODE       => SettingKeys::REGIONAL_MODE,
        self::COUNTRY_HEADER      => SettingKeys::COUNTRY_HEADER,
        self::GOOGLE_CONSENT_MODE => SettingKeys::GOOGLE_CONSENT_MODE,
        self::REVISION            => SettingKeys::REVISION,
    ];

    /**
     * Where the CMS mounts the add-on's admin handlers.
     */
    public const string HANDLERS = '/admin/extension/';

    /**
     * The section, with field types named for the add-on's handle.
     */
    public static function define(Handle $handle): SettingsSection
    {
        return new SettingsSection(
            name: self::NAME,
            title: 'Cookie Consent',
            category: SettingsCategory::Site,
            form: new Form(
                [
                    new Field(
                        name: self::TITLE,
                        label: 'Banner heading',
                        type: FieldType::Text,
                        description: 'Leave blank for "' . Texts::TITLE . '".',
                        setting: SettingKeys::TITLE,
                    ),
                    new Field(
                        name: self::DESCRIPTION,
                        label: 'Banner text',
                        type: FieldType::Textarea,
                        description: 'What the banner tells visitors, as plain text. Leave blank for a short'
                            . ' explanation that some cookies make the site work and others measure visits and'
                            . ' advertising.',
                        setting: SettingKeys::DESCRIPTION,
                        attributes: ['rows' => 4],
                    ),
                    new Field(
                        name: self::PRIVACY_URL,
                        label: 'Privacy policy',
                        type: $handle->fieldType(PrivacyLinkField::NAME),
                        description: 'The page the banner and preferences link to, such as /privacy. Leave blank'
                            . ' for no link.',
                        setting: SettingKeys::PRIVACY_URL,
                    ),
                    new Field(
                        name: self::REGIONAL_MODE,
                        label: 'Who is asked first',
                        type: FieldType::Select,
                        description: 'Asked visitors run nothing but what the site needs until they choose. Told'
                            . ' visitors run everything at once, and the banner offers them the same choices.',
                        setting: SettingKeys::REGIONAL_MODE,
                        default: RegionalMode::OptIn->value,
                        options: self::options(RegionalMode::cases()),
                    ),
                    new Field(
                        name: self::COUNTRY_HEADER,
                        label: 'Country header',
                        type: $handle->fieldType(HeaderField::NAME),
                        description: 'The request header naming the visitor\'s country, when only the EEA, UK and'
                            . ' Switzerland are asked. Cloudflare sends CF-IPCountry. A visitor without one is'
                            . ' asked.',
                        setting: SettingKeys::COUNTRY_HEADER,
                        default: ConsentConfig::COUNTRY_HEADER,
                    ),
                    new Field(
                        name: self::GOOGLE_CONSENT_MODE,
                        label: 'Google Consent Mode',
                        type: FieldType::Select,
                        description: 'Google\'s tags always start with every signal denied. Advanced lets them'
                            . ' send Google cookieless pings before visitors choose, which Google models'
                            . ' conversions from.',
                        setting: SettingKeys::GOOGLE_CONSENT_MODE,
                        default: GoogleConsentMode::Basic->value,
                        options: self::options([GoogleConsentMode::Basic, GoogleConsentMode::Advanced]),
                    ),
                    new Field(
                        name: self::REVISION,
                        label: 'Revision',
                        type: FieldType::Hidden,
                        description: 'Raised by Ask everyone again.',
                        setting: SettingKeys::REVISION,
                        default: 0,
                    ),
                ],
                [
                    new Group('banner', 'Banner', [self::TITLE, self::DESCRIPTION, self::PRIVACY_URL, self::REVISION]),
                    new Group('visitors', 'Visitors', [self::REGIONAL_MODE, self::COUNTRY_HEADER]),
                    new Group('google', 'Google', [self::GOOGLE_CONSENT_MODE]),
                ],
            ),
            contributions: [
                new Contribution(
                    region: 'layout-nav',
                    type: 'link',
                    privilege: 'update',
                    label: 'Ask everyone again',
                    icon: 'refresh',
                    url: self::HANDLERS . $handle->value . '/' . AskEveryoneAgain::NAME,
                ),
            ],
        );
    }

    /**
     * Select options from a mode's cases: value => label.
     *
     * @param list<RegionalMode|GoogleConsentMode> $cases
     *
     * @return array<string, string>
     */
    private static function options(array $cases): array
    {
        $options = [];
        foreach ($cases as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
