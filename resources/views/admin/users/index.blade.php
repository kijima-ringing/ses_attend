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
    <form method="POST" action="{{ route('admin.users.update') }}" id="modal-form">
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
                        <div class="alert-danger d-none col-12">
                            <ul>

                            </ul>
                        </div>
                        <div class="form-group row">
                            <label for="inputLastname" class="col-sm-2 col-form-label">姓</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="last_name" name="last_name" value="">
                            </div>
                            <label for="inputFirstname" class="offset-sm-1 col-sm-2 col-form-label">名</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="first_name" name="first_name" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputLastnameKana" class="col-sm-2 col-form-label">セイ</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="last_name_kana" name="last_name_kana" value="">
                            </div>
                            <label for="inputFirstnameKana" class="offset-sm-1 col-sm-2 col-form-label">メイ</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="first_name_kana" name="first_name_kana" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputMail" class="col-sm-2 col-form-label">メールアドレス</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" id="email" name="email" value="" placeholder="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPassword" class="col-sm-2 col-form-label">パスワード</label>
                            <div class="col-sm-5">
                                <input type="password" class="form-control" id="inputPassword" name="password">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <label for="inputDepartment" class="col-form-label">所属部門</label>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control js-select">
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <button class="btn btn-primary js-department-add-btn" type="button">+</button>
                            </div>
                        </div>
                        <div class="form-group row js-added-list">
                        </div>
                        <div class="form-group row">
                            <label for="admin_flag" class="col-sm-2 col-form-label">管理者権限</label>
                            <div class="col-sm-10">
                                <input type="checkbox" id="admin_flag" name="admin_flag" value="1"
                                {{ old('admin_flag', !empty($user) ? $user->admin_flag : 0) == 1 ? 'checked' : '' }}>
                                <label for="admin_flag" class="col-sm-2 col-form-label">管理者として登録</label>
                            </div>
                        </div>
                        <div class="form-group row d-none">

                            <div class="col-sm-10">
                                <div class="form-group" data-department_select_json="{{ $department_select_list->toJson() }}">
                                    @foreach ($department_select_list AS $item)
                                        <div class="checkbox">
                                            <label><input type="checkbox" value="{{ $item->id }}" class="mr-1 js-department-checkbox js-checkbox-{{ $item->id }}" name="department_ids[]" {{ in_array($item->id, (array)old('department_ids')) ? 'checked' : '' }} data-label="{{ $item->name }}">{{ $item->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <button type="submit" class="btn btn-primary float-right col-sm-1 js-save-submit" id="user-submit">保存</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection
@section('addJs')
    <script src="{{ asset('/js/admin/users.js') }}"></script>
@endsection