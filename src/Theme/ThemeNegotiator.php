<?php

namespace Drupal\themerules\Theme;

use Drupal;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ThemeNegotiator implements ThemeNegotiatorInterface {
  private $entity_manager;
  private $_cache;

  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entity_manager = $entity_manager;
  }
  public function applies(RouteMatchInterface $match) {
    $override = $this->matchingOverride(Drupal::request());
    return $override != null;
  }
  public function determineActiveTheme(RouteMatchInterface $match) {
    if ($override = $this->matchingOverride(Drupal::request())) {
      return $override->getTheme();
    }
  }

  private function matchingOverride(Request $request) {
    $host = Drupal::request()->getHost();
    $path = Drupal::request()->getRequestUri();

    if (substr($path, 0, 6) === '/admin') {
      return null;
    }
    foreach ($this->cache() as $override) {
      if (in_array($host, $override->getDomains())) {
        return $override;
      }
      foreach ($override->getPaths() as $rule_path) {
        if (substr($rule_path, 0, strlen($path)) === $path) {
          return $override;
        }
      }
    }
  }

  private function cache() {
    if (is_null($this->_cache)) {
      $this->_cache = $this->entity_manager->getStorage('theme_override')->loadMultiple();
    }
    return $this->_cache;
  }
}
