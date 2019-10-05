<?php

namespace Drupal\afrikaburn_bio\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use \Drupal\Core\Site\Settings;

/**
 * Provides a Ticket Block.
 * TODO: Move to shared
 *
 * @Block(
 *   id = "ticket_block",
 *   admin_label = @Translation("Ticket Block"),
 *   category = @Translation("Afrikaburn Blocks"),
 * )
 */
class TicketBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $links = ['<a class="button bio-update" href="/tickets"><strong>Tickets</strong></a>'];
    $links[] = '<a class="more" target="_blank" href="https://www.afrikaburn.com/the-event/tickets">More about tickets</a>';

    return [
      '#type' => 'markup',
      '#markup' => implode($links, '<br />'),
    ];
  }
}
