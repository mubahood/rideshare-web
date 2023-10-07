<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;


    static public function boot()
    {
        parent::boot();

        static::created(function ($model) {
            Project::update_progress($model->project_id);
        });
        static::updated(function ($model) {
            Project::update_progress($model->project_id);
        });
        static::deleted(function ($model) {
            Project::update_progress($model->project_id);
        });

        static::creating(function ($model) {
            $model->manager_submission_status = 'Not Submitted'; 
            $model->delegate_submission_status = 'Not Submitted'; 
            return Task::prepare_saving($model);
        });
        static::updating(function ($model) {
            return Task::prepare_saving($model);
        });
    }

    public static function prepare_saving($model)
    {
        $project_section = ProjectSection::find($model->project_section_id);
        if ($project_section == null) {
            throw new \Exception("Project section not found");
        }
        $assigned_to_user = Administrator::find($model->assigned_to);
        if ($assigned_to_user == null) {
            throw new \Exception("Assigned to user not found");
        }
        if ($assigned_to_user->manager_id  == null) {
            $model->manager_id = $assigned_to_user->id;
        } else {
            $model->manager_id = $assigned_to_user->manager_id;
        }
        $model->project_id = $project_section->project_id;

    }


    public function assigned_to_user()
    {
        return $this->belongsTo(Administrator::class, 'assigned_to');
    }
    public function created_by_user()
    {
        return $this->belongsTo(Administrator::class, 'created_by');
    }
    public function manager_user()
    {
        return $this->belongsTo(Administrator::class, 'manager_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function project_section()
    {
        return $this->belongsTo(ProjectSection::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
