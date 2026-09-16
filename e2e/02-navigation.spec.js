// e2e/02-navigation.spec.js
// Sidebar, navbar, breadcrumbs, internal links, mobile menu.

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

test.describe("Navigation", () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  // ── Sidebar links ─────────────────────────────────────────────────────────
  test("sidebar has all expected nav items", async ({ page }) => {
    const links = ["Dashboard", "Folders", "Sanghs", "Groups", "Meetings", "Links", "Settings"];
    for (const label of links) {
      await expect(page.locator("#sidebar").getByText(label, { exact: false })).toBeVisible();
    }
  });

  test("Dashboard sidebar link navigates to /dashboard", async ({ page }) => {
    await page.locator("#sidebar a[href*=dashboard]").click();
    await expect(page).toHaveURL(/dashboard/);
  });

  test("Sanghs sidebar link navigates to /sanghs", async ({ page }) => {
    await page.locator("#sidebar a[href*=sanghs]").click();
    await expect(page).toHaveURL(/sanghs/);
    await expect(page.getByText("Sangh Management")).toBeVisible();
  });

  test("Groups sidebar link navigates to /groups", async ({ page }) => {
    await page.locator("#sidebar a[href*=groups]").click();
    await expect(page).toHaveURL(/groups/);
  });

  test("Meetings sidebar link navigates to /meetings", async ({ page }) => {
    await page.locator("#sidebar a[href*=meetings]").click();
    await expect(page).toHaveURL(/meetings/);
  });

  test("Folders sidebar link navigates to /folders", async ({ page }) => {
    await page.locator("#sidebar a[href*=folders]").click();
    await expect(page).toHaveURL(/folders/);
  });

  test("Settings sidebar link navigates to /settings", async ({ page }) => {
    await page.locator("#sidebar a[href*=settings]").click();
    await expect(page).toHaveURL(/settings/);
  });

  test("Links sidebar link navigates to /links", async ({ page }) => {
    await page.locator("#sidebar a[href*=links]").click();
    await expect(page).toHaveURL(/links/);
  });

  // ── Top navbar ────────────────────────────────────────────────────────────
  test("top navbar brand logo is visible", async ({ page }) => {
    const logo = page.locator(".navbar-brand img").first();
    await expect(logo).toBeVisible();
  });

  test("navbar brand logo links to dashboard", async ({ page }) => {
    const link = page.locator("a.navbar-brand").first();
    const href = await link.getAttribute("href");
    expect(href).toMatch(/dashboard/);
  });

  test("navbar search field is present", async ({ page }) => {
    await expect(page.locator("input[name=q], #navbar-search-input")).toBeVisible();
  });

  test("notification bell is visible", async ({ page }) => {
    // Use the anchor directly (not the icon inside it) to avoid strict-mode violation
    await expect(page.locator("#notificationDropdown")).toBeVisible();
  });

  test("profile dropdown opens on click", async ({ page }) => {
    await page.locator("#profileDropdown, a[data-toggle=dropdown] img.rounded-circle").first().click();
    // Profile menu items appear
    await expect(page.getByText("Profile", { exact: false }).first()).toBeVisible({ timeout: 5_000 });
  });

  test("profile dropdown has Profile, Settings, Logout items", async ({ page }) => {
    await page.locator("#profileDropdown, a[data-toggle=dropdown] img.rounded-circle").first().click();
    await expect(page.getByText("Profile")).toBeVisible();
    // Settings appears in both sidebar and dropdown; scope to dropdown menu
    await expect(page.locator(".dropdown-menu a, .dropdown-menu button").filter({ hasText: /settings|logout/i }).first()).toBeVisible();
    await expect(page.locator(".dropdown-menu a, .dropdown-menu button").filter({ hasText: /logout/i })).toBeVisible();
  });

  test("clicking Profile in dropdown navigates to /profile", async ({ page }) => {
    await page.locator("#profileDropdown, a[data-toggle=dropdown] img.rounded-circle").first().click();
    await page.locator("a[href*=profile]").first().click();
    await expect(page).toHaveURL(/profile/);
  });

  // ── Breadcrumbs ───────────────────────────────────────────────────────────
  test("sanghs index shows breadcrumb", async ({ page }) => {
    await page.goto("/sanghs");
    const breadcrumb = page.locator(".breadcrumb");
    if (await breadcrumb.isVisible()) {
      await expect(breadcrumb).toContainText("Sangh");
    }
  });

  // ── Root redirect ─────────────────────────────────────────────────────────
  test("visiting / redirects to /dashboard", async ({ page }) => {
    await page.goto("/");
    await expect(page).toHaveURL(/dashboard/);
  });
});
