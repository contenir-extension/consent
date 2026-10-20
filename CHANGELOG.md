# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project
adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased] - 2026-10-21

### Added

- The Cookie Consent settings section (Site Settings > Site): the banner's heading and text, the privacy policy
  link, who is asked first (everyone, the EEA, UK and Switzerland by a country header, or nobody), the country
  header, basic or advanced Google Consent Mode, and the hidden revision, each under the key contenir/consent reads.
- Field types that check the privacy link and the country header as they are saved (`consent:privacy-link`,
  `consent:header`).
- Ask everyone again (`Handler\AskEveryoneAgain`): a confirmation screen that raises the revision, so every visitor
  is asked again, linked from the section's header.
- The documentation, with notes for later add-ons on handlers, hidden fields and contributions.
