// @ts-check
const { defineConfig, devices } = require('@playwright/test');
require('dotenv').config();

/**
 * Playwright configuration for the newsletter sign-up line in Hyvä Checkout
 *
 * Every test places real orders and creates real subscribers with fresh addresses on a reserved domain,
 * so run it against a development or staging store, never production.
 *
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  testDir: './tests',
  timeout: 180000,
  expect: { timeout: 15000 },
  fullyParallel: false,
  workers: 1,
  retries: process.env.CI ? 1 : 0,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    ...devices['Desktop Chrome'],
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'https://shop.example.com',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    // Required for self-signed SSL certificates in development
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 1000 },
  },
  outputDir: './test-artifacts/',
});
