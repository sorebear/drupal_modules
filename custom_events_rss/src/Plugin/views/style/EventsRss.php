<?php

namespace Drupal\custom_events_rss\Plugin\views\style;

use Drupal\views\Plugin\views\style\Rss;

/**
 * Style plugin to render a Google News rss feed.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "google_news_feed",
 *   title = @Translation("Google News"),
 *   help = @Translation("Google News Feed"),
 *   theme = "views_view_rss_google_news_feed",
 *   display_types = { "feed" }
 * )
 */
class EventsRss extends Rss {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }
}
