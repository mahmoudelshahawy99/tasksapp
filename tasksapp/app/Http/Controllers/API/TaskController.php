<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Task;
use Validator;
use App\Http\Resources\Task as TaskResource;
use Illuminate\Support\Facades\Auth;

class TaskController extends BaseController
{

    /**

    * function to view all tasks added if the user is Supervisor
    * return the name, description, project_id and if the task is done or not for all tasks if founded

    */

    public function index()
    {
        $user = Auth::user();
        if($user['is_admin']){
            $tasks = Task::all();
            return $this->sendResponse(TaskResource::collection($tasks),'All tasks sent');
        }else{
            return $this->sendError('error' ,['error'=> 'You are not Supervisor'] );
        }
    }

    /**

    * function to save new task if the user is Supervisor
    * accept name and description of the task and the project_id for this task
    * return the name, description, project_id and if the task is done or not if added successfully

    */

    public function store(Request $request)
    {

        $user = Auth::user();
        if($user['is_admin']){
            $input = $request->all();
            $validator = Validator::make($input,[
                'name'=> 'required',
                'description'=> 'required',
                'project_id'=> 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Please validate error' ,$validator->errors());
            }
            $task = Task::create($input);
            return $this->sendResponse(new TaskResource($task) ,'Task created successfully');
        }else{
            return $this->sendError('error' ,['error'=> 'You are not Supervisor'] );
        }

    }

    /**

    * function to update data for specific task if the user is Supervisor and task not done yet
    * accept id of the task wanted to be updated
    * return the name, description, project_id and if the task is done or not if there is no error

    */

    public function update(Request $request, $id)
    {

        $user = Auth::user();
        $task = Task::find($id);
        if($user['is_admin'] && !$task['done']){
            $input = $request->all();
            $validator = Validator::make($input,[
            'name'=> 'required',
            'description'=> 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Please validate error' ,$validator->errors() );
            }

            $task->name = $input['name'];
            $task->description = $input['description'];
            $task->save();
            return $this->sendResponse(new TaskResource($task) ,'Task updated successfully' );
        }else{
            return $this->sendError('error' ,['error'=> 'You are not Supervisor'] );
        }

    }

    /**

    * function to assign task for an Employee if the user is Supervisor and the task not done yet
    * accept id of the task wanted to be assigned for the Employee
    * return the name, description, project_id,user_id assigned for this task and if the task is done or not if there is no error

    */

    public function assignTask(Request $request, $id)
    {

        $user = Auth::user();
        $task = Task::find($id);
        if($user['is_admin'] && !$task['done']){
            $input = $request->all();
            $validator = Validator::make($input,[
            'user_id'=> 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Please validate error' ,$validator->errors() );
            }

            $task->user_id = $input['user_id'];
            $task->save();
            return $this->sendResponse(new TaskResource($task) ,'Task updated successfully' );
        }else{
            return $this->sendError('error' ,['error'=> 'You are not Supervisor'] );
        }

    }

    /**

    * function to show tasks for this Employee if the user is Employee
    * return the name, description, project_id,user_id assigned for this task and if the task is done or not if there is no error

    */

    public function tasksEmployee()
    {
        $user = Auth::user();
        $tasks = Task::where('user_id' , $user['id'])->get();
        if(!empty($tasks[0])){
            return $this->sendResponse(TaskResource::collection($tasks),'Tasks retrieved Successfully!');
        }else{
            return $this->sendError('error' ,['error'=> 'You do not have tasks assigned'] );
        }
    }

    /**

    * function to submit task for an Employee if the user is Employee and the task assigned to him and not done yet
    * accept the id of task and the Employee updated the done field to be done and put the details of the task after submission
    * return the name, description, project_id,user_id assigned for this task and if the task is done or not if there is no error

    */

    public function submitTask(Request $request, $id)
    {

        $user = Auth::user();
        $task = Task::find($id);
        if($user['id'] != $task['user_id']){
            return $this->sendError('error' ,['error'=> 'you can not submit this task as it is not assignd to you']);
        }
        if(!$task['done']){
            $input = $request->all();
            $validator = Validator::make($input,[
            'done'=> 'required',
            'details'=> 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Please validate error' ,$validator->errors() );
            }

            $task->done = $input['done'];
            $task->details = $input['details'];
            $task->save();
            return $this->sendResponse(new TaskResource($task) ,'Task submitted successfully' );
        }else{
            return $this->sendError('error' ,['error'=> 'Task already submitted before']);
        }

    }
}
