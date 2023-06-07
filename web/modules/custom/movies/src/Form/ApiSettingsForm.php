<?php

namespace Drupal\movies\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Movies settings for this site.
 */
class ApiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'movies_api_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['movies.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['apikey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api Key'),
      '#default_value' => $this->config('movies.settings')->get('apikey'),
      '#required' => TRUE,
    ];

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search Term'),
      '#default_value' => $this->config('movies.settings')->get('search'),
      '#required' => TRUE,
      '#placeholder' => 'Dog',
    ];

    $form['year'] = [
      '#type' => 'number',
      '#title' => $this->t('Year'),
      '#default_value' => $this->config('movies.settings')->get('year'),
      '#required' => TRUE,
      '#placeholder' => '2018',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo Add validation
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Saves all configurations from the form.
    $this->config('movies.settings')
      ->set('apikey', $form_state->getValue('apikey'))
      ->set('search', $form_state->getValue('search'))
      ->set('year', $form_state->getValue('year'))
      ->save();

    // Checks if movies exist with the search parameters.
    $allMovies = \Drupal::service('movies.import.movies')
      ->searchMovies($this->config('movies.settings')->get('year'), $this->config('movies.settings')->get('search'));

    // If no movies, return a message.
    if (!$allMovies) {
      \Drupal::messenger()->addError('There are no movies that match your search, please try something else.');
    }
    else {
      // Batch process movies to import them into site.
      $operations = [
        ['create_movies', [$allMovies]],
      ];
      $batch = [
        'title' => $this->t('Creating 10 movies based on ' . $this->config('movies.settings')->get('search') . ' and in year ' . $this->config('movies.settings')->get('year') . ' ...'),
        'operations' => $operations,
        'progress_message' => t('Processed @current out of @total.'),
        'finished' => 'create_movies_finished',
      ];

      batch_set($batch);

      parent::submitForm($form, $form_state);
    }

  }

}
