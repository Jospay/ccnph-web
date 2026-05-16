<?php

namespace App\Services\IntellectualProperty;

use App\Models\IntellectualProperty;
use App\Models\IntellectualPropertyClaim;
use App\Models\IntellectualPropertyDocument;
use App\Models\IntellectualPropertySetting;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IntellectualPropertyService
{
    /**
     * Get settings of intellectual property.
     */
    public function getSettings($intellectualProperty): array
    {
        $setting = IntellectualPropertySetting::where(
            'intellectual_property_id',
            $intellectualProperty->id
        )->first();

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
     * List intellectual properties.
     */
    public function listIntellectualProperty(
        $user,
        array $includes = []
    ): \Illuminate\Database\Eloquent\Collection {
        return $user->intellectualProperties()
            ->when(
                !empty($includes),
                fn($q) => $q->with($includes)
            )
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create intellectual property.
     */
    public function create(
        array $data,
        User $user
    ): IntellectualProperty {
        return DB::transaction(function () use ($user, $data) {

            $application = IntellectualProperty::create([
                'user_id' => $user->id,

                'status_id' => Status::PENDING,

                'creation_type' => $data['creation_type'],

                'form_type' => $data['form_type'],

                'title' => $data['title'],

                'description' => $data['description'],

                'applicability' => $data['applicability'],
            ]);

            $this->syncClaims(
                $application,
                $data['claims'] ?? []
            );

            if (!empty($data['documents'])) {
                $this->storeFiles(
                    $application,
                    $data['documents']
                );
            }

            return $application->load([
                'claims',
                'documents',
                'status',
            ]);
        });
    }

    /**
     * Update intellectual property.
     */
    public function update(
        IntellectualProperty $application,
        array $data
    ): IntellectualProperty {
        return DB::transaction(function () use ($application, $data) {

            $fillable = array_filter(
                array_diff_key(
                    $data,
                    array_flip([
                        'claims',
                        'documents',
                        'delete_document_ids'
                    ])
                ),
                fn($v) => $v !== null
            );

            $application->update($fillable);

            if (isset($data['claims'])) {
                $this->syncClaims(
                    $application,
                    $data['claims']
                );
            }

            if (!empty($data['delete_document_ids'])) {

                $application->documents()
                    ->whereIn('id', $data['delete_document_ids'])
                    ->get()
                    ->each(function ($doc) {

                        if ($doc->attachment) {
                            Storage::disk('public')
                                ->delete($doc->attachment);
                        }

                        $doc->delete();
                    });
            }

            if (!empty($data['documents'])) {
                $this->storeFiles(
                    $application,
                    $data['documents']
                );
            }

            return $application->load([
                'claims',
                'documents',
                'status',
            ]);
        });
    }

    /**
     * Attach files.
     */
    public function attachFiles(
        IntellectualProperty $application,
        array $files
    ): IntellectualProperty {

        $this->storeFiles($application, $files);

        return $application->load('documents');
    }

    /**
     * Delete upload.
     */
    public function deleteUpload(
        IntellectualPropertyDocument $upload
    ): void {

        if ($upload->attachment) {
            Storage::disk('public')
                ->delete($upload->attachment);
        }

        $upload->delete();
    }

    /**
     * Sync claims.
     */
    private function syncClaims(
        IntellectualProperty $application,
        array $claims
    ): void {

        $application->claims()->delete();

        foreach ($claims as $claim) {

            IntellectualPropertyClaim::create([
                'intellectual_property_id' => $application->id,

                'description' => $claim['description'] ?? '',
            ]);
        }
    }

    /**
     * Store uploaded files.
     */
    private function storeFiles(
        IntellectualProperty $application,
        array $files
    ): void {

        foreach ($files as $file) {

            /** @var UploadedFile $file */

            $path = $file->store(
                "ip_applications/{$application->id}/documents",
                'public'
            );

            IntellectualPropertyDocument::create([
                'intellectual_property_id' => $application->id,

                'attachment' => $path,
            ]);
        }
    }
}
