<?php

namespace Drupal\themerules;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

class OverrideListBuilder extends EntityListBuilder {
  public function buildHeader() {
    $header = [
      // 'id' => $this->t('ID'),
      'label' => $this->t('Name'),
      'weight' => $this->t('Weight'),
    ];
    return $header + parent::buildHeader();
  }

  public function buildRow(EntityInterface $template) {
    $row = [
      // 'id' => $template->id(),
      'label' => $template->label(),
      'weight' => $template->getWeight(),
    ];
    return $row + parent::buildRow($template);
  }

  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    return $operations;
  }
}
