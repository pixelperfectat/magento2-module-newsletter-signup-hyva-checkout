# Newsletter Signup for Hyvä Checkout

Adds the sign-up line of `pixelperfectat/magento2-module-newsletter-signup` to Hyvä Checkout: a short benefit,
not pre-ticked, shown only to shoppers who are not subscribed.

Hyvä Checkout is a commercial package, so Composer only suggests it. Install it before this module.

## How it works

The line sits under the payment methods of the payment step:

- It shows only when the core module invites the email address on the shopper's own cart: not subscribed, or
  unsubscribed unless the shop hides the checkout invitation for unsubscribed people. Pending and confirmed
  subscribers never see it. It is checked again whenever an address of the cart is saved or switched.
- It is never ticked by default. Ticking stores the choice on the cart, the same way the Luma checkout line
  does. A tick is dropped when the email address on the cart is no longer invited.
- After the order, the core module signs the order's email address up. Magento sends its confirmation email
  when the shop requires one, and the subscriber's source is "Checkout".

Nothing on the storefront takes an email address: the line only ever asks about the cart's own address.

## Settings

The line uses the core module's checkout settings under Stores > Configuration > Customers > Newsletter: the
line on or off, hide for unsubscribed people, and the benefit text, all per store view.

## Hyvä theme styles

The module adds itself to the Hyvä Tailwind configuration. After installing it, run
`bin/magento hyva:config:generate` and rebuild the theme styles, so the classes of its template are compiled.

## Automated testing (Playwright)

The Playwright suite in `Test/Playwright` tests the line in a running store with Hyvä Checkout and both
modules enabled. It is not part of CI. Every test places a real guest order and creates subscribers with
fresh addresses on a reserved domain, so run it against a development or staging store, never production.

```bash
cd Test/Playwright
npm install
npx playwright install chromium
cp .env.example .env
# Edit .env: PLAYWRIGHT_BASE_URL, STORE_CODE, the product and the shipping and payment methods
npm test
```

It checks that a new guest sees the line unticked, that a ticked order ends with the note on the
confirmation page instead of the sign-up button, that an unticked order gets the button, and that a
subscriber sees no line.

## Status

In development, not released yet. The first release is `0.1.0`.

## License

MIT, see [LICENSE](LICENSE).
