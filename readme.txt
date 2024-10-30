=== Plugin Name ===
Contributors: felipe152
Tags: woocommerce and Eduzz , woocommerce , Eduzz , woocommerce integration with eduzz ,woocommerce Order to eduzz
Requires at least: 4.0.1
Tested up to: 6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integração entre Eduzz e Woocommerce

== Description ==

Este plugin não é oficial e não possui qualquer vinculo com a Eduzz ou Woocommerce. Apenas foi feito com integrações disponibilizadas por ambos.
Este plugin permite pegar os pedidos feito pela plataforma Eduzz e converte-los em pedidos no seu woocommerce. Também possui a opção webhook que recebe automaticamene os pedidos da Eduzz e faz a conversão e webhook para conteúdo customizado que envia os dados de acesso por email.

== HOW IT WORKS ==

Este plugin utiliza a API oficial da Eduzz para poder fazer a integração. Se os seus produtos estiverem com o mesmo nome do que cadastrados no Eduz, eles serão importados nos pedidos automaticamente.

== Standard Features ==

- Configuração simples, basta cadastrar os dados de acesso que a Eduzz fornece;
- Veja a lista de pedidos feitos na Eduzz prontos para importar;
- Ative a opção webhook cadastrando no painel da Eduzz, a url fornecida pelo plugin para importar pedidos automaticamente;
- Em qualquer um dos modos, o cliente é cadastrado no Woocommerce caso já não tenha um registro;
- *Novo!* Área de opções, escolha se deseja desativar o cliente caso o pedido seja cancelado ou reembolsado

== Installation ==

1. Upload `wc-eduzz-powers` para a pasta `/wp-content/plugins/` 
2. Ative o plugin na área de "Plugins" do Wordpress
3. No painel do Eduzz gere a sua API Key e Chave Publica para usar no plugin. 

== Frequently Asked Questions ==

Se você tiver alguma dúvida, problema ou solicitação de mudança, pergunte-me no Fórum. Vou tentar o meu melhor para resolver o mais cedo possível. Se você gostou deste plugin, por favor deixe um comentário que será muito apreciado. Obrigado.

== Screenshots ==

1. Plugin admin área.


== Changelog ==
* 1.7.5
* Nova integração com Brazilian Market on WooCommerce para salvar o documento de cpf ou cnpj
* 1.7.0
* Novo Webhook para entrega de conteudo customizado
* 1.6.0
* Novas opções para definição de função para clientes importados
* Correção na validação do webhook
* 1.5.0
* Área de opções, escolha se deseja desativar o cliente caso o pedido seja cancelado ou reembolsado;
* 1.0
* first release;
