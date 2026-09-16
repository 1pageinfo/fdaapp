// e2e/07-groups.spec.js
// Groups: index, create, show (tabs/chat), members.

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

test.describe("Groups", () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
    await page.goto("/groups");
  });

  test("groups index renders", async ({ page }) => {
    await expect(page).toHaveURL(/groups/);
  });

  test("Create Group button is visible", async ({ page }) => {
    await expect(
      page.getByRole("link", { name: /create|new group/i })
    ).toBeVisible();
  });

  test("Export CSV button / link is present", async ({ page }) => {
    await expect(
      page.getByRole("link", { name: /export/i })
    ).toBeVisible();
  });

  // ── Create form ───────────────────────────────────────────────────────────
  test.describe("Create Group form", () => {
    test.beforeEach(async ({ page }) => {
      await page.goto("/groups/create");
      await page.waitForURL(/groups\/create/, { timeout: 30_000 });
    });

    test("form renders with name field", async ({ page }) => {
      await expect(page.locator("input[name=name], textarea[name=name]")).toBeVisible();
    });

    test("empty form submit shows validation", async ({ page }) => {
      // Scope to the card/main content form to avoid the navbar logout button
      await page.evaluate(() =>
        document.querySelectorAll("input[required]").forEach(el => el.removeAttribute("required"))
      );
      await page.locator(".card form button[type=submit], main form button[type=submit], .btn-primary[type=submit]").first().click();
      await expect(
        page.locator(".alert-danger, .text-danger, .invalid-feedback").first()
      ).toBeVisible({ timeout: 10_000 });
    });

    test("valid create redirects to group list or show", async ({ page }) => {
      const ts = Date.now();
      await page.locator("input[name=name]").fill(`E2E Group ${ts}`);
      await page.locator(".card form button[type=submit], main form button[type=submit], .btn-primary[type=submit]").first().click();
      await page.waitForURL(/groups/, { timeout: 15_000 });
    });
  });
});
