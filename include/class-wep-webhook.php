<?php
include_once(plugin_dir_path(__FILE__) . "class-wep-eduzz.php");
class Wooeduzzpowers_Webhook{

	private $api;

	function __construct($dados, $origem = 'pedido') {
		$this->api = new Wooeduzzpowers_Eduzz;
		if ($origem=='produto') {
			$this->wep_dadosProdutoWebhook($dados);
		} else {
			$this->wep_dadosWebhook($dados);
		}
	}
	public function wep_dadosProdutoWebhook($dados){
		$fields = [
		    'edz_fat_cod' => $dados['edz_fat_cod'],
		    'edz_cnt_cod' => $dados['edz_cnt_cod'],
		    'edz_cli_cod' => $dados['edz_cli_cod'],
		    'edz_cli_taxnumber' => $dados['edz_cli_taxnumber'],
		    'edz_cli_rsocial' => $dados['edz_cli_rsocial'],
		    'edz_cli_email' => $dados['edz_cli_email'],
		    'edz_fat_dtcadastro' => $dados['edz_fat_dtcadastro'],
		    'edz_cli_cel' => $dados['edz_cli_cel'],
		    'edz_gtr_dist' => $dados['edz_gtr_dist'],
		    'edz_fat_status' => $dados['edz_fat_status'],
		    'edz_cli_apikey' => $dados['edz_cli_apikey'],
		    'edz_valorpago' => $dados['edz_valorpago'],
		    'edz_gtr_param1' => $dados['edz_gtr_param1'],
		    'edz_gtr_param2' => $dados['edz_gtr_param2'],
		    'edz_gtr_param3' => $dados['edz_gtr_param3'],
		    'edz_gtr_param4' => $dados['edz_gtr_param4'],
		];
		ksort($fields);
		$stringSid = "";
		foreach ($fields as $key => $value) {
		    $stringSid .= $value;
		}
		$access = $this->api->getAccess();
		$sid = md5($stringSid . $access['apikey']);

		$opcoes = get_option( 'wep_options', array() );
		if (!empty($opcoes)) {
			$opcoes = json_decode($opcoes,true);
		}
		$user = get_user_by( 'email', $dados['edz_cli_email'] );
		if ($user!=false) {
  			$user_id = $user->ID;
  		} else{
  			if ($dados['type']!='create') {
  				exit('Usuario não encontrado. type:'.$dados['type']);
  			}
  			$random_password = wp_generate_password( 8, false );
  			$user_name = explode('@', $dados['edz_cli_email']);
  			$user_name = $user_name[0].date("is");
  			$user_id = wp_create_user( $user_name, $random_password, $dados['edz_cli_email'] );			
  			if( !is_wp_error($user_id) ) {
  				$user = get_user_by( 'id', $user_id );
  				foreach ($user->roles as $role) {
	    			$user->remove_role( $role );
	    		}
  				$user->add_role( $opcoes['funcao-aprovado'] );
  			}	
  		}
		wp_new_user_notification($user_id, null, 'user');
		exit('enviado');
	}

	public function wep_dadosWebhook($dados){

 		foreach ($dados as $key => $value)	{
			if (is_array($value)) {
				$$key =  $value ;
			} else {
				$$key = sanitize_text_field( trim($value) );
			}
		}
		if (!isset($api_key)) {
			$api_key = isset($pro_cod) ? $pro_cod : '' ;
		}
		if (!isset($pro_cod)) {
			$pro_cod = isset($api_key) ? $api_key : '' ;
		}
		
		$access = $this->api->getAccess();

		if( $pro_cod != $access['publickey'] and $api_key != 'testWebhook' ){
			exit($pro_cod.' api_key ou pro_cod incorretos.('.$access['publickey'].')');
		}
		if ($api_key == 'testWebhook') {
			$dados['cus_email'] = rand(100,999).$dados['cus_email'];
			$dados['trans_cod'] = rand(1,999);
		}
		$opcoes = get_option( 'wep_options', array() );
		if (!empty($opcoes)) {
			$opcoes = json_decode($opcoes,true);
		}
		$opcoes['funcao-aprovado'] = !isset($opcoes['funcao-aprovado']) ? 'customer' : $opcoes['funcao-aprovado'];
		$opcoes['funcao-reprovado'] = !isset($opcoes['funcao-reprovado']) ? 'subscriber' : $opcoes['funcao-reprovado'];


		$isAssinatura = false;
		if (is_array($trans_items)) {
			foreach ($trans_items as $i) {
				if ($i['item_product_chargetype']=='A') {
					$isAssinatura = true;
				}
			}
		}
		
		$dados['isAssinatura'] = $isAssinatura;
		switch ($trans_status){
			case '3' :
				#Pagou
				$this->wep_importa_venda($dados);
				break;
			case '6':   #Aguardando reembolso
			case '7':   #Reembolsado
				$this->wep_reembolsa_venda($dados);
				break;
		}
		if ($isAssinatura) {
			$user = get_user_by( 'email', $cus_email );
			if ($user!=false) {
				foreach ($user->roles as $role) {
	    			$user->remove_role( $role );
	    		}
				switch ($recurrence_status) {
					case '3':
					case '4':
					case '7':
						$user->add_role( $opcoes['funcao-reprovado'] );
						break;
					case '1':
					case '9':
					case '10':
						$user->add_role( $opcoes['funcao-aprovado'] );
						break;
				}
			}
		}
 	}


