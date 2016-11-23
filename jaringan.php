<?php



function koneksi(){
$servername = "sql6.freemysqlhosting.net";
$username = "sql6144721";
$password = "tfu7NEeYxp";
$database = "sql6144721";


  return $conn = new mysqli($servername, $username, $password, $database);
}
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

  function insertOrUpdate($kon, $sql){
    if ($kon->query($sql) === TRUE) {
      return "sukses bray";
    } else {
      return "Error: " . $sql . "<br>" . $kon->error;
    }
  }

  function read($kon, $sql){
    $result = $kon->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row;
    } else {
        return null;
    }
    $conn->close();
  }

  function ambilAkhir($grupnama){
    $penghitung="1";
        $urut="1";
        $nada="0";
        $kolom="no";
        while($urut<=10)
        {
        $konten=tampilRules(koneksi(),$kolom,$penghitung,$grupnama);
        if($nada<=$konten)
        {
            $nada=$konten;
        }
        $penghitung++;
        $urut++;
        }

        return $nada;
  }

function ambilmin($grupnama){
    $penghitung="10";
        $urut="10";
        $nada="1";
        $kolom="no";
        while($urut>=0)
        {
        $konten=tampilRules(koneksi(),$kolom,$penghitung,$grupnama);
        if($nada>=$konten&&$konten!=0)
        {
            $nada=$konten;
        }
        $penghitung--;
        $urut--;
        }

        return $nada;
  }



  function updateRules($kon,$id, $isi,$nomor,$grup){
    $sql = "update $grup set nama = '$isi',id = '$id' where no = '$nomor'";
    insertOrUpdate($kon, $sql);
  }

  function tampilRules($kon, $kolom,$nomor,$grup){
    $sql="select * from $grup where no like '$nomor'";
    $data = read($kon, $sql);
    return $data["$kolom"];
  }
  function menghapus($kon,$id,$grup)
  {
    $sql= "delete from $grup where id='$id'";
    insertOrUpdate($kon, $sql);
  }
  
  function tambahtable($kon,$grup)
  {
    $sql = "CREATE TABLE IF NOT EXISTS $grup (
  no int NOT NULL AUTO_INCREMENT primary key,
  id int NOT NULL  ,
  user text (255) NOT NULL,

  nama text (255) NOT NULL)";

insertOrUpdate($kon, $sql);
  }
  function menambahdata($kon,$id,$nama,$grup,$user)
  {
    $sql = "INSERT INTO $grup (id ,nama,user) VALUES ( '$id', '$nama','$user')";
    insertOrUpdate($kon,$sql);
  }
 function menghapustable($kon,$grup)
 {
  $sql="DROP TABLE $grup";
  insertOrUpdate($kon,$sql);
 }
  
 ?>
