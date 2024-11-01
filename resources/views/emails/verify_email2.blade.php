<!DOCTYPE html>
<html>
<head>
    <title>Verifica o seu novo email</title>
</head>
<body>
    <p>Olá {{ $data['name'] }},</p>
    <p>Recebemos uma solicitação de mudança de email. Clique no link abaixo para verificar seu novo endereço de e-mailÇ</p>
    <p><a href="{{ $data['verificationUrl'] }}">Link para verificar email</a></p>
    <p>Este email é uma surpresa? Por favor, ignore este email e não clique no link acima. Alguém pode ter digitado incorretamente seu endereço de email e acidentalmente tentou adicionar o seu. Neste caso, o seu endereço de email não será adicionado à outra conta.</p>
</body>
</html>
