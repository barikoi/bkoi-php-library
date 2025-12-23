# Discord Test Notifications

Get instant alerts in Discord when your tests fail!

## Features

- **Instant Failure Alerts**: Get notified immediately when a test fails
- **Detailed Error Information**: See error messages, stack traces, and test names
- **Test Summary**: Receive a summary after the entire test suite completes
- **Environment Information**: Know which environment and branch the tests ran on
- **Beautiful Embeds**: Rich Discord embeds with color-coded messages

## Setup Instructions

### 1. Create a Discord Webhook

1. Open your Discord server
2. Go to **Server Settings** > **Integrations** > **Webhooks**
3. Click **"New Webhook"** or **"Create Webhook"**
4. Give it a name (e.g., "Test Notifications")
5. Select the channel where you want notifications
6. Click **"Copy Webhook URL"**

### 2. Configure Your Environment

Add the webhook URL to your `.env` file:

```bash
# Discord Test Notifications
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/YOUR_WEBHOOK_ID/YOUR_WEBHOOK_TOKEN
DISCORD_NOTIFICATIONS_ENABLED=true
```

**Note:** Set `DISCORD_NOTIFICATIONS_ENABLED=false` to temporarily disable notifications without removing the webhook URL.

### 3. Run Your Tests

Simply run your tests as usual:

```bash
# Run all tests
composer test

# Run specific test suite
composer test:unit
composer test:feature
composer test:integration

# Or use PHPUnit directly
./vendor/bin/phpunit
```

## What You'll Receive

### Failure Notifications

When a test fails, you'll receive a Discord message with:

- ❌ **Test name** that failed
- **Error message** with details
- **Stack trace** (first 5 lines)
- **Environment** (PHP version, environment)
- **Timestamp** when the failure occurred

### Summary Notification

After all tests complete, you'll receive a summary with:

- ✅ or ❌ Overall status
- **Total tests** run
- **Number of failures**
- **Number of errors**
- **Number of skipped tests**
- **Total execution time**
- **Git branch** name
- **Environment** information

## Example Discord Messages

### Failure Alert Example:
```
❌ Test Failure
Test: LocationServiceTest::test_reverse_geocode_sends_correct_params

Error Message:
Failed asserting that false is true.

Stack Trace:
#0 LocationServiceTest.php(45): PHPUnit\...
#1 TestCase.php(126): LocationServiceTest->...
...

Environment: PHP 8.2.0 (local)
Time: 2024-12-22 15:30:45
```

### Summary Example:
```
✅ Test Suite Passed
Total: 155 tests | Failures: 0 | Errors: 0 | Skipped: 41
Time: 5.23 seconds

Branch: main
Environment: PHP 8.2.0 (local)
```

## Configuration Options

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DISCORD_WEBHOOK_URL` | Discord webhook URL | None (required) |
| `DISCORD_NOTIFICATIONS_ENABLED` | Enable/disable notifications | `true` (if webhook is set) |

### Disable Notifications

Temporarily disable without removing configuration:

```bash
DISCORD_NOTIFICATIONS_ENABLED=false
```

Or remove the webhook URL:

```bash
DISCORD_WEBHOOK_URL=
```

## CI/CD Integration

### GitHub Actions

Add webhook as a secret and use in your workflow:

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2

      - name: Install Dependencies
        run: composer install

      - name: Run Tests
        env:
          DISCORD_WEBHOOK_URL: ${{ secrets.DISCORD_WEBHOOK_URL }}
          DISCORD_NOTIFICATIONS_ENABLED: true
        run: composer test
```

### GitLab CI

```yaml
# .gitlab-ci.yml
test:
  script:
    - composer install
    - composer test
  variables:
    DISCORD_WEBHOOK_URL: $DISCORD_WEBHOOK_URL
    DISCORD_NOTIFICATIONS_ENABLED: "true"
```

## Troubleshooting

### Notifications Not Sending

1. **Check webhook URL**: Ensure it's correct and active in Discord
2. **Check environment variable**: Verify `DISCORD_WEBHOOK_URL` is set
3. **Check enabled flag**: Ensure `DISCORD_NOTIFICATIONS_ENABLED` is not `false`
4. **Check logs**: Look for errors in your PHP error log
5. **Test webhook manually**: Use curl to test:

```bash
curl -X POST "YOUR_WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{"content": "Test message"}'
```

### Too Many Notifications

If you're getting too many notifications during development:

```bash
# Disable temporarily
DISCORD_NOTIFICATIONS_ENABLED=false
```

### Webhook Rate Limiting

Discord webhooks have rate limits (30 requests per minute). If you're running large test suites very frequently, you might hit this limit. The system automatically handles this gracefully.

## Advanced Usage

### Custom Channel for Different Environments

Create different webhooks for different environments:

```bash
# .env.local
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/LOCAL_ID/LOCAL_TOKEN

# .env.ci
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/CI_ID/CI_TOKEN
```

### Summary Only Mode

If you only want summary notifications (no individual failures), you can modify the listener:

Edit `tests/Support/DiscordTestListener.php` and comment out the notifications in `addFailure()` and `addError()` methods.

## Files Added

- `tests/Support/DiscordNotifier.php` - Main notification service
- `tests/Support/DiscordTestListener.php` - PHPUnit test listener
- Updated `phpunit.xml` - Registered the listener
- Updated `.env` - Added Discord configuration

## Security Notes

⚠️ **Never commit your webhook URL to version control!**

- Keep webhook URLs in `.env` (which should be in `.gitignore`)
- Use environment variables in CI/CD
- Regenerate webhook if accidentally exposed
- Use different webhooks for different environments

## Support

If you have issues with Discord notifications:

1. Check the troubleshooting section above
2. Verify your Discord webhook is active
3. Check PHP error logs for detailed error messages
4. Test with a simple unit test first

Happy testing! 🎉
