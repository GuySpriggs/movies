<?php

namespace Drupal\movies\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\media\Entity\Media;
use Drupal\Core\File\FileSystemInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * A service used to import/create movies from OMDB.
 */
class CreateMovieService implements CreateMovieServiceInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * ImportMovieService constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger channel.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    LoggerChannelInterface $logger,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->logger = $logger;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Used to process movies from the Openimdb api and create them on the site.
   *
   * @param array $movie
   *   The full movie details and fields from the api.
   *
   * @return void
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function processMovie(array $movie) : void {
    $movieExists = $this->checkMovieExists($movie['imdbID']);
    if (!$movieExists) {
      $this->createMovie($movie);
    }
    else {
      $this->logger->notice('Unable to create movie ' . $movie['Title'] . ' as it already exists.');
    }
  }

  /**
   * Used to check if a movie already exists on the site.
   *
   * @param string $imdbId
   *   The full movie details and fields from the api.
   *
   * @return bool
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function checkMovieExists(string $imdbId) : bool {
    $existsQuery = $this->entityTypeManager->getStorage('node')->loadByProperties(['field_imdb_id' => $imdbId]);
    if (empty($existsQuery)) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  /**
   * Used to create the movie on the site.
   *
   * @param array $movie
   *   The full movie details and fields from the api.
   *
   * @return void
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createMovie(array $movie) : void {

    $mediaId = $this->createPoster($movie['Poster'], $movie['Title']);
    $genreIds = $this->checkTaxonomyTerm($movie['Genre'], 'genre');

    $movie = $this->entityTypeManager->getStorage('node')->create([
      'title' => $movie['Title'],
      'type' => 'movie',
      'field_year' => $movie['Year'],
      'body' => $movie['Plot'],
      'field_imdb_id' => $movie['imdbID'],
      'field_image' => [
        'target_id' => $mediaId,
      ],
    ]);

    foreach ($genreIds as $termId) {
      $movie->get('field_genre')->appendItem(['target_id' => $termId]);
    }

    $movie->save();

  }

  /**
   * Used to create a media entity for the poster.
   *
   * @param string $imageUrl
   *   The url to the poster of the movie.
   * @param string $title
   *   The title of the movie.
   *
   * @return string
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createPoster(string $imageUrl, string $title): string {
    $exists = $this->entityTypeManager->getStorage('media')->loadByProperties(['name' => $title . ' Poster']);

    if (!empty($exists)) {
      $image = reset($exists);
      return $image->id();
    }

    $image_data = file_get_contents($imageUrl);

    $file_repository = \Drupal::service('file.repository');
    $image = $file_repository->writeData($image_data, "public://" . $title . '.jpg', FileSystemInterface::EXISTS_REPLACE);

    $image_media = Media::create([
      'name' => $title . ' Poster',
      'bundle' => 'image',
      'uid' => 1,
      'langcode' => 'en',
      'status' => 1,
      'field_media_image' => [
        'target_id' => $image->id(),
        'alt' => $title,
        'title' => $title,
      ],
    ]);
    $image_media->save();

    return $image_media->id();
  }

  /**
   * Used to check if a taxonomy term exists, if it doesn't create it.
   *
   * @param string $terms
   *   The terms associated to the movie.
   * @param string $vocabulary
   *   The vocabulary id.
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function checkTaxonomyTerm(string $terms, string $vocabulary) : array {
    $termsArray = explode(',', $terms);

    $termIds = [];
    foreach ($termsArray as $term) {
      $exists = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(
        ['name' => trim($term), 'vid' => $vocabulary]);
      if (!empty($exists)) {
        $existingTerm = reset($exists);
        $termIds[] = $existingTerm->id();
      }
      else {
        $term = Term::Create([
          'name'     => trim($term),
          'vid'      => $vocabulary,
        ]);
        $term->save();

        $termIds[] = $term->id();
      }
    }
    return $termIds;

  }

}
