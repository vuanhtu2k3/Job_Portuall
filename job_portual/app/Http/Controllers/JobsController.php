<?php

namespace App\Http\Controllers;

use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Js;

class JobsController extends Controller
{

    // public function index(Request $request)
    // {
    //     $categories = Category::where('status', 1)->get();
    //     $jobTypes = JobType::where('status', 1)->get();

    //     $jobs = Job::where('status', 1);

    //     //Search using keyword
    //     if (!empty($request->keyword)) {
    //         $jobs = $jobs->where(function ($query) use ($request) {
    //             $query->orWhere('title', 'like', '%' . $request->keyword . '%');
    //             $query->orWhere('keywords', 'like', '%' . $request->keyword . '%');
    //         });
    //     }
    //     //Search using location
    //     if (!empty($request->location)) {
    //         $jobs = $jobs->where('location', $request->location);
    //     }
    //     //Search using category
    //     if (!empty($request->category)) {
    //         $jobs = $jobs->where('category_id', $request->location);
    //     }

    //     $jobTypeArray = [];
    //     //Search using Job Type
    //     if (!empty($request->jobType)) {
    //         $jobTypeArray = explode(',', $request->jobType);
    //         $jobs = $jobs->whereIn('job_type_id', $jobTypeArray);
    //     }
    //     // Search using experience
    //     if (!empty($request->experience)) {
    //         $jobs = $jobs->where('experience', $request->experience);
    //     }

    //     $jobs = $jobs->with(['jobType', 'category']);

    //     if ($request->sort == 0) {
    //         $jobs = $jobs->orderBy('created_at', 'ASC');
    //     } else {
    //         $jobs = $jobs->orderBy('created_at', 'DESC');
    //     }


    //     $jobs = $jobs->paginate(9);



    //     return view('front.jobs', [
    //         'categories' => $categories,
    //         'jobTypes' => $jobTypes,
    //         'jobs' => $jobs,
    //         'jobTypeArray' => $jobTypeArray
    //     ]);
    // }

    public function index(Request $request)
    {
        $categories = Category::where('status', 1)->get();
        $jobTypes = JobType::where('status', 1)->get();

        $jobs = Job::where('status', 1);

        // Search using keyword
        if (!empty($request->keyword)) {
            $jobs = $jobs->where(function ($query) use ($request) {
                $query->orWhere('title', 'like', '%' . $request->keyword . '%')
                    ->orWhere('keywords', 'like', '%' . $request->keyword . '%');
            });
        }

        // Search using location
        if (!empty($request->location)) {
            $jobs = $jobs->where('location', $request->location);
        }

        // Search using category
        if (!empty($request->category)) {
            $jobs = $jobs->where('category_id', $request->category);
        }

        // Search using job type
        $jobTypeArray = [];
        if (!empty($request->job_type)) {
            $jobTypeArray = explode(',', $request->job_type);
            $jobs = $jobs->whereIn('job_type_id', $jobTypeArray);
        }

        // Search using experience
        if (!empty($request->experience)) {
            $jobs = $jobs->where('experience', $request->experience);
        }

        // Sorting jobs
        if ($request->sort == 0) {
            $jobs = $jobs->orderBy('created_at', 'ASC');
        } else {
            $jobs = $jobs->orderBy('created_at', 'DESC');
        }

        $jobs = $jobs->with(['jobType', 'category'])->paginate(9);

        return view('front.jobs', [
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'jobs' => $jobs,
            'jobTypeArray' => $jobTypeArray
        ]);
    }

    // public function detail($id)
    // {

    //     if (!Auth::check()) {

    //         return redirect()->route('account.login')->with('error', 'You must be logged in to view this job detail.');
    //     }


    //     $job = Job::where([
    //         'id' => $id,
    //         'status' => 1
    //     ])->with(['jobType', 'category'])->first();


    //     if ($job == null) {
    //         abort(404);
    //     }


    //     $count = SavedJob::where([
    //         ['user_id', Auth::user()->id],
    //         ['job_id', $id]
    //     ])->count();


    //     return view('front.jobDetail', [
    //         'job' => $job,
    //         'count' => $count
    //     ]);
    // }
    public function detail($id)
    {

        $job = Job::where([
            'id' => $id,
            'status' => 1
        ])->with(['jobType', 'category'])->first();


        if ($job == null) {
            abort(404);
        }


        $count = 0;


        if (Auth::check()) {

            $count = SavedJob::where([
                ['user_id', Auth::user()->id],
                ['job_id', $id]
            ])->count();
        }


        return view('front.jobDetail', [
            'job' => $job,
            'count' => $count
        ]);
    }

    public function applyJob(Request $request)
    {

        $id = $request->id;

        $job = Job::where('id', $id)->first();
        //if Job is not found database
        if ($job == null) {
            Session::flash('error', 'Job does not exist');
            return response()->json([
                'status' => false,
                'message' => ' Job does not exist'
            ]);
        }
        // You can not apply on your own job
        $employee_id = $job->user_id;
        Session::flash('error', 'You can not apply on your own job');
        if ($employee_id == Auth::user()->id) {
            Session::flash('error', 'You can not apply on your own job');
            return response()->json([
                'status' => false,
                'message' => 'You can not apply on your own job'
            ]);
        }

        //You can not apply on a job twise
        $jobApplicationCount = JobApplication::where([
            'job_id' => $id,
            'user_id' => Auth::user()->id,
        ])->count();

        Session::flash('error', 'You have already applied on this job');

        if ($jobApplicationCount > 0) {
            Session::flash('error', 'You have already applied on this job');
            return response()->json([
                'status' => false,
                'message' => 'You have already applied on this job'
            ]);
        }

        $application = new JobApplication();
        $application->job_id = $id;
        $application->user_id = Auth::user()->id;
        $application->employee_id = Auth::user()->id;
        $application->applied_date = now();
        $application->save();


        Session::flash('success', 'Job applied successfully');
        return response()->json([
            'status' => true,
            'message' => 'Job applied successfully'
        ]);


        //Send Notification Email to Employer
        $employee = User::where('id', $employee_id)->first();
        $mailData = [
            'employee' => $employee,
            'user' => Auth::user(),
            'job' => $job,
        ];

        Mail::to($employee->email)->send(new JobNotificationEmail($mailData));
    }
    public function saveJobs(Request $request)
    {
        $id = $request->id;
        $job = Job::find($id);

        if ($job == null) {
            Session::flash('error', 'Job not found');
            return response()->json([
                'status' => false,
                'message' => 'Job not found'
            ]);
        }

        // Check if user already saved jobs

        $count = SavedJob::where([
            ['user_id', Auth::user()->id],
            ['job_id', $id]
        ])->count();

        if ($count > 0) {
            Session::flash('error', 'You have already saved this job');
            return response()->json([
                'status' => false,
                'message' => 'You have already saved this job'
            ]);
        }
        $saveJob = new SavedJob();
        $saveJob->job_id = $id;
        $saveJob->user_id = Auth::user()->id;
        $saveJob->save();
        Session::flash('success', 'Job saved successfully');
        return response()->json([
            'status' => true,
            'message' => 'Job saved successfully'
        ]);
    }
}
