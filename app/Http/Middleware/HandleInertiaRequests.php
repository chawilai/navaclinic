<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $unreadBookingsCount = 0;
        $latestUnreadBookings = [];

        if ($user && $user->is_admin) {
            $unreadBookingsCount = \App\Models\Booking::where('admin_acknowledged', false)->count();
            if ($unreadBookingsCount > 0) {
                // Eager load only necessary data
                // Make sure to select or load relationships properly
                // Using limit(5) directly on Eloquent builder before get() works fine.
                $latestUnreadBookings = \App\Models\Booking::with(['user:id,name,phone_number', 'doctor:id,name'])
                    ->where('admin_acknowledged', false)
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get();
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? $user->load('doctor') : null,
            ],
            'unreadBookingsCount' => $unreadBookingsCount,
            'latestUnreadBookings' => $latestUnreadBookings, // Pass the collection directly, Inertia handles serialization
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
        ];
    }
}
