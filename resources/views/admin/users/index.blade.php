@extends('layouts.app')

@section('addCss')
<link rel="stylesheet" href="{{ asset('/css/admin/users.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="col-sm-2 float-right text-right mb-1 pr-0">
            <button class="btn btn-primary js-add-button" data-toggle="modal" data-target="#InputForm">＋社員追加</button>
        </div>
        <table class="table table-bordered">
            <thead class="bg-info">
            <tr>
                <th width="30%">名前</th>
                <th>カナ</th>
                <th>メールアドレス</th>
                <th width="10%"></th>
            </tr>
            </thead>
            <tbody class="selectable">
                @foreach ($user_list AS $user)
                    <form method="POST" action="{{ route('admin.users.destroy') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <tr data-user_id="{{ $user->id }}">
                            <td>{{ $user->last_name }}　{{ $user->first_name }}</td>
                            <td>{{ $user->last_name_kana }}　{{ $user->first_name_kana }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center"><button class="btn btn-danger" value="{{ $user->id }}">削除</button></td>
                        </tr>
                    </form>
                @endforeach
            </tbody>
        </table>
    </div>
    <form method="POST" action="{{ route('admin.users.update') }}">
        @csrf
        <input name="id" type="hidden" id="HiddenId" value="{{ empty(old('id')) ? '' : old('id') }}">
        <input type="hidden" name="placeholder_email">
        <div class="modal fade" id="InputForm" tabindex="-1" role="dialog" aria-labelledby="label1" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                    <div class="modal-body">
                        @if(count($errors) > 0)
                            <div class="alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group row">
                            <label for="inputLastname" class="col-sm-2 col-form-label">姓</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control {{ $errors->has('last_name') ? 'is-invalid' : '' }}" id="last_name" name="last_name" value="{{ empty(old('last_name')) ? '' : old('last_name') }}">
                            </div>
                            <label for="inputFirstname" class="offset-sm-1 col-sm-2 col-form-label">名</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control {{ $errors->has('first_name') ? 'is-invalid' : '' }}" id="first_name" name="first_name" value="{{ empty(old('first_name')) ? '' : old('first_name') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputLastnameKana" class="col-sm-2 col-form-label">セイ</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control {{ $errors->has('last_name_kana') ? 'is-invalid' : '' }}" id="last_name_kana" name="last_name_kana" value="{{ empty(old('last_name_kana')) ? '' : old('last_name_kana') }}">
                            </div>
                            <label for="inputFirstnameKana" class="offset-sm-1 col-sm-2 col-form-label">メイ</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control {{ $errors->has('first_name_kana') ? 'is-invalid' : '' }}" id="first_name_kana" name="first_name_kana" value="{{ empty(old('first_name_kana')) ? '' : old('first_name_kana') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputMail" class="col-sm-2 col-form-label">メールアドレス</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" id="email" name="email" value="{{ empty(old('email')) ? '' : old('email') }}" placeholder="{{ old('placeholder_email') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPassword" class="col-sm-2 col-form-label">パスワード</label>
                            <div class="col-sm-5">
                                <input type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" id="inputPassword" name="password">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <label for="inputDepartment" class="col-form-label">所属部門</label>
                            </div>
                            <div class="col-sm-10">
                                <div class="form-group" data-department_select_json="{{ $department_select_list->toJson() }}">
                                    @foreach ($department_select_list AS $item)
                                        <div class="checkbox">
                                            <label><input type="checkbox" value="{{ $item->id }}" class="mr-1 js-department-checkbox" name="department_ids[]" {{ in_array($item->id, (array)old('department_ids')) ? 'checked' : '' }}>{{ $item->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <button type="submit" class="btn btn-primary float-right col-sm-1 js-save-submit">保存</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection
@section('addJs')
    <script src="{{ asset('js/admin/users.js') }}"></script>
@endsection
