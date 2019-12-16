<?php

namespace Drupal\druki_content\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\druki_content\Entity\DrukiContentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a druki content toc block for mobile phones..
 *
 * @Block(
 *   id = "druki_content_toc_moble",
 *   admin_label = @Translation("Druki content TOC (mobile)"),
 *   category = @Translation("Druki content")
 * )
 */
class MobileDrukiContentTocBlock extends DrukiContentTocBlock implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = parent::build();

    if (!isset($build['toc'])) {
      return [];
    }

    $build = [
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['druki-mobile-toc'],
        ],
        'header' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['druki-mobile-toc__header'],
          ],
          'toggle' => [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['druki-mobile-toc__toggle'],
            ],
            '#markup' => new TranslatableMarkup('Contents'),
          ],
        ],
        'content' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['druki-mobile-toc__content'],
          ],
          'toc' => $build['toc'],
        ],
        '#attached' => [
          'library' => ['druki_content/mobile-toc'],
        ],
      ],
    ];

    return $build;
  }

}
