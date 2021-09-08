<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Sightengine\SightengineClient;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('q')) {
            $query->where('username', 'like', '%' . $request->q . '%');
        }

        return view('users.index', [
            'users' => $query->paginate(User::PAGINATE_COUNT),
            'query' => $request->q
        ]);
    }

    public function show(User $user)
    {
        auth()->user()->attachFollowStatus($user);

        $user->loadCount(['posts', 'followers', 'followings']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user->profile);

        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user->profile);

        $data = request()->validate([
            'description' => 'required',
            'url' => 'url',
            'image' => '',
        ]);

        if (request('image')) {
            //            $imagePath = request('image')->store('profile', 'public');
            //
            //            $image = Image::make(public_path("/storage/{$imagePath}"))->fit(1080, 1080, function ($constraint) {
            //                $constraint->upsize();
            //            });

            $imagePath = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'laragram/avatar',
                'transformation' => [
                    'background' => 'white',
                    'width' => 400,
                    'height' => 400,
                    'crop' => 'pad'
                ]
            ])->getSecurePath();

            $client = new SightengineClient(config('services.sightengine.user'), config('services.sightengine.secret'));
            $output = $client->check(['nudity'])->set_url($imagePath);

            if ($output->nudity->safe > 0.5 && $output->nudity->raw < 0.1) {

                $imageArray = ['image' => $imagePath];
                auth()->user()->profile->update(array_merge(
                    $data,
                    $imageArray ?? []
                ));

                return redirect()->route('users.show', $user)->with([
                    'status' => 'success',
                    'message' => 'Profile uploaded successfully!'
                ]);
            }

            return redirect()->route('users.show', $user)->with([
                'status' => 'error',
                'message' => 'Your image contains inappropriate content!'
            ]);
        }

        auth()->user()->profile->update($data);

        return redirect()->route('users.show', $user)->with([
            'status' => 'success',
            'message' => 'Profile uploaded successfully!'
        ]);
    }

    public function followings($username)
    {
        $profiles = User::where('username', $username)
            ->first()
            ->following()
            ->paginate(Profile::PAGINATE_COUNT);

        return view('users.followings', compact('profiles'));
    }

    public function followers($username)
    {
        $users = User::where('username', $username)
            ->first()
            ->profile
            ->followers()
            ->with('profile')
            ->paginate(User::PAGINATE_COUNT);

        return view('users.followers', compact('users'));
    }

    public function notifications()
    {
        return view('users.notifications');
    }
}
