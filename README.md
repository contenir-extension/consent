# contenir-extension/consent

[![Continuous Integration](https://github.com/contenir-extension/consent/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/contenir-extension/consent/actions/workflows/continuous-integration.yml)

The Consent add-on for Contenir CMS: a cookie banner and preferences, regional modes, Global Privacy Control, Google
Consent Mode v2, and gating for every add-on's scripts on public pages. The consent log, which proves each choice,
follows in a later version.

It is built like the [Tracking add-on](https://github.com/contenir-extension/tracking), whose README describes how
every add-on is laid out, built, tested and released.

## What it adds

**A settings section**, Site Settings > Site > Cookie Consent:

| Field | Stored under | Default |
|---|---|---|
| Banner heading | `extensions.consent.title` | blank: "We use cookies" |
| Banner text | `extensions.consent.description` | blank: a short explanation |
| Privacy policy | `extensions.consent.privacy_url` | blank: no link |
| Who is asked first | `extensions.consent.regional_mode` | ask everyone first |
| Country header | `extensions.consent.country_header` | `CF-IPCountry` |
| Google Consent Mode | `extensions.consent.google_consent_mode` | basic |
| Revision (hidden) | `extensions.consent.revision` | 0 |

The privacy link and the header are checked as they are saved, by the add-on's field types (`consent:privacy-link`,
`consent:header`).

"Who is asked first" offers three modes (CON-1):

- **Ask everyone first.** Nothing but what the site needs runs until a visitor chooses.
- **Ask visitors from the EEA, UK and Switzerland; tell everyone else.** The country comes from the header above.
  A visitor without one is asked.
- **Tell visitors, without asking first.** Everything runs at once, and the banner offers the same choices.

**Ask everyone again**, a link in the section's header. It opens a screen where an editor confirms, then raises the
revision, so every visitor's stored choice stops counting and the banner asks again. It needs the right to update the
section (`extension.consent.ask-again`).

**On public pages**, through a site adapter such as
[contenir/contenir-consent-laminas-mvc](https://github.com/contenir/contenir-consent-laminas-mvc):

- the banner, on [vanilla-cookieconsent](https://cookieconsent.orestbida.com) v3, in the bottom right, with Accept
  all, Reject all and Manage preferences equally prominent;
- the preferences: necessary, analytics, marketing and preferences, each listing its services, with the Tracking
  add-on's tags listed from the codes the site has set;
- every script that isn't necessary held back until its category is allowed, whether it comes from an add-on (P14)
  or the site's own embeds (`consentScript()`);
- Google Consent Mode's defaults denied before any tag, and the `contenir:consent` event the Tracking add-on listens
  for, fired from the stored choice on every page;
- Global Privacy Control keeping marketing off by default, and recorded with the choice;
- choices kept for 182 days, and `consentPreferencesLink()` for the footer.

[contenir/contenir-consent](https://github.com/contenir/contenir-consent) describes all of it.

**With the Tracking add-on**, the Tracking adapter holds the tags back for the banner, whatever Tracking's "Wait for
consent" says, and Tracking's consent check passes (TRK-1).

It needs no listeners, tables or other add-ons. It stores no personal data: the visitor's choice stays in their
browser's `cc_cookie` cookie.

## Requirements

- PHP 8.3, 8.4 or 8.5
- A CMS that hosts contenir/contenir-extension-api 0.6.1 or later

## Installing

```bash
composer require contenir-extension/consent
```

Then enable it in Site Settings > Extensions, or in the site's `config/autoload/extensions.global.php`:

```php
return ['extensions' => ['enabled' => ['tracking', 'consent']]];
```

Enable it only once the site's layout prints the adapter's helpers. Until then there is no banner, and with the
Tracking add-on the tags would wait for a choice nobody can make.

---

## Notes for later add-ons

What this add-on does differently from the Tracking reference, and why:

- **An action that writes the add-on's own settings is an admin handler, not an operation.** Operations (buttons on a
  settings section) get an `OperationContextInterface`, which has no `settings()`. A handler gets the full
  `ExtensionContextInterface`. So "Ask everyone again" is a handler (`Registry::handler()`), which answers GET with a
  described `Screen` holding a confirmation form, and POST by saving through `settings()->set()` and answering
  `HandlerResult::back()` with a message. The CMS's screen form carries its handler token. A handler needs View to
  read and Update to post, so viewers can see the screen but not use it.
- **A value only code changes is a hidden field.** `settings()->set()` refuses an undeclared field, so the revision
  is declared as `FieldType::Hidden`.
- **The section links to its handler with a contribution** (`region: 'layout-nav'`, `type: 'link'`). The URL is the
  CMS's `/admin/extension/<handle>/<name>`, since the API has no URL builder for handlers yet.
- **The in-memory host doesn't run field types on save.** Test their `validate()` and `normalise()` directly.
- **Browser assets belong to the library**, so the Laminas and Mezzio adapters serve the same files. The add-on
  doesn't declare the banner as a P14 script: it depends on the request (the visitor's country) and on where the site
  adapter serves the files.

## Licence

BSD-3-Clause. See [LICENSE.md](LICENSE.md).
