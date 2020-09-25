<?php

namespace Drupal\druki\Cron;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\druki\Drupal\DrupalProjects;
use Drupal\druki\Drupal\DrupalReleases;

/**
 * Provides cron processor to check Drupal releases.
 */
final class CheckDrupalReleasesCron implements CronProcessorInterface {

  /**
   * The datetime service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * The Drupal projects.
   *
   * @var \Drupal\druki\Drupal\DrupalProjects
   */
  protected $drupalProjects;

  /**
   * The Drupal releases.
   *
   * @var \Drupal\druki\Drupal\DrupalReleases
   */
  protected $drupalReleases;

  /**
   * Constructs a new CheckDrupalReleasesCron object.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The datetime.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   * @param \Drupal\druki\Drupal\DrupalReleases $drupal_releases
   *   The Drupal releases.
   * @param \Drupal\druki\Drupal\DrupalProjects $drupal_projects
   *   The Drupal projects.
   */
  public function __construct(TimeInterface $time, CacheTagsInvalidatorInterface $cache_tags_invalidator, DrupalReleases $drupal_releases, DrupalProjects $drupal_projects) {
    $this->time = $time;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->drupalReleases = $drupal_releases;
    $this->drupalProjects = $drupal_projects;
  }

  /**
   * {@inheritdoc}
   */
  public function process(): void {
    $request_time = $this->time->getRequestTime();
    $drupal_releases = $this->drupalReleases->get();

    // The information still valid.
    if ($request_time < $drupal_releases['expires']) {
      return;
    }

    // Trying to get new last stable release for Drupal project.
    $stable_version = $this->drupalProjects->getCoreLastStableVersion();
    $minor_version = $this->drupalProjects->getCoreLastMinorVersion();

    // If release can't be retrieved, we skip everything else and wait for
    // next cron to try again.
    if (!$stable_version || !$minor_version) {
      return;
    }

    $drupal_releases['last_stable_release'] = $stable_version;
    $drupal_releases['last_minor_release'] = $minor_version;

    // We check releases on wednesday evey hour, since it release window.
    $request_datetime = DrupalDateTime::createFromTimestamp($request_time);
    $next_wednesday = new DrupalDateTime('next wednesday');
    $next_wednesday_interval = $request_datetime->diff($next_wednesday);
    if ($next_wednesday_interval->d == 6) {
      // It's wednesday my dudes.
      $drupal_releases['expires'] = $request_time + (60 * 60 * 1);
    }
    elseif ($next_wednesday_interval->d == 0) {
      // Wednesday is tomorrow. Delay it to the beginning of the day.
      $drupal_releases['expires'] = $next_wednesday->getTimestamp();
    }
    else {
      // On other days check once a day.
      $drupal_releases['expires'] = $request_time + (60 * 60 * 24);
    }

    $this->drupalReleases->set($drupal_releases);
    // Invalidate all caches which uses last stable release value.
    $this->cacheTagsInvalidator->invalidateTags([DrupalReleases::CACHE_TAG]);
  }

}
