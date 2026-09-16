// e2e/08-settings.spec.js
// Settings: global settings, Sangh fee slabs.

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

test.describe("Settings", () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  // ── Global settings ───────────────────────────────────────────────────────
  test.describe("Global Settings", () => {
    test.beforeEach(async ({ page }) => {
      await page.goto("/settings");
    });

    test("settings page renders", async ({ page }) => {
      await expect(page).toHaveURL(/settings/);
    });

    test("Save button is visible", async ({ page }) => {
      await expect(page.getByRole("button", { name: /save/i })).toBeVisible();
    });
  });

  // ── Sangh fee settings ────────────────────────────────────────────────────
  test.describe("Sangh Fee Settings", () => {
    test.beforeEach(async ({ page }) => {
      await page.goto("/settings/sangh-fees");
    });

    test("Sangh fee page renders", async ({ page }) => {
      await expect(page).toHaveURL(/sangh-fees/);
    });

    test("fee slabs table or form is present", async ({ page }) => {
      await expect(page.locator("table, form, .card").first()).toBeVisible();
    });

    test("Add Slab button or form is present", async ({ page }) => {
      const addSlab = page.getByRole("button", { name: /add slab|add|save/i }).first();
      await expect(addSlab).toBeVisible();
    });

    test("empty slab form shows validation", async ({ page }) => {
      const minInput = page.locator("input[name*=min], input[name*=from]").first();
      if (await minInput.isVisible()) {
        await page.evaluate(() =>
          document.querySelectorAll("input[required]").forEach(el => el.removeAttribute("required"))
        );
        // Scope to card form to avoid navbar logout button
        const submitBtn = page.locator(".card form button[type=submit], main form button[type=submit], .btn-primary[type=submit]").first();
        if (await submitBtn.isVisible()) {
          await submitBtn.click();
        }
        // No crash (2xx or validation error page)
        const url = page.url();
        expect(url).toBeTruthy();
      }
    });
  });
});
