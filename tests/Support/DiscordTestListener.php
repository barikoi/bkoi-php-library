<?php

namespace Vendor\BarikoiApi\Tests\Support;

use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\Test\Finished as TestFinished;
use PHPUnit\Event\Test\FinishedSubscriber as TestFinishedSubscriber;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;
use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\TestRunner\StartedSubscriber;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Illuminate\Support\Facades\Http;

/**
 * PHPUnit Extension to send test failures to Discord (PHPUnit 10+)
 */
final class DiscordTestListener implements Extension
{
    public static DiscordNotifier $notifier;
    public static float $startTime = 0;
    public static int $tests = 0;
    public static int $failures = 0;
    public static int $errors = 0;
    public static int $skipped = 0;

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        self::$notifier = new DiscordNotifier();
        self::$tests = 0;
        self::$failures = 0;
        self::$errors = 0;
        self::$skipped = 0;
        self::$startTime = 0;

        // Only register subscribers if Discord is enabled
        if (!self::$notifier->isEnabled()) {
            return;
        }

        // Track test runner start
        $facade->registerSubscriber(new class implements StartedSubscriber {
            public function notify(Started $event): void
            {
                DiscordTestListener::$startTime = microtime(true);
                DiscordTestListener::$tests = 0;
                DiscordTestListener::$failures = 0;
                DiscordTestListener::$errors = 0;
                DiscordTestListener::$skipped = 0;
            }
        });

        // Count each test
        $facade->registerSubscriber(new class implements TestFinishedSubscriber {
            public function notify(TestFinished $event): void
            {
                DiscordTestListener::$tests++;
            }
        });

        // Listen for test failures
        $facade->registerSubscriber(new class implements FailedSubscriber {
            public function notify(Failed $event): void
            {
                DiscordTestListener::$failures++;

                $testName = $event->test()->name();
                $throwable = $event->throwable();
                $message = $throwable->message();
                $trace = $this->formatTrace($throwable->stackTrace());
                $context = $this->captureRequestContext();

                DiscordTestListener::$notifier->notifyTestFailure($testName, $message, $trace, $context);
            }

            private function formatTrace(string $trace): string
            {
                $lines = explode("\n", $trace);
                $lines = array_slice($lines, 0, 5);
                return implode("\n", $lines);
            }

            private function captureRequestContext(): array
            {
                try {
                    // Try to get recorded requests from Http facade
                    if (!class_exists('Illuminate\Support\Facades\Http')) {
                        return [];
                    }

                    // Try Http::recorded() first (Laravel 9+)
                    if (method_exists('Illuminate\Support\Facades\Http', 'recorded')) {
                        $recorded = Http::recorded();
                        if (!empty($recorded) && is_array($recorded)) {
                            $lastRequest = end($recorded);
                            if (is_array($lastRequest) && count($lastRequest) >= 2) {
                                [$request, $response] = $lastRequest;
                                return $this->extractRequestData($request);
                            }
                        }
                    }

                    // Fallback: Try to access factory's internal state
                    $factory = Http::getFacadeRoot();
                    if ($factory && method_exists($factory, 'recorded')) {
                        $recorded = $factory->recorded();
                        if (!empty($recorded) && is_array($recorded)) {
                            $lastRequest = end($recorded);
                            if (is_array($lastRequest) && count($lastRequest) >= 2) {
                                [$request, $response] = $lastRequest;
                                return $this->extractRequestData($request);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Silently fail if we can't capture request info
                }

                return [];
            }

            private function extractRequestData($request): array
            {
                if (!$request || !is_object($request) || !method_exists($request, 'method')) {
                    return [];
                }

                return [
                    'request' => [
                        'method' => $request->method(),
                        'url' => $request->url(),
                        'headers' => method_exists($request, 'headers') ? $request->headers() : [],
                        'body' => method_exists($request, 'body') ? $request->body() : '',
                    ],
                ];
            }
        });

        // Listen for test errors
        $facade->registerSubscriber(new class implements ErroredSubscriber {
            public function notify(Errored $event): void
            {
                DiscordTestListener::$errors++;

                $testName = $event->test()->name();
                $throwable = $event->throwable();
                $message = "Error: " . $throwable->message();
                $trace = $this->formatTrace($throwable->stackTrace());
                $context = $this->captureRequestContext();

                DiscordTestListener::$notifier->notifyTestFailure($testName, $message, $trace, $context);
            }

            private function formatTrace(string $trace): string
            {
                $lines = explode("\n", $trace);
                $lines = array_slice($lines, 0, 5);
                return implode("\n", $lines);
            }

            private function captureRequestContext(): array
            {
                try {
                    // Try to get recorded requests from Http facade
                    if (!class_exists('Illuminate\Support\Facades\Http')) {
                        return [];
                    }

                    // Try Http::recorded() first (Laravel 9+)
                    if (method_exists('Illuminate\Support\Facades\Http', 'recorded')) {
                        $recorded = Http::recorded();
                        if (!empty($recorded) && is_array($recorded)) {
                            $lastRequest = end($recorded);
                            if (is_array($lastRequest) && count($lastRequest) >= 2) {
                                [$request, $response] = $lastRequest;
                                return $this->extractRequestData($request);
                            }
                        }
                    }

                    // Fallback: Try to access factory's internal state
                    $factory = Http::getFacadeRoot();
                    if ($factory && method_exists($factory, 'recorded')) {
                        $recorded = $factory->recorded();
                        if (!empty($recorded) && is_array($recorded)) {
                            $lastRequest = end($recorded);
                            if (is_array($lastRequest) && count($lastRequest) >= 2) {
                                [$request, $response] = $lastRequest;
                                return $this->extractRequestData($request);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Silently fail if we can't capture request info
                }

                return [];
            }

            private function extractRequestData($request): array
            {
                if (!$request || !is_object($request) || !method_exists($request, 'method')) {
                    return [];
                }

                return [
                    'request' => [
                        'method' => $request->method(),
                        'url' => $request->url(),
                        'headers' => method_exists($request, 'headers') ? $request->headers() : [],
                        'body' => method_exists($request, 'body') ? $request->body() : '',
                    ],
                ];
            }
        });

        // Listen for skipped tests
        $facade->registerSubscriber(new class implements SkippedSubscriber {
            public function notify(Skipped $event): void
            {
                DiscordTestListener::$skipped++;
            }
        });

        // Send summary when test runner finishes
        $facade->registerSubscriber(new class implements FinishedSubscriber {
            public function notify(Finished $event): void
            {
                $totalTime = microtime(true) - DiscordTestListener::$startTime;

                DiscordTestListener::$notifier->notifyTestSummary(
                    DiscordTestListener::$tests,
                    DiscordTestListener::$failures,
                    DiscordTestListener::$errors,
                    DiscordTestListener::$skipped,
                    $totalTime
                );
            }
        });
    }
}
