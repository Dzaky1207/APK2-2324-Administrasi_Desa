<?php
// Set waktu
date_default_timezone_set('Asia/Jakarta');
$tgl = date('Y-m-d H:i:s');

//Koneksi Database
$HOSTNAME = "localhost";
$DATABASE = "db_apartement_fix";
$USERNAME = "root";
$PASSWORD = "";



$KONEKSI = mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);

if (!$KONEKSI) {
    die("koneksi database error ai sia") . mysqli_connect_error($KONEKSI);
}


//Fungsi Autonumber
function autonumber($tabel, $kolom, $lebar = 0, $awalan)
{
    global $KONEKSI;

    $auto = mysqli_query($KONEKSI, "SELECT $kolom FROM 
     $tabel ORDER BY $kolom desc limit 1") or die(mysqli_error($KONEKSI));
    $jumlah_record = mysqli_num_rows($auto);

    if ($jumlah_record == 0) {
        $nomor = 1;
    } else {
        $row = mysqli_fetch_array($auto);
        $nomor = intval(substr($row[0], strlen($awalan))) + 1;
    }
    if ($lebar > 0) {
        $angka = $awalan . str_pad($nomor, $lebar, "0", STR_PAD_LEFT);
    } else {
        $angka = $awalan . $nomor;
    }
    return $angka;
}
//   echo autonumber ("tbl_users", "id_user",3, "USR");


// Fungsi Register
if (!function_exists('registrasi')) {
    function registrasi($data) {
        
    global $KONEKSI;
    global $tgl;
    
    $id_user = stripslashes($data['id_user']);
    $nama = stripslashes($data['nama']); // Untuk cek form register dari input nama
    $email = strtolower(stripslashes($data['email'])); // Memastikan form register mengirim input enail berupa huruf kecil semua
    $password = mysqli_real_escape_string($KONEKSI, $data['password']);
    $password2 = mysqli_real_escape_string($KONEKSI, $data['password2']);

    //echo $nama ."|" . $email . "|" . $password . "|" . $password;

    // Cek Email yang di input ada belum di database
    $result = mysqli_query($KONEKSI, "SELECT email from tbl_users WHERE email='$email'");
    //var_dump($result);

    if (mysqli_fetch_assoc($result)) {
        echo "<script>
    alert('Email yang di input sudah di database!!!');
    </script>";
        return false;
    }

    // Cek Konfirmasi password
    if ($password !== $password2) {
        echo "<script>
    alert('Konfirmasi Password tidak sesuai!!!');
    document.location.href='register.php';
    </script>";
        return false;
    }

    // Enkripsi password yang akan kita masukan kedatabase
    $password_hash = password_hash($password, PASSWORD_DEFAULT); // Mengunakan algoritma default dari hash
    //var_dump($password_hash);

    // Ambil id_tipe_user yang ada di tabel tbl_tipe_user

    $tipe_user = "SELECT * FROM tbl_tipe_user WHERE tipe_user='Admin'";
    $hasil = mysqli_query($KONEKSI, $tipe_user);
    $row = mysqli_fetch_assoc($hasil);
    $id = $row ['id_tipe_user'];

    // Tambah user baru ke tbl_user
    $SQL = "INSERT INTO tbl_users SET
    id_user = '$id_user',
    role = '$id',
    email = '$email',
    password ='$password_hash',
    create_at = '$tgl' ";

    mysqli_query($KONEKSI, $SQL) or die("Gagal Menambahkan User" . mysqli_error($KONEKSI));

    // Tambah user baru ke tbl_admin
    $SQL = "INSERT INTO tbl_admin SET
    id_user = '$id_user',
    nama_admin = '$nama',
    create_at = '$tgl' ";
        
    mysqli_query($KONEKSI, $SQL) or die("Gagal Menambahkan User" . mysqli_error($KONEKSI));

    echo "<script>
    document.location.href='login.php';
    </script>";
    return mysqli_affected_rows($KONEKSI);
    }
}
//fungsi tampil data
function tampil($data)
{
    global $KONEKSI;

    $HASIL = mysqli_query($KONEKSI, $data);
    $data = []; //MEYIAPKAN VARIABLE/WADAH YG MASI KOSONG UNTUK NANTINYA AKAN KITA GUNAKAN UNTUK MENYIMPAN DATA YANG KITA QUERY/PANGGIL DARI DATA BASE
    while ($row = mysqli_fetch_assoc($HASIL)) {
        $data[] = $row;
    }
    return $data; // kita kembalikan nilainya, di munculkan 

}

//fungsi tambah data admin
function tambah_admin($DATA)
{

    global $KONEKSI;
    global $tgl;

    $id_admin   = stripslashes($_POST['id_admin']);
    $nama_admin = stripslashes($_POST['nama_admin']);
    $email      = strtolower(stripslashes($_POST['email']));
    $telepon    = stripslashes($_POST['telepon']);
    $role       = stripslashes($_POST['role']);
    $password   = mysqli_real_escape_string($KONEKSI, $_POST['password']);
    $password2  = mysqli_real_escape_string($KONEKSI, $_POST['password2']);


    //cek email yang di daftar apakah sudah di pakai apa belum
    $result = mysqli_query($KONEKSI, "SELECT email FROM tbl_users WHERE email='$email' ");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>alert('Email Yang Di Input Sudah Ada Di DataBase!!!')
        document.location.href='?inc=user_admin'
        </script>";
        return false;
    }

    //cek konfirmasi password
    if ($password !== $password2) {
        echo "<script>alert('Konfirmasi Password Yang Di Input Tidak Sama!!!')
        document.location.href='?inc=user_admin'
        </script>";
        return false;
    }

    //kita lakukan enkripsi  password yang dia input
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    //pastikan data gambar terupload
    $gambar_foto = upload_file();

    //jika tidak upload foto proses kita hentikaan
    if (!$gambar_foto) {
        return false;
    }

    //tambahkan data user baru ke tb_users
    $sql_user = "INSERT INTO tbl_users SET
    id_user ='$id_admin',
    email ='$email',
    password ='$password_hash',
    role ='$role',
    create_at = '$tgl' ";

    mysqli_query($KONEKSI, $sql_user) or die("GAGAL MENAMBAHKAN USER BARU!!") . mysqli_errno($KONEKSI);

    //tambahkan user baru ke tb_admin
    $sql_user = "INSERT INTO tbl_admin SET
    nama_admin ='$nama_admin',
    telepon_admin ='$telepon',
    path_photo_admin ='$gambar_foto',
    id_user ='$id_admin',
    create_at ='$tgl' ";

    mysqli_query($KONEKSI, $sql_user) or die("GAGAL MENAMBAHKAN ADMIN BARU!!") . mysqli_errno($KONEKSI);

    return mysqli_affected_rows($KONEKSI);
}

