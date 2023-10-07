<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    public static function update_progress($project_id)
    {
        $project = Project::find($project_id);
        $sections = ProjectSection::where('project_id', $project_id)->get();
        $progress = 0;
        $section_progress = 0;
        foreach ($sections as $section) {
            $section_progress += (int)$section->progress;
        }
        if (count($sections) > 0) {
            $progress = $section_progress / count($sections);
        }
        $project->progress = $progress;
        $project->save();
    }

    public function project_sections()
    {
        return $this->hasMany(ProjectSection::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getOtherClientsAttribute($value)
    {
        return explode(',', $value);
    }

    public function setOtherClientsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['other_clients'] = implode(',', $value);
        }
    }
}
