<?php

namespace Drupal\movies\Service;

/**
 * A service used to get movies from OMDB.
 */
interface ImportMovieServiceInterface {

  /**
   * Queries the API to see if there are any movies based on their year and title.
   *
   * @param int $year
   *   The year the movies was searched in.
   * @param string $searchQuery
   *   The search query for movies to import.
   *
   * @return array
   */
  public function searchMovies(int $year, string $searchQuery) : array;

}
