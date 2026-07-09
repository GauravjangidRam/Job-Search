<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\JobListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    /**
     * Toggle a bookmark for the authenticated user on the given job listing.
     *
     * If a bookmark already exists for the (user_id, job_listing_id) pair it is
     * removed (un-bookmarked); otherwise a new bookmark is created. Redirects back
     * with a flash message indicating the resulting state.
     */
    public function toggle(string $hash): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $job = JobListing::findByHash($hash);
        abort_if(!$job, 404);

        $userId = Auth::id();

        $bookmark = Bookmark::query()
            ->where('user_id', $userId)
            ->where('job_listing_id', $job->id)
            ->first();

        if ($bookmark !== null) {
            $bookmark->delete();
            if (request()->expectsJson()) {
                return response()->json([
                    'bookmarked' => false,
                    'message' => 'Bookmark removed.'
                ]);
            }
            return back()->with('status', 'Bookmark removed.');
        }
        Bookmark::create([
            'user_id' => $userId,
            'job_listing_id' => $job->id,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'bookmarked' => true,
                'message' => 'Job bookmarked.'
            ]);
        }

        return back()->with('status', 'Job bookmarked.');
    }

    /**
     * Display the authenticated user's bookmarked job listings.
     */
    public function index(): View
    {
        $bookmarks = Auth::user()
            ->bookmarks()
            ->with('jobListing.company')
            ->orderByDesc('created_at')
            ->get();

        return view('bookmarks.index', ['bookmarks' => $bookmarks]);
    }
}
