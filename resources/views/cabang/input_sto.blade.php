<?php session_start(); ?>
@extends('main')

@section('contents')
    <div class="container-fluid">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"> Input Data Satelite Office
                </h3>
            </div>
            @if (session()->has('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            <!-- /.card-header -->
            <!-- form start -->
            <form class="form-horizontal" action="/store" method="post">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Kode</label>
                        <div class="col-sm-6">
                            <input type="text" name="CODE_STOF"
                                class="form-control @error('CODE_STOF') is-invalid @enderror"
                                placeholder="Kode Satelite Office" value="{{ old('CODE_STOF') }}" required>
                            @error('CODE_STOF')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Satelite Office</label>
                        <div class="col-sm-6">
                            <input type="hidden" name="FC_BRANCH" value="{{ $FC_BRANCH }}" required>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Nama" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="submit" class="btn btn-info">Save</button>
                </div>
                <!-- /.card-footer -->
            </form>
        </div>
    </div>
@endsection
