<?php

namespace Drupal\movies\Service;

/**
 * A service used to get movies from OMDB.
 */
interface CreateMovieServiceInterface {

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
  public function processMovie(array $movie) : void;

}
