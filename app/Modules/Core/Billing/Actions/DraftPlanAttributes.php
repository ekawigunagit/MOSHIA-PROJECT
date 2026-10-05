<?php

namespace App\Modules\Core\Billing\Actions;

class DraftPlanAttributes
{
    /**
     * Called only with validated admin input; prices never come from the browser.
     * Editing without changing the preset preserves the existing draft snapshot.
     */
    public function build(array $validated, ?array $existingTerms = null): array
    {
        $key = $validated['wedding_package'] ?? null;
        $terms = $key ? config('wedding_plans.packages.'.$key) : null;

        if ($key && ($existingTerms['key'] ?? null) === $key) {
            $terms = $existingTerms;
        }

        return [
            'product_id' => $validated['product_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'commercial_terms' => $terms,
        ];
    }
}
