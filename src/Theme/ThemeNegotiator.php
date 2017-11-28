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
    return $override != NULL;
  }

  public function determineActiveTheme(RouteMatchInterface $match) {
    if ($override = $this->matchingOverride($this->request())) {
      return $override->getTheme();
    }
  }

  private function matchingOverride(Request $request) {
    $host = $this->request()->getHost();
    $path = $this->request()->getRequestUri();

    // if (substr($path, 0, 6) === '/admin') {
    //   return NULL;
    // }

    foreach ($this->cache() as $override) {
      if (in_array($host, $override->getDomains())) {
        return $override;
      }
      foreach ($override->getPaths() as $rule_path) {
        if (mb_substr($path, 0, mb_strlen($rule_path)) === $rule_path) {
          return $override;
        }
      }
    }

    return NULL;
  }

  private function request() {
    return $this->request_stack->getCurrentRequest();
  }

  private function cache() {
    if (is_null($this->_cache)) {
      $this->_cache = $this->entity_manager->getStorage('theme_override')->loadMultiple();

      usort($this->_cache, function($a, $b) {
        if ($a->getWeight() != $b->getWeight()) {
          return $a->getWeight() - $b->getWeight();
        }
        return strcasecmp($a->id(), $b->id());
      });
    }
    return $this->_cache;
  }
}
