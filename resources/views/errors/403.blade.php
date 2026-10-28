@extends('errors.minimal')

@section('code', '403')
@section('message', 'Không có quyền truy cập')
@section('hint', $exception->getMessage() ?: 'Trang này chỉ dành cho tài khoản được cấp quyền.')
