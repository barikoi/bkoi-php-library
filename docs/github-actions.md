# GitHub Actions CI/CD Setup

This package includes automated testing via GitHub Actions that runs on every push and pull request.

## Overview

The workflow runs:
- **Unit & Feature Tests** on PHP 8.1, 8.2, 8.3 with Laravel 10 & 11
- **Integration Tests** (if API key is provided)
- **Code Quality Checks** (syntax, PHPStan, PHP CS Fixer)
- **Security Audits** (composer audit for vulnerabilities)
- **Test Coverage Reports** (uploaded to Codecov)
- **Discord Notifications** (automatic test failure alerts)

## Required GitHub Secrets

To enable all features, add these secrets to your GitHub repository:

### 1. Navigate to Repository Settings
`Your Repository` → `Settings` → `Secrets and variables` → `Actions` → `New repository secret`

### 2. Add Required Secrets

#### BARIKOI_API_KEY (Required for Integration Tests)
```
Name: BARIKOI_API_KEY
Value: bkoi_your_api_key_here
```
Get your API key from: https://barikoi.com

**Note:** Integration tests will be skipped if this is not provided. Unit and Feature tests will still run.

#### DISCORD_WEBHOOK_URL (Optional - Test Notifications)
```
Name: DISCORD_WEBHOOK_URL
Value: https://discord.com/api/webhooks/your_webhook_url
```

This enables Discord notifications for test failures in CI/CD:
- Real-time alerts when tests fail
- Includes error messages and stack traces
- Shows curl commands to reproduce API failures
- Test suite summaries after each run

**Setup Discord Webhook:**
1. Open your Discord server
2. Go to Server Settings → Integrations → Webhooks
3. Click "New Webhook"
4. Name it "CI Test Notifications"
5. Select the channel for notifications
6. Copy the webhook URL
7. Add to GitHub secrets

#### CODECOV_TOKEN (Optional - Coverage Reports)
```
Name: CODECOV_TOKEN
Value: your_codecov_token
```

Get your token from: https://codecov.io after connecting your repository.

**Benefits:**
- Track test coverage over time
- See coverage changes in pull requests
- Beautiful coverage reports and badges

## Workflow Configuration

### Matrix Testing

The workflow tests against multiple PHP and Laravel versions:

```yaml
matrix:
  php: [8.1, 8.2, 8.3]
  laravel: [10.*, 11.*]
  dependency-version: [prefer-lowest, prefer-stable]
```

This ensures compatibility across:
- **3 PHP versions**
- **2 Laravel versions**
- **2 dependency strategies** (lowest & stable)
- **Total: 10 test combinations**

### Test Suites

#### 1. Unit & Feature Tests
```bash
vendor/bin/phpunit --testsuite Unit --testsuite Feature
```
- Always runs (no API key needed)
- Fast execution (~30 seconds)
- Tests core functionality with mocked responses

