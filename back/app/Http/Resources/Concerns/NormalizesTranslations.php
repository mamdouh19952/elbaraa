<?php

namespace App\Http\Resources\Concerns;

trait NormalizesTranslations
{
    /**
     * Raw per-locale translations for a translatable attribute, normalized to a
     * stable {ar, en, zh} shape (null for missing locales) so the frontend can
     * always rely on all three keys being present — including to detect gaps
     * for the admin completeness indicator.
     *
     * Uses getTranslations() (no fallback), not getTranslation(), so a missing
     * translation stays visible here rather than being silently backfilled.
     *
     * @return array<string, string|null>
     */
    private function translations(string $field): array
    {
        return array_merge(['ar' => null, 'en' => null, 'zh' => null], $this->getTranslations($field));
    }
}
