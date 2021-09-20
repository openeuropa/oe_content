<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\TaskRunner\Commands;

use Behat\Gherkin\Keywords\ArrayKeywords;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Parser;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Symfony\Component\Finder\Finder;

/**
 * TaskRunner commands related to Behat tests setup.
 */
class BehatCommands extends AbstractCommands {

  /**
   * Ensure that all scenarios have been tagged with a batch tag.
   *
   * @command behat:ensure-batching
   */
  public function ensureBehatBatching(): int {
    // Setup Gherkin parser.
    $keywords = new ArrayKeywords([
      'en' => [
        'feature'          => 'Feature',
        'background'       => 'Background',
        'scenario'         => 'Scenario',
        'scenario_outline' => 'Scenario Outline|Scenario Template',
        'examples'         => 'Examples|Scenarios',
        'given'            => 'Given',
        'when'             => 'When',
        'then'             => 'Then',
        'and'              => 'And',
        'but'              => 'But',
      ],
    ]);
    $lexer = new Lexer($keywords);
    $parser = new Parser($lexer);
    $finder = new Finder();
    $finder->files()->in(__DIR__ . '/../../../../tests/features');

    // Collect all scenarios that are not tagged with '@batch*'.
    $not_tagged = [];
    foreach ($finder as $file) {
      $feature = $parser->parse($file->getContents());
      foreach ($feature->getScenarios() as $scenario) {
        $tags = $scenario->getTags();
        if (empty(preg_grep('/^batch(\d+)$/', $tags))) {
          $not_tagged[] = $scenario->getTitle();
        }
      }
    }

    // If no tagged scenarios found exit with status 1, so that builds can fail.
    if (!empty($not_tagged)) {
      $error_messages = array_merge(['The following scenarios have not been assigned to a test batch:'], $not_tagged);
      $this->io()->error($error_messages);
      return 1;
    }

    $this->io()->success('All scenarios have been assigned to a test batch.');
    return 0;
  }

}
