<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Event;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {

            $user = Auth::user();
            $token = $user->createToken('Token Name')->accessToken;

            $data['access_token'] = $token;
            $data['user'] =$user->only('id', 'name', 'email', 'username');
            $data['admin'] = $user->is_admin;
            $data['success'] = 'Y';
            return $data;
        }else{
            $data['success'] = 'N';

            $check_email_exist = User::where('email', $request->email)->count();

            if($check_email_exist){
                $data['msg'] = 'Invalid credentials';
                $data['email_exist'] = 'Y';
                $data['invalid'] = 'Y';
            }else{
                $data['msg'] = 'Email does not exist';
                $data['email_exist'] = 'N';
            }

            return $data;
        }
    }

    public function register(Request $request){
        $email = $request->email;

        $check_email_exist = User::where('email', $request->email)->count();
        $data = [];
        if($check_email_exist){
            $data['msg'] = 'Email already exists';
            $data['success'] = 'N';
            return $data;
        }else{
            $user = new User();
            $user->name = $request->name;
            $user->email = $email;
            $user->password = Hash::make($request->password);
            $user->save();

            $data['success'] = 'Y';
            $token = $user->createToken('Token Name')->accessToken;

            $data['admin'] = $user->is_admin;
            $data['access_token'] = $token;
            $data['user'] =$user->only('id', 'name', 'email', 'username');
            return $data;
        }
    }

    public function get_categories(Request $request){
        $user = Auth::user();
        $categories = Category::withCount('events')->get()->map(function ($category){
            $category->selected = false;
            return $category;
        });
        return compact('categories');
    }

    public function add_category(Request $request){
        $user = Auth::user();
        $category = new Category();
        $category->category_name = $request->name;
        $category->save();

        $category->events_count = 0;
        return compact('category');
    }

    public function update_category(Request $request, $id){
        $category = Category::find($id);

        $category->category_name = $request->name;
        $category->save();
        return ['success'=>'Y'];
    }

    public function delete_category(Request $request, $id){
        $category = Category::find($id);
        $category->event_categories()->delete();

        $category->delete();
        return ['success'=>'Y'];
    }

    public function get_locations(Request $request){
        $user = Auth::user();
        $locations = Location::withCount('events')->get();
        return compact('locations');
    }

    public function add_location(Request $request){
        $user = Auth::user();
        $location = new Location();
        $location->name = $request->name;
        $location->created_by = $user->id;
        $location->save();

        $location->events_count = 0;
        return compact('location');
    }

    public function update_location(Request $request, $id){
        $location = Location::find($id);

        $location->name = $request->name;
        $location->save();
        return ['success'=>'Y'];
    }

    public function delete_location(Request $request, $id){
        $location = Location::find($id);
        if($location->events->count() >0){
            return ['success'=>'N', 'msg'=>'Some events are using this location'];
        }
        $location->delete();
        return ['success'=>'Y'];
    }

    public function get_events(Request $request){
        $date = $request->date ?: null;
        $name = $request->name ?: null;
        $cat_id = $request->category_id ?: null;
        $location_id = $request->location_id ?: null;

        $events = Event::when($date, function ($query) use($date){
            $query->where('date', $date);
        })->when($name, function ($query)use($name){
            $query->where('title', 'Like', '%'.$name.'%');
        })->when($location_id, function ($query) use($location_id){
            $query->where('location_id', $location_id);
        })->when($cat_id, function ($query) use($cat_id){
            $query->whereHas('categories', function ($query1) use($cat_id){
                $query1->where('category_id', $cat_id);
            });
        })->with('location:id,name')->with('categories:id,category_name')
            ->get();


        $locations = Location::all();
        $categories = Category::all();
        return compact('events', 'locations', 'categories');
    }

    public function add_event(Request $request){

        $check_exists = Event::where('title', $request->title)->first();
        if($check_exists){
            return ['success'=>'N', 'msg'=>'Event already exists with the title.'];
        }

        $event = new Event();
        $event->title = $request->title;
        $event->description = $request->description;
        $event->date = $request->date;
        $event->location_id = $request->location_id;
        $event->save();

        if($request->categories){
            foreach ($request->categories as $category){
                if($category['selected']){
                    $event->event_categories()->create(['category_id'=>$category['id']]);
                }
            }
        }
        return ['success'=>'Y'];
    }

    public function edit_event(Request $request, $id){
        $event = Event::find($id);
        $event->title = $request->title;
        $event->description = $request->description;
        $event->date = $request->date;
        $event->location_id = $request->location_id;
        $event->save();
        $event->event_categories()->delete();
        if($request->categories){
            foreach ($request->categories as $category){
                if($category['selected']) {
                    $event->event_categories()->create(['category_id' => $category['id']]);
                }
            }
        }
        return ['success'=>'Y'];
    }

    public function delete_event(Request $request, $id){
        $event = Event::find($id);
        $event->event_categories()->delete();
        $event->delete();
        return ['success'=>'Y'];
    }

    public function add_comment(Request $request, $id){
        $user = Auth::user();
        $comment = new Comment();
        $comment->event_id = $id;
        $comment->user_id = $user->id;
        $comment->text = $request->text;
        $comment->save();
        $comment->load('user');
        return compact('comment');
    }

    public function get_event(Request $request, $id){
        $event = Event::find($id);
        $event->load('categories')->load('location');
        $event->load('comments.user');

        return $event;
    }

    public function get_event_for_update(Request $request, $id){
        $event = Event::find($id);
        $event_categories = $event->event_categories()->pluck('category_id');

        $locations = Location::all(['id', 'name']);
        $categories = Category::all(['id', 'category_name'])->map(function ($cat) use($event_categories){
            $cat->selected = $event_categories->contains($cat->id);
            return $cat;
        });

        return compact('event', 'locations', 'categories');
    }
}
