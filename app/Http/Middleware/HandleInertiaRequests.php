<?php

namespace App\Http\Middleware;

use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user()?->only('id', 'name', 'email', 'avatar', 'family_id', 'role'),
                'family' => $request->user()?->family?->only('id', 'name', 'avatar'),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'info' => $request->session()->get('info'),
            ],
            'notifications' => fn () => $this->notificationsFor($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array{items: array<int, array{id: int, message: string, sent_at: string|null, sent_at_human: string}>, count: int}
     */
    private function notificationsFor(Request $request): array
    {
        $user = $request->user();

        if (! $user?->family_id) {
            return [
                'items' => [],
                'count' => 0,
            ];
        }

        $query = NotificationLog::query()
            ->where('family_id', $user->family_id)
            ->where('channel', 'system')
            ->where('status', 'sent');

        $items = (clone $query)
            ->latest('sent_at')
            ->latest()
            ->limit(8)
            ->get(['id', 'message', 'sent_at', 'created_at'])
            ->map(function (NotificationLog $notification) {
                $sentAt = $notification->sent_at ?? $notification->created_at;

                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'sent_at' => $sentAt?->toISOString(),
                    'sent_at_human' => $sentAt?->diffForHumans() ?? 'Baru saja',
                ];
            })
            ->values()
            ->all();

        return [
            'items' => $items,
            'count' => (clone $query)->count(),
        ];
    }
}
