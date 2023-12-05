<?php
/*

Este é um exemplo de implementação do padrão de código de barras PIX em PHP, desenvolvido por @luizsnv. O código permite a geração dinâmica de códigos PIX para pagamentos, seguindo as diretrizes do Banco Central do Brasil. Pode ser facilmente inicializado usando XAMPP ou Apache2.

**Recursos:**
- Geração dinâmica de códigos PIX.
- Padrão de código de barras PIX conforme as normas do Banco Central do Brasil.
- Integração fácil em projetos PHP para facilitar pagamentos.

**Como Usar:**
1. Clone o repositório.
2. Personalize os parâmetros conforme suas necessidades.
3. Integre o código em seus projetos PHP para gerar códigos PIX de forma eficiente.

**Iniciando com XAMPP:**
1. Certifique-se de ter o XAMPP instalado.
2. Clone o repositório dentro da pasta `htdocs` do seu XAMPP.
3. Acesse `http://localhost/seu_caminho_para_o_repositorio/index.php` no navegador.

**Iniciando com Apache2:**
1. Certifique-se de ter o Apache2 instalado.
2. Clone o repositório dentro da pasta de hospedagem do seu Apache2.
3. Acesse `http://seu_endereco_para_o_apache/seu_caminho_para_o_repositorio/index.php` no navegador.

**Autor:**
@luizsnv

*/

function montaPix($px) {

    $ret = "";
    foreach ($px as $k => $v) {
        if (!is_array($v)) {
            if ($k == 54) {
                $v = number_format($v, 2, '.', '');
            } else {
                $v = remove_char_especiais($v);
            }
            $ret .= c2($k) . cpm($v) . $v;
        } else {
            $conteudo = montaPix($v);
            $ret .= c2($k) . cpm($conteudo) . $conteudo;
        }
    }
    return $ret;
}

function remove_char_especiais($txt) {
    return preg_replace('/\W /', '', remove_acentos($txt));
}

function remove_acentos($texto) {
    $search = explode(",", "à,á,â,ä,æ,ã,å,ā,ç,ć,č,è,é,ê,ë,ē,ė,ę,î,ï,í,ī,į,ì,ł,ñ,ń,ô,ö,ò,ó,œ,ø,ō,õ,ß,ś,š,û,ü,ù,ú,ū,ÿ,ž,ź,ż,À,Á,Â,Ä,Æ,Ã,Å,Ā,Ç,Ć,Č,È,É,Ê,Ë,Ē,Ė,Ę,Î,Ï,Í,Ī,Į,Ì,Ł,Ñ,Ń,Ô,Ö,Ò,Ó,Œ,Ø,Ō,Õ,Ś,Š,Û,Ü,Ù,Ú,Ū,Ÿ,Ž,Ź,Ż");
    $replace = explode(",", "a,a,a,a,a,a,a,a,c,c,c,e,e,e,e,e,e,e,i,i,i,i,i,i,l,n,n,o,o,o,o,o,o,o,o,s,s,s,u,u,u,u,u,y,z,z,z,A,A,A,A,A,A,A,A,C,C,C,E,E,E,E,E,E,E,I,I,I,I,I,I,L,N,N,O,O,O,O,O,O,O,O,S,S,U,U,U,U,U,Y,Z,Z,Z");
    return remove_emoji(str_replace($search, $replace, $texto));
}

function remove_emoji($string) {
    return preg_replace('%(?:
   \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
 | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
 | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
)%xs', '  ', $string);
}

function cpm($tx) {

    if (strlen($tx) > 99) {
        die("Tamanho máximo deve ser 99, inválido: $tx possui " . strlen($tx) . " caracteres.");
    }
    return c2(strlen($tx));
}

function c2($input) {

    return str_pad($input, 2, "0", STR_PAD_LEFT);
}

function crcChecksum($str) {

    function charCodeAt($str, $i) {
        return ord(substr($str, $i, 1));
    }

    $crc = 0xFFFF;
    $strlen = strlen($str);
    for ($c = 0; $c < $strlen; $c++) {
        $crc ^= charCodeAt($str, $c) << 8;
        for ($i = 0; $i < 8; $i++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc = $crc << 1;
            }
        }
    }
    $hex = $crc & 0xFFFF;
    $hex = dechex($hex);
    $hex = strtoupper($hex);
    $hex = str_pad($hex, 4, '0', STR_PAD_LEFT);

    return $hex;
}

$valor = "10.00";


if (isset($_POST['submit'])) {
    $valor = $_POST['valor'];
}

/*

  # Coloque suas informações

*/

$px[00] = "01";
$px[26][00] = "BR.GOV.BCB.PIX";
$px[26][01] = "b0378d25-7a7e-447c-a53a-24cbe0d7774c";
$px[52] = "0000";
$px[53] = "986";
$px[54] = $valor;
$px[58] = "BR";
$px[59] = "Luiz Henrique";
$px[60] = "São Paulo";
$px[62][05] = "***";

$pix = montaPix($px);
$pix .= "6304";
$pix .= crcChecksum($pix);

$qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($pix);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pix QRCode</title>
    <style>
        body {
            background-color: #060606;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            align-items: center;
            justify-content: center;
        }

        #content-panel {
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
            text-align: center;

            width: 500px;
            height: 610px;
        }

        img {
            width: 200px;
            height: 200px;
        }

        header {
            padding: 20px;
            text-align: center;
        }

        form {
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input {
            padding: 5px;
            margin-bottom: 10px;
            width: 300px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #222;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease-in-out;
            color: white;
        }

        button {
            background-color: #ff0;
            color: #333;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: transform 0.3s ease-in-out;
            max-width: 200px;
            border-radius: 10%;
            font-family: Brush Script MT, Brush Script Std, cursive;
        }

        button:hover {
            transform: scale(1.1);
            font-size: 20px;
        }

        footer {
            background-color: #222;
            color: #fff;
            padding: 10px;
            text-align: center;
            margin-top: 98px
        }
        #pix-code {
            line-height: 40px;
            background-color: #222222;
            color: #ffffff;
            padding: 5px;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div id="content-panel">
        <form method="post" action="">
            <label for="valor">Valor:</label>
            <input type="text" id="valor" name="valor" value="<?php echo $valor; ?>" required>
            <button type="submit" name="submit">Gerar Pix</button>
        </form>



        <div id="qrcode-container">
            <img src="<?php echo $qr_code_url; ?>" alt="QR Code">
            <div id="pix-code"><?php echo $pix; ?></div>
        </div>

        <footer>
            <p>Gerador de Qr Code feito por @luizsnv</p>
        </footer>
    </div>
</body>

</html>
