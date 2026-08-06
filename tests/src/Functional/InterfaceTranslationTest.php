<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_content\Traits\InterfaceTranslationTrait;
use Drupal\language\Entity\ConfigurableLanguage;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests the interface translations shipped with the module.
 *
 * @group oe_content
 * @group batch2
 */
class InterfaceTranslationTest extends BrowserTestBase {

  use CachedDatabaseInstallTrait;
  use InterfaceTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'language',
    'locale',
    'oe_content',
    'oe_content_timeline_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->cacheDbInstall = TRUE;
    parent::setUp();

    ConfigurableLanguage::createFromLangcode('bg')->save();
    ConfigurableLanguage::createFromLangcode('fr')->save();
    $this->importTranslations('bg');
    $this->importTranslations('fr', 'oe_content_timeline_field');
  }

  /**
   * Tests that the string translations are imported.
   */
  public function testStringsAreImported(): void {
    $user = $this->drupalCreateUser([
      'translate interface',
      'access administration pages',
      'view the administration theme',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('admin/config/regional/translate');

    // Date field strings.
    $this->submitForm([
      'langcode' => 'bg',
      'string' => 'Sunday',
    ], 'Filter');
    $this->assertSession()->pageTextContains('неделя');

    $this->submitForm([
      'string' => 'January',
    ], 'Filter');
    $this->assertSession()->pageTextContains('януари');

    // Timeline string.
    $this->submitForm([
      'langcode' => 'fr',
      'string' => 'Show full timeline',
    ], 'Filter');
    $this->assertSession()->pageTextContains('Voir l’historique complet');

  }

}
