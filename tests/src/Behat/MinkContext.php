<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\RawMinkContext;
use Drupal\DrupalExtension\TagTrait;

/**
 * A generic Mink context for the module.
 */
class MinkContext extends RawMinkContext {

  use TagTrait;

  /**
   * Disables browser validation for required fields.
   *
   * This step is executed only on scenarios that are tagged with @javascript
   * and @disable-browser-required-field-validation.
   * Note that jQuery is required in the page in order to work.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeStepScope $event
   *   The event.
   *
   * @BeforeStep
   */
  public function disableNativeBrowserRequiredFieldValidation(BeforeStepScope $event): void {
    // Make sure the feature is registered in case this hook fires before
    // ::registerFeature() which is also a @BeforeStep. Behat doesn't
    // support ordering hooks.
    $this->registerFeature($event);

    if (!$this->hasTag('javascript') || !$this->hasTag('disable-browser-required-field-validation')) {
      return;
    }

    $driver = $this->getSession()->getDriver();
    if (!$driver instanceof Selenium2Driver or !$driver->isStarted()) {
      return;
    }

    // Check if any forms are available in the page.
    $forms = $this->getSession()->getPage()->findAll('css', 'form');
    if (empty($forms)) {
      return;
    }

    $this->getSession()->executeScript("typeof jQuery === 'undefined' || jQuery(':input[required]').prop('required', false);");
  }

}
