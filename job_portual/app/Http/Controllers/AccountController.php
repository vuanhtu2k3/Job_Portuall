<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDO;

class AccountController extends Controller
{
    public function registration()
    {
        return view('front.account.registration');
    }

    public function processRegistration(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:5',
            'confirm_password' => 'required|same:password'

        ]);
        if ($validator->passes()) {

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            Session::flash('success', 'You have registerd successfully');

            return response()->json([
                'status' => true,
                'message' => 'Registration successfully'

            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function login()
    {
        return view('front.account.login');
    }

    public function authenticate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:3'

        ]);
        if ($validator->passes()) {

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

                return redirect()->route('account.profile');
            } else {
                return redirect()->route('account.login')->withErrors('error', 'Either Email/Password is incorect');
            }
        } else {
            return redirect()->route('account.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));


            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function profile()
    {
        $id = Auth::user()->id;
        $user = User::where('id', $id)->first();

        return view('front.account.profile', [
            'user' => $user
        ]);
    }
    public function updateProfile(Request $request)
    {
        $id = Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->passes()) {

            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->designation = $request->designation;
            $user->save();

            Session::flash('success', 'Profile update successfully');
            return response()->json([
                'status' => true,
                'message' => 'Profile update successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function updateProfilePic(Request $request)
    {

        $id = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'image' => 'required|image'
        ]);

        if ($validator->passes()) {

            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = $id . '-' . time() . '.' . $ext;
            $image->move(public_path('/profile_pic/'), $imageName);

            User::where('id', $id)->update(['image' => $imageName]);
            Session::flash('success', 'Profile picture update successfully');
            return response()->json([
                'status' => true,
                'message' => 'Profile picture update successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function createJob()
    {
        $categories = Category::orderBy('name', 'ASC')->where('status', 1)->get();

        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', 1)->get();

        return view(
            'front.account.job.create',
            [
                'categories' => $categories,
                'jobTypes' => $jobTypes
            ]
        );
    }
    public function saveJob(Request $request)
    {
        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:10',
            'description' => 'required',
            'company_name' => 'required|max:10'


        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $job = new Job();
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->user_id = Auth::user()->id;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsibility = $request->responsibility;
            $job->qualifications = $request->qualifications;
            $job->keywords = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->website;

            $job->save();
            Session::flash('success', 'Job created successfully');
            return response()->json([
                'status' => true,
                'message' => 'Job created successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function myJobs()
    {
        $jobs = Job::where('user_id', Auth::user()->id)->with('jobType')->orderBy('created_at', 'DESC')->paginate(10);

        return view('front.account.job.my-jobs', [
            'jobs' => $jobs
        ]);
    }

    public function editJob(Request $request, $id)
    {
        $categories = Category::orderBy('name', 'ASC')->where('status', 1)->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', 1)->get();
        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $id,
        ])->first();
        if ($job == null) {
            abort(404);
        }
        return view('front.account.job.edit', [
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'job' => $job
        ]);
    }

    public function updateJob(Request $request, $id)
    {
        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:10',
            'description' => 'required',
            'company_name' => 'required|max:10'


        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $job = Job::find($id);
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->user_id = Auth::user()->id;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsibility = $request->responsibility;
            $job->qualifications = $request->qualifications;
            $job->keywords = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->website;

            $job->save();
            Session::flash('success', 'Job updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'Job updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function deleteJob(Request $request)
    {
        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $request->jobId,
        ])->first();

        if ($job == null) {
            return response()->json([
                'status' => false,
                'message' => 'Jobs is not found',
            ]);
        }

        Job::where('id', $request->jobId)->delete();
        Session::flash('success', 'Job deleted successfully');
        return response()->json([
            'status' => true,
            'message' => ' Job deleted successfully'

        ]);
    }
    public function myJobApplications()
    {
        $jobApplications = JobApplication::where('user_id', Auth::user()->id)
            ->with(['job', 'job.jobType', 'job.applications'])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('front.account.job.my-job-applications', [
            'jobApplications' => $jobApplications
        ]);
    }
    public function removeJobs(Request $request)
    {
        $jobApplications = JobApplication::where([
            'id' => $request->id,
            'user_id' => Auth::user()->id
        ])
            ->first();

        if ($jobApplications == null) {
            Session::flash('error', 'Job application is not found');
            return response()->json([
                'status' => false,
                'message' => 'Job application is not found'
            ]);
        }
        JobApplication::find($request->id)->delete();
        Session::flash('success', 'Job application removed successfully');
        return response()->json([
            'status' => true,
            'message' => 'Job application removed successfully'
        ]);
    }

    public function savedJobs()
    {


        $savedJobs = SavedJob::where([
            'user_id' => Auth::user()->id
        ])->with(['job', 'job.jobType', 'job.applications'])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('front.account.job.saved-jobs', [

            'savedJobs' => $savedJobs



        ]);
    }

    public function removeSavedJobs(Request $request)
    {
        $savedJob = SavedJob::where([
            'id' => $request->id,
            'user_id' => Auth::user()->id
        ])
            ->first();

        if ($savedJob == null) {
            Session::flash('error', 'Job  is not found');
            return response()->json([
                'status' => false,
                'message' => 'Job  is not found'
            ]);
        }
        SavedJob::find($request->id)->delete();
        Session::flash('success', 'Job  removed successfully');
        return response()->json([
            'status' => true,
            'message' => 'Job  removed successfully'
        ]);
    }

    public function updatePasword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:3',
            'confirm_password' => 'required|same:new_password'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }
        if (Hash::check($request->old_password, Auth::user()->password) == false) {
            Session::flash('error', 'Your password is incorrect');
            return response()->json([
                'status' => true,
                'message' => 'Your password is incorrect'
            ]);
        }
        $user = User::find(Auth::user()->id);
        $user->password = Hash::make($request->new_password);
        $user->save();

        Session::flash('success', 'Password updated successfully');

        return response()->json([
            'status' => true,
            'message' => 'Password updated successfully'
        ]);
    }
    public function forgotPassword()
    {
        return view('front.account.forgot-password');
    }

    public function processForgetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.forgotPassword')
                ->withInput()
                ->withErrors($validator);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now()
        ]);

        $user = User::where('email', $request->email)->first();

        $formData = [
            'token' => $token,
            'user' => $user,
            'mailSubject' => 'You have requested to reset password'
        ];

        Mail::to($request->email)->send(new ResetPasswordEmail($formData));

        return redirect()->route('account.forgotPassword')->with('success', 'Please check your email to reset your password');
    }

    public function resetPassword($token)
    {
        $tokenExist = DB::table('password_reset_tokens')->where('token', $token)->first();

        if ($tokenExist == null) {
            return redirect()->route('account.forgotPassword')
                ->with('error', 'Invalid request');
        }

        return view('front.account.forgot-password', [
            'token' => $token
        ]);
    }
    public function processResetPassword(Request $request)
    {

        $token = $request->token;
        $tokenObj = DB::table('password_reset_tokens')->where('token', $token)->first();

        if ($tokenObj == null) {
            return redirect()->route('account.forgotPassword')->with('error', 'Invalid request');
        }
        $user = User::where('email', $tokenObj->email)->first();
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password'
        ]);
        if ($validator->fails()) {
            return redirect()->route('account.resetPassword', $token)
                ->withErrors($validator);
        }
        User::where('id', $user->id)->update([
            'password' => Hash::make($request->new_password)

        ]);
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return redirect()->route('account.login')
            ->with('success', 'You have successfully updated your password');
    }
}