	function wep_importa_venda ($dados){
		if (!isset($dados) or empty($dados['trans_cod'])) { 
			exit();
		}

		$opcoes = get_option( 'wep_options', array() );
		if (!empty($opcoes)) {
			$opcoes = json_decode($opcoes,true);
		}
		$opcoes['funcao-aprovado'] = !isset($opcoes['funcao-aprovado']) ? 'customer' : $opcoes['funcao-aprovado'];
		
		foreach ($dados as $key => $value)	{
			if (is_array($value)) {
				$$key =  $value ;
			} else {
				$$key = sanitize_text_field( trim($value) );
			}
		}


		$orders = wc_get_orders(array(
		    'type'=> 'shop_order',
		    'meta_key' => 'wep_eduzz_saleid',
		    'meta_compare' => '==',
		    'meta_value' =>  $trans_cod
	    ));

	    if (!empty($orders)) { 
	    	if ($dados['isAssinatura']) {
	    		return;
	    	}
	    	exit('Pedido já existe.');
	    }


	    $order = wc_create_order();
		$newOrderId = $order->get_id();

	    $clienteNome  = $cus_name;
	    $clienteNome = explode(' ', $cus_name);
	    $primeiroNome = $clienteNome[0];
	    unset($clienteNome[0]);
	    $sobrenome = implode(' ', $clienteNome);


		$address = array(
		  'first_name' => $primeiroNome,
		  'last_name'  => $sobrenome,
		  'company'    => '',
		  'email'      => $cus_email,
		  'phone'      => $cus_cel,
		  'address_1'  => $cus_address,
		  'address_2'  => $cus_address_number,
		  'city'       => $cus_address_city,
		  'state'      => $cus_address_state,
		  'postcode'   => $cus_address_zip_code,
		  'country'    => $cus_address_country
		);

		if (isset($pro_document_number)) {
			if (strlen($pro_document_number) <= 11 ) {
				$address['persontype'] = 1;
				$address['cpf'] = $pro_document_number;
			}else{
				$address['persontype'] = 2;
				$address['cnpj'] = $pro_document_number;
			}
		}



		//Adicionar usuario
	    $user = get_user_by( 'email', $cus_email );
  		if ($user!=false) {
  			$address['first_name'] = $user->first_name;
  			$address['last_name'] = $user->last_name;
  			$user_id = $user->ID;
  		} else{
  			$random_password = wp_generate_password( 8, false );
  			$user_name = explode('@', $cus_email);
  			$user_name = $user_name[0].date("is");
  			$user_id = wp_create_user( $user_name, $random_password, $cus_email );
  			if( !is_wp_error($user_id) ) {
  				$user = get_user_by( 'id', $user_id );  			
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
		if (!empty($trans_items)) {			
  			foreach ($trans_items as $key => $protudo_items) {
  				$produto = get_page_by_title($protudo_items['item_name'],  OBJECT, 'product'); 
  				if (!empty($produto)){
					$order->add_product( get_product($produto->ID), 1);
				}
  			}
  		}

  		$order->calculate_totals();
  		$order->update_status("completed", 'Pedido Importado do Eduzz', TRUE);  

  		$up = update_post_meta($newOrderId, 'wep_eduzz_saleid', $trans_cod);
		if (!$up) {
			$up = add_post_meta($newOrderId, 'wep_eduzz_saleid', $trans_cod);
		}
		$up = update_post_meta($newOrderId, 'wmp_eduzz_dados', $dados);

		exit();
	}


	function wep_reembolsa_venda ($dados){
		if (!isset($dados) or empty($dados['trans_cod'])) { 
			exit('Dados para reembolso inválidos');
		}
		$opcoes = get_option( 'wep_options', array() );
		if (!empty($opcoes)) {
			$opcoes = json_decode($opcoes,true);
		}
		$opcoes['funcao-reprovado'] = !isset($opcoes['funcao-reprovado']) ? 'subscriber' : $opcoes['funcao-reprovado'];
		foreach ($dados as $key => $value)	{
			if (is_array($value)) {
				$$key =  $value ;
			} else {
				$$key = sanitize_text_field( trim($value) );
			}
		}


		$orders = wc_get_orders(array(
		    'type'=> 'shop_order',
		    'meta_key' => 'wep_eduzz_saleid',
		    'meta_compare' => '==',
		    'meta_value' =>  $trans_cod
	    ));

	    if (!empty($orders)) { 
	    	foreach ($orders as $order) {
	    		$order->update_status("refunded", 'Pedido do Eduzz reembolsado ', TRUE);
		    	$user =  $order->get_user();
	    		if (isset($opcoes['desativar-user'])) {   			
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

}