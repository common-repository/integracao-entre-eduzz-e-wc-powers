<?php
include_once($wep_path . "include/class-wep-eduzz.php");
class Wooeduzzpowers_Admin {

	private $api;

	function __construct() {

		$this->api = new Wooeduzzpowers_Eduzz;

		//Change Status order to refund for refund in eduzz
		add_action( 'woocommerce_order_status_refunded',  array( $this, 'wep_action_refunded' ), 10,1);

		//ajax
		add_action( 'wp_ajax_wep_conecta', array( $this, 'wep_conecta' ) );
		add_action( 'wp_ajax_wep_opcoes', array( $this, 'wep_opcoes' ) );
		add_action( 'wp_ajax_wep_importa_venda', array( $this, 'wep_importa_venda' ) );
		add_action( 'wp_ajax_wep_reembolsa_venda', array( $this, 'wep_reembolsa_venda' ) );

		add_action( 'admin_menu', array( $this, 'wep_settings_add_menu' ) );
		
	}

	function wep_settings_add_menu (){
		add_menu_page( 'Woo & Eduzz Powers', 'Pedidos Eduzz', 'manage_options', 'wooeduzzpowers', array( $this, 'wep_settings_startview' ), 'dashicons-download');		
	}

	function wep_erro_view (){
?>
	    <div class="notice notice-error is-dismissible">
	    	<h3>Woocommerce não encontrado</h3>
	    	<p>Para utilizar este plugin é necessário que o woocommerce esteja instalado.</p>
	    </div>
<?php
	}

	function wep_settings_startview (){
		if (!is_plugin_active('woocommerce/woocommerce.php')){
			$this->wep_erro_view();
			return false;
		}

		wp_enqueue_script(  'wep-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'), rand(0,1000), true );
		wp_enqueue_style( 'wep-style', plugin_dir_url( __FILE__ ) . 'css/style.css', '', rand(0,1000), false );
		$access = $this->api->getAccess();
		$opcoes = get_option( 'wep_options', '' );
		if (!empty($opcoes)) {
			$opcoes = json_decode($opcoes,true);
		} else{
			$opcoes = array();	
		}
		

		global $wp_roles;
   		$all_roles = $wp_roles->roles;
    	$editable_roles = apply_filters('editable_roles', $all_roles);
		$opcoes['funcao-aprovado'] = !isset($opcoes['funcao-aprovado']) ? 'customer' : $opcoes['funcao-aprovado'];
		$opcoes['funcao-reprovado'] = !isset($opcoes['funcao-reprovado']) ? 'subscriber' : $opcoes['funcao-reprovado']; 

		//Montagem da lista
		$orders = wc_get_orders(array(
		    'type'=> 'shop_order',
		    'meta_key' => 'wep_eduzz_saleid',
		    'meta_compare' => '!=',
		    'meta_value' =>  ''
	    ));
	    $importados = array();
	    foreach ($orders as $order) {
	    	$saleID = get_post_meta($order->id,'wep_eduzz_saleid', true);
	    	if (!empty($saleID)) {
	    		$importados[$saleID] = $order->id;
	    	}
	    }
	    
		$argsFilter = array(
 			'start_date' => date("Y-m-d", strtotime("-1 day ")),
 			'end_date' => date("Y-m-d"),
 			'page' => '1',
 			'date_type' => 'payment'
 		);

