<?php

declare(strict_types = 1);

namespace Drupal\oe_content_canonical\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for Node routes.
 */
class CanonicalUrlController extends ControllerBase implements ContainerInjectionInterface {

    public function __construct() {

    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
        );
    }

    public function index(string $uuid): RedirectResponse {
        return $this->redirect('entity.node.canonical', ['node' => 333]);
    }


}
