<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::query()->orderBy('sort')->get();

        $categoryGroups = $categories->groupBy(fn (Category $category) => $category->group->value);

        return view('home', [
            'categories' => $categories,
            'categoryGroups' => $categoryGroups,
            'categoryOptions' => $categoryGroups->mapWithKeys(fn ($group, string $key) => [
                __('categories.groups.'.$key) => $group->mapWithKeys(fn (Category $category) => [$category->slug => $category->label()])->all(),
            ])->all(),
        ]);
    }
}
