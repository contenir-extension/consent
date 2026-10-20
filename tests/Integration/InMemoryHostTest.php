<?php

declare(strict_types=1);

namespace Contenir\Extension\Consent\Tests\Integration;

use Contenir\Consent\ConsentConfig;
use Contenir\Consent\GoogleConsentMode;
use Contenir\Consent\RegionalMode;
use Contenir\Extension\Api\Testing\FrozenClock;
use Contenir\Extension\Api\Testing\InMemoryHost;
use Contenir\Extension\Consent\ConsentExtension;
use Contenir\Extension\Consent\ConsentSection;
use DateTimeImmutable;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_replace_recursive;
use function array_reverse;
use function explode;

/**
 * The add-on on the API's in-memory host: settings saved as the settings
 * screen saves them, read back as a site adapter reads them, and the screen
 * that asks everyone again. The in-memory host doesn't run field types, so
 * their checks are unit tested.
 */
#[Group('integration')]
final class InMemoryHostTest extends TestCase
{
    #[Test]
    public function asksEveryoneAgainForAnEditorWhoMayUpdateIt(): void
    {
        $http    = new Psr17Factory();
        $host    = $this->host();
        $request = $http->createServerRequest('POST', '/admin/extension/consent/ask-again')
            ->withParsedBody(['confirm' => '1']);

        $refused  = $host->handle($request, ['extension.consent.ask-again:view']);
        $answered = $host->handle($request, ['extension.consent.ask-again:update']);

        static::assertSame(
            [403, 302, 1],
            [
                $refused->getStatusCode(),
                $answered->getStatusCode(),
                $this->config($host)->revision,
            ],
        );
    }

    #[Test]
    public function savesSettingsTheLibraryReads(): void
    {
        $host = $this->host();
        $host->context('consent')
            ->settings()
            ->set([
                'title'               => 'Cookies',
                'privacy_url'         => '/privacy',
                'regional_mode'       => 'by-country',
                'google_consent_mode' => 'advanced',
            ]);

        $config = $this->config($host);

        static::assertSame(
            ['Cookies', '/privacy', RegionalMode::ByCountry, GoogleConsentMode::Advanced, 'CF-IPCountry'],
            [
                $config->texts->title,
                $config->privacyUrl,
                $config->regionalMode,
                $config->googleConsentMode,
                $config->countryHeader,
            ],
        );
    }

    #[Test]
    public function showsTheConfirmationToAnEditorWhoMayView(): void
    {
        $http     = new Psr17Factory();
        $response = $this->host()->handle(
            $http->createServerRequest('GET', '/admin/extension/consent/ask-again'),
            ['extension.consent.ask-again:view'],
        );

        static::assertSame([200, 'Ask everyone again'], [$response->getStatusCode(), (string) $response->getBody()]);
    }

    /**
     * The section's saved values, read as a site adapter reads the settings
     * file: nested under each field's key.
     */
    private function config(InMemoryHost $host): ConsentConfig
    {
        $settings = [];
        foreach ($host->context('consent')->settings()->all() as $field => $value) {
            $settings = array_replace_recursive($settings, $this->nest(ConsentSection::KEYS[$field], $value));
        }

        return ConsentConfig::fromSettings($settings);
    }

    private function host(): InMemoryHost
    {
        $http = new Psr17Factory();
        $host = new InMemoryHost($http, $http, new FrozenClock(new DateTimeImmutable('2026-10-21T09:00:00+00:00')));
        $host->boot(['consent' => new ConsentExtension()], ['consent']);

        return $host;
    }

    /**
     * @return array<string, mixed>
     */
    private function nest(string $key, mixed $value): array
    {
        foreach (array_reverse(explode('.', $key)) as $segment) {
            $value = [$segment => $value];
        }

        /** @var array<string, mixed> $value */
        return $value;
    }
}
