<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class JobApplicationController extends Controller
{
    public function index()
    {
        $applications =  JobApplication::orderBy('created_at', 'DESC')
            ->with('job', 'user', 'employee')
            ->paginate(10);

        return view('admin.job_applications.list', [
            'applications' => $applications
        ]);
    }

    public  function destroy(Request $request)
    {

        $id = $request->id;
        $application = JobApplication::find($id);
        if ($application == null) {
            Session::flash('error', 'Application not found');
            return response()->json([
                'status' => 'error',
                'message' => 'Application not found'
            ]);
        }
        $application->delete();
        Session::flash('success', 'Application deleted successfully');
        return response()->json([
            'status' => 'success',
            'message' => 'Application deleted successfully'
        ]);
    }
}
