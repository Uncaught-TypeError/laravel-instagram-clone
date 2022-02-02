<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Artesaos\SEOTools\Facades\SEOTools;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('media');

        if ($request->has('q')) {
            $query->where('username', 'like', '%' . $request->q . '%')
                ->orWhere('name', 'like', '%' . $request->q . '%');
        }

        return view('users.index', [
            'users' => $query->paginate(User::PAGINATE_COUNT),
            'query' => $request->q
        ]);
    }

    public function show(User $user)
    {
        SEOTools::setTitle($user->username);
        SEOTools::setDescription($user->name);
        SEOTools::setCanonical(route('users.show', $user));
        SEOTools::opengraph()->setUrl(route('users.show', $user));
        SEOTools::opengraph()->addProperty('type', 'website');
        SEOTools::opengraph()->addImage($user->avatar);
        SEOTools::twitter()->setSite($user->name);
        SEOTools::twitter()->setType('summary_large_image');
        SEOTools::jsonLd()->addImage($user->avatar);

        auth()->user()->attachFollowStatus($user);

        $user->loadCount(['posts', 'followers', 'followings']);

        $user->load('posts.media');

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    public function update(User $user, UpdateUserRequest $request)
    {
        $this->authorize('update', $user);

        if (!empty($request->password)) {
            $request->merge(['password' => bcrypt($request->password)]);
        }

        if (!is_null($user->username_last_updated_at)) {
            if ($user->username_update_attempts < 2) {
                if (Carbon::parse($user->username_last_updated_at)->addDays(14) <= now()) {
                    $user->update($request->validated() + [
                            $request->password
                        ]);
                    if ($user->wasChanged('username')) {
                        $user->increment('username_update_attempts');
                        $user->username_last_updated_at = now();
                        $user->save();
                    }
                }

                $user->update($request->validated() + [
                        $request->password
                    ]);
                if ($user->wasChanged('username')) {
                    $user->increment('username_update_attempts');
                    $user->username_last_updated_at = now();
                    $user->save();
                }
            } else {
                return back()->with([
                    'status' => 'error',
                    'message' => __('Username update attempts exceeded!'),
                ]);
            }
        } else {
            $user->update($request->validated() + [
                    $request->password
                ]);
            if ($user->wasChanged('username')) {
                $user->increment('username_update_attempts');
                $user->username_last_updated_at = now();
                $user->save();
            }
        }

        if (!empty($request->avatar)) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        }

        return redirect()->route('users.show', $user)->with([
            'status' => 'success',
            'message' => 'Profile updated successfully!'
        ]);
    }

    public function followings(User $user)
    {
        $users = $user->followings()->paginate(User::PAGINATE_COUNT);

        return view('users.followings', compact('users'));
    }

    public function followers(User $user)
    {
        $users = $user->followers()->paginate(User::PAGINATE_COUNT);

        return view('users.followers', compact('users'));
    }
}
