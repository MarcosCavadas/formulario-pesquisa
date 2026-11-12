 
<?php
    //recebe os dados do formulario.html
    // depois vai validar, depois vai salvar e mostrar
    // o resutlado na tela par o usuário
   
    //vamos começar pelo fuso horário, para ficar com a data registrada correta
    date_default_timezone_set("America/Sao_Paulo");
 
    // só aceitaremos dados vindo do post (lá do index,html)
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: index.html");
        exit;
    }
 
    // Função de segurança - para exibir o texto com segurança
    // vai impedir que tags ou scripts digitados pelo usuário sejam executados na página
    function e($texto) {
        return htmlspecialchars($texto, ENT_QUOTES, "UTF-8");
    }
 
    //Recebendo os dados vindos do formulário
    // ?? devolve um valor padrão se o campo não existir
    // ele vai evitar erros do tipo: undefined index
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $idade = trim($_POST["idade"] ?? "");
 
    $estado = $_POST["estado"] ?? "";
    $nota = $_POST["nota"] ?? "";
    $origem = $_POST["origem"] ?? "";
 
    $comentario = trim($_POST["comentario"] ?? "");
   
    //vai verificar se o check está marcado - não deixa prosseguir, se não estiver
    $termos = isset($_POST["termos"]);
   
    //vamos garantir que origem, realmente seja um array
    if (!is_array($origem)){
        //vamos fazer com que ele seja um array!
        $origem = [];
    }
 
    // lista de estados permitidos no formulario
    // estes estados vem do index.html, vejas quais vocÊ informou lá no value do campo estado
    $estadosValidos = ["AC", "AL", "AM", "AP", "PR", "SC", "RS"];
 
    //vamos validar os campos de origem
    $origensValidas = [
        "indicacao" => "Indicacação de amigos",
        "redes" => "Redes Sociais",
        "busca" => "Pesquisa na Internet",
        "outro" => "Outro"
    ];
 
    //validações no servidor
    $erros = [];
 
    //validação do nome, para que tenha mais que 3 caracteres
    if (mb_strlen($nome) < 3){
        $erros[] = "O nome deve ter pelo menos três (3) cacateres.";
    } elseif (!preg_match("/^[\p{L}\s']+$/u", $nome)) {
        $erros[] = "Use apenas letras no nome.";  
    }
 
    // validação do e-mail
    // aqui vamos usar a função filter_var, ela já faz tudo, já é um validador pronto
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $erros[] = "Digite um e-mail válido";
    }
 
    //validação da idade, do lado do servidor
    // verificar se é número interiro e está entre 10 e 120
    $idadeInt = filter_var($idade, FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 10, "max_range" => 120]
    ]);
    if ($idadeInt === false) {
        $erros[] = "A idade deve ser um número inteiro entre 10 e 120.";
    }
 
     // verificando os esttados v´lidos
     if (!in_array($estado, $stadoValidos, true)){
       $erros[] = "Selecione um estado válido";
      
     }

     // Nota, tem que ser inteiro de 1 a 5 apenas

      $notaInt = filter_var($nota, FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 1, "max_range" => 5]
    ]);
    if ($notaInt === false) {
        $erros[] = "Escoha una nota de 1 e 5";
    }
 

    // vamo validar a origem - os checkbox
    if (count($origem) === 0) {
       $erros[] = "Marque pelo menos uma opção em 'Como você conheceu o curso'."; 
       } else {
         // agora vamos verificar se os campos marcados estão válidos no sevidor
         foreach($origem as $item) {
            if (!is_string($item) || !array_key_exists($item, $origensValidas)){
               $erros[] = "Opção de origem inválida.";
               break;
            
            }
         
       }

    }
   
   // comentário: deixamos ele como opcional - validar que tenha de 0 a 300 caracteres, além da questão que o usuário começar a digitar algo, deve terr pelo menos 10 caracteres
   // vou verificar se tem pelo menos 10 caracteres
   if ($comentario !== "") {
       if (mb_strlen($comentario) < 10) {
         $erros[] = "O comentário deve ter pelo menos 10 caracteres ou ficar em branco!";
     } elseif (mb_strlen($comentario) > 300) {
        $erros[] = "O comentário pode ter no máximo 300 caracteres";
     }
   }
    // vamos validar os termos, queprecisa se marcado
    if (!$termos ) {
      $erros[] = "Você precisa concordar com os termos para enviar.";
    }
     
    // SE TIVER ERROS, VAMOS SALVAR DOS DADOS
    // SE NÃO TIVER ERROS, VAMOS SALVAR OS DADOS
    if (count($erros) > 0) {
      // se entrou nesta condição, quer dizer que tem erros no formulário
      $titulo = "Corrija os campos abaixo";
      $tipo = "erro"; 
    } else {
      // se não tem erro no formulário, vamos gravar os dados em um arquivo CSV
      $arquivo = __DIR__ ."/respostas.csv";

      //Se ele não existir será criado
      $novoArquivo = !file_exists($arquivo);

      //Abrindo o arquivo e adicionar as colunas
      $f = fopen($arquivo, "a");     // "a" significa que vou adicionar no final do arquivo
      
      if($f) {

      // se o arquivo ainda não existir, criamos as colns dele
          If ($novoArquivo) {
            fputcsv($f, ["Data", "Nome", "E-mail", "Idade", "Estado", "Nota", "Origem", "Comentário"]);
          }

            // agora vamos inserir os dados dos campos no arquivo
            fputcsv($f, [
                date("d/m/Y H:i:s"), $nome, $email, $idade, $estado, $nota, implode(" | ", $origem), $comentario
                
         // tudo em amarelo é função do php
         
        ]);
        // fechando o rquivo
        fclose($f);
    
      }
    
       // agradeça ao usuário pelas respostas
       $titulo = "Obrigada pela sua resposta!";
       $tipo = "sucesso";

    }

