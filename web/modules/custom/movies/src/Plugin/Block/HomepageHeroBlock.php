<?php

namespace Drupal\movies\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a homepage hero block block.
 *
 * @Block(
 *   id = "movies_homepage_hero_block",
 *   admin_label = @Translation("Homepage Hero Block"),
 *   category = @Translation("Custom")
 * )
 */
class HomepageHeroBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'heading' => $this->t('This is a super cool heading!'),
      'body' => $this->t('Wow this is some cool text, my word!'),
      'image' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['heading'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Heading'),
      '#default_value' => $this->configuration['heading'],
    ];

    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#default_value' => $this->configuration['body'],
    ];

    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => 'Hero Image',
      '#name' => 'hero_image',
      '#description' => $this->t('A image used for the hero image.'),
      '#default_value' => $this->configuration['image'],
      '#upload_location' => 'public://',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['heading'] = $form_state->getValue('heading');
    $this->configuration['body'] = $form_state->getValue('body');
    $this->configuration['image'] = $form_state->getValue('image');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Sets the values from the block form.
    $image = $this->configuration['image'] ? : $this->defaultConfiguration()['image'];

    if ($image) {
      $file = File::load((int) $image[0]);
      $url = $file->createFileUrl();
    }
    else {
      $url = drupal_get_path('module', 'movies') . '/images/dog.jpeg';
    }
    $heading = $this->configuration['heading'] ? : $this->defaultConfiguration()['heading'];
    $body = $this->configuration['body'] ? : $this->defaultConfiguration()['body'];

    // Passes the variables to the twig template.
    $build = [
      '#theme' => 'homepage_hero_block',
      '#heading' => $heading,
      '#body' => $body,
      '#image' => $url,
    ];
    return $build;
  }

}
