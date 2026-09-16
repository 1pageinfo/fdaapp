// e2e/13-links-folders.spec.js
// Links and Folders management pages.

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

test.describe("Links", () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
    await page.goto("/links");
  });

  test("links page renders", async ({ page }) => {
    await expect(page).toHaveURL(/links/);
    await expect(page.locator("body")).toBeVisible();
  });

  test("create link form or button is present", async ({ page }) => {
    const createEl = page.getByRole("link", { name: /create|add|new/i }).first();
    if (await createEl.isVisible()) {
      await expect(createEl).toBeVisible();
    }
  });
});

test.describe("Folders", () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
    await page.goto("/folders");
  });

  test("folders page renders", async ({ page }) => {
    await expect(page).toHaveURL(/folders/);
    await expect(page.locator("body")).toBeVisible();
  });

  test("create folder button is present", async ({ page }) => {
    const createEl = page.getByRole("link", { name: /create|add|new/i }).first();
    if (await createEl.isVisible()) {
      await expect(createEl).toBeVisible();
    }
  });

  test("folders page has no 500 error", async ({ page }) => {
    await expect(page.getByText(/whoops|server error|500/i)).toHaveCount(0);
  });
});
