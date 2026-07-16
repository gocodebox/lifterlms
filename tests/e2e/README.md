LifterLMS E2E Tests (Playwright)
================================

End-to-end tests run in a real browser via [Playwright](https://playwright.dev/) against a [`wp-env`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/) WordPress instance.

## Prerequisites

+ Node.js 24 and npm 10+
+ Docker (required by `wp-env`)

## Installing

1. Install all Node dependencies from the repository root:

   ```
   npm ci
   ```

2. Build the plugin assets:

   ```
   npm run build
   ```

3. Install the Playwright browsers (Chromium is all you need):

   ```
   npx playwright install --with-deps chromium
   ```

4. Start the `wp-env` environment:

   ```
   npx wp-env start
   ```

5. Bootstrap the test data (creates pages, users, courses, etc.):

   ```
   bash tests/e2e/bin/setup-env.sh
   ```

## Running Tests

Run the full suite:

```
npm test
```

Run a single spec file:

```
npx wp-scripts test-playwright -- tests/e2e/specs/student/login.spec.js
```

Run tests matching a name:

```
npx wp-scripts test-playwright -- --grep "login"
```

## Automated Testing

Tests run automatically on pull requests via [GitHub Actions](https://github.com/gocodebox/lifterlms/actions/workflows/test-e2e-playwright.yml). On failure the workflow uploads a `playwright-report` artifact you can download to review traces and screenshots.

## Writing Tests

+ Test specs live in `tests/e2e/specs/` and are organized by feature area.
+ Shared helpers live in `tests/e2e/utils/`.
+ Playwright config is at the repository root in `playwright.config.js` (extends `@wordpress/scripts`).
+ Focus E2E coverage on core user-facing workflows — enrollment, checkout, access restrictions, login, and similar critical paths.
+ Use `@wordpress/e2e-test-utils-playwright` helpers (such as `Admin` and `RequestUtils`) whenever possible to keep tests concise.
+ If a test needs specific data, add setup steps to `tests/e2e/bin/setup-env.sh` so the environment is reproducible.
