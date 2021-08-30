<?php

//HTMLタグの入力を無効にし、文字コードをutf-8にする
//（PHPのおまじないのようなもの）
function h($v){
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

//変数の準備
$FILE = 'todo.txt'; //保存ファイル名

$id = uniqid(); //ユニークなIDを自動生成

//タイムゾーン設定
date_default_timezone_set('Japan');
$date = date('Y年m月d日H時i分'); //日時（年/月/日/ 時:分）

$text = ''; //入力テキスト

$DATA = []; //一回分の投稿の情報を入れる

$BOARD = []; //全ての投稿の情報を入れる

//$FILEというファイルが存在しているとき
if(file_exists($FILE)) {
    //ファイルを読み込む
    $BOARD = json_decode(file_get_contents($FILE));
}
//$_SERVERは送信されたサーバーの情報を得る
//REQUEST_METHODはフォームからのリクエストのメソッドがPOSTかGETか判断する
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //$_POSTはHTTPリクエストで渡された値を取得する
    //リクエストパラメーターが空でなければ
    if(!empty($_POST['txt'])){
        //投稿ボタンが押された場合

        //$textに送信されたテキストを代入
        $text = $_POST['txt'];
        $kind = $_POST['kind'];

        //新規データ
        $DATA = [$id, $date, $text,$kind];
        //新規データを全体配列に代入する
        $BOARD[] = $DATA;

        //全体配列をファイルに保存する
        file_put_contents($FILE, json_encode($BOARD));

    }else if(isset($_POST['del'])){
        //削除ボタンが押された場合

        //新しい全体配列を作る
        $NEWBOARD = [];

        //削除ボタンが押されるとき、すでに$BOARDは存在している
        foreach($BOARD as $DATA){
            //$_POST['del']には各々のidが入っている
            //保存しようとしている$DATA[0]が送信されてきたidと等しくないときだけ配列に入れる
            if($DATA[0] !== $_POST['del']){
                $NEWBOARD[] = $DATA;
            }
        }
        //全体配列をファイルに保存する
        file_put_contents($FILE, json_encode($NEWBOARD));

    }else if(isset($_POST['jpgid_value'])){
        if($_SERVER["REQUEST_METHOD"] === "POST"){
            if($_FILES["userfile"]["error"] == UPLOAD_ERR_OK){
             $tempfile = $_FILES["userfile"]["tmp_name"];
             $filename = $_FILES["userfile"]["name"];
             $jpgid= $_POST["jpgid_value"];  
             $filename = mb_convert_encoding($jpgid.".jpg", "cp932", "utf8");
             $result = move_uploaded_file($tempfile, "../jpg/".$filename);
             if($result == TRUE){
              $message ="upload success";
             }
             else{
              $message ="upload fail";
             }
            }
            elseif($_FILES["userfile"]["error"] == UPLOAD_ERR_NO_FILE) {
             $message ="upload fail";
            }
            else {
             $message ="upload fail";
            }
           }

    }

    //header()で指定したページにリダイレクト
    //今回は今と同じ場所にリダイレクト（つまりWebページを更新）
    header('Location: '.$_SERVER['SCRIPT_NAME']);
    //プログラム終了
    exit;
}
?>

<!DOCTYPE html>
<html lang= "ja">
<head>
    <meta name= "viewport" content= "width=device-width, initial-scale= 1.0">
    <meta http-equiv= "content-type" charset= "utf-8">
    <title>ToDoApp</title>
</head>
<body>
    <h1>ToDoアプリ</h1>

    <section class= "main">
        <h2>ToDoAppに投稿する</h2>

        <!--投稿-->
        <form method= "post">
        　　<p>題名</p>
            <textarea name="kind" ROWS=1 COLS=30></textarea>
            <input type= "submit" value= "投稿"></br>
            <p>内容</p>
            <textarea name="txt" ROWS=10 COLS=80></textarea>
        </form>    

        <table style= "border-collapse: collapse">
        <!--tableの中でtr部分をループ-->
        <?php foreach((array)$BOARD as $DATA): ?>
        <tr>
        <form method= "post">
            <table border =\"3\">
            <td>
                <!--日時-->
                <?php echo $DATA[1]; ?>
            </td>
            <td>
            <?php echo"<textarea name=\"kind\" ROWS=1 COLS=30 style=\"background-color:#bde9ba\">$DATA[3] </textarea>"?></br>
                <!--kind-->
            </td>
            <td>
                <!--テキスト-->
                <?php echo "<textarea name=\"txt\" ROWS=10 COLS=80 style=\"background-color:#bde9ba\">$DATA[2]</textarea>"?>
            </td>
            <td>
                 <img src=../jpg/<?php echo $DATA[0]; ?>.jpg  width=150 height=135><br>
            </td>



            <td>

            
                <!--削除-->
　　　　　　　　　<!--この時その投稿のidがサーバーに送信される-->
                <p>左記内容を消してから</p>
                <p>削除ボタンを押してください</p>
                <input type= "hidden" name= "del" value= "<?php echo $DATA[0]; ?>">
                <input type= "submit" value= "削除">
            </td>
            <td>

        </form>
            <form action="" method="post" enctype="multipart/form-data">
            <p>file：<input type="file" name="userfile" size="40" /></p>
            <p><input type="hidden" size=5 id="jpgid_value" name="jpgid_value" value= "<?php echo $DATA[0]; ?>"></p>
            <p><input type="submit" value="upload" /></p>
        </form>
        </td>



        </tr>
        <?php endforeach; ?>
        </table>
    </section>