//fungsi upload file
function upload_file()
{
    //inisialisasi element dari photo/gambar
    $namaFile = $_FILES['Photo']['name'];
    $ukuranFile = $_FILES['Photo']['size'];
    $error = $_FILES['Photo']['error'];
    $tmpName = $_FILES['Photo']['tmp_name'];
    $tipeFile = $_FILES['Photo']['type'];
    $id_admin = $_POST['id_admin'];

    //kita pastikan user upload file
    if ($error == 4) { // 4 artinya tidak ada file yang di upload
        echo "<script>alert('Tidak ada File Yang Di Upload')</script>";
        return false;
    }

    //kita pastikan validasi ekstensi file
    $ekstensiValid = ['jpg', 'jpeg', 'bmp', 'png'];
    $ekstensiFile = explode('.', $namaFile);
    $ekstensiFile = strtolower(end($ekstensiFile));

    if (!in_array($ekstensiFile, $ekstensiValid)) {
        echo "<script>alert('file yang anda upload bukan gambar')</script>";
        return false;
    }

    //kita validasi ukuran maksimal gambar
    if ($ukuranFile > 1 * 1024 * 1024) {
        echo "<script>alert('GAMBAR TIDAK BOLEH LEBIH DARI 1M')</script>";
        return false;
    }

    //membuat nama file baru yang uniq
    $id_random = uniqid();
    $namaFileBaru = $id_admin . "_" . $id_random . "." . $ekstensiFile;

    $target = '../images/users/';
    $file_path = $target . $namaFileBaru;

    //kita cek/debag apalah nama baru sudah terbentuk, jika ada langsung upload file
    echo "Menyalin File ke : " . $file_path;
    if (move_uploaded_file($tmpName, $file_path)) {
        echo "<script>alert('file berhasil di upload')</script>";
        return $namaFileBaru;
    } else {
        echo "<script>alert('Upload gagal, Cobalagii!!')</script>";
        return false;
    }
}

