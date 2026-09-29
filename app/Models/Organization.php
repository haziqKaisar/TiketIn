<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'description',
        'logo',
        'status',
        'rejected_reason',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    /**
     * Sisa saldo yang bisa ditarik organisasi ini:
     * total pendapatan dari order PAID di event-event miliknya,
     * dikurangi yang sudah ditransfer (completed) DAN yang masih
     * dalam pengajuan (pending) -- supaya tidak bisa dobel klaim.
     */
    public function availableBalance(): int
    {
        $totalRevenue = \App\Models\Order::where('status', 'PAID')
            ->whereHas('tickets.ticketCategory.event', fn ($q) => $q->where('organization_id', $this->id))
            ->sum('total_amount');

        $totalPaidOut = $this->withdrawalRequests()->where('status', 'completed')->sum('amount');
        $totalPending = $this->withdrawalRequests()->where('status', 'pending')->sum('amount');

        return (int) ($totalRevenue - $totalPaidOut - $totalPending);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