 		if (isset($_POST['dateini']) and isset($_POST['datefim'])) {
 			$args = array(
	 			'start_date' => sanitize_text_field( trim($_POST['dateini']) ),
	 			'end_date' => sanitize_text_field( trim($_POST['datefim']) ),
	 			'date_type' => 'payment'
	 		);
	 		$argsFilter = array_merge($argsFilter, $args);
 		}
		$ultimasVendas = $this->getSaleList($argsFilter);
		if ($ultimasVendas == false) {
			$ultimasVendas = array();
		}
		require_once plugin_dir_path(dirname(__FILE__)).'admin/views/wep-settings-startview.php';

	}

	function wep_conecta (){

		if (!isset($_POST) or empty($_POST['wep_email']) or empty($_POST['wep_apikey']) or empty($_POST['wep_key'])) { 
			echo 'false'; exit();
		}
		$access = array(
			'email' => sanitize_text_field( trim($_POST['wep_email']) ), 
			'publickey' => sanitize_text_field( trim($_POST['wep_key']) ), 
			'apikey' => sanitize_text_field( trim($_POST['wep_apikey']) ), 
		);

		$resp = $this->api->saveAccess($access);
		

		if ($resp) {
		 	echo "true";
		} else{
			echo 'false';		
		}
		exit();
	}

	function wep_opcoes (){

		$dados = array();
		if (isset($_POST['dados'])) {
			parse_str($_POST['dados'],$dados);
			foreach ($dados as $key => $value) {
				$dados[$key] = sanitize_text_field( trim($value) );
			}			
		}
		$dados = json_encode($dados);
		$up = update_option('wep_options', $dados,FALSE);
		if (!$up) {
			$up = add_option('wep_options', $dados);
		}

		exit();
	}

	function wep_importa_venda (){
		if (!isset($_POST) or empty($_POST['wep_sale_id'])) { 
			echo 'false'; exit();
		}
		$saleId = sanitize_text_field( trim($_POST['wep_sale_id']) );
		$sale = $this->api->getSaleByID($saleId);

		$opcoes = get_option( 'wep_options', array() );
		if (!empty($opcoes)) {
			$opcoes = json_decode($opcoes,true);
		}
		$opcoes['funcao-aprovado'] = !isset($opcoes['funcao-aprovado']) ? 'customer' : $opcoes['funcao-aprovado'];


		$orders = wc_get_orders(array(
		    'type'=> 'shop_order',
		    'meta_key' => 'wep_eduzz_saleid',
		    'meta_compare' => '==',
		    'meta_value' =>  $saleId
	    ));

	    if (!empty($orders)) { exit('false'); }


	    $order = wc_create_order();
		$newOrderId = $order->get_id();

	    $clienteNome  = $sale->client_name;
	    $clienteNome = explode(' ', $clienteNome);
	    $primeiroNome = $clienteNome[0];
	    unset($clienteNome[0]);
	    $sobrenome = implode(' ', $clienteNome);


		$address = array(
		  'first_name' => $primeiroNome,
		  'last_name'  => $sobrenome,
		  'company'    => '',
		  'email'      => $sale->client_email,
		  'phone'      => $sale->client_telephone,
		  'address_1'  => $sale->client_street,
		  'address_2'  => '',
		  'city'       => $sale->client_city,
		  'state'      => $sale->client_district,
		  'postcode'   => $sale->client_zip_code,
		  'country'    => $sale->client_street
		);

		//Adicionar usuario
	    $user = get_user_by( 'email', $sale->client_email );
  		if ($user!=false) {
  			$address['first_name'] = $user->first_name;
  			$address['last_name'] = $user->last_name;
  			$user_id = $user->ID;
  		} else{
  			$random_password = wp_generate_password( 8, false );
  			$user_name = explode('@', $sale->client_email);
  			$user_name = $user_name[0].date("is");
  			$user_id = wp_create_user( $user_name, $random_password, $sale->client_email );
  			if( !is_wp_error($user_id) ) {
  				$user = get_user_by( 'id', $user_id );  			
	  			$user->add_role( 'subscriber' );
	  			wp_update_user([
				    'ID' => $user_id, // this is the ID of the user you want to update.
				    'first_name' => $primeiroNome,
				    'last_name' => $sobrenome,
				]);
				wp_new_user_notification($user_id, null, 'user');
  			}	
  		}
  		if (!is_wp_error($user_id)) {
  			$user->add_role( $opcoes['funcao-aprovado'] );
  		}
		$order->set_customer_id( $user_id );
  		$order->set_address( $address, 'billing' );
  		
  		//Produtos
		if (!empty($sale->content_title)) {
			$produto = get_page_by_title($sale->content_title,  OBJECT, 'product'); 
			if (!empty($produto)){
				$order->add_product( get_product($produto->ID), 1);
			}
  		}

  		$order->calculate_totals();
  		$order->update_status("completed", 'Pedido Importado do Eduzz', TRUE);  

  		$up = update_post_meta($newOrderId, 'wep_eduzz_saleid', $saleId);
		if (!$up) {
			$up = add_post_meta($newOrderId, 'wep_eduzz_saleid', $saleId);
		}

		exit('true');
	}

	function wep_action_refunded($order_id){
		$saleId = get_post_meta($order_id,'wep_eduzz_saleid', true);
		if (!empty($saleId)) {
			$refund = $this->api->refundSale($saleId);
			if ($refund !== false) {
				return true;
			}
		}
		return false;
	}

	function wep_reembolsa_venda (){
		if (!isset($_POST) or empty($_POST['wep_sale_id'])) { 
			exit('false');
		}
		$opcoes = get_option( 'wep_options', array() );
		if (!empty($opcoes)) {
			$opcoes = json_decode($opcoes,true);
		}
		$opcoes['funcao-reprovado'] = !isset($opcoes['funcao-reprovado']) ? 'subscriber' : $opcoes['funcao-reprovado'];

		$saleId = sanitize_text_field( trim($_POST['wep_sale_id']) );
		$refund = $this->api->refundSale($saleId);

		if (!$refund) {
			exit('false');
		}
		$orders = wc_get_orders(array(
		    'type'=> 'shop_order',
		    'meta_key' => 'wep_eduzz_saleid',
		    'meta_compare' => '==',
		    'meta_value' =>  $saleId
	    ));

	    if (!empty($orders)) { 
	    	foreach ($orders as $order) {
	    		$order->update_status("refunded", 'Pedido do Eduzz reembolsado ', TRUE);
	    		if (isset($opcoes['desativar-user'])) {   			
		    		$user =  $order->get_user();
		    		foreach ($user->roles as $role) {
		    			$user->remove_role( $role );
		    		}
	    		} else{
	    			$user->add_role( $opcoes['funcao-reprovado'] );
	    		}
	    	}
	    }  
		exit('true');
	}


	public function getSaleList($args){
 		$defaultArgs = array(
 			'start_date' => date("Y-m-d", strtotime("-1 day ")),
 			'end_date' => date("Y-m-d"),
 			'page' => '1',
 			'date_type' => 'payment'
 		);
 		$args = array_merge($defaultArgs, $args);
 		$resp = $this->api->getSaleList($args);
 		if ($resp === FALSE) {
 			return false;
 		}
 		return $resp;
 	}


}