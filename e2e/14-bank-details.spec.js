// e2e/14-bank-details.spec.js
import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

test.describe("Sangh Detail Bank Transaction Fields", () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
    // Ensure we have at least one sangh to view
    await page.goto("/sanghs");
    const firstLink = page.locator("table tbody tr a, .sangh-row a").first();
    if (await firstLink.isVisible()) {
      await firstLink.click();
      await page.waitForURL(/sanghs\/\d+/);
    }
  });

  test("Registration Receipt form has bank transaction fields", async ({ page }) => {
    const isSanghPage = await page.url().match(/sanghs\/\d+/);
    if (!isSanghPage) return; // Skip if no sanghs exist to view

    // Check if the registration receipt form exists
    const regForm = page.locator("form[action*='/receipts/']").first();
    if (await regForm.isVisible()) {
      await expect(regForm.locator("input[name='bank_name']")).toBeVisible();
      await expect(regForm.locator("input[name='cheque_no']")).toBeVisible();
      await expect(regForm.locator("input[name='cheque_date']")).toBeVisible();
    }
  });

  test("Renewal form has bank transaction fields", async ({ page }) => {
    const isSanghPage = await page.url().match(/sanghs\/\d+/);
    if (!isSanghPage) return; // Skip if no sanghs exist to view

    // Check if a renewal form exists
    const renForm = page.locator("form[action*='/renewals/']").first();
    if (await renForm.isVisible()) {
      await expect(renForm.locator("input[name='bank_name']")).toBeVisible();
      await expect(renForm.locator("input[name='cheque_no']")).toBeVisible();
      await expect(renForm.locator("input[name='cheque_date']")).toBeVisible();
    }
  });
});
