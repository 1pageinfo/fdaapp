// e2e/11-responsive.spec.js
// Responsive design checks at mobile + tablet viewports.
// (Run in mobile/tablet Playwright projects)

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

// These tests are designed for mobile/tablet viewports but run on all projects
test.describe("Responsive Design", () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test("dashboard has no horizontal overflow", async ({ page }) => {
    await page.goto("/dashboard");
    const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
    const viewWidth = await page.evaluate(() => window.innerWidth);
    // Allow 1px tolerance
    expect(bodyWidth).toBeLessThanOrEqual(viewWidth + 1);
  });

  test("sanghs index has no horizontal overflow", async ({ page }) => {
    await page.goto("/sanghs");
    const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
    const viewWidth = await page.evaluate(() => window.innerWidth);
    expect(bodyWidth).toBeLessThanOrEqual(viewWidth + 1);
  });

  test("mobile menu toggle button is present on small viewports", async ({ page, isMobile }) => {
    if (!isMobile) return; // only run on mobile project
    await page.goto("/dashboard");
    const mobileToggle = page.locator("button.navbar-toggler-right, button[data-toggle=offcanvas]");
    await expect(mobileToggle).toBeVisible();
  });

  test("login page renders correctly on mobile", async ({ page }) => {
    await page.goto("/login");
    await expect(page.locator("input[name=email]")).toBeVisible();
    await expect(page.locator("button[type=submit]")).toBeVisible();
    // Input should not be clipped
    const box = await page.locator("input[name=email]").boundingBox();
    expect(box).not.toBeNull();
    if (box) expect(box.width).toBeGreaterThan(50);
  });

  test("profile form is usable on mobile", async ({ page }) => {
    await page.goto("/profile");
    const nameField = page.locator("input[name=name]");
    await expect(nameField).toBeVisible();
    const box = await nameField.boundingBox();
    expect(box).not.toBeNull();
    if (box) expect(box.width).toBeGreaterThan(50);
  });

  test("meetings create form is usable on mobile", async ({ page }) => {
    await page.goto("/meetings/create");
    await expect(page.locator("input[name=title]")).toBeVisible();
    // Use scoped selector to avoid matching navbar logout dropdown button
    await expect(page.locator(".card form button[type=submit], main form button[type=submit], .btn-primary[type=submit]").first()).toBeVisible();
  });
});
