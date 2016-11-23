
<?php
include 'jaringan.php';
$TOKEN      = "251510629:AAFwY3YWOF1avAEryXBD9fHoKy5EWbRIktU";
$usernamebot= "@PlayToDbot";
$debug = false;
 
$penghitung="0";
$admin = array(1 =>"295422432" ,2 =>"230573047" ); 
$nomor = "1";
$penghitung="1";
$mw="0";
$kunci= false;
 
function request_url($method)
{
    global $TOKEN;
    return "https://api.telegram.org/bot" . $TOKEN . "/". $method;
}
 

function get_updates($offset) 
{
    $url = request_url("getUpdates")."?offset=".$offset;
        $resp = file_get_contents($url);
        $result = json_decode($resp, true);
        if ($result["ok"]==1)
            return $result["result"];
        return array();
}

function send_reply($chatid, $msgid, $text)
{
    global $debug;
    $data = array(
        'chat_id' => $chatid,
        'text'  => $text,
        'reply_to_message_id' => $msgid   
    );
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );
    $context  = stream_context_create($options); 
    $result = file_get_contents(request_url('sendMessage'), false, $context);
    if ($debug) 
        print_r($result);
}
 

function create_response($text, $message)
{
    global $usernamebot;
    global $admin;
    global $nomor;
    global $penghitung;
    global $mw;
    global $kunci;
    global $grupnama;


    $hasil = '';  
    $fromid = $message["from"]["id"]; // variable penampung id user
    $chatid = $message["chat"]["id"];
    $tandagrup= substr($chatid,0,1);
    $kodegrup= substr($chatid,1,13); 

    $grupnama="group_"."$kodegrup";
    $pesanid= $message['message_id'];
    // variable penampung id message
    // variable penampung username nya user
    isset($message["from"]["username"])
        ? $chatuser = $message["from"]["username"]
        : $chatuser = '';
    
    // variable penampung nama user
    isset($message["from"]["last_name"]) 
        ? $namakedua = $message["from"]["last_name"] 
        : $namakedua = '';   
    $namauser = $message["from"]["first_name"]. ' ' .$namakedua;

    // ini saya pergunakan untuk menghapus kelebihan pesan spasi yang dikirim ke bot.
    $textur = preg_replace('/\s\s+/', ' ', $text); 
    // memecah pesan dalam 2 blok array, kita ambil yang array pertama saja
    $command = explode(' ',$textur,2); 
   
    switch ($command[0]) {
        
            case '/join':
            case '/join'.$usernamebot :
            if ($tandagrup=="-")
            {
            $grupnama="group_"."$kodegrup";
            $h=tambahtable(koneksi(),"$grupnama");
            $maxplayer=ambilAkhir($grupnama);
            $minplayer=ambilmin($grupnama);
            $batasmax=$minplayer+9;
            if($kunci==false)
            {

            if ($maxplayer <=$batasmax)
            {
                $kolom="id";
                $penghitung="1";
                
                $mw="0";
                $cari="1";
                while($cari<=10)
                {
                    $konten=tampilRules(koneksi(),$kolom,$penghitung,$grupnama);
                if($fromid!=$konten)
                {
                    $mw++;
                    $penghitung++;
                    $cari++;
                }
                else
                {
                 $cari++;   
                }
                }
                if($mw=="10"){
              $r = menambahdata(koneksi(), "$fromid", "$namauser",$grupnama,$chatuser);
              $hasil = "$namauser berhasil bergabung";
                }
                else{
                    $hasil ="anda telah terdaftar";
                }
            }
              else
                $hasil="jumlah sudah penuh";
        }
        else
        {
            $hasil="game sudah di mulai";
        }
    }
    else
    {
        $hasil="command berlaku di grup";
    }
          break;



          case '/mulai':
          case '/mulai'.$usernamebot :
          $jumlah=ambilAkhir($grupnama);
          $batasmain=ambilmin($grupnama);
          $batas=$batasmain+1;
              if ($jumlah>=$batas)
              {
                $hasil="game dimulai";
                $kunci=true;
              }
              else {
                $hasil="jumlah tidak mencukupi";
              }
              break;

        case '/pilih':
        case '/pilih'.$usernamebot:
        $konten=tampilRules(koneksi(),"id","1",$grupnama);
        if($fromid==$konten||$konten=='')
        {
        $kolom="no";
        $jumlah=ambilAkhir($grupnama);
        $keluar=rand(1,10);
        $konten=tampilRules(koneksi(),$kolom,$keluar,$grupnama);
        $pembatas=$jumlah;
        if($pembatas>0)
    {
        while($konten==null)
        {
        $keluar=rand(1,10);
        $konten=tampilRules(koneksi(),$kolom,$keluar,$grupnama); 
        }
        
        $namapilih=tampilRules(koneksi(),"user",$keluar,$grupnama);

        $hasil="arah putaran ke "."@"."$namapilih ,"."@"."$namapilih memilih truth or dare";
        $idpilih=tampilRules(koneksi(),"id",$keluar,$grupnama);
        $hapus=menghapus(koneksi(),$idpilih,$grupnama);
    }
    else 
    {
        $hasil="game telah berakhir ,main kembali /join";
        $kunci=false;
        $hapus=menghapustable(koneksi(),$grupnama);
    }
}
else
{
    $nama=tampilRules(koneksi(),"nama","1",$grupnama);
    $hasil="Yang bisa menekan tombol pilih adalah $nama";
}
break;


        case '!end':
            $hapus=menghapustable(koneksi(),$grupnama);
            $hasil = "pengakhiran paksa berhasil";

            break;
        case '!id':
            $hasil="@"."$chatuser";
            break;



        
        default:
            $hasil = '';
            break;
        
   
    }
    
    return $hasil;
}
 
// fungsi pesan yang sekaligus mengupdate offset 
// biar tidak berulang-ulang pesan yang di dapat 
function process_message($message)
{
    $updateid = $message["update_id"];
    $message_data = $message["message"];
    if (isset($message_data["text"])) {
    $chatid = $message_data["chat"]["id"];
        $message_id = $message_data["message_id"];
        $text = $message_data["text"];
        $response = create_response($text, $message_data);
        if (!empty($response))
          send_reply($chatid, $message_id, $response);
    }
    return $updateid;
}
 
function process_one()
{
    global $debug;
    $update_id  = 0;
    echo "-";
 
    if (file_exists("last_update_id")) 
        $update_id = (int)file_get_contents("last_update_id");
 
    $updates = get_updates($update_id);
    // jika debug=0 atau debug=false, pesan ini tidak akan dimunculkan
    if ((!empty($updates)) and ($debug) )  {
        echo "\r\n===== isi diterima \r\n";
        print_r($updates);
    }
 
    foreach ($updates as $message)
    {
        echo '+';
        $update_id = process_message($message);
    }
    
    // update file id, biar pesan yang diterima tidak berulang
    file_put_contents("last_update_id", $update_id + 1);
}
// metode poll
// proses berulang-ulang
// sampai di break secara paksa
// tekan CTRL+C jika ingin berhenti 
while (true) {
    process_one();
    sleep(1);
}
// metode webhook
// secara normal, hanya bisa digunakan secara bergantian dengan polling
// aktifkan ini jika menggunakan metode webhook
/*
$entityBody = file_get_contents('php://input');
$pesanditerima = json_decode($entityBody, true);
process_message($pesanditerima);
*/
/*
 * -----------------------
 * Grup @botphp
 * Jika ada pertanyaan jangan via PM
 * langsung ke grup saja.
 * ----------------------
 
* Just ask, not asks for ask..
Sekian.
*/
    
?>
