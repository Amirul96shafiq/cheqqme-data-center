<?php

namespace App\Forms\Components;

use Asmit\FilamentMention\Forms\Components\RichMentionEditor as VendorRichMentionEditor;

class SafeRichMentionEditor extends VendorRichMentionEditor
{
  private const MENTION_PATTERN = '/\(--([a-zA-Z0-9_\.]+)--\)/';

  public function dehydrateState(array &$state, bool $isDehydrated = true): void
  {
    $name = $this->getName();

    // Preferred path: standard data shape
    if (isset($state['data']) && is_array($state['data'])) {
      $this->processIntoArray($state['data'], $name);

      return;
    }

    // Fallback: try mounted action shape or any nested arrays
    $firstKey = array_key_first($state);
    if ($firstKey !== null) {
      $segment = $state[$firstKey][0] ?? $state[$firstKey] ?? null;
      if (is_array($segment)) {
        if (isset($segment['data']) && is_array($segment['data'])) {
          $this->processIntoArray($segment['data'], $name);
          $state[$firstKey][0]['data'] = $segment['data'];

          return;
        }

        if (array_key_exists($name, $segment)) {
          $this->processIntoArray($segment, $name);
          $state[$firstKey][0] = $segment;

          return;
        }
      }
    }

    // Last resort: no-op to avoid vendor bug when structure is unexpected
  }

  /**
   * Apply mention extraction and cleaning into the given array using the given field name.
   */
  protected function processIntoArray(array &$arr, string $fieldName): void
  {
    $raw = $arr[$fieldName] ?? null;

    if (!blank($this->getPluck())) {
      $mentions = $this->extractMentionsFromText($raw);
      $mentionKey = 'mentions_' . $this->getName();
      $arr[$mentionKey] = $mentions;
      // Keep vendor parity: expose on Livewire data if available (guarded)
      $livewire = null;
      try {
        $livewire = $this->getLivewire();
      } catch (\Throwable $e) {
        $livewire = null;
      }
      if ($livewire && property_exists($livewire, 'data')) {
        try {
          $livewire->data[$mentionKey] = $mentions;
        } catch (\Throwable $e) { /* ignore */
        }
      }
    }

    $arr[$fieldName] = $this->removeAppendedMarkers($raw);
  }

  protected function extractMentionsFromText(?string $text): array
  {
    preg_match_all(self::MENTION_PATTERN, $text ?? '', $matches);

    return array_unique($matches[1] ?? []);
  }

  protected function removeAppendedMarkers(?string $text): ?string
  {
    return preg_replace(self::MENTION_PATTERN, '', $text ?? '');
  }
}
