<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Lista categorie (pannello staff)
     */
    public function index()
    {
        // paginazione semplice per gestione backoffice
        $categories = Category::orderBy('name')->paginate(20);

        return view('staff.categories.index', compact('categories'));
    }

    /**
     * Form creazione categoria
     */
    public function create()
    {
        return view('staff.categories.create');
    }

    /**
     * Salvataggio nuova categoria
     */
    public function store(Request $request)
    {
        // validazione base (slug generato automaticamente)
        $data = $request->validate([
            'name'        => ['required','string','max:100','unique:categories,name'],
            //'slug'        => ['nullable','string','max:100','unique:categories,slug'],
            'description' => ['nullable','string'],
            'is_active'   => ['nullable','boolean'],
            'image'       => ['nullable','image','max:2048'],
        ]);

        // slug derivato dal nome se non fornito
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // default: categoria attiva
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $cat = Category::create($data);

        // hook futuro: capacità cucina per categoria (default)
        // Setting::updateOrCreate(['key'=>"kitchen.capacity.default.{$cat->slug}"], ['value'=>'30']);

        return redirect()
            ->route('staff.categories.index')
            ->with('success','Categoria creata.');
    }

    /**
     * Form modifica categoria
     */
    public function edit(Category $category)
    {
        return view('staff.categories.edit', compact('category'));
    }

    /**
     * Aggiornamento categoria esistente
     */
    public function update(Request $request, Category $category)
    {
        // unique:name escluso l'id corrente
        $data = $request->validate([
            'name'        => ['required','string','max:100','unique:categories,name,'.$category->id],
            'description' => ['nullable','string'],
            'is_active'   => ['nullable','boolean'],
            'image'       => ['nullable','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        // se checkbox non arriva -> false
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        // rimozione manuale immagine esistente
        if ($request->boolean('remove_image') && $category->image_path) {
            Storage::disk('public')->delete($category->image_path);
            $data['image_path'] = null;
        }

        // upload nuova immagine (sostituzione)
        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()
            ->route('staff.categories.index')
            ->with('success','Categoria aggiornata.');
    }

    /**
     * Eliminazione categoria
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success','Categoria eliminata.');
    }
}
