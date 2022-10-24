@extends('main')
@section('contents')
    <div class="container-fluid">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Input Data Barang
                </h3>
            </div>
            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            <!-- /.card-header -->
            <!-- form start -->
            <form class="form-horizontal" action="/store-barang" method="post">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">STOCKCODE</label>
                        <div class="col-sm-6">
                            <input type="text" name="FC_STOCKCODE"
                                class="form-control @error('FC_STOCKCODE') is-invalid @enderror" placeholder="STOCKCODE"
                                value="{{ old('FC_STOCKCODE') }}" required>
                            @error('FC_STOCKCODE')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">STOCKNAME</label>
                        <div class="col-sm-6">
                            <input type="text" name="FV_STOCKNAME"
                                class="form-control @error('FV_STOCKNAME') is-invalid @enderror" placeholder="STOCKNAME"
                                value="{{ old('FV_STOCKNAME') }}" required>
                            @error('FV_STOCKNAME')
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
                        <label class="col-sm-2 col-form-label">BRAND</label>
                        <div class="col-sm-6">
                            <select class="form-control" name="FV_BRANDNAME">
                                @foreach ($data as $w)
                                    <option value="{{ $w->FV_BRANDNAME }}">{{ $w->FV_BRANDNAME }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">UOM</label>
                        <div class="col-sm-6">
                            <input type="number" name="UOM" class="form-control @error('UOM') is-invalid @enderror"
                                placeholder="UOM" required>
                            @error('UOM')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">KUBIKASI</label>
                        <div class="col-sm-6">
                            <input type="number" name="KUBIKASI_CTN"
                                class="form-control @error('KUBIKASI_CTN') is-invalid @enderror" placeholder="KUBIKASI"
                                required>
                            @error('KUBIKASI_CTN')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button data-bs-toggle="modal" type="button" data-bs-target="#exampleModal2"
                        class="btn btn-info">Simpan</button>
                </div>
                <!-- /.card-footer -->
        </div>
    </div>
    <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Informasi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Sudah Benar Data Kubikasi Anda ?
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" name="store" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
