<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile with their applications and
     * bookmarks, most recent first.
     */
    public function show(): View
    {
        $user = Auth::user();

        $applications = $user->applications()
            ->with('jobListing.company')
            ->orderByDesc('created_at')
            ->get();

        $bookmarks = $user->bookmarks()
            ->with('jobListing.company')
            ->orderByDesc('created_at')
            ->get();

        return view('profile.show', [
            'user' => $user,
            'applications' => $applications,
            'bookmarks' => $bookmarks,
        ]);
    }

    /**
     * Display the profile edit form for the authenticated user.
     */
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Validate and persist changes to the authenticated user's profile.
     *
     * The email address is intentionally never updated here because it is
     * immutable. A newly uploaded avatar is stored via the FileUploadService
     * and its returned path is saved to the user's avatar_path.
     */
   public function update(ProfileUpdateRequest $request, FileUploadService $fileUploadService): RedirectResponse{
        $user = Auth::user();

        $data = $request->safe()->only(['name', 'phone', 'bio']);

        if ($request->hasFile('avatar')) {
            // Cloudinary URL milega — avatar_url mein save karo
            $data['avatar_url'] = $fileUploadService->uploadAvatar(
                $request->file('avatar'), 
                $user->id
            );
        }

        $user->update($data);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}
