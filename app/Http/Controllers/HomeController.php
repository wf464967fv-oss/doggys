<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        // Only specialities that already have listings: a tile must never lead to an empty page.
        $categories = Category::query()->whereHas('providers', fn ($query) => $query->active())->orderBy('sort')->get();

        $categoryGroups = $categories->groupBy(fn (Category $category) => $category->group->value);

        return view('home', [
            'categories' => $categories,
            'categoryGroups' => $categoryGroups,
            'categoryOptions' => Category::groupedOptions($categories),
        ]);
    }
}
