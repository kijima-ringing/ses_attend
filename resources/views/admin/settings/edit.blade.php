@extends('layouts.app')

@section('addCss')
    <link rel="stylesheet" href="{{ asset('/css/admin/settings.css') }}">
@endsection

@section('content')
    <div class="container">
        @if(count($errors) > 0)
            <div class="alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            <div class="form-group row">
                <label for="working_time" class="col-sm-2 col-sm-form-label text-right">基準時間</label>
                <div class="offset-md-1 col-sm-2">
                    <input id="base_time_from" type="time" name="base_time_from" class="form-control mx-auto {{ $errors->has('base_time_from') || $errors->has('base_time') ? 'is-invalid' : '' }}" value="{{ ($errors->has('base_time_from') || $errors->has('base_time')) ? old('base_time_from') : $company->base_time_from }}">
                </div>
                <div class="col-sm-1 text-center my-auto">
                    〜
                </div>
                <div class="col-sm-2">
                    <input id="base_time_to" size="8" type="time" name="base_time_to" class="form-control mx-auto {{ $errors->has('base_time_from') || $errors->has('base_time') ? 'is-invalid' : '' }}" value="{{ ($errors->has('base_time_to') || $errors->has('base_time')) ? old('base_time_to') : $company->base_time_to }}">
                </div>
            </div>
            <div class="form-group row">
                <label for="working_time" class="col-sm-2 col-sm-form-label text-right">端数処理</label>
                <div class="offset-md-1 col-sm-2">
                    <select class="form-control mx-auto {{ $errors->has('time_fraction') ? 'is-invalid' : '' }}" name="time_fraction">
                        @foreach ($time_fraction_list AS $key => $value)
                            <option value="{{ $key }}" {{ $key == ($errors->has('time_fraction') ? old('time_fraction') : $company->time_fraction) ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="btn btn-primary col-sm-2 float-right">更新</button>
        </form>
    </div>
@endsection
@section('addJs')
@endsection
