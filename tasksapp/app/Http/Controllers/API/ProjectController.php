<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Project;
use Validator;
use App\Http\Resources\Project as ProjectResource;
use Illuminate\Support\Facades\Auth;

class ProjectController extends BaseController
{

    /**

    * function to view all projects added if the user is Supervisor
    * return the name and description for all projects if founded

    */

    public function index()
    {
        $user = Auth::user();
        if($user['is_admin']){
            $projects = Project::all();
            return $this->sendResponse(ProjectResource::collection($projects),'All projects sent');
        }else{
            return $this->sendError('error' ,['error'=> 'You are not Supervisor'] );
        }
    }

    /**

    * function to save new project if the user is Supervisor
    * accept name and description of the project
    * return the name and description for this project if added successfully

    */

    public function store(Request $request)
    {

        $user = Auth::user();
        if($user['is_admin']){
            $input = $request->all();
            $validator = Validator::make($input,[
                'name'=> 'required',
                'description'=> 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Please validate error' ,$validator->errors());
            }
            $project = Project::create($input);
            return $this->sendResponse(new ProjectResource($project) ,'Project created successfully');
        }else{
            return $this->sendError('error' ,['error'=> 'You are not Supervisor'] );
        }

    }

    /**

    * function to show data for specific project if the user is Supervisor
    * accept id of the project wanted to know its details
    * return the name and description for this project if it's exist

    */


    public function show($id)
    {

        $user = Auth::user();
        if($user['is_admin']){
            $project = Project::find($id);
            if (is_null($project) ) {
                return $this->sendError('Project not found'  );
            }
            return $this->sendResponse(new ProjectResource($project) ,'Project found successfully' );
        }else{
            return $this->sendError('error' ,['error'=> 'You are not Supervisor'] );
        }

    }

    /**

    * function to update data for specific project if the user is Supervisor
    * accept id of the project wanted to be updated
    * return the name and description for this project after updating if there is no error

    */

    public function update(Request $request, Project $project)
    {

        $user = Auth::user();
        if($user['is_admin']){
            $input = $request->all();
            $validator = Validator::make($input,[
            'name'=> 'required',
            'description'=> 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Please validate error' ,$validator->errors() );
            }

            $project->name = $input['name'];
            $project->description = $input['description'];
            $project->save();
            return $this->sendResponse(new ProjectResource($project) ,'Project updated successfully' );
        }else{
            return $this->sendError('error' ,['error'=> 'You are not Supervisor'] );
        }

    }

    /**

    * function to delete specific project if the user is Supervisor
    * accept id of the project wanted to be deleted
    * return the name and description for deleted project with successfull message

    */


    public function destroy(Project $project)
    {
        $user = Auth::user();
        if($user['is_admin']){
            $project->delete();
            return $this->sendResponse(new ProjectResource($project) ,'Project deleted successfully' );
        }else{
            return $this->sendError('error' ,['error'=> 'You are not Supervisor'] );
        }

    }
}
