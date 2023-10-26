## O que é
IDMail é um hack que permite trabalhar com a interface do id-admin da USP sem precisar de um navegador.

Ele dispõe por ora de um módulo de login e de uma interface de consulta de email, pode ser estendido para trabalhar com os JSONs oriundos de lá sem muito trabalho.

## O que ele fornece
  * uma busca pela conta de email pessoal mais recente do usuário via `find_email()`;
  * um método para gerenciamento de membros via `members()`;
  * uma lista de emails não-pessoais de um usuário via `find_lists()`.

## Como usar

    composer require uspdev/idmail

## Execução via CLI
Ajustar o .env e copiar o `idmail.php` de `vendor/uspdev/idmail`.

Rodar `php idmail.php <MODO> <COMPLEMENTOS>` onde o modo pode ser:
  * `list <NUSP>`: devolve os emails não-pessoais dado um NUSP;
  * `add/remove <endereço da lista> <txt com lista de emails>`: adiciona ou remove os emails, vindos do txt, de uma dada lista;
  * `default <NUSP>`: devolve o email pessoal mais recente dado um NUSP.

## Dependências
  * Dotenv (só para execução via CLI);
  * GuzzleHttp.
