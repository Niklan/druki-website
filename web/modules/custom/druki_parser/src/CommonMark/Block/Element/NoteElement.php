<?php

namespace Drupal\druki_parser\CommonMark\Block\Element;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Cursor;

/**
 * Class NoteElement
 *
 * @package Drupal\druki_parser\Plugin\Markdown\Extension
 */
class NoteElement extends AbstractBlock {

  /**
   * The note type.
   *
   * @var string
   */
  protected $type;

  /**
   * NoteElement constructor.
   *
   * @param string $type
   *   The note type.
   */
  public function __construct(string $type) {
    $this->type = $type;
  }

  /**
   * {@inheritdoc}
   */
  public function canContain(AbstractBlock $block): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function acceptsLines(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isCode(): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldLastLineBeBlank(Cursor $cursor, $currentLineNumber): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function matchesNextLine(Cursor $cursor): bool {
    if (!$cursor->isIndented() && $cursor->getNextNonSpaceCharacter() === '>') {
      $cursor->advanceToNextNonSpaceOrTab();
      // Pass ">" char.
      $cursor->advance();
      // Skip any spaces after ">".
      $cursor->advanceBySpaceOrTab();

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Gets type.
   *
   * @return string
   *   The note type.
   */
  public function getType(): string {
    return $this->type;
  }

}
