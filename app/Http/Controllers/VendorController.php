<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(): View
    {
        $query = Vendor::with('category');

        if (auth()->user()->isVendor()) {
            $query->where('email', auth()->user()->email);
        }

        return view('vendors.index', [
            'vendors' => $query->latest()->paginate(15),
            'categories' => VendorCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Vendor::create($request->validate([
            'category_id' => 'required|exists:vendor_categories,id',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]));

        return back()->with('success', 'Vendor registered.');
    }
}
