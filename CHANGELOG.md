# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project
adheres to [Semantic Versioning](https://semver.org/).

## [0.1.1] - 2026-10-21

### Changed

- Accepts contenir/contenir-extension-api 0.7 as well as 0.6, so the CMS can host 0.7. 0.7's changes are
  additions for add-ons, and only hosts must implement the new methods.

## [0.1.0] - 2026-10-21

### Changed

- Requires the library by its new name, `contenir/contenir-consent`, where it was `contenir/consent`, after Simon's
  rule that libraries are named after their repositories. The add-on keeps its name, `contenir-extension/consent`.
  It comes from Packagist at `^0.1`, where it was `0.1.x-dev` through a `vcs` repository entry.
- Requires the API by its new name, contenir/contenir-extension-api `^0.6.1`, its first release under that name, where
  it allowed contenir/extension-api 0.5 and 0.6. The add-on uses nothing 0.6 added.

### Added

- The Cookie Consent settings section (Site Settings > Site): the banner's heading and text, the privacy policy
  link, who is asked first (everyone, the EEA, UK and Switzerland by a country header, or nobody), the country
  header, basic or advanced Google Consent Mode, and the hidden revision, each under the key
  contenir/contenir-consent reads.
- Field types that check the privacy link and the country header as they are saved (`consent:privacy-link`,
  `consent:header`).
- Ask everyone again (`Handler\AskEveryoneAgain`): a confirmation screen that raises the revision, so every visitor
  is asked again, linked from the section's header.
- The documentation, with notes for later add-ons on handlers, hidden fields and contributions.
