<?php

namespace App\Http\Controllers;

use App\Enums\DogSex;
use App\Http\Requests\DogRequest;
use App\Models\Dog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Private dog profiles of the signed-in user. Ownership is enforced by DogPolicy (route ->can()).
 */
class DogController extends Controller
{
    public function create(): View
    {
        return view('dogs.form', ['dog' => new Dog, 'sexes' => DogSex::cases()]);
    }

    public function store(DogRequest $request): RedirectResponse
    {
        $dog = $request->user()->dogs()->create($request->validated());

        return redirect()->to(localized_route('account'))->with('status', __('dogs.created', ['name' => $dog->name]));
    }

    public function edit(Dog $dog): View
    {
        return view('dogs.form', ['dog' => $dog, 'sexes' => DogSex::cases()]);
    }

    public function update(DogRequest $request, Dog $dog): RedirectResponse
    {
        $dog->update($request->validated());

        return redirect()->to(localized_route('account'))->with('status', __('dogs.updated', ['name' => $dog->name]));
    }

    public function destroy(Dog $dog): RedirectResponse
    {
        $dog->delete();

        return redirect()->to(localized_route('account'))->with('status', __('dogs.deleted', ['name' => $dog->name]));
    }
}
