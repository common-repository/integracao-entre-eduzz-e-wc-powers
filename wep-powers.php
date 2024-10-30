<?php 
/**
 * Plugin Name: Integração entre Eduzz e Woocommerce
 * Plugin URI:	
 * Description:	Cria gatilhos para interação com o Woocommerce e eduzz
 * Version:		1.7.5
 * Author:		Felipe Peixoto
 * Author URI:	http://felipepeixoto.tecnologia.ws/
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}
$wep_path = plugin_dir_path(__FILE__);
if (is_admin()){
	require plugin_dir_path( __FILE__ ) . 'admin/class-wep-admin.php';
	$settings = new Wooeduzzpowers_Admin();
}

if ( (isset($_GET['wooeduzzpowers']) or isset($_GET['wooeduzzpowers-produto'])) and isset($_POST)) {
	add_action( 'init', 'wep_action_eduzz_webhook', 10, 0 ); 
}


function wep_action_eduzz_webhook () {
	if (empty($_POST)) {
		exit('dados vazio.');
	}
	$origem = 'pedido';
	if (isset($_GET['wooeduzzpowers-produto'])) {
		$origem = 'produto';
	}
	$wep_path = plugin_dir_path(__FILE__);
	include_once($wep_path . "include/class-wep-webhook.php");
	$eduzz = new Wooeduzzpowers_Webhook($_POST, $origem);
	exit();
}
register_activation_hook( __FILE__, 'wep_active_plugin' );
function wep_active_plugin() {
	add_action( 'admin_notices', function(){
		?>
	    <div class="notice notice-success is-dismissible">
	    	<p><b>Integração entre Eduzz e Woocommerce:</b></p>
	        <p>Obrigado por baixar e instalar este plugin! Precisa de um desenvolvedor Wordpress para o seu negócio ? <a target="_blank" href="http://felipepeixoto.tecnologia.ws/">Entre em contato</a>.</p>
	    </div>
	    <?php
	});
}


?>