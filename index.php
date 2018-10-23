<?php
 
header('Content-Type: text/html; charset=utf-8');
header("Access-Control-Allow-Origin: *");

// https://comet-server.com/wiki/doku.php/comet:authentication
function getJWT($data, $pass, $dev_id = 0)
{
    // Create token header as a JSON string
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

    if(isset($data['user_id']))
    {
        $data['user_id'] = (int)$data['user_id'];
    }

    // Create token payload as a JSON string
    $payload = json_encode($data);

    // Encode Header to Base64Url String
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

    // Encode Payload to Base64Url String
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    // Create Signature Hash
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $pass.$dev_id, true);

    // Encode Signature to Base64Url String
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    // Create JWT
    return trim($base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature);
}


$user_id = rand(1, 1000);

// We connect to the comet server with login and password for the access demo (you can get your data for connection after registration at comet-server.com)
// Login 15
// Password lPXBFPqNg3f661JcegBY0N0dPXqUBdHXqj2cHf04PZgLHxT6z55e20ozojvMRvB8
// CometQL_v1 database
$user_key = getJWT(['user_id' => $user_id, "exp" => date("U")+3600], 'lPXBFPqNg3f661JcegBY0N0dPXqUBdHXqj2cHf04PZgLHxT6z55e20ozojvMRvB8', '15');


?><!DOCTYPE HTML>
<html>
<head>
    <!-- Подключаем библиотеки -->
    <script src="//comet-server.com/CometServerApi.js" type="text/javascript"></script>
    <script src="//comet-server.com/doc/CometQL/simplePhpChat/jquery.min.js" type="text/javascript"></script>
    <script src="//comet-server.com/doc/CometQL/messages-demo/iziToast-master/dist/js/iziToast.min.js" type="text/javascript"></script>
    
    <link rel="stylesheet" href="//comet-server.com/doc/CometQL/messages-demo/iziToast-master/dist/css/iziToast.min.css">
</head>
<body>

<h1>Demo chat example, you user_id=<?php echo $user_id ?></h1>
<a href="https://comet-server.com/wiki/doku.php/start">Документация</a>,
<a href="https://comet-server.com/wiki/doku.php/comet:cometql">Описание CometQL Api</a>,
<a href="https://comet-server.com/wiki/doku.php/comet:javascript_api">Описание JavaScript Api</a>
 
<hr>

<h4>Отправить в общий канал</h4>
<input type="text" id="msgText" placeholder="Текст сообщения">
<input type="button" value="Отправить"  onclick="sendMsg();">


<hr>
<h4>Отправить персонально пользователю</h4>
<input type="text" id="msgPrivateId" placeholder="Id получателя">
<input type="text" id="msgPrivateText" placeholder="Текст сообщения">
<input type="button" value="Отправить"  onclick="sendPrivateMsg();">


<script type="text/javascript">

$(document).ready(function(){

    /** 
     * Подписываемся на получение сообщения из канала Pipe_name
     */
    CometServer().subscription("iziMessages", function(event){
         
        iziToast.success({
            title: "Public message",
            message: event.data,
            maxWidth: 500,
            position: "topRight",
        });
        
    })

    CometServer().subscription("msg.iziMessages", function(event){
         
        iziToast.warning({
            title: "Persanal Message",
            message: ""+event.data,
            maxWidth: 500,
            position: "topRight",
        });
        
    })
    
    
    /** 
     * Подключение к комет серверу. Для возможности принимать команды.
     * dev_id ваш публичный идентифиукатор разработчика
     */
    CometServer().start({dev_id:15, user_key:'<?php echo $user_key ?>', user_id:<?php echo $user_id ?> })
})

function sendMsg(){

    var text = $("#msgText").val();

    jQuery.ajax({
        url: "//comet-server.com/doc/CometQL/messages-demo/public_pipe.php",
        type: "GET",
        data:"text="+encodeURIComponent(text),
        success: function(res){
            iziToast.error({
                title: "Sended",
                message: res,
                maxWidth: 500,
                position: "topRight",
            });
        }
    });
}

function sendPrivateMsg(){

    var id = $("#msgPrivateId").val();
    var text = $("#msgPrivateText").val();

    jQuery.ajax({
        url: "//comet-server.com/doc/CometQL/messages-demo/private_messages.php",
        type: "GET",
        data:"text="+encodeURIComponent(text)+"&user_id="+encodeURIComponent(id),
        success: function(res){
            iziToast.error({
                title: "Sended",
                message: res,
                maxWidth: 500,
                position: "topRight",
            });
        }
    });
}
</script>
 
</body>
</html>
