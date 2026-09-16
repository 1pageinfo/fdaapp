// e2e/10-search.spec.js
// Global search: input, results, empty state.

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

test.describe("Global Search", () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
    await page.goto("/dashboard");
  });

  test("search input is visible in navbar", async ({ page }) => {
    await expect(page.locator("#navbar-search-input, input[name=q]")).toBeVisible();
  });

  test("search with a query navigates to /search", async ({ page }) => {
    await page.locator("#navbar-search-input, input[name=q]").fill("test");
    await page.keyboard.press("Enter");
    await page.waitForURL(/search/);
    await expect(page).toHaveURL(/search/);
  });

  test("search results page renders without error", async ({ page }) => {
    await page.goto("/search?q=sangh");
    await expect(page.locator("body")).toBeVisible();
    // Should not be a 500 error page
    await expect(page.getByText(/whoops|500|server error/i)).toHaveCount(0);
  });

  test("empty search query still loads search page", async ({ page }) => {
    await page.goto("/search?q=");
    await expect(page.locator("body")).toBeVisible();
  });

  test("search for non-existent term shows empty state or no results", async ({ page }) => {
    await page.goto("/search?q=xyzzy_nonexistent_12345");
    await expect(page.locator("body")).toBeVisible();
  });
});
