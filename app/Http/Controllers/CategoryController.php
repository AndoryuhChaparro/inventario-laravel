<?php


namespace App\Http\Controllers;


use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->orderBy('category_id', 'desc')
            ->get();


        return view('categories.index', compact('categories'));
    }


    public function create()
    {
        return view('categories.create');
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100|unique:categories,name',
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no puede superar los 100 caracteres.',
            'name.unique' => 'Ya existe una categoría registrada con ese nombre.',
            'description.max' => 'La descripción no puede superar los 255 caracteres.',
        ]);


        Category::create($validated);


        return redirect()->route('categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }


    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }


    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100|unique:categories,name,' . $category->category_id . ',category_id',
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no puede superar los 100 caracteres.',
            'name.unique' => 'Ya existe otra categoría con ese nombre.',
            'description.max' => 'La descripción no puede superar los 255 caracteres.',
        ]);


        $category->update($validated);


        return redirect()->route('categories.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }


    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'No se puede eliminar la categoría "' . $category->name . '" porque tiene productos asociados.');
        }


        try {
            $category->delete();


            return redirect()->route('categories.index')
                ->with('success', 'Categoría eliminada correctamente.');
        } catch (QueryException $e) {
            return redirect()->route('categories.index')
                ->with('error', 'No se pudo eliminar la categoría debido a restricciones de integridad referencial.');
        }
    }
}