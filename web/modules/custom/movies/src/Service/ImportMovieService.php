<?php

namespace Drupal\movies\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use GuzzleHttp\Client;

/**
 * A service used to get movies from OMDB.
 */
class ImportMovieService implements ImportMovieServiceInterface {

  /**
   * The ras_user logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * The http client.
   *
   * @var GuzzleHttp\Client
   */
  protected Client $httpClient;

  /**
   * The creation movie service.
   *
   * @var \Drupal\movies\Service\CreateMovieService
   */
  protected CreateMovieService $createMovieService;

  /**
   * The api key.
   *
   * @var string
   */
  protected string $apiKey;

  /**
   * ImportMovieService constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger channel.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \GuzzleHttp\Client $http_client
   *   A Guzzle client object.
   * @param \Drupal\movies\Service\CreateMovieService $createMovieService
   */
  public function __construct(
    LoggerChannelInterface $logger,
    ConfigFactoryInterface $configFactory,
    Client $http_client,
    CreateMovieService $createMovieService
  ) {
    $this->createMovieService = $createMovieService;
    $this->logger = $logger;
    $this->apiKey = $configFactory->get('movies.settings')->get('apikey');
    $this->httpClient = $http_client;
  }

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
  public function searchMovies(int $year, string $searchQuery) : array {
    $allMovies = [];

    $query = [
      'apikey' => $this->apiKey,
      'y' => $year,
      's' => $searchQuery,
    ];

    foreach ($this->apiCall($query)['Search'] as $movie) {
      $allMovies[] = $this->getMovieSpecifics($movie['imdbID']);
    }

    return $allMovies;

  }

  /**
   * Does an API call to get the specific movie details.
   *
   * @param string $imdbId
   *   The imdb ID.
   *
   * @return array
   */
  protected function getMovieSpecifics(string $imdbId) : array {
    $query = [
      'apikey' => $this->apiKey,
      'i' => $imdbId,
    ];

    return $this->apiCall($query);

  }

  /**
   * Used to make API calls to the OpenIMDB endpoint.
   *
   * @param array $query
   *   The query for each specific API call.
   *
   * @return array
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function apiCall(array $query): array {
    try {
      $response = $this->httpClient->request('GET', 'https://www.omdbapi.com/', [
        'query' => $query,
      ]);
    }
    catch (ClientException $error) {
      $this->logger->error('Unable to do the api call due to ' . $error->getMessage());
    }
    return json_decode($response->getBody()->getContents(), TRUE);
  }

}
