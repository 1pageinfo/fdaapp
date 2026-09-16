// e2e/01-auth.spec.js
// Authentication tests: login, register, forgot password, session, protected routes.

import { test, expect } from "@playwright/test";
import { login, logout, TEST_EMAIL, TEST_PASSWORD } from "./helpers/auth.js";

test.describe("Authentication", () => {

  // ── Login page ────────────────────────────────────────────────────────────
  test.describe("Login page", () => {
    test("renders login form with all elements", async ({ page }) => {
      await page.goto("/login");
      await expect(page).toHaveTitle(/Login|FESCOM/i);
      await expect(page.locator("input[name=email]")).toBeVisible();
      await expect(page.locator("input[name=password]")).toBeVisible();
      await expect(page.locator("button[type=submit]")).toBeVisible();
      await expect(page.locator("a[href*=forgot], a[href*=password]")).toBeVisible();
      await expect(page.locator("a[href*=register]")).toBeVisible();
    });

    test("shows validation error on empty submit", async ({ page }) => {
      await page.goto("/login");
      // Remove required attrs so the browser allows empty submit
      await page.evaluate(() => {
        document.querySelectorAll("input[required]").forEach(el => el.removeAttribute("required"));
      });
      await page.locator("button[type=submit]").click();
      // Either browser blocks it (valid) or server returns error
      // Use first() to avoid strict mode when multiple error elements exist
      await expect(page.locator(".text-danger, .alert-danger").first()).toBeVisible({ timeout: 10_000 });
    });

    test("shows error for invalid credentials", async ({ page }) => {
      await page.goto("/login");
      await page.locator("input[name=email]").fill("notareal@email.com");
      await page.locator("input[name=password]").fill("wrongpassword123");
      await page.locator("button[type=submit]").click();
      await expect(page.locator(".text-danger, .alert-danger")).toBeVisible({ timeout: 10_000 });
      // Still on login page
      await expect(page).toHaveURL(/login/);
    });

    test("shows error for malformed email", async ({ page }) => {
      await page.goto("/login");
      await page.locator("input[name=email]").fill("not-an-email");
      await page.locator("input[name=password]").fill(TEST_PASSWORD);
      await page.locator("button[type=submit]").click();
      // HTML5 validation or server validation should catch this
      const hasNativeErr = await page.evaluate(() =>
        !document.querySelector("input[name=email]").validity.valid
      );
      if (!hasNativeErr) {
        await expect(page.locator(".text-danger, .alert-danger")).toBeVisible({ timeout: 8_000 });
      }
    });

    test("successful login redirects to dashboard", async ({ page }) => {
      await login(page);
      await expect(page).toHaveURL(/dashboard/);
      // Sidebar should have navigation elements
      await expect(page.locator("#sidebar")).toBeVisible();
    });

    test("remember-me checkbox is present and clickable", async ({ page }) => {
      await page.goto("/login");
      const checkbox = page.locator("input[name=remember]");
      await expect(checkbox).toBeVisible();
      await checkbox.check();
      await expect(checkbox).toBeChecked();
    });

    test("forgot-password link navigates correctly", async ({ page }) => {
      await page.goto("/login");
      await page.click("a[href*=password]");
      await expect(page).toHaveURL(/password/);
    });
  });

  // ── Register page ─────────────────────────────────────────────────────────
  test.describe("Register page", () => {
    test("renders all fields", async ({ page }) => {
      await page.goto("/register");
      await expect(page.locator("input[name=name]")).toBeVisible();
      await expect(page.locator("input[name=email]")).toBeVisible();
      await expect(page.locator("input[name=password]")).toBeVisible();
      await expect(page.locator("input[name=password_confirmation]")).toBeVisible();
      await expect(page.locator("button[type=submit]")).toBeVisible();
    });

    test("register link from login page works", async ({ page }) => {
      await page.goto("/login");
      await page.click("a[href*=register]");
      await expect(page).toHaveURL(/register/);
    });

    test("login link from register page works", async ({ page }) => {
      await page.goto("/register");
      await page.click("a[href*=login]");
      await expect(page).toHaveURL(/login/);
    });

    test("shows error when passwords do not match", async ({ page }) => {
      await page.goto("/register");
      const timestamp = Date.now();
      await page.locator("input[name=name]").fill("Test User");
      await page.locator("input[name=email]").fill(`test${timestamp}@example.com`);
      await page.locator("input[name=password]").fill("password123");
      await page.locator("input[name=password_confirmation]").fill("differentpassword");
      // Accept terms checkbox
      const termsCheck = page.locator("input[type=checkbox]").first();
      if (await termsCheck.isVisible()) await termsCheck.check();
      await page.locator("button[type=submit]").click();
      await expect(page.locator(".text-danger, .alert-danger")).toBeVisible({ timeout: 10_000 });
    });
  });

  // ── Forgot password ───────────────────────────────────────────────────────
  test.describe("Forgot password page", () => {
    test("page renders correctly", async ({ page }) => {
      await page.goto("/password/forgot");
      await expect(page.locator("input[name=email], input[type=email]")).toBeVisible();
    });

    test("submitting unknown email shows info message", async ({ page }) => {
      await page.goto("/password/forgot");
      const emailField = page.locator("input[name=email], input[type=email]").first();
      await emailField.fill("nobody@doesntexist.com");
      await page.locator("button[type=submit]").click();
      // Laravel sends back a status regardless (security)
      await expect(page.locator(".alert, .text-danger, .status")).toBeVisible({ timeout: 10_000 });
    });
  });

  // ── Session & protected routes ────────────────────────────────────────────
  test.describe("Session & protected routes", () => {
    test("unauthenticated user accessing /dashboard is redirected to login", async ({ page }) => {
      await page.goto("/dashboard");
      await expect(page).toHaveURL(/login/);
    });

    test("unauthenticated user accessing /sanghs is redirected to login", async ({ page }) => {
      await page.goto("/sanghs");
      await expect(page).toHaveURL(/login/);
    });

    test("unauthenticated user accessing /profile is redirected to login", async ({ page }) => {
      await page.goto("/profile");
      await expect(page).toHaveURL(/login/);
    });

    test("authenticated user can log out", async ({ page }) => {
      await login(page);
      await logout(page);
      await expect(page).toHaveURL(/login/);
    });

    test("after logout, /dashboard redirects back to login", async ({ page }) => {
      await login(page);
      await logout(page);
      await page.goto("/dashboard");
      await expect(page).toHaveURL(/login/);
    });
  });

});
