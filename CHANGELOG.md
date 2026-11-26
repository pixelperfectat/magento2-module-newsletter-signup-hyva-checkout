# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-11-26

### Added

- Sign-up line with a short benefit in the payment step of Hyvä Checkout, never ticked by default, shown only when the core newsletter sign-up module invites the email address on the shopper's cart.
- The line is checked again whenever an address of the cart is saved or switched, and a tick is dropped when the address is no longer invited.
- After the order, a ticked line signs the order's email address up through the core module, with Magento's own confirmation email and the source "Checkout".
- The module adds itself to the Hyvä Tailwind configuration.
- Playwright storefront tests for the line, for a development or staging store.

[Unreleased]: https://github.com/pixelperfectat/magento2-module-newsletter-signup-hyva-checkout/compare/0.1.0...HEAD
[0.1.0]: https://github.com/pixelperfectat/magento2-module-newsletter-signup-hyva-checkout/releases/tag/0.1.0
