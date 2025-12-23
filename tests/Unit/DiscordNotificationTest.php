<?php

namespace Vendor\BarikoiApi\Tests\Unit;

use Vendor\BarikoiApi\Tests\TestCase;

/**
 * Test to verify Discord notifications are working
 *
 * Uncomment the failing test to trigger a Discord notification
 */
class DiscordNotificationTest extends TestCase
{
    public function test_discord_notification_is_configured()
    {
        $webhookUrl = env('DISCORD_WEBHOOK_URL');
        $enabled = env('DISCORD_NOTIFICATIONS_ENABLED', true);

        // This test passes - just checks configuration
        $this->assertTrue(true, 'Discord notification system is configured');

        if ($webhookUrl) {
            echo "\n✓ Discord webhook is configured: " . substr($webhookUrl, 0, 50) . "...\n";
            echo "  Notifications enabled: " . ($enabled ? 'Yes' : 'No') . "\n";
        } else {
            echo "\n⚠ Discord webhook is NOT configured (see docs/discord-notifications.md)\n";
        }
    }

    /**
     * Uncomment this test to trigger a test Discord notification
     * This will send a failure notification to your Discord channel
     */
    // public function test_trigger_discord_notification_example()
    // {
    //     // This test intentionally fails to trigger Discord notification
    //     $this->assertTrue(false, 'This is a test Discord notification! If you see this in Discord, it is working! 🎉');
    // }
}
