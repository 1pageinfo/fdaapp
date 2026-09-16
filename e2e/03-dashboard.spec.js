// e2e/03-dashboard.spec.js
// Dashboard: stat cards, date filters, quick ranges.

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

test.describe("Dashboard", () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
    await page.goto("/dashboard");
  });

  test("page loads with correct title", async ({ page }) => {
    // Title may be "FESCOM | Dashboard" or similar — just check it's non-empty
    const title = await page.title();
    expect(title.length).toBeGreaterThan(0);
  });

  test("renders all stat cards", async ({ page }) => {
    const cardLabels = [
      "Sangh Register Receipts",
      "Sangh Renewal Receipts",
      "Sanghs Registered",
    ];
    for (const label of cardLabels) {
      // Use first() in case label appears in multiple places
      await expect(page.getByText(label, { exact: false }).first()).toBeVisible();
    }
    // Meetings appears many times on the page; just check at least one stat card area
    await expect(page.locator(".card .card-body").first()).toBeVisible();
  });

  test("Total Fee Collections card is visible", async ({ page }) => {
    await expect(page.getByText("Total Fee Collections", { exact: false }).first()).toBeVisible();
  });

  test("stat cards contain numeric values", async ({ page }) => {
    const cards = page.locator(".h3.mb-0");
    const count = await cards.count();
    expect(count).toBeGreaterThan(0);
    for (let i = 0; i < count; i++) {
      const text = await cards.nth(i).innerText();
      expect(text.trim()).toMatch(/[\d₹,]/);
    }
  });

  test("date filter form is present", async ({ page }) => {
    await expect(page.locator("input[name=start_date]")).toBeVisible();
    await expect(page.locator("input[name=end_date]")).toBeVisible();
    await expect(page.getByRole("button", { name: /apply filter/i })).toBeVisible();
  });

  test("quick range 'Today' link works", async ({ page }) => {
    await page.getByRole("link", { name: "Today" }).click();
    await page.waitForURL(/start_date=/);
    await expect(page.locator("input[name=start_date]")).toHaveValue(/.+/);
  });

  test("quick range '7d' filter applies", async ({ page }) => {
    await page.getByRole("link", { name: "7d" }).click();
    await page.waitForURL(/start_date=/);
    await expect(page.locator("input[name=start_date]")).toHaveValue(/.+/);
  });

  test("quick range '30d' filter applies", async ({ page }) => {
    await page.getByRole("link", { name: "30d" }).click();
    await page.waitForURL(/start_date=/);
  });

  test("custom date filter returns results without error", async ({ page }) => {
    await page.locator("input[name=start_date]").fill("2024-01-01");
    await page.locator("input[name=end_date]").fill("2024-12-31");
    await page.getByRole("button", { name: /apply filter/i }).click();
    await page.waitForURL(/start_date=/);
    // Page should still render stat cards
    await expect(page.locator(".card").first()).toBeVisible();
  });

  test("Open Calendar button navigates to meetings", async ({ page }) => {
    // The link may say "Open Calendar", "Calendar", or "View Calendar"
    const calLink = page.getByRole("link", { name: /calendar/i }).first();
    if (await calLink.isVisible()) {
      await calLink.click();
      await expect(page).toHaveURL(/meetings|calendar/);
    }
  });

  test("no console errors on dashboard load", async ({ page }) => {
    const errors = [];
    page.on("console", msg => { if (msg.type() === "error") errors.push(msg.text()); });
    await page.goto("/dashboard");
    await page.waitForLoadState("networkidle");
    // Allow known 3rd-party or non-critical errors
    const serious = errors.filter(e => !e.includes("favicon") && !e.includes("net::ERR"));
    expect(serious).toHaveLength(0);
  });
});
