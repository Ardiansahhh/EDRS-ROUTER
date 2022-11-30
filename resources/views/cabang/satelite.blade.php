@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>BRANCH</th>
                                        <th>CODE SATELITE</th>
                                        <th>SATELITE OFFICE</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>{{ $item->FC_BRANCH }}</td>
                                            <td>{{ $item->CODE_STOF }}</td>
                                            <td>{{ $item->SATELITE_OFFICE }}</td>
                                            <td>
                                                <a href="/setup-gt/{{ $item->CODE_STOF }}/{{ $item->FC_BRANCH }}"
                                                    class="btn btn-primary">Setup GT</a>
                                                <a href="/setup-mt/{{ $item->CODE_STOF }}/{{ $item->FC_BRANCH }}"
                                                    class="btn btn-primary">Setup MT</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
