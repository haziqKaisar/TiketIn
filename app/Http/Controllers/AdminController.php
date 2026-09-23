<?php

namespace App\Http\Controllers;

use App\Jobs\SendETicketJob;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * ID organisasi milik user yang login, atau NULL kalau dia super_admin
     * (artinya: jangan difilter sama sekali, tampilkan semua organisasi).
     */
    private function currentOrganizationId(): ?int
    {
        $user = Auth::user();

        return $user->isSuperAdmin() ? null : $user->organization_id;
    }

    /**
     * Pastikan $event ini benar-benar milik organisasi user yang login.
     * Super admin selalu lolos. Ini yang mencegah org_admin satu
     * organisasi mengedit/menghapus event organisasi lain lewat ubah ID.
     */
    private function authorizeOrganizationOwnership(Event $event): void
    {
        $orgId = $this->currentOrganizationId();

        if ($orgId !== null && $event->organization_id !== $orgId) {
            abort(403, 'Kamu tidak punya akses ke event ini.');
        }
    }

    // =========================================================
    // 1. DASHBOARD STATISTIK
    // =========================================================
    public function dashboard()
    {
        $orgId = $this->currentOrganizationId();

        $scopeByOrg = fn ($query, string $relation) => $orgId
            ? $query->whereHas($relation, fn ($q) => $q->where('organization_id', $orgId))
            : $query;

        $revenue = $scopeByOrg(
            Order::where('status', 'PAID'),
            'tickets.ticketCategory.event'
        )->sum('total_amount');

        $ticketsSold = $scopeByOrg(
            Ticket::whereIn('status', ['AVAILABLE', 'SCANNED']),
            'ticketCategory.event'
        )->count();

        $checkedIn = $scopeByOrg(
            Ticket::where('status', 'SCANNED'),
            'ticketCategory.event'
        )->count();

        return view('admin.dashboard', compact('revenue', 'ticketsSold', 'checkedIn'));
    }

    // =========================================================
    // 2. TAMPILKAN DAFTAR EVENT (READ)
    // =========================================================
    public function eventIndex()
    {
        $orgId = $this->currentOrganizationId();

        $events = Event::when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->orderBy('event_date', 'desc')
            ->get();

        return view('admin.events.index', compact('events'));
    }

    // =========================================================
    // 3. FORM TAMBAH EVENT (CREATE)
    // =========================================================
    public function eventCreate()
    {
        return view('admin.events.form');
    }

    // =========================================================
    // 4. SIMPAN EVENT BARU (STORE)
    // =========================================================
    public function eventStore(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'location'    => 'required|string|max:255',
            'event_date'  => 'required|date',
            'poster'      => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        // Event baru otomatis milik organisasi user yang login. Kalau yang
        // bikin super_admin (tidak terikat organisasi manapun), masukkan
        // ke organisasi pertama yang ada sebagai fallback.
        $data['organization_id'] = Auth::user()->organization_id
            ?? Organization::query()->value('id');

        Event::create($data);

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil ditambahkan!');
    }

    // =========================================================
    // 5. FORM EDIT EVENT (EDIT)
    // =========================================================
    public function eventEdit(Event $event)
    {
        $this->authorizeOrganizationOwnership($event);

        return view('admin.events.form', compact('event'));
    }

    // =========================================================
    // 6. SIMPAN PERUBAHAN EVENT (UPDATE)
    // =========================================================
    public function eventUpdate(Request $request, Event $event)
    {
        $this->authorizeOrganizationOwnership($event);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'location'    => 'required|string|max:255',
            'event_date'  => 'required|date',
            'poster'      => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'is_active'   => 'required|boolean',
        ]);

        if ($request->hasFile('poster')) {
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        $event->update($data);

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil diperbarui!');
    }

    // =========================================================
    // 7. HAPUS EVENT (DELETE)
    // =========================================================
    public function eventDestroy(Event $event)
    {
        $this->authorizeOrganizationOwnership($event);

        if ($event->poster) {
            Storage::disk('public')->delete($event->poster);
        }

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus!');
    }

    // =========================================================
    // 8. TAMPILKAN SEMUA PESANAN
    // =========================================================
    public function orderIndex()
    {
        $orgId = $this->currentOrganizationId();

        $orders = Order::with(['tickets.ticketCategory.event'])
            ->when($orgId, fn ($q) => $q->whereHas(
                'tickets.ticketCategory.event',
                fn ($q2) => $q2->where('organization_id', $orgId)
            ))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.orders.index', compact('orders'));
    }

    // =========================================================
    // 9. KONFIRMASI BAYAR MANUAL
    // =========================================================
    public function orderMarkPaid(Order $order)
    {
        $orgId = $this->currentOrganizationId();

        if ($orgId !== null) {
            $eventOrgId = optional(
                optional($order->tickets->first())->ticketCategory?->event
            )->organization_id;

            if ($eventOrgId !== $orgId) {
                abort(403, 'Kamu tidak punya akses ke pesanan ini.');
            }
        }

        if ($order->status === 'UNPAID') {
            $order->update(['status' => 'PAID']);
            Ticket::where('order_id', $order->id)->update(['status' => 'AVAILABLE']);
            SendETicketJob::dispatch($order->id);

            return back()->with('success', 'Status pesanan berhasil diubah menjadi PAID! E-Ticket sedang dikirim.');
        }

        return back()->with('error', 'Pesanan sudah diproses sebelumnya.');
    }
}
