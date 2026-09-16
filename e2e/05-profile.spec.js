// e2e/05-profile.spec.js
// Profile: view, update name/email, change password.

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth.js";

test.describe("Profile", () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
    await page.goto("/profile");
  });

  test("page renders My Profile heading", async ({ page }) => {
    await expect(page.getByRole("heading", { name: /my profile/i })).toBeVisible();
  });

  test("name field is pre-filled", async ({ page }) => {
    const nameVal = await page.locator("input[name=name]").inputValue();
    expect(nameVal.trim().length).toBeGreaterThan(0);
  });

  test("email field is pre-filled", async ({ page }) => {
    const emailVal = await page.locator("input[name=email]").inputValue();
    expect(emailVal).toMatch(/@/);
  });

  test("Save Profile button is present", async ({ page }) => {
    await expect(page.getByRole("button", { name: /save profile/i })).toBeVisible();
  });

  test("Reset button is present", async ({ page }) => {
    await expect(page.getByRole("link", { name: /reset/i })).toBeVisible();
  });

  test("profile photo upload input accepts images", async ({ page }) => {
    const photoInput = page.locator("input[type=file][name=photo]");
    await expect(photoInput).toBeVisible();
    const accept = await photoInput.getAttribute("accept");
    expect(accept).toMatch(/image/);
  });

  test("Change Password form is present", async ({ page }) => {
    await expect(page.getByRole("heading", { name: /change password/i })).toBeVisible();
    await expect(page.locator("input[name=current_password]")).toBeVisible();
    await expect(page.locator("input[name=password]")).toBeVisible();
    await expect(page.locator("input[name=password_confirmation]")).toBeVisible();
  });

  test("Change Password button is present", async ({ page }) => {
    await expect(page.getByRole("button", { name: /change password/i })).toBeVisible();
  });

  test("wrong current password shows error", async ({ page }) => {
    await page.locator("input[name=current_password]").fill("definitely-wrong-password");
    await page.locator("input[name=password]").fill("newpassword123");
    await page.locator("input[name=password_confirmation]").fill("newpassword123");
    await page.getByRole("button", { name: /change password/i }).click();
    await expect(page.locator(".alert-danger, .text-danger, .invalid-feedback")).toBeVisible({ timeout: 10_000 });
  });

  test("updating profile with invalid email shows error", async ({ page }) => {
    await page.locator("input[name=email]").fill("not-a-valid-email");
    await page.getByRole("button", { name: /save profile/i }).click();
    const hasHtml5 = await page.evaluate(() =>
      !document.querySelector("input[name=email]").validity.valid
    );
    if (!hasHtml5) {
      await expect(page.locator(".alert-danger, .text-danger")).toBeVisible({ timeout: 10_000 });
    }
  });
});
