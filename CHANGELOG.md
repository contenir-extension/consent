# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project
adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased] - 2026-10-21

### Changed

- Requires the library by its new name, `contenir/contenir-consent`, where it was `contenir/consent`, after Simon's
  rule that libraries are named after their repositories. The add-on keeps its name, `contenir-extension/consent`.

- Allows contenir/extension-api 0.6, whose changes (services add-ons provide, and their context in settings
  operations) are additions the add-on doesn't use yet, so the CMS can install it beside the Cloudflare add-on.

### Added

- The Cookie Consent settings section (Site Settings > Site): the banner's heading and text, the privacy policy
  link, who is asked first (everyone, the EEA, UK and Switzerland by a country header, or nobody), the country
  header, basic or advanced Google Consent Mode, and the hidden revision, each under the key contenir/consent reads.
- Field types that check the privacy link and the country header as they are saved (`consent:privacy-link`,
  `consent:header`).
- Ask everyone again (`Handler\AskEveryoneAgain`): a confirmation screen that raises the revision, so every visitor
  is asked again, linked from the section's header.
- The documentation, with notes for later add-ons on handlers, hidden fields and contributions.
