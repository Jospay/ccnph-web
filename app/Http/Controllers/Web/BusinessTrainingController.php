<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BusinessTraining;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\UserType;
use Inertia\Inertia;
use App\Models\BusinessTrainingType;
use App\Models\BusinessTrainingCategory;
use App\Http\Requests\BusinessTraining\StoreTypeRequest;
use App\Http\Requests\BusinessTraining\StoreCategoryRequest;

class BusinessTrainingController extends Controller
{
    // for write permission
    private function canMutate(): bool
    {
        return Auth::user()->user_type_id === UserType::ADMIN;
    }

    public function index()
    {
        return Inertia::render('business-training/Index', [
            'types' => BusinessTrainingType::all(),
            'can_mutate' => $this->canMutate(), 
        ]);
    }

    public function showType($slug)
    {
        $type = BusinessTrainingType::where('slug', $slug)
            ->with('categories')
            ->firstOrFail();

        return Inertia::render('business-training/ShowType', [
            'type' => $type,
            'can_mutate' => $this->canMutate(),
        ]);
    }

    public function getCategoryModules($slug)
    {
        $category = BusinessTrainingCategory::where('slug', $slug)
            ->with('trainings')
            ->firstOrFail();

        return response()->json([
            'category' => $category,
            'modules' => $category->trainings
        ]);
    }

    public function storeType(StoreTypeRequest $request)
    {
        // Authorization and Validation
        $validated = $request->validated();
        // Generate the Slug
        $validated['slug'] = Str::slug($validated['name']);

        // Handle File Upload
        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('business-training-icons', 'public');
            // Save the path to the database
            $validated['icon'] = $path;
        }

        // Create the Record
        BusinessTrainingType::create($validated);

        return back();
    }

    public function updateType(StoreTypeRequest $request, BusinessTrainingType $type)
    {
        // Authorization and Validation
        $validated = $request->validated();
        
        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('business-training-icons', 'public');
            $validated['icon'] = $path;
        }

        $type->update($validated);

        return back();
    }

    public function storeCategory(StoreCategoryRequest $request, BusinessTrainingType $type)
    {
        // Authorization and Validation
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $type) {

            // CREATE CATEGORY
            $category = BusinessTrainingCategory::create([
                'business_training_type_id' => $type->id,
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
            ]);

            // CREATE 6 MODULES
            foreach ($validated['modules'] as $index => $moduleData) {

                BusinessTraining::create([
                    'business_training_category_id' => $category->id,
                    'module' => $index + 1,
                    'content' => $this->buildModuleContent($index + 1, $moduleData),
                ]);
            }
        });

        return back();
    }

    public function updateCategory(StoreCategoryRequest $request, BusinessTrainingCategory $category)
    {
        // Authorization and Validation
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $category) {

            // UPDATE CATEGORY
            $category->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
            ]);

            // UPDATE MODULES
            foreach ($validated['modules'] as $index => $moduleData) {

                $moduleNumber = $index + 1;

                $training = BusinessTraining::where(
                    'business_training_category_id',
                    $category->id
                )->where('module', $moduleNumber)->first();

                if ($training) {
                    $training->update([
                        'content' => $this->buildModuleContent($moduleNumber, $moduleData),
                    ]);
                }
            }
        });

        return back();
    }

    public function destroyType(BusinessTrainingType $type)
    {
        abort_unless($this->canMutate(), 403, 'This action is unauthorized');

        $type->delete();

        return back();
    }

    public function destroyCategory(BusinessTrainingCategory $category)
    {
        abort_unless($this->canMutate(), 403 , 'This action is unauthorized');

        $category->delete();

        return back();
    }

    private function buildModuleContent(int $module, array $data): array
    {
        return match ($module) {

            // MODULE 1
            1 => [
                [
                    'title' => $data['intro_title'],
                    'description' => $data['intro_description'],
                ],
                [
                    'title' => 'Advantages & Challenges',
                    'advantages' => array_slice($data['advantages'] ?? [], 0, 10),
                    'challenges' => array_slice($data['challenges'] ?? [], 0, 10),
                ],
                [
                    'title' => 'Required mindset',
                    'description' => 'To succeed in a food cart business, you need',
                    'required_mindset' => array_slice($data['required_mindset'] ?? [], 0, 10),
                ],
            ],

            // MODULE 2,4,5,6 (same structure)
            2,4,5,6 => array_slice($data['items'] ?? [], 0, 5),

            // MODULE 3
            3 => [
                'title' => 'Sample Budget Breakdown (Philippines)',
                'budget' => array_slice($data['budget'] ?? [], 0, 10),
                'estimated_total' => [
                    'min_cost' => $data['min_cost'],
                    'max_cost' => $data['max_cost'],
                ],
            ],

            default => [],
        };
    }
}
