@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Setting Routing Cabang</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <form action="/create" method="POST">
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
                                            <label>Vehicle</label>
                                            <select class="form-control" name="NOPOL">
                                                @if (!$vehicle)
                                                    <option>Belum Ada Data Kendaraan</option>
                                                @endif
                                                @foreach ($vehicle as $v)
                                                    <option value="{{ $v->NOPOL }}">{{ $v->NOPOL }} -
                                                        {{ $v->TIPE }} -
                                                        {{ $v->KUBIKASI }} M<sup>3</sup></option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Petugas Loading</label>
                                            <select class="form-control" name="FC_NIK">
                                                @if (!$emp)
                                                    <option>Belum Ada Data Loader</option>
                                                @endif
                                                @foreach ($emp as $e)
                                                    <option value="{{ $e->FC_NIK }}">{{ $e->FC_NAME }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">

                                    <button type="submit" <?php if (!$emp) {
                                        echo 'disabled';
                                    } elseif (!$vehicle) {
                                        echo 'disabled';
                                    } ?> name="create_cabang" class="btn btn-info"><i
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
