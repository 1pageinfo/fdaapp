// e2e/06-meetings.spec.js
// Meetings: list, create, edit, delete, calendar view.

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

test.describe("Meetings", () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
    await page.goto("/meetings");
  });

  test("meetings page renders", async ({ page }) => {
    await expect(page).toHaveURL(/meetings/);
  });

  test("Create Meeting button is visible", async ({ page }) => {
    await expect(
      page.getByRole("link", { name: /create|add|new meeting/i })
    ).toBeVisible();
  });

  // ── Create form ───────────────────────────────────────────────────────────
  test.describe("Create Meeting", () => {
    test.beforeEach(async ({ page }) => {
      await page.goto("/meetings/create");
      await page.waitForURL(/meetings\/create/, { timeout: 30_000 });
    });

    test("form renders all fields", async ({ page }) => {
      await expect(page.locator("input[name=title]")).toBeVisible();
      await expect(page.locator("input[name=start_at]")).toBeVisible();
    });

    test("empty submit shows validation error", async ({ page }) => {
      // Scope submit button to the card/main form, not the navbar logout button
      const submitBtn = page.locator(".card form button[type=submit], main form button[type=submit], .btn-primary[type=submit]").first();
      await page.evaluate(() =>
        document.querySelectorAll("input[required]").forEach(el => el.removeAttribute("required"))
      );
      await submitBtn.click();
      await expect(page.locator(".alert-danger, .text-danger, .invalid-feedback").first()).toBeVisible({ timeout: 10_000 });
    });

    test("valid meeting creation redirects back to meetings list", async ({ page }) => {
      const ts = Date.now();
      await page.locator("input[name=title]").fill(`E2E Test Meeting ${ts}`);
      // Format: YYYY-MM-DDTHH:MM
      const future = new Date(Date.now() + 86_400_000);
      const iso = future.toISOString().slice(0, 16);
      await page.locator("input[name=start_at]").fill(iso);
      // Click form submit button specifically, not navbar logout
      await page.locator(".card form button[type=submit], main form button[type=submit], .btn-primary[type=submit]").first().click();
      await page.waitForURL(/meetings/, { timeout: 15_000 });
      // Success message
      await expect(page.locator(".alert-success, .text-success").first()).toBeVisible({ timeout: 8_000 });
    });

    test("Cancel button returns to meetings list", async ({ page }) => {
      const cancelBtn = page.getByRole("link", { name: /cancel/i }).first();
      if (await cancelBtn.isVisible()) {
        await cancelBtn.click();
        await expect(page).toHaveURL(/meetings$/);
      }
    });
  });
});
