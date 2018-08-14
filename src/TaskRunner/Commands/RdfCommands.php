<?php

namespace Drupal\oe_content\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;

/**
 * Class RdfCommands.
 */
class RdfCommands extends AbstractCommands {

  /**
   * Purge command scaffolding.
   *
   * @command rdf:purge
   */
  public function purge() {
    $this->io()->writeln($this->getConfig()->get('rdf.binary'));
  }

  /**
   * Import command scaffolding.
   *
   * @command rdf:import
   */
  public function import() {
    $this->io()->writeln($this->getConfig()->get('rdf.binary'));
  }

  /**
   * Cleanup command scaffolding.
   *
   * @command rdf:cleanup
   */
  public function cleanup() {
    $this->io()->writeln($this->getConfig()->get('rdf.binary'));
  }

}
