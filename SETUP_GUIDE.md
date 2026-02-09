# Guia de Configuração do Ambiente (Windows)

O erro que você encontrou indica que o **PHP** e o **MySQL** não estão instalados ou configurados no seu computador. Para rodar este projeto, a forma mais fácil é instalar o **XAMPP**.

## 1. Instalando o XAMPP
O XAMPP é um pacote que já vem com Apache (Servidor), MySQL (Banco de Dados) e PHP.

1. Baixe o XAMPP para Windows: [https://www.apachefriends.org/pt_br/index.html](https://www.apachefriends.org/pt_br/index.html)
2. Instale o programa (pode aceitar as opções padrão).
3. Após instalar, abra o **XAMPP Control Panel**.
4. Inicie os serviços **Apache** e **MySQL** clicando no botão "Start" ao lado de cada um.

## 2. Configurando o Projeto no XAMPP
O XAMPP usa a pasta `C:\xampp\htdocs` como raiz do servidor.

1. Mova a pasta `CRM-ANTIGRAVITY` para dentro de `C:\xampp\htdocs`.
   - O caminho ficará: `C:\xampp\htdocs\CRM-ANTIGRAVITY`

## 3. Configurando o Banco de Dados
1. Com o MySQL rodando no XAMPP, abra o navegador e acesse: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Clique em "Novo" na barra lateral esquerda.
3. Crie um banco de dados chamado `crm_antigravity`.
4. Clique na aba "Importar".
5. Selecione o arquivo `database.sql` que está dentro da pasta do projeto (`C:\xampp\htdocs\CRM-ANTIGRAVITY\database.sql`).
6. Clique em "Executar" no final da página.

## 4. Rodando o Projeto
Agora você não precisa usar o terminal para rodar `php -S`. O XAMPP já faz isso.

1. Abra seu navegador.
2. Acesse: [http://localhost/CRM-ANTIGRAVITY](http://localhost/CRM-ANTIGRAVITY)

E pronto! O sistema deve estar funcionando.

## Nota sobre a API Key
Lembre-se que você editou o arquivo `config.php`. Verifique se a chave da API do Gemini (`AIza...`) está correta e salva.
