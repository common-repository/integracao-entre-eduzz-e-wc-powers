<style>
div.wep-updated {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-left-width: 4px;
	border-left-color: #00a32a; 
    box-shadow: 0 1px 1px rgb(0 0 0 / 4%);
    padding: 1px 12px;
    display: flex; 
    justify-content: left;
    align-items: center;
}
.wep-updated p {
    font-size: 16px;
}
</style>
<div class="wrap metabox-holder wooeduzz">
	<h2>Integração entre Eduzz e Woocommerce</h2>
	<div class="row postbox">
		<form id="wep-form-token" action="#">
			<div class="col m3 s12">
				<p>Códigos de acesso do Eduzz: <br />
				</p>
			</div>	
			<div class="col m9 s12">
				<p id="tokenInput">
					<input size="30" autocomplete="no" id="email-input" placeholder="Email" name="email" value="<?php echo $access['email']; ?>" type="email">
					<input size="20" autocomplete="no" id="key-input" placeholder="Public KEY" name="key" value="<?php echo $access['publickey'];  ?>" type="text">  
					<input autocomplete="no" id="apikey-input" placeholder="Api KEY" name="apikey" value="<?php echo $access['apikey']; ?>" type="password">
					<button class="button button-primary" type="submit" value="Testar">Testar Acesso</button>
					<a style="text-decoration: none;" target="_blank" href="https://ajuda.eduzz.com/?article=a-eduzz-tem-api-para-integracao"><span class="dashicons dashicons-editor-help"></span></a>
				</p>				
			</div>	
		</form>
		<div class="col m3 s12">
			<p>URL de Webhook :</p>
		</div>
		<div class="col m9 s12">
			<input value="<?php echo get_site_url(); ?>/?wooeduzzpowers=1" size="50" type="text" disabled="disabled"><a style="text-decoration: none;" target="_blank" href="https://ajuda.eduzz.com/?article=o-que-e-e-como-usar-webhook"><span class="dashicons dashicons-editor-help"></span></a>
		</div>
		<br style="clear: both;">
		<div class="col m3 s12">
			<p>URL de Webhook de Produto:</p>
		</div>
		<div class="col m9 s12">
			<input value="<?php echo get_site_url(); ?>/?wooeduzzpowers-produto=1" size="50" type="text" disabled="disabled"><a style="text-decoration: none;" target="_blank" href="https://ajuda.eduzz.com/hc/pt-br/articles/4410578011547-Como-cadastrar-a-entrega-de-um-conte%C3%BAdo-customizado-"><span class="dashicons dashicons-editor-help"></span></a>
		</div>
		<br style="clear: both;">
		<form action="#"  id="wep-form-options" method="post">
			<div class="col m3 s12">
				<p><b>Opções</b> :</p>
			</div>
			<div class="col m7 s12">
				<div>
					<label>Função para usuarios com pedidos/assinaturas aprovados: 
					<select name="funcao-aprovado" id="">
						<?php foreach ($editable_roles as $k => $roles): ?>
						<option value="<?php echo $k; ?>" <?php echo $opcoes['funcao-aprovado']==$k ? 'selected' : ''; ?> ><?php echo $roles['name']; ?></option>
						<?php endforeach ?>
					</select>
					</label>
				</div>
				<div>
					<label>Função para usuarios com pedidos/assinaturas cancelados ou reembolsados: 
					<select name="funcao-reprovado" id="">
						<?php foreach ($editable_roles as $k => $roles): ?>
						<option value="<?php echo $k; ?>" <?php echo $opcoes['funcao-reprovado']==$k ? 'selected' : ''; ?>><?php echo $roles['name']; ?></option>
						<?php endforeach ?>
					</select>
					</label>
				</div>
				<div>
					<label>
						<input name="desativar-user" type="checkbox" <?php echo isset($opcoes['desativar-user']) ? 'checked="checked"' : '' ?>>
					Desativar usuário que pedir reembolso</label> <br /><br />
				</div>
			</div>
			<div class="col m2 s12">
				<p id="optionsInput"><button class="button button-primary" type="submit" value="opcoes">Salvar Opções</button></p>
			</div>
		</form>
	</div>
	<div class="row postbox">
		
			
			<h2 class="hndle"><span>Pedidos do Eduzz</span></h2>
			<div id="painel" class="col s12">
				<p>Para que os produtos sejam relacionados corretamente nos pedidos importados, é necessário que estejam com o mesmo nome.</p>
				<form action="" method="post">
					<fieldset>
						<legend>Filtro:</legend>
						<label for="">Data Inicial: <input name="dateini" value="<?php echo $argsFilter['start_date']; ?>" type="date" required="required" max="<?php echo date("Y-m-d"); ?>"></label>
						<label for="">Data Final: <input name="datefim" value="<?php echo $argsFilter['end_date']; ?>" type="date" required="required" max="<?php echo date("Y-m-d"); ?>"></label>
						<input type="submit" value="Filtrar">
					</fieldset>
				</form>
				<table id="wep-tabela-acoes" class=" widefat fixed striped margin-top-bottom15">
					<thead>
						<tr>
							<th>#</th>
							<th>Cliente</th>
							<th>Data</th>
							<th>Total</th>
							<th>Status</th>
							<th>Ação</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($ultimasVendas as $vendas): ?>
						<tr>
							<td><?php echo $vendas->sale_id; ?></td>
							<td>
								<?php echo $vendas->client_name; ?> <br />
								<?php echo $vendas->client_email; ?>
								
							</td>
							<td><?php echo date("d/m/Y" , strtotime($vendas->date_create)); ?></td>
							<td><?php echo 'R$ '.$vendas->sale_total; ?></td>
							<td><?php echo $vendas->sale_status_name; ?></td>
							<td>
							<?php if (!isset($importados[$vendas->sale_id])): ?>
							<a class="action-importar" data-saleid="<?php echo $vendas->sale_id; ?>" href="#">Importar</span></a>
							<?php else: ?>
							Importado #<?php echo $importados[$vendas->sale_id]; ?>
							<?php endif ?>

							<?php if ($vendas->sale_status_name == 'Paga'): ?>
							 | <a class="action-reembolso" data-saleid="<?php echo $vendas->sale_id; ?>" href="#">Reembolsar</span></a>
							<?php endif ?>

							</td>
						</tr>
						<?php endforeach ?>
					</tbody>
				</table>
				<?php if (empty($ultimasVendas)): ?>
					<p style="text-align: center;">Nenhum pedido encontrado com este filtro.</p>
				<?php endif ?>
			</div>	
			
		
	</div>
	<div class="footer">
		<div class="wep-updated"> 
	        <div class="notice-image">
	        	<a href="https://powerfulautochat.com.br/" target="_blank">
	            <img style="max-width: 90px;" src="https://ps.w.org/powers-triggers-of-woo-to-chat/assets/icon-128x128.png?rev=2460034" alt="Powerful Auto Chat" >
	        	</a>
	        </div>
	        <div class="notice-content" style="margin-left: 15px;">
	            <p>
	            	Já imaginou o seu cliente receber uma mensagem por <b>Whatsapp</b> assim que ele realizar o pedido? <br />
	            	Conheça essa e outras vantagens de automatizar o atendimento com o plugin <a href="https://powerfulautochat.com.br/" target="_blank"><b>Powerful Auto Chat</b></a>.
	            </p>
	        </div>
	    </div>
		<p>
			Encontrou algum bug ou quer fazer um comentário? <a href="https://wordpress.org/support/plugin/integracao-entre-eduzz-e-wc-powers/" target="_blank">Entre em contato aqui</a> ⭐⭐⭐⭐⭐ Gostou do plugin? Considere dar 5 estrelas em uma avaliação no <a href="https://wordpress.org/support/plugin/integracao-entre-eduzz-e-wc-powers/reviews/#new-post" target="_blank">wordpress.org</a>. Obrigado! :)
		</p>
		<p>Precisa de um desenvolvedor Wordpress para o seu negócio ? <a target="_blank" href="http://felipepeixoto.tecnologia.ws/">Entre em contato</a>.</p>
	</div>
	<input id="pluginurl" type="hidden" value="<?php echo plugin_dir_url( dirname( __FILE__ ) ); ?>">
	<input id="ajaxurl" type="hidden" value="<?php echo admin_url('admin-ajax.php'); ?>">
</div>