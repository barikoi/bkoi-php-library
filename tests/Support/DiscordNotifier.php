<?php

namespace Vendor\BarikoiApi\Tests\Support;

use Exception;

class DiscordNotifier
{
    protected ?string $webhookUrl;
    protected bool $enabled;

    public function __construct()
    {
        // Try Laravel's env() helper first, fallback to getenv()
        $this->webhookUrl = function_exists('env')
            ? env('DISCORD_WEBHOOK_URL')
            : (getenv('DISCORD_WEBHOOK_URL') ?: null);

        $enabled = function_exists('env')
            ? env('DISCORD_NOTIFICATIONS_ENABLED', 'true')
            : (getenv('DISCORD_NOTIFICATIONS_ENABLED') ?: 'true');

        $this->enabled = !empty($this->webhookUrl) && $enabled !== 'false' && $enabled !== false;
    }

    /**
     * Send test failure notification to Discord
     */
    public function notifyTestFailure(string $testName, string $message, string $trace = '', array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        $payload = $this->buildFailurePayload($testName, $message, $trace, $context);
        $this->sendToDiscord($payload);
    }

    /**
     * Send test suite summary to Discord
     */
    public function notifyTestSummary(int $tests, int $failures, int $errors, int $skipped, float $time): void
    {
        if (!$this->enabled) {
            return;
        }

        $payload = $this->buildSummaryPayload($tests, $failures, $errors, $skipped, $time);
        $this->sendToDiscord($payload);
    }

    /**
     * Build Discord embed payload for test failure
     */
    protected function buildFailurePayload(string $testName, string $message, string $trace, array $context = []): array
    {
        // Truncate long messages
        $message = $this->truncate($message, 1000);
        $trace = $this->truncate($trace, 1000);

        $color = 15158332; // Red color for failures

        // Extract file and line from trace
        $fileInfo = $this->extractFileInfo($trace);

        // Build fields array
        $fields = [
            [
                'name' => 'Error Message',
                'value' => "```\n{$message}\n```",
                'inline' => false,
            ],
        ];

        // Add curl command if available
        if (!empty($context['curl'])) {
            $curlCommand = $this->truncate($context['curl'], 1000);
            $fields[] = [
                'name' => '🔧 cURL Command to Reproduce',
                'value' => "```bash\n{$curlCommand}\n```",
                'inline' => false,
            ];
        } elseif (!empty($context['request'])) {
            // Generate curl from request info
            $curlCommand = $this->generateCurlCommand($context['request']);
            $fields[] = [
                'name' => '🔧 cURL Command to Reproduce',
                'value' => "```bash\n{$curlCommand}\n```",
                'inline' => false,
            ];
        }

        // Add stack trace
        $fields[] = [
            'name' => 'Stack Trace',
            'value' => empty($trace) ? 'No trace available' : "```\n{$trace}\n```",
            'inline' => false,
        ];

        // Add file info if available
        if ($fileInfo) {
            $fields[] = [
                'name' => 'Test Location',
                'value' => "`{$fileInfo}`",
                'inline' => false,
            ];
        }

        // Add environment and time
        $fields[] = [
            'name' => 'Environment',
            'value' => $this->getEnvironmentInfo(),
            'inline' => true,
        ];
        $fields[] = [
            'name' => 'Time',
            'value' => date('Y-m-d H:i:s'),
            'inline' => true,
        ];

        return [
            'embeds' => [
                [
                    'title' => '❌ Test Failure',
                    'description' => "**Test:** `{$testName}`",
                    'color' => $color,
                    'fields' => $fields,
                    'footer' => [
                        'text' => 'Barikoi API Package Tests',
                    ],
                ],
            ],
        ];
    }

    /**
     * Build Discord embed payload for test summary
     */
    protected function buildSummaryPayload(int $tests, int $failures, int $errors, int $skipped, float $time): array
    {
        $hasFailures = $failures > 0 || $errors > 0;
        $color = $hasFailures ? 15158332 : 3066993; // Red if failures, green if all pass
        $icon = $hasFailures ? '❌' : '✅';
        $title = $hasFailures ? 'Test Suite Failed' : 'Test Suite Passed';

        $description = sprintf(
            "**Total:** %d tests | **Failures:** %d | **Errors:** %d | **Skipped:** %d\n**Time:** %.2f seconds",
            $tests,
            $failures,
            $errors,
            $skipped,
            $time
        );

        return [
            'embeds' => [
                [
                    'title' => "{$icon} {$title}",
                    'description' => $description,
                    'color' => $color,
                    'fields' => [
                        [
                            'name' => 'Branch',
                            'value' => $this->getCurrentBranch(),
                            'inline' => true,
                        ],
                        [
                            'name' => 'Environment',
                            'value' => $this->getEnvironmentInfo(),
                            'inline' => true,
                        ],
                    ],
                    'footer' => [
                        'text' => 'Barikoi API Package Tests',
                    ],
                    'timestamp' => date('c'),
                ],
            ],
        ];
    }

    /**
     * Send payload to Discord webhook
     */
    protected function sendToDiscord(array $payload): void
    {
        if (!$this->webhookUrl) {
            return;
        }

        try {
            $ch = curl_init($this->webhookUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            curl_close($ch);

            if ($httpCode !== 204 && $httpCode !== 200) {
                $errorMsg = "Discord notification failed with HTTP code: {$httpCode}";
                if ($curlError) {
                    $errorMsg .= " | cURL error: {$curlError}";
                }
                if ($response) {
                    $errorMsg .= " | Response: " . substr($response, 0, 200);
                }
                error_log($errorMsg);
            }
        } catch (Exception $e) {
            error_log("Failed to send Discord notification: " . $e->getMessage());
        }
    }

    /**
     * Get current git branch
     */
    protected function getCurrentBranch(): string
    {
        try {
            $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD 2>/dev/null') ?: 'unknown');
            return $branch ?: 'unknown';
        } catch (Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Get environment information
     */
    protected function getEnvironmentInfo(): string
    {
        $env = function_exists('env')
            ? env('APP_ENV', (env('CI') ? 'CI' : 'local'))
            : (getenv('APP_ENV') ?: (getenv('CI') ? 'CI' : 'local'));
        $php = PHP_VERSION;
        return "PHP {$php} ({$env})";
    }

    /**
     * Truncate string to specified length
     */
    protected function truncate(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - 3) . '...';
    }

    /**
     * Check if notifications are enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Generate curl command from request information
     */
    protected function generateCurlCommand(array $request): string
    {
        $method = $request['method'] ?? 'GET';
        $url = $request['url'] ?? '';
        $headers = $request['headers'] ?? [];
        $body = $request['body'] ?? null;

        $curl = "curl -X {$method} '{$url}'";

        // Add headers
        foreach ($headers as $header => $value) {
            $curl .= " \\\n  -H '{$header}: {$value}'";
        }

        // Add body if present
        if ($body) {
            $bodyStr = is_array($body) ? json_encode($body) : $body;
            $curl .= " \\\n  -d '" . addslashes($bodyStr) . "'";
        }

        return $curl;
    }

    /**
     * Extract file and line information from stack trace
     */
    protected function extractFileInfo(string $trace): ?string
    {
        // Look for file path and line number in trace
        // Pattern: /path/to/file.php:123
        if (preg_match('/([\/\w\-\.]+\.php):(\d+)/', $trace, $matches)) {
            $file = basename($matches[1]);
            $line = $matches[2];
            return "{$file}:{$line}";
        }

        return null;
    }
}
