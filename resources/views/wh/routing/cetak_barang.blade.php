 <!DOCTYPE html>
 <html lang="en">
 <?php use Illuminate\Support\Facades\Auth; ?>

 <head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>EDRS - ROUTING</title>

     <!-- Google Font: Source Sans Pro -->
     <link rel="stylesheet"
         href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
         integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
     <!-- Font Awesome -->
     <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
         integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
     <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
     <!-- Ionicons -->
     <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
     <!-- Tempusdominus Bootstrap 4 -->
     <link rel="stylesheet"
         href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
     <!-- iCheck -->
     <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
     <!-- JQVMap -->
     <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
     <!-- Theme style -->
     <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
     <!-- overlayScrollbars -->
     <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
     <!-- Daterange picker -->
     <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
     <!-- summernote -->
     <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
 </head>

 <body class="hold-transition sidebar-mini layout-fixed" onload="window.print()">
     {{-- onload="window.print()" --}}
     <div class="wrapper">

         <!-- Navbar -->
         <nav class="main-header navbar navbar-expand navbar-white navbar-light">
             <!-- Left navbar links -->
             <ul class="navbar-nav">
                 <li class="nav-item">
                     <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                             class="fas fa-bars"></i></a>
                 </li>
             </ul>

             <!-- Right navbar links -->
             <ul class="navbar-nav ml-auto">
                 <li class="nav-item">
                     <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                         <i class="fas fa-expand-arrows-alt"></i>
                     </a>
                 </li>
             </ul>
         </nav>
         <!-- /.navbar -->

         <!-- Main Sidebar Container -->
         <aside class="main-sidebar sidebar-dark-primary elevation-4">
             <!-- Brand Logo -->
             <a href="/" class="brand-link">
                 <img src="{{ asset('dist/img/logocsa.png') }}" alt="csa logo"
                     class="brand-image img-circle elevation-3" style="opacity: .8">
                 <span class="brand-text font-weight-light">EDRS-ROUTER</span>
             </a>

             <!-- Sidebar -->
             <div class="sidebar">
                 <!-- Sidebar user panel (optional) -->
                 <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                     <div class="image">
                         <img src="{{ asset('dist/img/user.png') }}" class="img-circle elevation-2" alt="User Image">
                     </div>
                     <div class="info">
                         <a href="#" class="d-block">{{ Auth::user()->name }}</a>
                     </div>
                 </div>
                 <!-- Sidebar Menu -->
                 <nav class="mt-2">
                     <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                         data-accordion="false">
                         <?php if(Auth::user()->level == 2) { ?>
                         <li class="nav-item">
                             <a href="#" class="nav-link active">
                                 <i class="nav-icon fas fa-copy"></i>
                                 <p>
                                     Data Kendaraan
                                     <i class="fas fa-angle-left right"></i>
                                 </p>
                             </a>
                             <ul class="nav nav-treeview">
                                 <li class="nav-item">
                                     <a href="/vehicle" class="nav-link">
                                         <i class="far fa-circle nav-icon"></i>
                                         <p>TRUCK - 3 CYCLE</p>
                                     </a>
                                 </li>
                             </ul>
                         </li>
                         <li class="nav-item">
                             <a href="#" class="nav-link active">
                                 <i class="nav-icon fas fa-chart-pie"></i>
                                 <p>
                                     Loader / Picker
                                     <i class="right fas fa-angle-left"></i>
                                 </p>
                             </a>
                             <ul class="nav nav-treeview">
                                 <li class="nav-item">
                                     <a href="/employee" class="nav-link">
                                         <i class="far fa-circle nav-icon"></i>
                                         <p> Data Picker</p>
                                     </a>
                                 </li>
                             </ul>
                         </li>
                         <li class="nav-item">
                             <a href="#" class="nav-link active">
                                 <i class="nav-icon fas fa-chart-pie"></i>
                                 <p>
                                     SET ROUTING PLAN
                                     <i class="right fas fa-angle-left"></i>
                                 </p>
                             </a>
                             <ul class="nav nav-treeview">
                                 <li class="nav-item">
                                     <a href="/routing-list" class="nav-link">
                                         <i class="far fa-circle nav-icon"></i>
                                         <p>Setting Routing</p>
                                     </a>
                                 </li>
                             </ul>
                         </li>
                         <li class="nav-item">
                             <a href="#" class="nav-link active">
                                 <i class="nav-icon fas fa-chart-pie"></i>
                                 <p>
                                     Rayon
                                     <i class="right fas fa-angle-left"></i>
                                 </p>
                             </a>
                             <ul class="nav nav-treeview">
                                 <li class="nav-item">
                                     <a href="/area" class="nav-link">
                                         <i class="far fa-circle nav-icon"></i>
                                         <p>Data Area</p>
                                     </a>
                                 </li>
                             </ul>
                             <ul class="nav nav-treeview">
                                 <li class="nav-item">
                                     <a href="/rayon" class="nav-link">
                                         <i class="far fa-circle nav-icon"></i>
                                         <p>Data Rayon</p>
                                     </a>
                                 </li>
                             </ul>
                         </li>
                         <?php } ?>
                         <?php if(Auth::user()->level == 3) { ?>
                         <li class="nav-item">
                             <a href="/barang" class="nav-link active">
                                 <i class="nav-icon fas fa-chart-pie"></i>
                                 <p>
                                     Barang
                                     <i class="right fas fa-angle-left"></i>
                                 </p>
                             </a>
                             <ul class="nav nav-treeview">
                                 <li class="nav-item">
                                     <a href="/barang" class="nav-link">
                                         <i class="far fa-circle nav-icon"></i>
                                         <p>Data Barang</p>
                                     </a>
                                 </li>
                             </ul>
                         </li>
                         <?php } ?>
                         <?php if(Auth::user()->level == 1) { ?>
                         <li class="nav-item">
                             <a href="/barang" class="nav-link active">
                                 <i class="nav-icon fas fa-chart-pie"></i>
                                 <p>
                                     Cabang
                                     <i class="right fas fa-angle-left"></i>
                                 </p>
                             </a>
                             <ul class="nav nav-treeview">
                                 <li class="nav-item">
                                     <a href="/barang" class="nav-link">
                                         <i class="far fa-circle nav-icon"></i>
                                         <p>Data Cabang</p>
                                     </a>
                                 </li>
                             </ul>
                         </li>
                         <li class="nav-item">
                             <a href="" class="nav-link active">
                                 <i class="nav-icon fas fa-chart-pie"></i>
                                 <p>
                                     Hak Akses
                                     <i class="right fas fa-angle-left"></i>
                                 </p>
                             </a>
                             <ul class="nav nav-treeview">
                                 <li class="nav-item">
                                     <a href="/access" class="nav-link">
                                         <i class="far fa-circle nav-icon"></i>
                                         <p>Setting Hak Akses</p>
                                     </a>
                                 </li>
                             </ul>
                         </li>
                         <?php } ?>
                     </ul>
                 </nav>
                 <!-- /.sidebar-menu -->
             </div>
             <!-- /.sidebar -->
         </aside>

         <!-- Content Wrapper. Contains page content -->
         <div class="content-wrapper">
             <!-- Content Header (Page header) -->
             <div class="content-header">
                 <div class="container-fluid">
                     <div class="row mb-2">
                         <div class="col-sm-6">
                             <h1 class="m-0">Dashboard</h1>
                         </div><!-- /.col -->
                         <div class="col-sm-6">
                             <ol class="breadcrumb float-sm-right">
                                 <li class="breadcrumb-item"><a href="{{ route('logout') }}">Logout</a></li>
                             </ol>
                         </div><!-- /.col -->
                     </div><!-- /.row -->
                 </div><!-- /.container-fluid -->
             </div>
             <section class="content">
                 <div class="container-fluid">
                     <div class="row">
                         <div class="col-12">
                             <div class="card">
                                 <div class="card-header">
                                     Cabang : {{ $confirm->CODE_STOF }} <br>
                                     ROUTING : {{ $confirm->NOROUTING }} <br>
                                     VEHICLE : {{ $confirm->NOPOL }}
                                 </div>
                                 <!-- /.card-header -->
                                 <div class="card-body">
                                     <table id="example2" class="table table-bordered table-hover">
                                         <thead>
                                             <tr>
                                                 <th>NO</th>
                                                 <th>STOCKCODE</th>
                                                 <th>STOCKNAME</th>
                                                 <th>Karton</th>
                                                 <th>Pcs</th>
                                                 <th>EXTRA(pcs)</th>
                                                 <th>Total Keseluruhan</th>
                                                 <th>KUBIKASI</th>
                                             </tr>
                                         </thead>
                                         <tbody>
                                             <?php $no = 1; ?>
                                             @foreach ($barang as $d)
                                                 <tr>
                                                     <td>{{ $no }}</td>
                                                     <td>{{ $d->FC_STOCKCODE }}</td>
                                                     <td>{{ $d->FV_STOCKNAME }}</td>
                                                     <td>{{ floor((int) $d->QTY / (int) $d->UOM) }}</td>
                                                     <td>
                                                         <?php if ((int) $d->QTY >= (int) $d->UOM) {
                                                             echo (int) $d->QTY % (int) $d->UOM;
                                                         } else {
                                                             echo (int) $d->QTY;
                                                         } ?>
                                                     </td>
                                                     <td>{{ $d->EXTRA }}</td>
                                                     <td>{{ $d->QTY }}</td>
                                                     <td>{{ $d->KUBIK / 1000000 }}</td>
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
         </div>

         <aside class="control-sidebar control-sidebar-dark">
             <!-- Control sidebar content goes here -->
         </aside>
         <!-- /.control-sidebar -->
     </div>
     <!-- ./wrapper -->

     <!-- jQuery -->
     <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
     <!-- Bootstrap 4 -->
     <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
     <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
     <!-- jQuery UI 1.11.4 -->
     <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
     <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
     <script>
         $.widget.bridge('uibutton', $.ui.button)
     </script>
     <!-- ChartJS -->
     <script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
     <!-- Sparkline -->
     <script src="{{ asset('plugins/sparklines/sparkline.js') }}"></script>
     <!-- JQVMap -->
     <script src="{{ asset('plugins/jqvmap/jquery.vmap.min.js') }}"></script>
     <script src="{{ asset('plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
     <!-- jQuery Knob Chart -->
     <script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
     <!-- daterangepicker -->
     <script src="{{ asset('plugins/inputmask/jquery.inputmask.min.js') }}"></script>
     <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
     <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
     <!-- Tempusdominus Bootstrap 4 -->
     <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
     <!-- Summernote -->
     <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
     <!-- overlayScrollbars -->
     <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
     <!-- AdminLTE App -->
     <script src="{{ asset('plugins/dropzone/min/dropzone.min.js') }}"></script>
     <script src="{{ asset('dist/js/adminlte.js') }}"></script>
     <script src="{{ asset('plugins/bs-stepper/js/bs-stepper.min.js') }}"></script>
     <!-- AdminLTE for demo purposes -->
     <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
     <script src="{{ asset('dist/js/pages/dashboard.js') }}"></script>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
         integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
     </script>

 </body>

 </html>
