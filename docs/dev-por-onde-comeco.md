---
id: dev-por-onde-comeco
title: Por onde eu começo?
sidebar_label: Por onde começo?
---

> ATENÇÃO: Essa forma de instação tem o objetivo de facilitar demonstrações e
> desenvolvimento. Não é recomendado para ambientes de produção!

Antes de começar você vai precisar instalar o Docker e o Docker Compose em sua
máquina. Para mais informações veja estes links:

- [Docker](https://docs.docker.com/install/) (> 18.03.1-ce)
- [Docker Compose](https://docs.docker.com/compose/install/) (> 1.21.2)

Você também vai precisar do [Git](https://git-scm.com/downloads) caso ainda não
o tenha instalado.

Depois de ter o Docker e git instalados faça o clone deste repositório e execute
o Docker Compose para criar os containers da aplicação:

```terminal
$ git clone https://github.com/portabilis/i-educar.git i-educar
$ cd i-educar
$ cp .env.example .env
$ docker-compose up -d
```

Depois disto faça uma cópia do arquivo `ieducar/configuration/ieducar.ini.sample`
para `ieducar/configuration/ieducar.ini` realizando as alterações necessárias.

## Instalando relatórios

Os relatórios respondem por uma parte muito importante no i-Educar mas o
desenvolvimento destes relatórios ocorre de forma paralela em outro repositório.
Por isso, antes de prosseguir, é necessário "instalar" os relatórios em conjunto
com o i-Educar. Execute o seguinte comando a partir da pasta onde o i-Educar foi
clonado em sua máquina:

```terminal
$ git clone https://github.com/portabilis/i-educar-reports-package.git ieducar/modules/Reports
```

P.S.: Esses relatórios são legados e podem não funcionar. Em breve vamos lançar
um pacote de mais de 40 relatórios funcionais.

## Instalando outras dependências

O i-Educar usa o [Composer](https://getcomposer.org/) para gerenciar suas
dependências. O Composer já vem pré-instalado na imagem via Docker então para
instalar as dependências use os seguintes comandos:

```terminal
$ docker-compose exec ieducar_1604 composer install
```

## Inicializando o banco de dados

O próximo passo é inicializar o banco de dados do i-Educar. Nós utilizamos o
[Phinx](https://phinx.org/) para executar migrações e preencher os dados em
nosso banco. O Phinx já é instalado como dependência através do Composer no
passo anterior, mas é necessário configurá-lo antes de executar qualquer
comando.

Na raiz do projeto você encontra um arquivo chamado `phinx.php.sample`. Copie
este arquivo e altere seu nome para `phinx.php`. Verifique seu conteúdo e,
caso tenha feito alguma mudança na configuração do Docker, modifique as
credenciais do banco de acordo com suas alterações. Caso contrário o arquivo
estará pronto para ser utilizado.

**Atenção:**

Se quiser rodar o Phinx a partir de sua própria máquina, fora de um container,
modifique a chave `host` para `localhost` e `port` para `5434`.

Depois de ter feito a configuração do Phinx, basta rodar os seguintes comandos:

```terminal
$ docker-compose exec ieducar_1604 ieducar/vendor/bin/phinx seed:run -s StartingSeed -s StartingForeignKeysSeed
$ docker-compose exec ieducar_1604 ieducar/vendor/bin/phinx migrate
```

Este comando irá executar a criação de tabelas e inserção de dados iniciais
para utilização do i-Educar.

## Configurando permissões

Para que tudo funcione adequadamente, principalmente a parte de relatórios, é
necessário definir algumas permissões especiais em pastas e arquivos. Use os
comandos abaixo:

```terminal
$ docker-compose exec ieducar_1604 chmod +x ieducar/vendor/portabilis/jasperphp/src/JasperStarter/bin/jasperstarter
$ docker-compose exec ieducar_1604 chmod 777 -R ieducar/modules/Reports/ReportSources/Portabilis
```

## Primeiro acesso

Após realizar a instalação de acordo com as instruções acima você está pronta a
realizar seu primeiro acesso ao i-Educar. Basta acessar o seguinte endereço:

[http://localhost:8001](http://localhost:8001)

O usuário padrão é: `admin` / A senha padrão é: `123456789`

Assim que realizar seu primeiro acesso **não se esqueça de alterar a senha padrão**.

### Utilização do Xdebug

A ferramenta [Xdebug](https://xdebug.org/) está incluída no projeto com o 
intuito de facilitar o processo de debug durante o desenvolvimento. Para 
configurá-la, modifique os valores das variáveis `XDEBUG_*` no arquivo `.env` 
conforme orientações da sua IDE de desenvolvimento.