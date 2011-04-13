<?php

/*
 *
 * Administrator form script
 * Since: 2011.04.13
 * License: Public Domain
 *
 *
 */
class AdminForm {
    //コンストラクタ
    public function __construct(){
        session_start();
        $this->control();
    }

    // ログイン画面表示
    private function show_login($messsage){
        $xhtml =<<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=Shift-JIS" />
	<title>ログイン画面</title>
    <link rel="stylesheet" href="css/form.css" />
</head>
<body>
$messsage
<h1>ログイン</h1>
<form method="POST" action="admin.php">
<label for="name">ログインID</label><br />
<input name="name" type="text" value="" /><br />
<label for="name">パスワード</label><br />
<input name="password" type="password" value="" /><br />
<input type="submit" value="ログイン" />
</form>
</body>
</html>
EOT;
        echo $xhtml;
    }

    // ログインチェック
    private function checklogin(){
        $user = unserialize(file_get_contents("user.dat"));
        $name = $_POST['name'];
        $pass = hash_hmac('sha256', $_POST['password'] , false);


        if( isset($user[$name]) && strcmp($user[$name],$pass) == 0 ){
            $_SESSION['user'] = $name;
            $this->show_main("");
        }else{
            $this->show_login("ユーザ名かパスワードが間違っています");
        }
    }

    //ログアウト
    private function logout(){
        unset($_SESSION['user']);
        $this->show_login("");   //ログイン画面表示
    }

    //パスワード変更
    private function changepass(){
        $new_pass = $_POST['new_pass'];
        $conf = $_POST['new_pass_conf'];

        $messsage = "パスワード更新に失敗しました";
        if( strcmp($new_pass,$conf) == 0 ){
            $pass = hash_hmac('sha256', $new_pass , false);
            $data = array();
            $data[$_SESSION["user"]] = $pass;
            $ser = serialize($data);
            $fp = @fopen("user.dat","w");
            fwrite($fp,$ser);
            fclose($fp);
            $messsage = "パスワード変更しました";
        }
        $this->show_main($messsage);
    }

    private function writemessage(){
        //書き込み
        $contents = $_POST["content"];
        //XSS対策
        $contents = preg_replace("/<script>[\s\S]*<\/script>/","",$contents);
        $contents = preg_replace("/onerror=|onload=|javascript:/","",$contents);
        $fp = @fopen("pickup.dat","w");
        fwrite($fp, $contents);
        fclose($fp);
        $messsage = "文言を更新しました";

        $this->show_main($messsage);
    }

    //メイン画面
    private function show_main($messsage){


        //読込み
        $pickuphtml = "";
        $fp = @fopen("pickup.dat","r");
        if( $fp ){
            $pickuphtml = file_get_contents("pickup.dat");
        }else{
            $pickuphtml = "";
        }

        $pickuphtml = htmlspecialchars($pickuphtml);

        $xhtml =<<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=Shift-JIS" />
	<title>管理者画面</title>
    <link rel="stylesheet" href="css/form.css" />
    <script type="text/javascript">
        function init(){
            var form = document.getElementById("passchangeform");
            form.onsubmit = function(e){
                return checkpass(e);
            }
        }
        function checkpass(e){
            var eve = e || window.event;
            var new_pass = document.getElementById("new_pass").value;
            var new_pass_conf = document.getElementById("new_pass_conf").value;
            if( new_pass.length == 0 || new_pass_conf.length == 0 || new_pass != new_pass_conf ){
                alert('パスワード内容に不備があります。確認して下さい');
                if( isNaN(+[1,]) ){
                    eve.returnValue = false;
                }else{
                    eve.preventDefault();
                }
                return false;
            }
            return true;
        }
    </script>
</head>
<body onload="init()">
<h1>管理者画面</h1>
$messsage

<!-- フォームリスト -->
<ul>

<!-- 文言更新用フォーム -->

<li>
<h2>文言更新フォーム</h2>
<p>
HTMLを入力する事が出来ます。<br />
scriptタグは使用できません。
</p>
<form action="admin.php" method="POST">
<textarea id="" name="content" rows="10" cols="40">$pickuphtml</textarea><br />
<input type="submit" value="保存" />
</form>
</li>

<!-- パスワード変更用フォーム -->
<li>
<h2>パスワード変更</h2>
<form action="admin.php" method="POST" id="passchangeform">
<label for="new_pass">新しいパスワード</label><br />
<input type="password" name="new_pass" id="new_pass" value="" /><br />
<label for="new_pass">新しいパスワード(再入力)</label><br />
<input type="password" name="new_pass_conf" id="new_pass_conf" value="" /><br />
<input type="submit" value="パスワード変更" />
</form>
</li>


<!-- ログアウトフォーム -->
<li>
<h2>ログアウト</h2>
<form action="admin.php" method="post">
<input name="logout" type="hidden" value="logout" />
<input type="submit" value="ログアウト" />
</form>
</li>
</ul>


</body>
</html>
EOT;


        echo $xhtml;

    }


    // 分岐処理
    function control(){
        if( isset($_POST['logout']) ){
            $this->logout();
        }else if( isset($_POST['name'],$_POST['password']) ){
            $this->checklogin();
        }else if( isset($_POST['new_pass'],$_POST['new_pass_conf']) ){
            $this->changepass();
        }else if( isset($_POST["content"]) ){
            $this->writemessage();
        }else if( isset($_SESSION['user']) ){
            $this->show_main("");
        }else{
            $this->show_login("");
        }
    }

}


$admin = new AdminForm();

?>
