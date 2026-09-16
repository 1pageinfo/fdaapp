// e2e/12-journeys.spec.js
// End-to-end user journeys: login→dashboard→sanghs→create→list, etc.

import { test, expect } from "@playwright/test";
import { login, logout } from "./helpers/auth.js";

// Helper to click the main form submit (not navbar logout button)
async function clickMainSubmit(page) {
  const btn = page.locator(
    ".card form button[type=submit], main form button[type=submit], .btn-primary[type=submit]"
  ).first();
  await btn.waitFor({ state: "visible", timeout: 15_000 });
  await btn.click();
}

test.describe("Critical User Journeys", () => {

  // ── Journey 1: Login → Dashboard → Profile → Update → Logout ─────────────
  test("Journey: Login → Dashboard → Profile → Logout", async ({ page }) => {
    // Step 1: Login
    await login(page);
    await expect(page).toHaveURL(/dashboard/);

    // Step 2: Navigate to profile
    await page.goto("/profile");
    await expect(page.getByRole("heading", { name: /my profile/i })).toBeVisible();

    // Step 3: Update phone field (harmless)
    const phoneField = page.locator("input[name=phone]");
    if (await phoneField.isVisible()) {
      await phoneField.fill("+91-9999999999");
    }
    await page.locator(".card form button[type=submit], main form button[type=submit], .btn-primary[type=submit]").first().click();
    // Should redirect back with success
    await page.waitForURL(/profile/);

    // Step 4: Logout
    await logout(page);
    await expect(page).toHaveURL(/login/);
  });

  // ── Journey 2: Login → Sanghs → Apply Filter → View Record ───────────────
  test("Journey: Login → Sanghs → Filter → View", async ({ page }) => {
    await login(page);

    // Go to Sanghs
    await page.goto("/sanghs");
    await expect(page.getByText("Sangh Management")).toBeVisible();

    // Apply a status filter
    await page.locator("select[name=payment_status]").selectOption("paid");
    await page.getByRole("button", { name: /apply filters/i }).click();
    await page.waitForURL(/payment_status=paid/);

    // Page should still render
    await expect(page.locator(".card, table").first()).toBeVisible();

    // Reset
    await page.getByRole("link", { name: /reset/i }).click();
    await expect(page).toHaveURL(/\/sanghs/);
    expect(page.url()).not.toMatch(/payment_status/);
  });

  // ── Journey 3: Login → Create Meeting → Verify in list ───────────────────
  test("Journey: Create Meeting → Appears in meetings list", async ({ page }) => {
    await login(page);

    // Create a meeting
    await page.goto("/meetings/create");
    await page.waitForURL(/meetings\/create/, { timeout: 30_000 });

    const ts = Date.now();
    const meetingTitle = `E2E Journey Meeting ${ts}`;
    await page.locator("input[name=title]").fill(meetingTitle);
    const future = new Date(Date.now() + 2 * 86_400_000);
    await page.locator("input[name=start_at]").fill(future.toISOString().slice(0, 16));
    await clickMainSubmit(page);
    await page.waitForURL(/meetings/, { timeout: 15_000 });

    // The new meeting should appear in the list
    await expect(page.getByText(meetingTitle, { exact: false })).toBeVisible({ timeout: 10_000 });
  });

  // ── Journey 4: Login → Search → Results ──────────────────────────────────
  test("Journey: Login → Global Search → Results page loads", async ({ page }) => {
    await login(page);
    await page.goto("/dashboard");

    // Type in search box
    await page.locator("#navbar-search-input, input[name=q]").fill("sangh");
    await page.keyboard.press("Enter");
    await page.waitForURL(/search/);

    // Search results page should not throw 500
    await expect(page.getByText(/whoops|server error|500/i)).toHaveCount(0);
  });

  // ── Journey 5: Login → Create Group → Verify ─────────────────────────────
  test("Journey: Create Group → Appears in groups list", async ({ page }) => {
    await login(page);
    await page.goto("/groups/create");
    await page.waitForURL(/groups\/create/, { timeout: 30_000 });

    const ts = Date.now();
    const groupName = `E2E Group ${ts}`;
    await page.locator("input[name=name]").fill(groupName);
    await clickMainSubmit(page);
    await page.waitForURL(/groups/, { timeout: 15_000 });

    // Either on show page or index, the name should appear
    await expect(page.getByText(groupName, { exact: false })).toBeVisible({ timeout: 10_000 });
  });

  // ── Journey 6: Settings → Save ────────────────────────────────────────────
  test("Journey: Open Settings → Save without changes → No error", async ({ page }) => {
    await login(page);
    await page.goto("/settings");
    const saveBtn = page.locator(".card form button[type=submit], main form button[type=submit]").first();
    if (await saveBtn.isVisible()) {
      await saveBtn.click();
      await page.waitForURL(/settings/);
      await expect(page.getByText(/whoops|server error|500/i)).toHaveCount(0);
    }
  });
});