?>

<!-- agora vamos construir o HTML de Finalização do nosso projeto de pesquisa -->

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Pesquisa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos adicionais, que são só desta páina */
        .resumo {list-style: nome; padding: 0; margin: 20px 0;}
        .resumo li{padding: 8px 0; border-bottom: 1px solid #e2e8f0;}
        .lista-erros {color: #e53e3e; padding-left: ;}
        .titulo-erro {color: #e53e; }
        .titulo-sucesso {color: 38a169; }
        .link-voltar {display: block; text-align: center; text-decoration: none;}
    </style>    
</head>
<body>
    <main> class="container">
     <?php if ($tipo === "erro"): ?>
        <h1> class="titulo-erro"> <?= e($titulo) ?> </h1>
        <ul class="lista-erros">
         <!-- Recuperando os erros do nosso vetor de errro $erro --> 
         <?php foreach ($erros as $erro): ?>
            <li> <?= e($erro) ?> </li>
            <?php andforeach; ?>

     </ul>

     <!-- volta para a tela de pesquisa, mas mantém o que o usuário já preencheu  --> 
     <a href="javascript:hstory.back()" class="botao link-voltar">
         Voltar e corrigir    
    </a>

 <?php else: ?>
        <h1 class="tiulo-sucesso"> <?= e($titulo) ?> </h1>
        <p> <?= e($nome) ?> sua resposta foi registrada com sucesso! </p>

        <ul> class="resumo">
            <li> <strong>E-mail: </strong> <?= e($email) ?> </li>
            <li> <strong>Idade: </strong> <?= (int) $idadeInt  ?>  anos </li>
            <li> <strong>Estado: </strong> <?= e($estado) ?> </li>
            <li> <strong>Nota para o curso: </strong>  <?= (int) $notaInt ?> de 5 </li>

            <!-- agora vamos tratar a origem -->
            <li><strong>Como conheceu: </strong>
            <?php
                $textos = [];
                foreach ($origem as $item) {
                    $textos[] = $origensValidas[$item];
                }
                echo e(implode(", ", $textos));

                ?>
          </li>
          
          <!-- agora vamos trabalhar o coentário -->
           <!-- verificando se o usuário digitou algo no comentário -->
          <?php if ($comentario !== ""): ?>
            <li>
                <strong>Comentário:</strong><br>
                <!-- vou mostrar o comentário conforme digitação do usuário -->
                 <?= nl2br(e($comentario)) ?>

          </li>     

       </ul>    
            
         <!-- Mostrar botão para o usuário responder o questionário novamente -->
          <a href="index.html" class="botao link-voltar">Responder Novamente</a>
 <?php endif; ?>    
    </main>    
    
</body>
</html>
