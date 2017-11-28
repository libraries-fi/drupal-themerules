<?php

namespace Drupal\themerules\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the contact form entity.
 *
 * @ConfigEntityType(
 *   id = "theme_override",
 *   label = @Translation("Theme Override"),
 *   handlers = {
 *     "list_builder" = "Drupal\themerules\OverrideListBuilder",
 *     "form" = {
 *       "add" = "Drupal\themerules\Form\OverrideEditForm",
 *       "edit" = "Drupal\themerules\Form\OverrideEditForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "override",
 *   admin_permission = "administer themes",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "canonical" = "/admin/themerules/{themerules}",
 *     "collection" = "/admin/themerules",
 *     "delete-form" = "/admin/themerules/{themerules}/delete",
 *     "edit-form" = "/admin/themerules/{themerules}/edit",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "theme",
 *     "domains",
 *     "paths",
 *     "weight"
 *   }
 * )
 */
class Override extends ConfigEntityBase {
  protected $id;
  protected $label;
  protected $theme;
  protected $domains = [];
  protected $paths = [];
  protected $weight = 0;

  public function getTheme() {
    return $this->theme;
  }

  public function setTheme($theme) {
    $this->theme = $theme;
  }

  public function getDomains() {
    return $this->domains;
  }

  public function setDomains(array $domains) {
    $this->domains = $domains;
  }

  public function getPaths() {
    return $this->paths;
  }

  public function setPaths(array $paths) {
    $this->paths = $paths;
  }

  public function getWeight() {
    return $this->weight;
  }

  public function setWeight($weight) {
    $this->weight = (int)$weight;
  }
}
