@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        @if (session()->has('session'))
                            <div class="alert alert-danger alert-dismissible text-center fade show" role="alert">
                                {{ session('session') }}
                            </div>
                        @endif
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissible text-center fade show" role="alert">
                                {{ session('success') }}</div>
                        @endif
                        <div class="card-header">
                            <h3 class="card-title">Input Vehicle</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <form action="/store_vehicle" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>CABANG</label>
                                            <input type="text" name="FC_BRANCH" class="form-control" disabled
                                                value="{{ Auth::user()->fc_branch }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Tipe</label>
                                            <select class="form-control" name="TIPE">
                                                <option value="CDD">CDD</option>
                                                <option value="CDE LONG">CDE LONG</option>
                                                <option value="GRANDMAX">GRANDMAX</option>
                                                <option value="SEPEDA MOTOR">SEPEDA MOTOR</option>
                                                <option value="3 CYCLE">3 CYCLE - BAJAJ</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>NOPOL</label>
                                            <input type="text" name="NOPOL" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>VENDOR</label>
                                            <input type="text" name="VENDOR" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>KUBIKASI</label>
                                            <input type="number" name="KUBIKASI" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" name="create_vehicle" class="btn btn-info"><i
                                            class="fa-solid fa-send"></i>Save</button>
                                </div>
                            </form>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
        </div><!-- /.container-fluid -->
    </section>
@endsection
