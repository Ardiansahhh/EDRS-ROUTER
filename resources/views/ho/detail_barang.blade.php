 <?php use Illuminate\Support\Facades\DB; ?>
 @extends('main')
 @section('contents')
     <section class="content">
         <div class="container-fluid">
             <div class="row">
                 <div class="col-12">
                     <div class="card">
                         @if (session()->has('success'))
                             <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                 {{ session('success') }}
                             </div>
                         @endif
                         <div class="card-body">
                             <table id="example2" class="table table-bordered table-hover">
                                 <thead>
                                     <tr>
                                         <th>NO</th>
                                         <th>BRANCH</th>
                                         <th>STOCKCODE</th>
                                         <th>STOCKNAME</th>
                                         <th>ACTION</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <?php $no = 1; ?>
                                     @foreach ($barang as $d)
                                         <tr>
                                             <td>{{ $no }}</td>
                                             <td>{{ $d->CODE_STOF }}</td>
                                             <td>{{ $d->FC_STOCKCODE }}</td>
                                             <td>{{ $d->FV_STOCKNAME }}</td>
                                             <td>
                                                 <form action="/input-kubikasi" class="form-group" method="post">
                                                     @csrf
                                                     <input type="hidden" name="FC_BRANCH" value="{{ $d->CODE_STOF }}">
                                                     <input type="hidden" name="FC_STOCKCODE"
                                                         value="{{ $d->FC_STOCKCODE }}">
                                                     <input type="hidden" name="FV_STOCKNAME"
                                                         value="{{ $d->FV_STOCKNAME }}">
                                                     <button type="submit" name="btn_filter" class="btn btn-primary"
                                                         style="font-size: 15px"><i class="fas fa-pen"></i>
                                                         Lengkapi</button>
                                                 </form>
                                             </td>
                                             <?php $no++; ?>
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
 {{-- <a href="/hitung" class="btn btn-primary">Recount</a>
                             <a href="/group" class="btn btn-primary">Group</a> --}}
