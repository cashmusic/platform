Quando você instala a plataforma pela primeira vez, o instalador cria e configura um banco de dados **SQLite** para você. 
SQLite significa que nós não precisamos saber qualquer informação do servidor do banco de dados e pode colocar qualquer um usando a plataforma de forma rápida. Mas em relação á performance, ele fica bem atrás do **MySQL** de acordo com que o banco de dados cresce. 

Se você está construindo coisas para produção, nós recomendamos que você migre para um banco de dados MySQL, e nós incluímos uma ferramenta de migração para que seja fácil como conhecer as configurações de seu sistema. Onde e como você consegue as suas
configurações é deferente de hospedagem para hospedagem, mas geralmente você vai achar informações sobre MySQL em qualquer
painel de administração do serviço de hospedagem que você fazer login. 

Você vai precisar: 

 - O endereço do servidor MySQL
 - um nome de usuário
 - uma senha
 - o nome de um banco de dados vazio que você possa usar

Uma vez que você conseguiu tudo, abra o CASH Admin, faça seu login, e deixe o cursor sobre o seu endereço de email abaixo do menu principal. Você verá uma opção "Configurações do Sistema". Clique nela, e na coluna à esquerda você verá
seu tipo de banco de dados atual, e se for SQLite você também verá um formulário que permite que você digite suas
configurações do servidor do banco de dados e migre. 