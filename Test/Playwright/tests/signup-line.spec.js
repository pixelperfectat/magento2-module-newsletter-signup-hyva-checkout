// @ts-check
const { test, expect, freshEmail } = require('./helpers/shop');

test.describe('Newsletter sign-up line in Hyvä Checkout', () => {
  test('is shown unticked to a new guest', async ({ shop }) => {
    const result = await shop.placeGuestOrder(freshEmail('line-shown'));

    expect(result.lineVisible).toBe(true);
    expect(result.lineTickedByDefault).toBe(false);
  });

  test('ticked: the confirmation page shows a note instead of the sign-up button', async ({ shop }) => {
    await shop.placeGuestOrder(freshEmail('line-ticked'), { tickNewsletter: true });

    const block = shop.confirmationBlock();
    await expect(block).toBeVisible();
    await expect(block.locator('form')).toHaveCount(0);
  });

  test('left unticked: the confirmation page invites with the button', async ({ shop }) => {
    await shop.placeGuestOrder(freshEmail('line-unticked'));

    await expect(shop.confirmationBlock().locator('form button[type="submit"]')).toBeVisible();
  });

  test('is hidden for an address that is already subscribed', async ({ shop }) => {
    const email = freshEmail('line-subscribed');
    await shop.subscribeInFooter(email);

    const result = await shop.placeGuestOrder(email);

    expect(result.lineVisible).toBe(false);
    await expect(shop.confirmationBlock()).toHaveCount(0);
  });
});
