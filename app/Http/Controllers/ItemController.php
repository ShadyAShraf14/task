<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::all();
        return view('items.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('images', 'public');
            }
        }

        $item = new Item();
        $item->name = $request->name;
        $item->type = $request->type;
        $item->images = json_encode($images);
        $item->save();

        return response()->json(['success' => 'Item created successfully.', 'item' => $item]);
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $images = $item->images ? json_decode($item->images, true) : [];

        if ($request->hasFile('images')) {
            foreach ($images as $image) {
                \Storage::disk('public')->delete($image);
            }
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('images', 'public');
            }
        }

        $item->name = $request->name;
        $item->type = $request->type;
        $item->images = json_encode($images);
        $item->save();

        return response()->json(['success' => 'Item updated successfully.', 'item' => $item]);
    }


    public function destroy(Item $item)
    {
        $item->delete();
        return response()->json(['success' => 'Item deleted successfully.']);
    }
}
