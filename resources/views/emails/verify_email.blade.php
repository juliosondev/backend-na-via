<!DOCTYPE html>
<html>
<head>
    <title>Verifica o seu email</title>
</head>
<body>
    <p>Olá {{ $data['name'] }},</p>
    <p>Obrigado por criar conta no NaVia. Clique no link abaixo para verificar seu endereço de e-mail e ativar sua conta:</p>
    <p><a href="{{ $data['verificationUrl'] }}">Link para verificar email</a></p>
    <p>Este email é uma surpresa? Por favor, ignore este email e não clique no link acima. Alguém pode ter digitado incorretamente seu endereço de email e acidentalmente tentou adicionar o seu. Neste caso, o seu endereço de email não será adicionado à outra conta.</p>
</body>
</html>
