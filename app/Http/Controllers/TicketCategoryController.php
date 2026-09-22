<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    // Menampilkan halaman kelola tiket per event
    public function index(Event $event)
    {
        $categories = $event->ticketCategories;
        return view('admin.ticket_categories.index', compact('event', 'categories'));
    }

    // Menyimpan kategori tiket baru
    public function store(Request $request, Event $event)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:1',
        ]);

        $event->ticketCategories()->create([
            'name'  => $request->name,
            'price' => $request->price,
            'quota' => $request->quota,
        ]);

        return back()->with('success', 'Kategori tiket berhasil ditambahkan!');
    }

    // Menghapus kategori tiket
    public function destroy(TicketCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Kategori tiket berhasil dihapus!');
    }
}
