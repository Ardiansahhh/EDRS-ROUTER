 <?php use Illuminate\Support\Facades\DB;
 ?>
 @extends('main')
 @section('contents')
     <section class="content">
         <div class="container-fluid">
             <div class="row">
                 <div class="col-12">
                     <div class="card">
                         <div class="card-header">
                             <?php if($confirm->CONFIRM != 'YES') { ?>
                             <a href="/pilih/{{ $routing }}" class="btn btn-primary">Pilih Toko</a>
                             <?php } ?>
                             <a href="/detail-barang/{{ $routing }}" class="btn btn-warning">Detail Barang</a>
                             <a href="/cetak-toko/{{ $routing }}" class="btn btn-primary"><i class="fas fa-print"></i>
                                 Print Toko</a>
                         </div>
                         <!-- /.card-header -->
                         <div class="card-body">
                             <table id="example2" class="table table-bordered table-hover">
                                 <thead>
                                     <tr>
                                         <th>NO</th>
                                         <th>Branch</th>
                                         <th>SO NO</th>
                                         <th>CUSTCODE</th>
                                         <th>CUSTNAME</th>
                                         <th>KELURAHAN</th>
                                         <th>LATITUDE</th>
                                         <th>LONGITUDE</th>
                                         <th>KUBIKASI</th>
                                         <?php if($confirm->CONFIRM != 'YES') { ?>
                                         <th>ACTION</th>
                                         <?php } ?>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <?php $no = 1;
                                     ?>
                                     @foreach ($kubikasi as $d)
                                         <?php $file = $d->lat . '' . $d->long; ?>
                                         <tr>
                                             <td>{{ $no }}</td>
                                             <td>{{ $d->FC_BRANCH }}</td>
                                             <td>{{ $d->FC_SONO }}</td>
                                             <td>{{ $d->FC_CUSTCODE }}</td>
                                             <td>{{ $d->FV_CUSTNAME }}</td>
                                             <td>{{ $d->FC_REGIONDESC }}</td>
                                             <td>{{ $d->lat }}</td>
                                             <td>{{ $d->long }}</td>
                                             <td>{{ $d->KUBIKASI / 1000000 }}</td>
                                             <?php if($confirm->CONFIRM != 'YES') { ?>
                                             <td>
                                                 <form action="/delete-toko" method="post">
                                                     @csrf
                                                     <input type="hidden" name="norouting" value="{{ $routing }}">
                                                     <input type="hidden" name="fc_sono" value="{{ $d->FC_SONO }}">
                                                     <input type="hidden" name="fc_branch" value="{{ $d->FC_BRANCH }}">
                                                     <button type="submit"
                                                         onclick="alert('Apakah anda yakin menghapus toko <?php echo $d->FC_SONO . ' - ' . $d->FV_CUSTNAME; ?>')"
                                                         name="delete" class="btn btn-danger">Hapus</button>
                                                 </form>
                                             </td>
                                             <?php } ?>
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
