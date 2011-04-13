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
    //�R���X�g���N�^
    public function __construct(){
        session_start();
        $this->control();
    }

    // ���O�C����ʕ\��
    private function show_login($messsage){
        $xhtml =<<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=Shift-JIS" />
	<title>���O�C�����</title>
    <link rel="stylesheet" href="css/form.css" />
</head>
<body>
$messsage
<h1>���O�C��</h1>
<form method="POST" action="admin.php">
<label for="name">���O�C��ID</label><br />
<input name="name" type="text" value="" /><br />
<label for="name">�p�X���[�h</label><br />
<input name="password" type="password" value="" /><br />
<input type="submit" value="���O�C��" />
</form>
</body>
</html>
EOT;
        echo $xhtml;
    }

    // ���O�C���`�F�b�N
    private function checklogin(){
        $user = unserialize(file_get_contents("user.dat"));
        $name = $_POST['name'];
        $pass = hash_hmac('sha256', $_POST['password'] , false);


        if( isset($user[$name]) && strcmp($user[$name],$pass) == 0 ){
            $_SESSION['user'] = $name;
            $this->show_main("");
        }else{
            $this->show_login("���[�U�����p�X���[�h���Ԉ���Ă��܂�");
        }
    }

    //���O�A�E�g
    private function logout(){
        unset($_SESSION['user']);
        $this->show_login("");   //���O�C����ʕ\��
    }

    //�p�X���[�h�ύX
    private function changepass(){
        $new_pass = $_POST['new_pass'];
        $conf = $_POST['new_pass_conf'];

        $messsage = "�p�X���[�h�X�V�Ɏ��s���܂���";
        if( strcmp($new_pass,$conf) == 0 ){
            $pass = hash_hmac('sha256', $new_pass , false);
            $data = array();
            $data[$_SESSION["user"]] = $pass;
            $ser = serialize($data);
            $fp = @fopen("user.dat","w");
            fwrite($fp,$ser);
            fclose($fp);
            $messsage = "�p�X���[�h�ύX���܂���";
        }
        $this->show_main($messsage);
    }

    private function writemessage(){
        //��������
        $contents = $_POST["content"];
        //XSS�΍�
        $contents = preg_replace("/<script>[\s\S]*<\/script>/","",$contents);
        $contents = preg_replace("/onerror=|onload=|javascript:/","",$contents);
        $fp = @fopen("pickup.dat","w");
        fwrite($fp, $contents);
        fclose($fp);
        $messsage = "�������X�V���܂���";

        $this->show_main($messsage);
    }

    //���C�����
    private function show_main($messsage){


        //�Ǎ���
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
	<title>�Ǘ��҉��</title>
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
                alert('�p�X���[�h���e�ɕs��������܂��B�m�F���ĉ�����');
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
<h1>�Ǘ��҉��</h1>
$messsage

<!-- �t�H�[�����X�g -->
<ul>

<!-- �����X�V�p�t�H�[�� -->

<li>
<h2>�����X�V�t�H�[��</h2>
<p>
HTML����͂��鎖���o���܂��B<br />
script�^�O�͎g�p�ł��܂���B
</p>
<form action="admin.php" method="POST">
<textarea id="" name="content" rows="10" cols="40">$pickuphtml</textarea><br />
<input type="submit" value="�ۑ�" />
</form>
</li>

<!-- �p�X���[�h�ύX�p�t�H�[�� -->
<li>
<h2>�p�X���[�h�ύX</h2>
<form action="admin.php" method="POST" id="passchangeform">
<label for="new_pass">�V�����p�X���[�h</label><br />
<input type="password" name="new_pass" id="new_pass" value="" /><br />
<label for="new_pass">�V�����p�X���[�h(�ē���)</label><br />
<input type="password" name="new_pass_conf" id="new_pass_conf" value="" /><br />
<input type="submit" value="�p�X���[�h�ύX" />
</form>
</li>


<!-- ���O�A�E�g�t�H�[�� -->
<li>
<h2>���O�A�E�g</h2>
<form action="admin.php" method="post">
<input name="logout" type="hidden" value="logout" />
<input type="submit" value="���O�A�E�g" />
</form>
</li>
</ul>


</body>
</html>
EOT;


        echo $xhtml;

    }


    // ���򏈗�
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