#### 2. Integration Tests
```bash
vendor/bin/phpunit --testsuite Integration
```
- Requires BARIKOI_API_KEY secret
- Tests against real Barikoi API
- Marked as `continue-on-error: true` (won't fail the build)
- Longer execution (~60 seconds)

#### 3. Code Quality Checks
- PHP syntax validation
- PHPStan static analysis (if configured)
- PHP CS Fixer style checks (if configured)
- Runs in parallel with tests

#### 4. Security Audit
- `composer audit` checks for known vulnerabilities
- Runs on every push
- Reports outdated packages with security issues

## Workflow Files

### Main Test Workflow
`.github/workflows/tests.yml`
- Triggered on push/PR to main/develop branches
- Runs all test suites
- Generates coverage reports
- Sends notifications to Discord

## Discord Notifications in CI

When tests run in GitHub Actions, failures are automatically sent to Discord with:

✅ **Success Notifications:**
- Test suite passed
- Total tests, assertions, and execution time
- Branch and environment info

❌ **Failure Notifications:**
- Test name and error message
- **cURL command to reproduce API calls**
- Stack trace (first 5 lines)
- Test file location
- Environment info (PHP version, CI environment)
- Link to GitHub Actions run

Example Discord message for failed test:
```
❌ Test Failure

Test: `test_reverse_geocode_with_valid_coordinates`

Error Message:
```
Expected status 200, got 404
```

🔧 cURL Command to Reproduce:
```bash
curl -X GET 'https://barikoi.xyz/v2/api/search/reverse/90.3957/23.7386?api_key=***' \
  -H 'Accept: application/json' \
  -H 'User-Agent: BarikoiPHP/1.0'
```

Stack Trace:
LocationServiceTest.php:45
TestCase.php:82
...

Test Location: `LocationServiceTest.php:45`
Environment: PHP 8.2 (CI)
Time: 2025-12-22 15:30:45
```

## Status Badges

Add these badges to your README.md:

### Tests Badge
```markdown
![Tests](https://github.com/your-username/your-repo/workflows/Tests/badge.svg)
```

### Coverage Badge (with Codecov)
```markdown
[![codecov](https://codecov.io/gh/your-username/your-repo/branch/main/graph/badge.svg)](https://codecov.io/gh/your-username/your-repo)
```

## Running Locally

To run tests locally with the same environment as CI:

```bash
# Copy environment variables
cp .env.example .env

# Add your API key and Discord webhook
nano .env

# Run unit & feature tests (fast)
vendor/bin/phpunit --testsuite Unit --testsuite Feature

# Run integration tests (requires API key)
vendor/bin/phpunit --testsuite Integration

# Run all tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage
```

## Troubleshooting

### Tests Failing in CI but Passing Locally

1. **Check PHP version:** CI may use different PHP version
2. **Check dependencies:** CI uses `prefer-lowest` strategy in matrix
3. **Check environment variables:** Ensure secrets are properly set
4. **Check API rate limits:** Integration tests may hit rate limits

### Discord Notifications Not Sending

1. **Verify webhook URL:** Test it with curl:
   ```bash
   curl -X POST "$DISCORD_WEBHOOK_URL" \
     -H "Content-Type: application/json" \
     -d '{"content":"Test message from GitHub Actions"}'
   ```

2. **Check GitHub secret:** Ensure `DISCORD_WEBHOOK_URL` is set correctly

3. **Check workflow logs:** Look for Discord-related errors in Actions tab

### Integration Tests Failing

1. **API key validity:** Verify your Barikoi API key is active
2. **Rate limiting:** You may be hitting API rate limits
3. **API changes:** Barikoi API may have changed endpoints
4. **Network issues:** GitHub Actions may have connectivity issues

## Customization

### Disable Integration Tests in CI

If you don't want integration tests in CI, comment out or remove this section in `.github/workflows/tests.yml`:

```yaml
# - name: Execute Integration Tests
#   if: ${{ secrets.BARIKOI_API_KEY != '' }}
#   run: vendor/bin/phpunit --testsuite Integration
#   continue-on-error: true
```

### Add More PHP Versions

Update the matrix in `.github/workflows/tests.yml`:

```yaml
matrix:
  php: [8.1, 8.2, 8.3, 8.4]  # Add 8.4
```

### Change Trigger Branches

Update the workflow triggers:

```yaml
on:
  push:
    branches: [ main, develop, staging ]  # Add staging
  pull_request:
    branches: [ main, develop, staging ]
```

## Best Practices

1. **Keep API keys secure:** Never commit `.env` files or expose secrets
2. **Monitor Discord notifications:** Set up a dedicated channel for CI notifications
3. **Review failed tests promptly:** Use the curl commands in Discord to debug
4. **Update dependencies regularly:** Run `composer update` to get security patches
5. **Keep test coverage high:** Aim for >80% code coverage
6. **Use pull requests:** Always run tests before merging to main branch

## Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Barikoi API Documentation](https://docs.barikoi.com)
- [Discord Webhooks Guide](https://support.discord.com/hc/en-us/articles/228383668)
- [Codecov Documentation](https://docs.codecov.com)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
