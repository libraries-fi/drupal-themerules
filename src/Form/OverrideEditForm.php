<?php

namespace Drupal\themerules\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\ConfigFormBaseTrait;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OverrideEditForm extends EntityForm implements ContainerInjectionInterface {
  use ConfigFormBaseTrait;

  private $themes;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme_handler')
    );
  }

  public function __construct(ThemeHandlerInterface $themes) {
    $this->themes = $themes;
  }

  public function getEditableConfigNames() {
    return ['themerules.settings'];
  }

  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $list = array_filter($this->themes->listInfo(), function($info) {
      return empty($info->info['hidden']);
    });

    $names = array_map(function($info) { return $info->info['name']; }, $list);
    asort($names);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->entity->label(),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => '\Drupal\themerules\Entity\Override::load',
      ],
      '#disabled' => !$this->entity->isNew(),
      '#default_value' => $this->entity->id(),
    ];


    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#options' => $names,
      '#default_value' => $this->entity->getTheme() ?: $this->themes->getDefault(),
    ];

    $form['field_domains'] = [
      '#type' => 'details',
      '#title' => $this->t('Domain settings'),
      '#open' => !empty($this->entity->getDomains()),
      'domains' => [
        '#type' => 'textarea',
        '#title' => $this->t('Domains'),
        '#default_value' => implode(PHP_EOL, $this->entity->getDomains()),
        '#description' => $this->t('One domain per row.'),
        '#rows' => 10,
      ]
    ];

    $form['field_paths'] = [
      '#type' => 'details',
      '#title' => $this->t('Path settings'),
      '#open' => !empty($this->entity->getPaths()),
      'paths' => [
        '#type' => 'textarea',
        '#title' => $this->t('Paths'),
        '#description' => $this->t('One path per row. Paths must start with a slash'),
        '#default_value' => implode(PHP_EOL, $this->entity->getPaths()),
        '#rows' => 10,
      ]
    ];

    return $form;
  }

  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->setDomains(array_filter(array_map('trim', explode(PHP_EOL, $form_state->getValue('domains')))));
    $this->entity->setPaths(array_filter(array_map('trim', explode(PHP_EOL, $form_state->getValue('paths')))));
    $status = $this->entity->save();
    $form_state->setRedirectUrl($this->entity->urlInfo('collection'));

    if ($status = SAVED_UPDATED) {
      drupal_set_message($this->t('Theme override %override has been updated.', [
        '%override' => $this->entity->label(),
      ]));
    } else {
      drupal_set_message($this->t('Theme override %override has been added.', [
        '%override' => $this->entity->label(),
      ]));
    }
  }
}
