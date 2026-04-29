<?php

namespace App\Services\IntellectualProperty;

use App\Models\IntellectualProperty;
use App\Models\IntellectualPropertyClaim;
use App\Models\IntellectualPropertyDocument;
use App\Models\IntellectualPropertySetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IntellectualPropertyService
{
    /**
     * Create a new class instance.
     */
    public function getSettings($intellectualProperty): array
    {
        $setting = IntellectualPropertySetting::where('intellectual_property_id', $intellectualProperty->id)->first();

        if (!$setting) {
            return [];
        }

        return [
            'amount' => $setting->amount,
            'payment_options' => collect($setting->allowed_term_months)
                ->map(fn($months) => [
                    'term_months' => $months,
                    'label' => $months === 1
                        ? 'Pay in Full'
                        : "{$months} Monthly Installments",
                    'amount_per_term' => (int) ceil($setting->amount / $months),
                ])
                ->values()
                ->toArray(),
        ];
    }

    /**
     * Get intellectual property for the user.
     *
     * @param $user
     * @param array $includes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listIntellectualProperty($user, array $includes = []): \Illuminate\Database\Eloquent\Collection
    {
        return $user->intellectualProperties()
            ->when(!empty($includes), fn($q) => $q->with($includes))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new intellectual property.
     *
     * @param array $data
     * @param User $user
     * @return \App\Models\IntellectualProperty
     */
    public function create(array $data, User $user): IntellectualProperty
    {
        return DB::transaction(function () use ($user, $data) {
            $application = IntellectualProperty::create([
                'user_id' => $user->id,
                'creation_type' => $data['creation_type'],
                'form_type' => $data['form_type'],
                'title' => $data['title'],
                'description' => $data['description'],
                'applicability' => $data['applicability'],
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            $this->syncClaims($application, $data['claims'] ?? []);

            if (!empty($data['documents'])) {
                $this->storeFiles($application, $data['documents'], $data['file_type'] ?? null);
            }

            return $application->load(['claims', 'documents']);
        });
    }

    /**
     * Update a draft application.
     */
    public function update(IntellectualProperty $application, array $data): IntellectualProperty
    {
        return DB::transaction(function () use ($application, $data) {
            $fillable = array_filter(
                array_diff_key($data, array_flip(['claims', 'documents', 'delete_document_ids'])),
                fn($v) => $v !== null
            );

            $application->update($fillable);

            if (isset($data['claims'])) {
                $this->syncClaims($application, $data['claims']);
            }

            if (!empty($data['delete_document_ids'])) {
                $application->documents()
                    ->whereIn('id', $data['delete_document_ids'])
                    ->get()
                    ->each(function ($doc) {
                        Storage::disk('public')->delete($doc->attachment);
                        $doc->delete();
                    });
            }

            if (!empty($data['documents'])) {
                $this->storeFiles($application, $data['documents'], null);
            }

            return $application->load(['claims', 'documents']);
        });
    }

    /**
     * Attach uploaded files to an application.
     */
    public function attachFiles(IntellectualProperty $application, array $files, ?string $fileType = null): IntellectualProperty
    {
        $this->storeFiles($application, $files, $fileType);

        return $application->load('documents');
    }

    /**
     * Delete a specific upload.
     */
    public function deleteUpload(IntellectualPropertyDocument $upload): void
    {
        Storage::delete($upload->file_path);
        $upload->delete();
    }

    /**
     * Sync claims (delete old, insert new with ordering).
     */
    private function syncClaims(IntellectualProperty $application, array $claims): void
    {
        $application->claims()->delete();

        foreach ($claims as $index => $claim) {
            IntellectualPropertyClaim::create([
                'intellectual_property_id' => $application->id,
                'description' => $claim['description'],
            ]);
        }
    }

    /**
     * Store files to disk and persist records.
     */
    private function storeFiles(IntellectualProperty $application, array $files, ?string $fileType): void
    {
        foreach ($files as $file) {
            /** @var UploadedFile $file */
            $path = $file->store("ip_applications/{$application->id}/documents", 'public');

            IntellectualPropertyDocument::create([
                'intellectual_property_id' => $application->id,
                'attachment' => $path,
            ]);
        }
    }
}