//fungsi edit admin
function edit_admin($data)
{
    global $KONEKSI;
    global $tgl;

    $id_admin   = htmlspecialchars($data['id_admin']);
    $nama_admin = htmlspecialchars($data['nama_admin']);
    $email      = htmlspecialchars($data['email']);
    $telepon    = htmlspecialchars($data['telepon']);
    $foto_lama  = htmlspecialchars($data['photo_db']);

    $target = '../images/users/';
    $cek_file_lama = $target . $foto_lama; //lokasi foto lama

    // cek apakah ada file baru yang di upload oleh user
    if (isset($_FILES['Photo']) && $_FILES['Photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        //jika ada file gambar baru yang di upload
        $foto_edit = upload_file();

        //kita pastikan nama file baru terupload (Debuggin)
        echo "File Baru :" . $foto_edit . "Berhasil Di Upload";

        //kita pastikan file lama terhapus (unlink)
        //cek dulu file lama di db apakah ada di folder target
        if ($foto_edit && file_exists($cek_file_lama)) {
            if (unlink($cek_file_lama)) {
                // true ==> Berhasil hapus data lama
                echo "Berhasil hapus file lama";
            } else {
                //false ==> gagal hapus file lama
                echo "Gagal hapus file lama";
            }
        }
    } else {
        //jika tidak ada file gambar baru yang di upload
        $foto_edit = $foto_lama;
        echo "Menggunakan Foto Lama : " . $foto_lama;
    }


    //update (edit) data ke tbl_admin
    $sql_user = "UPDATE tbl_admin SET
    nama_admin ='$nama_admin',
    telepon_admin ='$telepon',
    path_photo_admin ='$foto_edit',
    update_at ='$tgl' WHERE tbl_admin.id_user ='$id_admin' ";


    //cek apakah query update data berhasil
    if (mysqli_query($KONEKSI, $sql_user)) {
        echo "<script>alert('Data Berhasil Di Update!!')</script>";
    } else {
        echo "<script>alert('Data Gagal Di Update!!')</script>";
    }


    return mysqli_affected_rows($KONEKSI);
} //kurung tutup function edit_admin


// fungsi hapus user admin
function hapus_admin()
{
    global $KONEKSI;
    $id_user = $_GET['id'];

    // hapus file gambar yang usernya kita hapus
    $sql = "SELECT * FROM tbl_admin WHERE id_user='$id_user' " or die("Data tidak ditemukan" . mysqli_error($KONEKSI));
    $hasil = mysqli_query($KONEKSI, $sql);
    $row = mysqli_fetch_assoc($hasil);

    $photo = $row['path_photo_admin'];
    $target = '../images/users/';

    if (!$photo == "") {
        // Jika ada maka kita hapus
        unlink($target . $photo);
    }


    // hapus data di tabel admin
    $query_admin = "DELETE FROM tbl_admin WHERE id_user='$id_user' ";
    mysqli_query($KONEKSI, $query_admin) or die("Gagal melakukan hapus data admin" . mysqli_error($KONEKSI));

    // hapus data di tabel users
    $query_user = "DELETE FROM tbl_users WHERE id_user='$id_user' ";
    mysqli_query($KONEKSI, $query_user) or die("Gagal melakukan hapus data user" . mysqli_error($KONEKSI));


    return mysqli_affected_rows($KONEKSI);
}

//fungsi upload file menggunakan parameter (NEW)
function upload_file_new($data, $file, $target)
{
    //inisialisasi elemen dari foto/filenya
    $namaFile = $file['Photo']['name'];
    $ukuranFile = $file['Photo']['size'];
    $error = $file['Photo']['error'];
    $tmpName = $file['Photo']['tmp_name'];
    $tipeFile = $file['Photo']['type'];

    $kode   = htmlspecialchars($data['kode']);

    //debug buat element $data dan $file

    echo "<pre>";
    print_r($data);  //melihat data yang akan di terima
    print_r($file);  //melihat data yang akan di terima
    echo "</pre>";

    //pastikan bahwa user melakukan upload file

    if ($error == UPLOAD_ERR_NO_FILE) {
        echo "<script>alert('Tidak ada file yang di upload!!');
        </script>";
        return false;
    }

    //validasi ekstenssi file
    $ekstensiValid = ['jpg', 'jpeg', 'bmp', 'png'];
    $ekstensiFile = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

    if (! in_array($ekstensiFile, $ekstensiValid)) {
        echo "<script>alert('file yang anda upload bukan gambar!!');
        </script>";
        return false;
    }

    //Validasi ukuran gambar
    if ($ukuranFile > 1 * 1024 * 1024) {
        echo "<script>alert('ukuran file tidak boleh lebih dari 1MB!!');
        </script>";
        return false;
    }

    //Membuat nama file baru yang uniq
    $id_random = uniqid();
    $namaFileBaru = $kode . "_" . $id_random . "." . $ekstensiFile;

    $file_path = $target . $namaFileBaru;


    //cekk apakah file sudah terupload
    if (move_uploaded_file($tmpName, $file_path)) {
        echo "<script>alert('file berhasil di upload!!');
        </script>";
        return $namaFileBaru;
    } else {
        echo "<script>alert('Gagak Upload File!!');
        </script>";
        return false;
    }
}

//fungsi tambah branch
function tambah_branch($data, $file, $target)
{
    global $KONEKSI;
    global $tgl;

    $kode        = htmlspecialchars($data['kode']);
    $nama_branch = htmlspecialchars($data['nama_cab']);
    $alamat      = htmlspecialchars($data['alamat']);
    $email       = htmlspecialchars($data['email']);
    $telepon     = htmlspecialchars($data['telepon']);
    $kecamatan   = htmlspecialchars($data['kecamatan']);
    $kota        = htmlspecialchars($data['kota']);
    $provinsi    = htmlspecialchars($data['provinsi']);
    $kodepos     = htmlspecialchars($data['kodepos']);

    echo "<pre>";
    print_r($data);  //melihat data yang akan di terima
    print_r($file);  //melihat data yang akan di terima
    echo "</pre>";

    //kita harus upload file
    $gambar_foto = upload_file_new($data, $file, $target);
    echo $gambar_foto;
    //kits input data ke tabel
    if ($gambar_foto) {
        //jika upload berhasil maka lanjut dengan insert
        $sql = "INSERT INTO tbl_branch SET
        kode_branch = '$kode', 
        nama_perusahaan = '$nama_branch', 
        alamat_perusahaan = '$alamat',
        email_perusahaan = '$email',
        telepon_perusahaan = '$telepon',
        kecamatan_perusahaan = '$kecamatan',
        kota_perusahaan = '$kota',
        provinsi_perusahaan = '$provinsi',
        path_logo = '$gambar_foto',
        kode_pos = '$kodepos',
        update_at = '$tgl' ";
        //yang kiri data base, yg kanan nama variable di atas



        //cek apakah query berhasil apa tidak
        if (mysqli_query($KONEKSI, $sql)) {
            echo "<script>alert('Data Berhasil Di Tambahkan!!');
        </script>";
            return true;
        } else {
            echo "<script>alert('Data Tidak Berhasil Di Tambahkan!! " . mysqli_error($KONEKSI) . "');
        </script>";
            return false;
        }
    } else {
        echo "<script>alert('Gagal Melakukan Upload File!!');
        </script>";
        return false;
    }
}

//fungsi edit branch
function edit_branch($data, $target, $file)
{
    global $KONEKSI;
    global $tgl;

    $kode_branch            = stripslashes($data['kode']);
    $nama_perusahaan        = stripslashes($data['nama_cab']);
    $alamat_perusahaan      = stripslashes($data['alamat']);
    $email_perusahaan       = stripslashes($data['email']);
    $telepon_perusahaan     = stripslashes($data['telepon']);
    $kecamatan_perusahaan   = stripslashes($data['kecamatan']);
    $kota_perusahaan        = stripslashes($data['kota']);
    $provinsi_perusahaan    = stripslashes($data['provinsi']);
    $foto_lama              = stripslashes($data['photo_db']);
    $kode_pos               = stripslashes($data['kodepos']);

    $cek_file_lama = $target . $foto_lama; //lokasi foto lama

    // cek apakah ada file baru yang di upload oleh user
    if (isset($_FILES['Photo']) && $_FILES['Photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        //kita harus upload file
        $gambar_foto = upload_file_new($data, $file, $target);
        echo $gambar_foto;

        //kita pastikan nama file baru terupload (Debuggin)
        echo "File Baru :" . $gambar_foto . "Berhasil Di Upload";

        //kita pastikan file lama terhapus (unlink)
        //cek dulu file lama di db apakah ada di folder target
        if ($gambar_foto && file_exists($cek_file_lama)) {
            if (unlink($cek_file_lama)) {
                // true ==> Berhasil hapus data lama
                echo "Berhasil hapus file lama";
            } else {
                //false ==> gagal hapus file lama
                echo "Gagal hapus file lama";
            }
        }
    } else {
        //jika tidak ada file gambar baru yang di upload
        $gambar_foto = $foto_lama;
        echo "Menggunakan Foto Lama : " . $foto_lama;
    }

    //update (edit) data ke tbl_admin
    $sql_user = "UPDATE tbl_branch SET
        nama_perusahaan  ='$nama_perusahaan',
        alamat_perusahaan ='$alamat_perusahaan',
        email_perusahaan ='$email_perusahaan',
        telepon_perusahaan ='$telepon_perusahaan',
        path_logo ='$gambar_foto',
        kecamatan_perusahaan ='$kecamatan_perusahaan',
        kota_perusahaan ='$kota_perusahaan',
        provinsi_perusahaan ='$provinsi_perusahaan',
        kode_pos ='$kode_pos',
        update_at ='$tgl' WHERE kode_branch ='$kode_branch' ";


    //cek apakah query update data berhasil
    if (mysqli_query($KONEKSI, $sql_user)) {
        echo "<script>alert('Data Berhasil Di Update!!')</script>";
    } else {
        echo "<script>alert('Data Gagal Di Update!!')</script>";
    }


    return mysqli_affected_rows($KONEKSI);
} //kurung tutup function edit_branch


//fungsi hapus branch
function hapus_branch($data, $target)
{
    global $KONEKSI;

    $kode_branch = htmlspecialchars($data['id']);
    echo $kode_branch;

    //ambil nama file gambar yang terkait dengan cabang yang akan di hapus
    $query = "SELECT path_logo FROM tbl_branch WHERE kode_branch = '$kode_branch'";
    $result = mysqli_query($KONEKSI, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $gambar_foto = $data['path_logo'];
        //echo $gambar_foto;

        //Hapus Data Dari Data Base
        $deleteQuery = "DELETE FROM tbl_branch WHERE kode_branch = '$kode_branch'";
        if (mysqli_query($KONEKSI, $deleteQuery)) {
            //Hapus file gambar dari folder jika ada
            if ($gambar_foto && file_exists($target . $gambar_foto)) {
                unlink($target . $gambar_foto);
            }
            echo "<script>alert('Data Berhasil Di Hapus!!');
            </script>";
            return true;
        } else {
            echo "<script>alert('Gagal Menghapus Data!!" . mysqli_error($KONEKSI) . "');
            </script>";
            return false;
        }
    } else {
        echo "<script>alert('Data Tidak Di Temukan!!" . mysqli_error($KONEKSI) . "');
            </script>";
        var_dump(mysqli_num_rows($result));
        die;
        return false;
    }
}

//fungsi tambah petugas
function tambah_petugas($data, $file, $target)
{
     global $KONEKSI;
    global $tgl;

    $id_petugas   = htmlspecialchars($_POST['kode']);
    $nama_ptg = htmlspecialchars($_POST['nama_ptg']);
    $jenkel      = htmlspecialchars($_POST['jenkel']);
    $email    = htmlspecialchars($_POST['email']);
    $telepon    = htmlspecialchars($_POST['telepon']);
    $cabang   = htmlspecialchars($_POST['cabang']);
    $role       = stripslashes($data['role']);
    $password    = mysqli_real_escape_string($KONEKSI, $_POST['password']);
    $password2       = mysqli_real_escape_string($KONEKSI, $_POST['password2']);

    //kita harus upload file
    $gambar_foto = upload_file_new($data, $file, $target);

    //jika gambar di upload operasi di hentikan
    if (!$gambar_foto) {
        return false;
    }

    //cek email yang di daftar apakah sudah di pakai apa belum
    $result = mysqli_query($KONEKSI, "SELECT email FROM tbl_users WHERE email='$email' ");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>alert('Email Yang Di Input Sudah Ada Di DataBase!!!')
        document.location.href='?inc=user_petugas'
        </script>";
        return false;
    }

    //cek konfirmasi password
    if ($password !== $password2) {
        echo "<script>alert('Konfirmasi Password Yang Di Input Tidak Sama!!!')
        document.location.href='?inc=user_petugas'
        </script>";
        return false;
    }

    //kita lakukan enkripsi  password yang dia input
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    //tambahkan data user baru ke tb_users
    $sql_user = "INSERT INTO tbl_users SET
    id_user ='$id_petugas',
    email ='$email',
    password ='$password_hash',
    role ='$role',
    create_at = '$tgl' ";

    mysqli_query($KONEKSI, $sql_user) or die("GAGAL MENAMBAHKAN USER BARU!!") . mysqli_error($KONEKSI);

    //tambahkan user baru ke tb_admin
    $sql_petugas = "INSERT INTO tbl_petugas SET
    nama_petugas ='$nama_ptg',
    telepon_petugas ='$telepon',
    path_photo_petugas ='$gambar_foto',
    id_user ='$id_petugas',
    jenkel ='$jenkel',
    branch_id ='$cabang',
    create_at ='$tgl' ";

    mysqli_query($KONEKSI, $sql_petugas) or die("GAGAL MENAMBAHKAN PETUGAS BARU!!") . mysqli_error($KONEKSI);

    return mysqli_affected_rows($KONEKSI);
}

//fungsi edit petugas
function edit_petugas($data, $target, $file)
{
    global $KONEKSI;
    global $tgl;

    $id_petugas      = stripslashes($data['kode']);
    $nama_petugas    = stripslashes($data['nama_ptg']);
    $jenkel          = stripslashes($data['jenkel']);
    $email_petugas   = stripslashes($data['email']);
    $telepon_petugas = stripslashes($data['telepon']);
    $cabang          = stripslashes($data['cabang']);
    $foto_lama       = stripslashes($data['photo_db']);

    $cek_file_lama = $target . $foto_lama; //lokasi foto lama

    // cek apakah ada file baru yang di upload oleh user
    if (isset($_FILES['Photo']) && $_FILES['Photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        //kita harus upload file
        $gambar_foto = upload_file_new($data, $file, $target);
        echo $gambar_foto;

        //kita pastikan nama file baru terupload (Debuggin)
        echo "File Baru :" . $gambar_foto . "Berhasil Di Upload";

        //kita pastikan file lama terhapus (unlink)
        //cek dulu file lama di db apakah ada di folder target
        if ($gambar_foto && file_exists($cek_file_lama)) {
            if (unlink($cek_file_lama)) {
                // true ==> Berhasil hapus data lama
                echo "Berhasil hapus file lama";
            } else {
                //false ==> gagal hapus file lama
                echo "Gagal hapus file lama";
            }
        }
    } else {
        //jika tidak ada file gambar baru yang di upload
        $gambar_foto = $foto_lama;
        echo "Menggunakan Foto Lama : " . $foto_lama;
    }

    //update (edit) data ke tbl_petugas
    $sql_user = "UPDATE tbl_petugas SET
        nama_petugas  ='$nama_petugas',
        jenkel ='$jenkel',
        telepon_petugas ='$telepon_petugas',
        path_photo_petugas ='$gambar_foto',
        branch_id ='$cabang',
        update_at ='$tgl' WHERE id_user ='$id_petugas' ";


    //cek apakah query update data berhasil
    if (mysqli_query($KONEKSI, $sql_user)) {
        echo "<script>alert('Data Berhasil Di Update!!')</script>";
    } else {
        echo "<script>alert('Data Gagal Di Update!!')</script>";
    }


    return mysqli_affected_rows($KONEKSI);
} //kurung tutup function edit_petugas


// fungsi hapus petugas
function hapus_petugas()
{
    global $KONEKSI;
    $id_user = $_GET['id'];

    // hapus file gambar yang usernya kita hapus
    $sql = "SELECT * FROM tbl_petugas WHERE id_user='$id_user' " or die("Data tidak ditemukan" . mysqli_error($KONEKSI));
    $hasil = mysqli_query($KONEKSI, $sql);
    $row = mysqli_fetch_assoc($hasil);

    $photo = $row['path_photo_petugas'];
    $target = '../images/petugas/';

    if (!$photo == "") {
        // Jika ada maka kita hapus
        unlink($target . $photo);
    }


    // hapus data di tabel petugas
    $query_admin = "DELETE FROM tbl_petugas WHERE id_user='$id_user' ";
    mysqli_query($KONEKSI, $query_admin) or die("Gagal melakukan hapus data petugas" . mysqli_error($KONEKSI));

    // hapus data di tabel users
    $query_user = "DELETE FROM tbl_users WHERE id_user='$id_user' ";
    mysqli_query($KONEKSI, $query_user) or die("Gagal melakukan hapus data user" . mysqli_error($KONEKSI));


    return mysqli_affected_rows($KONEKSI);
}

//fungsi tambah jabatan
function tambah_jabatan()
{
    global $KONEKSI;
    global $tgl;

    $kode_jabatan   = stripslashes($_POST['kode']); //ini variable yg di atasnya
    $nama_jabatan = stripslashes($_POST['nama_jbt']);

    //tambahkan data user baru ke tbl_jabatan
    $sql_jabatan = "INSERT INTO tbl_jabatan SET 
    kode_jabatan = '$kode_jabatan',
    nama_jabatan = '$nama_jabatan',
    create_at = '$tgl' ";
    //kiri database, kanan variable yg di atas

    mysqli_query($KONEKSI, $sql_jabatan) or die("gagal menambahkan jabatan baru" . mysqli_error($KONEKSI));

    return mysqli_affected_rows($KONEKSI);
}

//fungsi edit jabatan

function edit_jabatan()
{
    global $KONEKSI;
    global $tgl;

    $kode_jabatan   = stripslashes($_POST['kode']);
    $nama_jabatan = stripslashes($_POST['nama_jbt']);

    //update (edit) data ke tbl_jabatan
    $sql_jbt = "UPDATE tbl_jabatan SET 
    kode_jabatan = '$kode_jabatan',
    nama_jabatan = '$nama_jabatan',
    update_at = '$tgl' WHERE tbl_jabatan.kode_jabatan = '$kode_jabatan' ";

    //cek apakah query update data berhasil
    if (mysqli_query($KONEKSI, $sql_jbt)) {
        echo "<script>alert('Data Berhasil Di Update!!')</script>";
    } else {
        echo "<script>alert('data gagal di update')</script>";
    }

    return mysqli_affected_rows($KONEKSI);
} //kurung tutup function edit_jabatan


//fungsi hapus jabatan
function hapus_jabatan()
{
    global $KONEKSI;
    $kode_jabatan = $_GET['id'];

    // hapus file gambar yang usernya kita hapus
    $sql = "SELECT * FROM tbl_jabatan WHERE kode_jabatan='$kode_jabatan' " or die("Data tidak ditemukan" . mysqli_error($KONEKSI));
    $hasil = mysqli_query($KONEKSI, $sql);
    $row = mysqli_fetch_assoc($hasil);

    // hapus data di tabel jabatan
    $query_jabatan = "DELETE FROM tbl_jabatan WHERE kode_jabatan='$kode_jabatan' ";
    mysqli_query($KONEKSI, $query_jabatan) or die("Gagal melakukan hapus data jabatan" . mysqli_error($KONEKSI));

    return mysqli_affected_rows($KONEKSI);
}
//fungsi tambah karyawan
function tambah_karyawan($data, $file, $target)
{
    global $KONEKSI;
    global $tgl;

    $id_karyawan   = htmlspecialchars($data['kode']);
    $nama_kry = htmlspecialchars($data['nama_kry']);
    $email    = htmlspecialchars($data['email']);
    $status_karyawan    = htmlspecialchars($data['status']);
    $alamat_ktp    = htmlspecialchars($data['alamat_ktp']);
    $no_ktp    = htmlspecialchars($data['no_ktp']);
    $date_start    = htmlspecialchars($data['date']);
    $date_finish    = htmlspecialchars($data['date2']);
    $telepon    = htmlspecialchars($data['telepon']);
    $alamat_domisili    = htmlspecialchars($data['alamat']);
    $jenkel      = htmlspecialchars($data['jenkel']);
    $cabang   = htmlspecialchars($data['cabang']);
    $jabatan  = htmlspecialchars($data['jabatan']);
    $role       = stripslashes($data['role']);
    $password    = mysqli_real_escape_string($KONEKSI, $data['password']);
    $password2       = mysqli_real_escape_string($KONEKSI, $data['password2']);

    //kita harus upload file
    $gambar_foto = upload_file_new($data, $file, $target);

    //jika gambar di upload operasi di hentikan
    if (!$gambar_foto) {
        return false;
    }

    //cek email yang di daftar apakah sudah di pakai apa belum
    $result = mysqli_query($KONEKSI, "SELECT email FROM tbl_users WHERE email='$email' ");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>alert('Email Yang Di Input Sudah Ada Di DataBase!!!')
        document.location.href='?inc=user_karyawan'
        </script>";
        return false;
    }

    //cek konfirmasi password
    if ($password !== $password2) {
        echo "<script>alert('Konfirmasi Password Yang Di Input Tidak Sama!!!')
        document.location.href='?inc=user_karyawan'
        </script>";
        return false;
    }

    //kita lakukan enkripsi  password yang dia input
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    //tambahkan data user baru ke tb_users
    $sql_user = "INSERT INTO tbl_users SET
    id_user ='$id_karyawan',
    email ='$email',
    password ='$password_hash',
    role ='$role',
    create_at = '$tgl' ";

    mysqli_query($KONEKSI, $sql_user) or die("GAGAL MENAMBAHKAN USER BARU!!") . mysqli_error($KONEKSI);

    //tambahkan user baru ke tb_admin
    $sql_karyawan = "INSERT INTO tbl_karyawan SET
    nama_karyawan ='$nama_kry',
    telepon_karyawan ='$telepon',
    alamat_domisili_karyawan ='$alamat_domisili',
    alamat_ktp_karyawan ='$alamat_ktp',
    no_ktp ='$no_ktp',
    jenkel ='$jenkel',
    date_start ='$date_start',
    date_finish ='$date_finish',
    status_karyawan ='$status_karyawan',
    path_photo_karyawan ='$gambar_foto',
    id_user ='$id_karyawan',
    branch_id ='$cabang',
    kode_jabatan ='$jabatan',
    create_at ='$tgl' ";

    mysqli_query($KONEKSI, $sql_karyawan) or die("GAGAL MENAMBAHKAN KARYAWAN BARU!!") . mysqli_error($KONEKSI);

    return mysqli_affected_rows($KONEKSI);
}

//fungsi edit karyawan
function edit_karyawan($data, $target, $file)
{
    global $KONEKSI;
    global $tgl;

    $id_karyawan        = stripslashes($data['kode']);
    $nama_karyawan      = stripslashes($data['nama_kry']);
    $email              = stripslashes($data['email']);
    $status_karyawan    = stripslashes($data['status']);
    $alamat_ktp         = stripslashes($data['alamat_ktp']);
    $no_ktp             = stripslashes($data['no_ktp']);
    $date_start         = stripslashes($data['date']);

    $date_finish        = stripslashes($data['date2']);
    $telepon_karyawan   = stripslashes($data['telepon']);
    $alamat_domisili      = stripslashes($data['alamat']);
    $jenkel             = stripslashes($data['jenkel']);
    $cabang             = stripslashes($data['cabang']);
    $foto_lama          = stripslashes($data['photo_db']);

    $cek_file_lama = $target . $foto_lama; //lokasi foto lama

    // cek apakah ada file baru yang di upload oleh user
    if (isset($_FILES['Photo']) && $_FILES['Photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        //kita harus upload file
        $gambar_foto = upload_file_new($data, $file, $target);
        echo $gambar_foto;

        //kita pastikan nama file baru terupload (Debuggin)
        echo "File Baru :" . $gambar_foto . "Berhasil Di Upload";

        //kita pastikan file lama terhapus (unlink)
        //cek dulu file lama di db apakah ada di folder target
        if ($gambar_foto && file_exists($cek_file_lama)) {
            if (unlink($cek_file_lama)) {
                // true ==> Berhasil hapus data lama
                echo "Berhasil hapus file lama";
            } else {
                //false ==> gagal hapus file lama
                echo "Gagal hapus file lama";
            }
        }
    } else {
        //jika tidak ada file gambar baru yang di upload
        $gambar_foto = $foto_lama;
        echo "Menggunakan Foto Lama : " . $foto_lama;
    }

    //update (edit) data ke tbl_karyawan
    $sql_user = "UPDATE tbl_karyawan SET
        nama_karyawan  ='$nama_karyawan',
        status_karyawan ='$status_karyawan',
        alamat_ktp_karyawan  ='$alamat_ktp',
        no_ktp  ='$no_ktp',
        date_start  ='$date_start',
        date_finish  ='$date_finish',
        telepon_karyawan  ='$telepon_karyawan',
        alamat_domisili_karyawan  ='$alamat_domisili',
        jenkel ='$jenkel',
        path_photo_karyawan ='$gambar_foto',
        branch_id ='$cabang',
      
        update_at ='$tgl' WHERE id_karyawan ='$id_karyawan' ";

    //cek apakah query update data berhasil
    if (mysqli_query($KONEKSI, $sql_user)) {
        echo "<script>alert('Data Berhasil Di Update!!')</script>";
    } else {
        echo "<script>alert('Data Gagal Di Update!!')</script>";
    }


    return mysqli_affected_rows($KONEKSI);
} //kurung tutup function edit_karyawan


//fungsi hapus karyawan
function hapus_karyawan()
{
    global $KONEKSI;
    $id_user = $_GET['id'];

    // hapus file gambar yang usernya kita hapus
    $sql = "SELECT * FROM tbl_karyawan WHERE id_user='$id_user' " or die("Data tidak ditemukan" . mysqli_error($KONEKSI));
    $hasil = mysqli_query($KONEKSI, $sql);
    $row = mysqli_fetch_assoc($hasil);

    $photo = $row['path_photo_karyawan'];
    $target = '../images/karyawan/';

    if (!$photo == "") {
        // Jika ada maka kita hapus
        unlink($target . $photo);
    }


    // hapus data di tabel karyawan
    $query_admin = "DELETE FROM tbl_karyawan WHERE id_user='$id_user' ";
    mysqli_query($KONEKSI, $query_admin) or die("Gagal melakukan hapus data karyawan" . mysqli_error($KONEKSI));

    // hapus data di tabel users
    $query_user = "DELETE FROM tbl_users WHERE id_user='$id_user' ";
    mysqli_query($KONEKSI, $query_user) or die("Gagal melakukan hapus data user" . mysqli_error($KONEKSI));


    return mysqli_affected_rows($KONEKSI);
}