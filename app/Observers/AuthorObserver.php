<?php
namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class AuthorObserver
{
    public function creating(Model $model){
        $model->created_by = \Auth::user()->id;
    }
    public function updating(Model $model){
        $model->updated_by = \Auth::user()->id;
    }
    public function saving(Model $model){
        $model->updated_by = \Auth::user()->id;
    }
    public function deleting(Model $model){
        $model->deleted_by = \Auth::user()->id;
        $model->save();
    }
    public function restoring(Model $model){
        $model->restored_by = \Auth::user()->id;
    }
}