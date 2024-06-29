<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::orderBy('created_at', 'DESC')->with('user', 'applications')->paginate(10);
        return view('admin.job.list', [
            'jobs' => $jobs
        ]);
    }

    public function edit($id)
    {
        $job = Job::find($id);
        $categories = Category::orderBy('name', 'ASC')->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->get();

        return view('admin.job.edit', [
            'job' => $job,
            'categories' => $categories,
            'jobTypes' => $jobTypes
        ]);
    }
    public function update(Request $request, $id)
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

            $job->status = $request->status;
            $job->isFeatured = (!empty($request->isFeatured)) ? $request->isFeatured : 0;


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
    public function destroy(Request $request)
    {
        $id = $request->id;

        $job = Job::find($id);
        if ($job == null) {
            return response()->json([
                'status' => false,
                'message' => 'Jobs is not found',
            ]);
        }

        $job->delete();
        Session::flash('success', 'Job deleted successfully');
        return response()->json([
            'status' => true,
            'message' => ' Job deleted successfully'

        ]);
    }
}
