// e2e/helpers/auth.js
export const TEST_EMAIL    = process.env.E2E_EMAIL    || "admin@example.com";
export const TEST_PASSWORD = process.env.E2E_PASSWORD || "password123";
export const APP_URL       = process.env.E2E_BASE_URL || "http://127.0.0.1:8000";

/**
 * Log in via the UI login form and wait for the dashboard.
 * Uses a generous 60s timeout because the Laravel dashboard page
 * can be slow on first load (session init, asset compilation, etc.)
 */
export async function login(page) {
  await page.goto("/login", { waitUntil: "domcontentloaded" });
  await page.locator("input[name=email]").fill(TEST_EMAIL);
  await page.locator("input[name=password]").fill(TEST_PASSWORD);
  await page.locator("button[type=submit]").click();
  // Wait for dashboard redirect - generous timeout for slow first-load
  await page.waitForURL(/dashboard/, { timeout: 60_000 });
}

/**
 * Log out via form submit (avoids UI dropdown flakiness).
 */
export async function logout(page) {
  await page.evaluate(() => {
    const form = document.querySelector("form[action*=logout]");
    if (form) form.submit();
  });
  await page.waitForURL(/login/, { timeout: 30_000 });
}
