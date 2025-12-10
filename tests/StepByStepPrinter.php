<?php

namespace Vendor\PackageName\Tests;

use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\Framework\TestCase;

/**
 * Custom test printer that shows step-by-step progress
 */
class StepByStepPrinter extends DefaultResultPrinter
{
    protected $className = '';
    protected $testNumber = 0;

    // Called when test starts
    public function startTest(\PHPUnit\Framework\Test $test): void
    {
        $testName = $test->getName();
        $className = get_class($test);

        // Show class name header if changed
        if ($this->className !== $className) {
            $this->className = $className;
            $shortName = substr($className, strrpos($className, '\\') + 1);
            $this->write("\n\n\033[1;36m" . str_repeat('=', 70) . "\033[0m\n");
            $this->write("\033[1;36m" . $shortName . "\033[0m\n");
            $this->write("\033[1;36m" . str_repeat('=', 70) . "\033[0m\n");
        }

        $this->testNumber++;

        // Convert test name to readable format
        $readableName = $this->makeReadable($testName);

        // Show test starting
        $this->write(sprintf(
            "\n\033[1;33m[%d/%d]\033[0m Testing: %s ... ",
            $this->testNumber,
            $this->numTests,
            $readableName
        ));
    }

    // Called when test ends successfully
    public function endTest(\PHPUnit\Framework\Test $test, float $time): void
    {
        $this->write(sprintf(
            "\033[1;32m✔ PASSED\033[0m (%.3fs)\n",
            $time
        ));
    }

    // Called when test fails
    protected function writeProgress(string $progress): void
    {
        if ($progress === 'F') {
            $this->write("\033[1;31m✖ FAILED\033[0m\n");
        } elseif ($progress === 'E') {
            $this->write("\033[1;31m✖ ERROR\033[0m\n");
        } elseif ($progress === 'S') {
            $this->write("\033[1;33m⊘ SKIPPED\033[0m\n");
        } elseif ($progress === 'I') {
            $this->write("\033[1;34mℹ INCOMPLETE\033[0m\n");
        }
    }

    // Convert test_method_name to readable format
    private function makeReadable(string $testName): string
    {
        // Remove 'test_' prefix
        $name = preg_replace('/^test_/', '', $testName);

        // Replace underscores with spaces
        $name = str_replace('_', ' ', $name);

        // Capitalize first letter of each word
        $name = ucwords($name);

        return $name;
    }

    // Show final summary
    public function printFooter(): void
    {
        $this->write("\n\n");
        $this->write("\033[1;36m" . str_repeat('=', 70) . "\033[0m\n");
        $this->write("\033[1;36mTEST SUMMARY\033[0m\n");
        $this->write("\033[1;36m" . str_repeat('=', 70) . "\033[0m\n\n");

        parent::printFooter();
    }
}
