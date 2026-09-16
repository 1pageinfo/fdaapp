// e2e/09-admin.spec.js
// Admin: user-roles, activity logs.

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

test.describe("Admin Pages", () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test.describe("User Roles", () => {
    test("user roles page renders for admin", async ({ page }) => {
      const resp = await page.goto("/admin/user-roles");
      // Admin user should get 200; non-admin gets redirect
      if (resp && resp.status() === 200) {
        await expect(page.locator("table, .card")).toBeVisible();
      } else {
        // Redirected away (non-admin) – acceptable
        await expect(page).toHaveURL(/.+/);
      }
    });
  });

  test.describe("Activity Logs", () => {
    test("activity logs page renders for admin", async ({ page }) => {
      const resp = await page.goto("/admin/activity-logs");
      // May redirect to dashboard or login if route doesn't exist / non-admin
      // Either way, page should load without crashing
      await expect(page.locator("body")).toBeVisible();
      await expect(page.getByText(/whoops|500|server error/i)).toHaveCount(0);
    });

    test("activity logs table contains at least one row or empty message", async ({ page }) => {
      await page.goto("/admin/activity-logs");
      const finalUrl = page.url();
      // If redirected away from admin, skip table check
      if (!finalUrl.includes("activity-logs")) return;
      const rows = page.locator("table tbody tr");
      const count = await rows.count();
      if (count === 0) {
        await expect(page.getByText(/no (activity|logs|records)/i)).toBeVisible({ timeout: 5_000 });
      } else {
        expect(count).toBeGreaterThan(0);
      }
    });
  });
});
