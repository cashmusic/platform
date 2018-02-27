Quando instalas a plataforma pela primeira vez, o instalador cria e configura uma base de dados **SQLite** para ti. 
SQLite significa que nós não precisamos de saber qualquer informação do servidor da base de dados e podes colocar qualquer um usando a plataforma de forma rápida. Mas em relação á performance, ele fica bem atrás do **MySQL** de acordo com a base de dados cresce. 

Se estás a construir coisas para a produção, nós recomendamos que migres para uma base de dados MySQL, e nós incluímos uma ferramenta de migração para que seja fácil como conhecer as configurações do teu sistema. Onde e como consegues as tuas
configurações é diferente de hospedagem para hospedagem, mas geralmente tu vais encontrar informações sobre MySQL em qualquer
painel de administração do serviço de hospedagem que tu fizeres login. 

Vais precisar: 

 - O endereço do servidor MySQL
 - um nome de utilizador
 - uma senha
 - o nome de uma base de dados vazia que tu possas usar

Depois de conseguires isto tudo, abre o CASH Admin, faz o teu login, e deixa o cursor sobre o seu endereço de email abaixo do menu principal. Verás uma opção "Configurações do Sistema". Clica nela, e na coluna à esquerda verás o teu tipo de base de dados atual, e se for SQLite também podes ver um formulário que permite que digites as tuas configurações do servidor da base de dados e migra. 
