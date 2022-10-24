<?php session_start(); ?>
@extends('main')

@section('contents')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Input Hak Akses</h3>
            </div>
            @if (session()->has('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            <!-- /.card-header -->
            <!-- form start -->
            <form class="form-horizontal" action="/store-access" method="post">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Nama</label>
                        <div class="col-sm-6">
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Nama" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-6">
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                placeholder="Email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            @if (session()->has('danger'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('danger') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Cabang</label>
                        <div class="col-sm-6">
                            <select class="form-control" name="fc_branch">
                                @foreach ($data as $w)
                                    <option value="{{ $w->FC_BRANCH }}">{{ $w->FV_NAME }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Level</label>
                        <div class="col-sm-6">
                            <select class="form-control" name="level">
                                <option value="1">IT</option>
                                <option value="2">WH</option>
                                <option value="3">Logistic Controll</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">KODE FINGER</label>
                        <div class="col-sm-6">
                            <input type="text" name="finger_code"
                                class="form-control @error('finger_code') is-invalid @enderror" placeholder="kode finger"
                                required>
                            @error('finger_code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="submit" name="store_access" class="btn btn-info">Simpan</button>
                </div>
                <!-- /.card-footer -->
            </form>
        </div>
    </div>
@endsection
