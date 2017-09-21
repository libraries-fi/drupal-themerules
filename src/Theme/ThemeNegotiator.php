<?php

namespace Drupal\themerules\Theme;

use Drupal;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ThemeNegotiator implements ThemeNegotiatorInterface {
  private $entity_manager;
  private $request_stack;
  private $_cache;

  public function __construct(EntityTypeManagerInterface $entity_manager, $request_stack) {
    $this->entity_manager = $entity_manager;
    $this->request_stack = $request_stack;
  }

  public function applies(RouteMatchInterface $match) {
    $override = $this->matchingOverride($this->request());
    return $override != null;
  }

  public function determineActiveTheme(RouteMatchInterface $match) {
    if ($override = $this->matchingOverride($this->request())) {
      return $override->getTheme();
    }
  }

  private function matchingOverride(Request $request) {
    $host = $this->request()->getHost();
    $path = $this->request()->getRequestUri();

    if (substr($path, 0, 6) === '/admin') {
      return null;
    }

    foreach ($this->cache() as $override) {
      if (in_array($host, $override->getDomains())) {
        return $override;
      }
      foreach ($override->getPaths() as $rule_path) {
        if (substr($path, 0, strlen($rule_path)) === $rule_path) {
          return $override;
        }
      }
    }
  }

  private function request() {
    return $this->request_stack->getCurrentRequest();
  }

  private function cache() {
    if (is_null($this->_cache)) {
      $this->_cache = $this->entity_manager->getStorage('theme_override')->loadMultiple();
    }
    return $this->_cache;
  }
}
