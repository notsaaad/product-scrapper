<?php
/*
Plugin Name: Product Scrapper
Plugin URI: https://github.com/ghost5egy/
Description: A plugin for scrapping URLs and get items by selectors
Version: 1.6
Author: Ghost5egy ( Ahmed Saeed )
Email: ahmedsaeedramadan@yahoo.com
Author URI: https://yasahost.com
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ){
  echo "Sry Ya Handsa It's ME";
  die;
}

register_activation_hook(__FILE__, 'product_scrapper_activate');
function product_scrapper_activate() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'product_scrapper';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id INT AUTO_INCREMENT,
        website varchar(255) NOT NULL,
        selector TEXT NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

	// require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

add_action('admin_menu', 'Product_scrapper_admin_menu');
function Product_scrapper_admin_menu() {
	add_menu_page(
		'Product Scrapper',
		'Product Scrapper',
		'manage_options',
		'Product-scrapper',
		'Product_scrapper_admin_page'
	);
	add_submenu_page('Product-scrapper', 'Add New Product Scrapper', 'Add New', 'manage_options', 'Product-scrapper-add', 'product_scrapper_add_page');

	add_submenu_page('Product-scrapper', 'Product Scrapper Settings', 'Settings', 'manage_options', 'Product-scrapper-settings', 'product_scrapper_settings_page');
}


function Product_scrapper_admin_page() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'product_scrapper';
	$results 	= $wpdb->get_results("SELECT * FROM $table_name" );
	$is_edit 	= $_GET['edit'] ?? '';
  // print_r($is_edit);
  //	sam_print_r( $is_edit );

	?>


	<style>
		table.sam-n-table {
			font-family: arial, sans-serif;
			border-collapse: collapse;
			width: 100%;
		}

		table.sam-n-table td, table.sam-n-table th {
			border: 1px solid #f5f5f5;
			text-align: left;
			padding: 8px;
		}

		table.sam-n-table tr:nth-child(even) {
			background-color: #f5f5f5;
		}
	</style>

	<?php

	if ( ! empty( $is_edit ) ) {

		$edit_id	= explode( '-' , $is_edit )[0];
		$result 	= $results[$edit_id] ??'';

		if ( empty( $result ) )
			return '';

		$selectors 	= $result->selector ? json_decode($result->selector) : '';

//		sam_print_r( $result );




		?>

		<div class="wrap">

			<h1>Update Product Scrapper </h1>
			<form id="product-scrapper-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="product_scrapper_update">
				<input type="hidden" name="result_id" value="<?php esc_attr_e( $result->id ); ?>">
				<?php wp_nonce_field('product_scrapper_update'); ?>

				<div style="margin-bottom: 8px;">
					<label for="website" style="display: block">Website:</label>
					<input type="text" name="website" id="website" value="<?php esc_attr_e( $result->website ); ?>" required>
				</div>
				<div id="selectors-container">
					<?php if ( ! empty( $selectors ) && is_array( $selectors ) ) {

						$i = 1;
						foreach ( $selectors as $selector ) {

							echo '<div class="selector-row" style="margin-bottom: 8px;">
									<label for="selector_1_name">Selector Name:</label>
									<input type="text" name="selectors[][name]" id="selector_'. $i .'_name" value="'. $selector->name .'" required>
									<label for="selector_1_code">Selector Code:</label>
									<input type="text" name="selectors[][code]" id="selector_'. $i .'_code" value="'. wp_unslash($selector->code) .'" required>
									<label for="selector_1_input">Selector Input:</label>
									<input type="text" name="selectors[][input]" id="selector_'. $i .'_input" value="'. $selector->input .'" required>
									<button class="remove-selector">Remove</button>
								</div>';

							$i++;
						}


					} ?>

				</div>
				<button id="add-selector" class="button button-primary">Add Selector</button>
				<button type="submit" class="button button-primary">Update</button>
			</form>
		</div>

		<?php

	} else {

//		sam_print_r( $results );
		echo '<div class="wrap">
		<h1>URL Scrapper</h1>
		<table class="widefat">
			<thead>
				<tr>
					<th>Website</th>
					<th>Selector</th>
				</tr>
			</thead>
			<tbody>';
		foreach ($results as $_key => $result) {
			echo '<tr>
					<td>'.$result->website.'<a href="'. $_SERVER['REQUEST_URI'] .'&edit='. $_key  .'-1" style="display: block"><span class="dashicons dashicons-edit"></span> Edit </a></td><td>';
			$selectors = json_decode($result->selector);


			?>

			<table class="sam-n-table">
				<tr>
					<th>Name</th>
					<th>Input</th>
					<th>Path</th>
				</tr>
				<?php

				foreach( $selectors as $selector ) {
					echo '<tr>
							<td>' . $selector->name . '</td>
							<td>' . $selector->input . '</td>
							<td>' . wp_unslash($selector->code) . '</td>
						</tr>';
				}

				?>
			</table>

			<?php
			echo '</td>
				</tr>';
		}
		echo '</tbody>
		</table>
	</div>';

	}

}


function product_scrapper_add_page() {
	?>
		<h1>Add New Product Scrapper</h1>
	<div class="wrap">
		<form id="product-scrapper-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="product_scrapper_add">
			<?php wp_nonce_field('product_scrapper_add'); ?>

			<label for="website">Website:</label><br />
			<input type="text" name="website" id="website" required><br /><br /><br />
			<div id="selectors-container"><div class="selector-row"><label for="selector_1_name">Selector Name:</label><input type="text" name="selectors[][name]" id="selector_1_name" required value="name"><label for="selector_1_code">Selector Code:</label><input type="text" name="selectors[][code]" id="selector_1_code" required><label for="selector_1_input">Selector Input:</label><input type="text" name="selectors[][input]" id="selector_1_input" value="field_wcpa-text-1683671671166" required><button class="remove-selector">Remove</button></div></div><br />

			<button id="add-selector" class="button button-primary">Add Selector</button>
			<button type="submit" class="button button-primary">Save</button>
		</form>
	</div>
	<?php
}

add_action('admin_footer', 'product_scrapper_add_page_footer');
function product_scrapper_add_page_footer() {
	?>
	<script>
		jQuery(document).ready(function($) {

			var selectorsCount = 1;

			$('#add-selector').click(function() {
				selectorsCount++;

				var newRow = '<br /><div class="selector-row">' +
					'<label for="selector_' + selectorsCount + '_name">Selector Name:</label>' +
					'<input type="text" name="selectors[][name]" id="selector_' + selectorsCount + '_name" required>' +
					'<label for="selector_' + selectorsCount + '_code">Selector Code:</label>' +
					'<input type="text" name="selectors[][code]" id="selector_' + selectorsCount + '_code" required>' +

					'<label for="selector_' + selectorsCount + '_input">Selector input:</label>' +
					'<input type="text" name="selectors[][input]" id="selector_' + selectorsCount + '_input" required>' +
					'<button class="remove-selector">Remove</button>' +
					'</div><br />';
          $('#selector_2_name').val('price');

          //Hatem easy Add New selector

      $('#selector_2_input').val('field_wcpa-text-1678106207042');

      $('#selector_3_name').val('SKU');
      $('#selector_3_input').val('field_wcpa-text-1683671723220');

      $('#selector_4_name').val('color');
      $('#selector_4_input').val('field_wcpa-text-1652377268636');

      $('#selector_5_name').val('hatem-img');
      $('#selector_5_input').val('img-hatem');

      $('#selector_6_name').val('size');
      $('#selector_6_input').val('field_wcpa-text-1686379361003');


				$('#selectors-container').append(newRow);
			});



			$(document).on('click', '.remove-selector', function() {
				$(this).closest('.selector-row').remove();
			});
		});
	</script>
	<?php
}

if ( ! function_exists( 'product_scrapper_update_post' ) ) {

	add_action('admin_post_product_scrapper_update', 'product_scrapper_update_post');
	function product_scrapper_update_post() {
		if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'product_scrapper_update')) {
			wp_die('Security check failed.');
		}
		$website = isset($_POST['website']) ? sanitize_text_field($_POST['website']) : '';
		if($website == ''){
			wp_redirect(admin_url('admin.php?page=Product-scrapper&message=added'));
			exit;
		}
		$selectors = isset($_POST['selectors']) ? $_POST['selectors'] : [];
		$selectorcounter = 0;
		$selectorarray = array("name" => "", "code" => "", "input" => "");
		$selectorsarray = array();
		foreach($selectors as $selector){
			if($selectorcounter == 3){
				$selectorcounter = 0;
				$selectorarray["name"] = "";
				$selectorarray["code"] = "";
				$selectorarray["input"] = "";
			}
			if(isset($selector['name'])){
				$selectorarray["name"] = $selector['name'];
			}
			if(isset($selector['code'])){
				$selectorarray["code"] = wp_unslash($selector['code']);
			}
			if(isset($selector['input'])){
				$selectorarray["input"] = $selector['input'];
				array_push($selectorsarray, $selectorarray);
			}
			$selectorcounter++;
		}

		$selectors_json = json_encode($selectorsarray);

		global $wpdb;
		$table_name = $wpdb->prefix . 'product_scrapper';

		$id = $_POST['result_id'] ?? ''; // ID of the row to be updated
		$data = array(
			'website' => $website,
			'selector' => $selectors_json
		);

		$wpdb->update( $table_name,
			$data,
			array( 'ID' => $id )
		);


		wp_redirect(admin_url('admin.php?page=Product-scrapper&message=added'));
		exit;
	}

}


add_action('admin_post_product_scrapper_add', 'product_scrapper_add_post');
function product_scrapper_add_post() {
	if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'product_scrapper_add')) {
		wp_die('Security check failed.');
	}
	$website = isset($_POST['website']) ? sanitize_text_field($_POST['website']) : '';
	if($website == ''){
		wp_redirect(admin_url('admin.php?page=Product-scrapper&message=added'));
		exit;
	}
	$selectors = isset($_POST['selectors']) ? $_POST['selectors'] : [];
	$selectorcounter = 0;
	$selectorarray = array("name" => "", "code" => "", "input" => "");
	$selectorsarray = array();
	foreach($selectors as $selector){
		if($selectorcounter == 3){
			$selectorcounter = 0;
			$selectorarray["name"] = "";
			$selectorarray["code"] = "";
			$selectorarray["input"] = "";
		}
		if(isset($selector['name'])){
			$selectorarray["name"] = $selector['name'];
		}
		if(isset($selector['code'])){
			$selectorarray["code"] = $selector['code'];
		}
		if(isset($selector['input'])){
			$selectorarray["input"] = $selector['input'];
			array_push($selectorsarray, $selectorarray);
		}
		$selectorcounter++;
	}
	$selectors_json = json_encode($selectorsarray);
	global $wpdb;
	$table_name = $wpdb->prefix . 'product_scrapper';
	$wpdb->insert($table_name, array(
		'website' => $website,
		'selector' => $selectors_json
	));


	wp_redirect(admin_url('admin.php?page=Product-scrapper&message=added'));
	exit;
}

function product_scrapper_settings_page() {
	$urlscrapper = get_option('product_scrapper_url_scrapper');
	$price_input = get_option('price_input_name');
	$excahngeurl = get_option('product_scrapper_rate_url');
	$exchangeselector = get_option('product_scrapper_exchange_selector');

	?>
	<div class="wrap">
		<h1>Product Scrapper Settings</h1>
		<form id="product-scrapper-settings-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="product_scrapper_settings_add">
			<?php wp_nonce_field('product_scrapper_settings_add'); ?>

			<label for="urlscrapper">URL Scrapper ID:</label><br />
			<input type="text" name="urlscrapper" id="urlscrapper" value="<?= $urlscrapper ?>" required><br /><br /><br />
			<label for="price_input_name">Price Input Name:</label><br />
			<input type="text" name="price_input_name" id="price_input_name" value="<?= $price_input ?>" required><br /><br /><br />
			<label for="excahngeurl">Excahnge URL :</label><br />
			<input type="text" name="excahngeurl" id="excahngeurl" value="<?= $excahngeurl ?>" required><br /><br /><br />
			<label for="exchangeselector">Excahnge Selector :</label><br />
			<input type="text" name="exchangeselector" id="exchangeselector" value="<?= $exchangeselector ?>" required><br /><br /><br />
<!--			<label for="bankperc">Bank Percentage :</label><br />-->
<!--			<input type="number" name="bankperc" id="bankperc" value="--><?php //= $bankperc ?><!--" required>%<br /><br /><br />-->
<!--			<button type="submit" class="button button-primary">Save</button>-->
		</form>

	</div>
	<?php
}


add_action('admin_post_product_scrapper_settings_add', 'product_scrapper_settings_add');
function product_scrapper_settings_add() {
	if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'product_scrapper_settings_add')) {
		wp_die('Security check failed.');
	}
	$urlscrapper = isset($_POST['urlscrapper']) ? sanitize_text_field($_POST['urlscrapper']) : '';
	$price_input = isset($_POST['price_input_name']) ? sanitize_text_field($_POST['price_input_name']) : '';
	$excahngeurl = isset($_POST['excahngeurl']) ? sanitize_text_field($_POST['excahngeurl']) : '';
	$exchangselector = isset($_POST['exchangeselector']) ? sanitize_text_field($_POST['exchangeselector']) : '';
		// $bankperc = isset($_POST['bankperc']) ? sanitize_text_field($_POST['bankperc']) : '';
	if(($urlscrapper != '' ) && ($excahngeurl != '' )){
		update_option('product_scrapper_url_scrapper', $urlscrapper);
		update_option('price_input_name', $price_input);
		update_option('product_scrapper_rate_url', $excahngeurl);
		update_option('product_scrapper_exchange_selector', $exchangselector);
//		update_option('product_scrapper_bank_perc', $bankperc);
	}
	wp_redirect(admin_url('admin.php?page=Product-scrapper-settings'));
	exit;
}

add_action('wp_ajax_product_scrapper', 'product_scrapper_ajax_handler');
add_action('wp_ajax_nopriv_product_scrapper', 'product_scrapper_ajax_handler');

function product_scrapper_ajax_handler() {
	$url = isset($_REQUEST['url'])? $_REQUEST['url'] : '';
	$pattern = '/^https?:\/\/(www\.)?([a-zA-Z0-9-]+\.)*([a-zA-Z0-9-]+\.[a-zA-Z]{2,})(\/.*)?$/';
	if (preg_match($pattern, $url, $matches)) {
		$domain = 'www.'.$matches[2] . $matches[3];
	}else{
		wp_die();
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'product_scrapper';
	$results = $wpdb->get_results("SELECT selector FROM $table_name where website = '$domain'");
	if ($results) {
		$selectors = json_decode($results[0]->selector);
		$html = file_get_contents($url);
		$doc = new DOMDocument();
		@$doc->loadHTML($html);
		$xpath = new DOMXPath(@$doc);
		// $xpath->registerNamespace('html', 'http://www.w3.org/1999/xhtml');

		$info = array();
		foreach($selectors as $selector){

//			if ( strpos($selector->code, '\\' ) !== false) {
//				$element = $xpath->query( wp_unslash($selector->code) );
//			} else {
				$element = $xpath->query($selector->code);
//			}

			if ($element) {
        if($selector->name == 'hatem-img'){
          $doc->preserveWhiteSpace = false;
          $doc->saveHTML();

          // $images = $doc-> getElementsByTagName('img');
          $img = $selector ->code ;
          $info[$selector-> name] = $xpath ->evaluate("string($img)");
          }


        else{
				$info[$selector->name] = trim($element[0]-> nodeValue);

				// echo("FROM NODE") .'<br>';
        }
			}else{
				$info[$selector->name] = 'NULL';
				// echo("FROM NULLLLLLLLL"). '<br>';
			}
			$info[$selector->name.'-input'] = $selector->input;
		}
		$info['try-rate'] = get_option('product_scrapper_rate');
		$textinfo = '';
		foreach ($info as $key => $value) {
			if(strpos($key, 'input') === false){
				$textinfo .= $key . '=' . $value . PHP_EOL;
			}
		}
		Product_Scrapper_webhook('https://script.google.com/macros/s/AKfycbxG-7ZroXgQ9rUelFoJmVsOudghx-YfTyx0nrJbO3RGsBsZJFdbJxz3LJriOG4q_ZqY/exec?Status=success&Products_Details='.$textinfo.'&Product_URL='.$url);
		wp_send_json($info);
	} else {
		Product_Scrapper_webhook('https://script.google.com/macros/s/AKfycbxG-7ZroXgQ9rUelFoJmVsOudghx-YfTyx0nrJbO3RGsBsZJFdbJxz3LJriOG4q_ZqY/exec?Status=failed&Products_Details=none&Product_URL='.$url);
		wp_send_json(array('error'=> 'this website not in the database.'));
	}
	wp_die();
}

function Product_Scrapper_webhook($url)
{
	if ( is_user_logged_in() ) {

		$current_user = wp_get_current_user();
		$url .= '&User_signIn=1&User_name='.$current_user->user_login;
		$url .= '&User_id='.$current_user->ID;
		$url .= '&User_phone='.get_user_meta( get_current_user_id() , 'phone', true);


	}else{

		$url .= '&User_signIn=no';

	}

	$args = array(
		'method' => 'GET',
		'timeout' => 10,
		'headers' => array(
			'Content-Type' => 'application/json',
		),
	);

	wp_remote_get($url, $args);

}


wp_schedule_event(time(), 'hourly', 'Product_Scrapper_cron_hook');
add_action('Product_Scrapper_cron_hook', 'Product_Scrapper_grab_rate');
function Product_Scrapper_grab_rate() {
	$url = get_option('product_scrapper_rate_url');
	$response = wp_remote_get($url);
	if (is_wp_error($response)) {
		return;
	}
	$body = wp_remote_retrieve_body($response);
	$rates = json_decode($body, true);
	if (is_null($rates)) {
		return;
	}
	update_option('product_scrapper_rate', $rates['rates']['USD']);
}

if ( ! function_exists( 'product_scrapper_code' ) ) {

	add_action('wp_footer', 'product_scrapper_code' );
	function product_scrapper_code() {

		$weights_html 	= '';
		$main_cats = sam_acf_get_field( 'sam_main_cats' , 'options' );
		// $inputid 		= get_option('product_scrapper_url_scrapper');
		$inputid 		= "field_wcpa-text-1652220613172.wcpa_field";

		$exchangselector 	= get_option('product_scrapper_exchange_selector');
		$exchangrate 		= get_option('product_scrapper_rate') ? get_option('product_scrapper_rate') : '0.051939';


		$bankperc 	= 0;
		$fees 		= sam_acf_get_field( 'sam_scraping' , 'options' );

		if ( ! empty( $fees ) && is_array( $fees ) ) {

			foreach ( $fees as $fee ) {

				$name  = $fee['cost_name']     ?? '';
				$price = $fee['cost_price']    ?? '';
				$status = $fee['cost_status']   ?? '';
				if ( $status )
					$bankperc += $price;


			}

		}

		$main_cats_html = $sub_cats_html = $products_html = '';
		$main_cats      = sam_acf_get_field( 'sam_main_cats' , 'options' );
		$default_cat		= sam_acf_get_field( 'sam_default_cat' , 'options' );
		$default_title 	= $default_cat['title'] 		?? sam_get_switch_language( 'أخري' , 'Other' );
		$default_img 		= $default_cat['img'] 			?? "";
		$max_weight 		= $default_cat['max_weight'] 	?? 99;

		if ( $default_img )
			$default_img = '<img src="' . esc_url( $default_img['url'] ) . '" alt="' . esc_attr( $default_img['alt'] ) . '" />';
		$default_main_html = '<li class="sam-main-cat-item">
									<input type="radio" id="samSelectDefaultCat" min="0" max="30" step="0.1" class="sam-select sam-default-cat-input" name="sam_main_cats" value="'. $default_title .'" />
									<label style="color: #555; display:flex;   justify-content: center; align-items:center; flex-direction: column;" for="samSelectDefaultCat" class="sam-default-cat sam-main-weight ">
										'. $default_img . $default_title . '
									</label>
								</li>';

		if ( ! empty( $main_cats ) ) {

			$main_cats_html .= '<div class="sam_weight_wcpa_form_item wcpa_form_item wcpa_type_text form-control_parent">
									<label>
										تصنيف المنتج(وزنه كيلو جرام)
										<span class="required_ast">*</span>
									</label>
									<ul class="sam-weights sam-cats sam-main-cats">';

			foreach ( $main_cats as $main_key => $main_cat ) {

				$img        	= $main_cat['img']      ? '<img src="'. $main_cat['img'] .'" />'    : '';
				$main_cat_name	= $main_cat['name']     ? '<h4>'. $main_cat['name'] .'</h4>'        : '';
				$sub_cats   	= $main_cat['sub_cats'] ?? '';

				$main_cats_html .= '<li class="sam-main-cat-item" id="sam-main-cat-'. $main_key .'">
										<input type="radio" id="samSelect'. $main_key .'" class="sam-select" name="sam_main_cats" value="'. $main_cat['name'] .'" />
										<label for="samSelect'. $main_key .'" class="sam-main-cat sam-main-weight" data-key="'. $main_key .'">
											'. $img . $main_cat_name . '
										</label>
									</li>';

				$sub_cats_html .= '<div class="sam-sub-cats sam-sub-cats-'. $main_key .'" style="display:none"><ul class="sam-weights">';

				$default_sub_html = '<li class="sam-sub-cat-item">
										<input type="radio" id="samSelectDefaultCat'. $main_key .'" class="sam-select sam-default-cat-input" name="sam_sub_cats" value="'. $default_title .'" />
										<label style="color: #555; display:flex;   justify-content: center; align-items:center; flex-direction: column" for="samSelectDefaultCat'. $main_key .'" class="sam-default-cat sam-sub-weight">
											'. $default_img . $default_title . '
										</label>
									</li>';

				if ( ! empty( $sub_cats ) ) {


					foreach ( $sub_cats as $sub_key => $sub_cat ) {

						$img        	= $sub_cat['img'] ? '<img src="'. $sub_cat['img'] .'" />' : '';
						$sub_cat_name	= $sub_cat['name'] ? '<h4>'. $sub_cat['name'] .'</h4>' : '';
						$products 		= $sub_cat['products'] ?? '';
						$product_weight = '';

						$sub_cats_html .= '<li class="sam-sub-cat-item" id="sam-sub-cat-'. $main_key . $sub_key .'">
												<input type="radio" id="samSelect'. $main_key . $sub_key . '" class="sam-select" name="sam_sub_cats" />
												<label for="samSelect'. $main_key . $sub_key .'" class="sam-sub-cat sam-sub-weight" data-main-key="'. $main_key .'" data-sub-key="'. $main_key . $sub_key .'" >
													'. $img . $sub_cat_name . '
												</label>
										  </li>';

						$products_html  .= '<div class="sam-products-weights sam-products-weights-'. $main_key . $sub_key .'" style="display:none"><ul class="sam-weights" id="">';

						$default_product_html = '<li class="sam-main-cat-item">
									<input type="radio" id="samSelectProductDefaultCat' . $main_key . $sub_key . '" class="sam-select sam-default-cat-input" name="sam_product_weight" value="'. $default_title .'" />
									<label style="color: #555; display:flex;   justify-content: center; align-items:center; flex-direction: column" for="samSelectProductDefaultCat' . $main_key . $sub_key . '" class="sam-default-cat sam-main-weight">
										'. $default_img . $default_title . '
									</label>
								</li>';

						if ( ! empty( $products ) ) {


							foreach ( $products as $id => $product ) {

								$img	= $product['img'] 		? '<img src="'. $product['img'] .'" />' : '';
								$title 	= $product['title']		? '<h4>'. $product['title'] .'</h4>' : '';
								$weight = $product['weight'] 	?? '';

								$products_html .= '<li id="sam-weight-item sam-weight-item-'. $main_key .'">
														<input type="radio" id="samSelect' . $main_key . $sub_key . $id . '" class="sam-select" name="sam_product_weight" />
														<label for="samSelect'. $main_key . $sub_key . $id .'" class="sam-sub-product sam-sub-product-weight" data-sub-key="'. $sub_key .'" data-main-key="'. $main_key .'" data-weight="'. $weight . '" >
															'. $img . $title . '
														</label>
											</li>';

							}

						}

						$products_html .= $default_product_html . '</ul>
												<button type="button" class="sam-pre-select sam-sub-select" data-key="sam-sub-cats-'. $main_key .'">
													الرجوع للخلف
												</button>
											</div>';

					}

				}

				$sub_cats_html .= $default_sub_html . '</ul>
										<button type="button" class="sam-pre-select sam-main-select" data-key="sam-main-cats">
											الرجوع للخلف
										</button>
									</div>';

			}

			$main_cats_html .= 	$default_main_html . '</ul>
                        </div>
                        <input type="hidden" id="sam_weight_kg" name="sam_weight_kg">';

		}

		$weights_html = $main_cats_html . $sub_cats_html . $products_html . '<input type="number" placeholder="'. sam_get_switch_language( ' قم بإضافة الوزن المتوقع للمنتج كيلو جرام' , 'Add the expected weight of the product' ) .'" class="form-control sam-product-weight" max="'. $max_weight .' min="0" max="30" step="0.1" style="display: none">';

		?>
		<script>



			(function($) {
				let Hatem_link;


				$('.product-main form.cart').append(`<div class="sam-overlay"><div class="sam-loading"></div></div>`);
				$('.page-id-6959 .product-main form.cart').append(`<div class="hatem-back-To-Home"><a href="https://sawyancom.com">العودة الي طلب الشراء</a></div>`);
        let Hatem_mobile_img ;
        // console.log(BuyForMe[0]);
        try{
					let BuyForMe = document.getElementsByClassName("wcpa_form_outer");
        // BuyForMe[0].removeAttribute('data-wcpa');


        let label = BuyForMe[0].getElementsByTagName('label');

        // label[0].remove();
        // label[0].innerText=" أدخل رابط المنتج ثم";


        // let URL_Input =BuyForMe[0].getElementsByTagName('input');

        // $('#sec_3649da2d142015').append(`<div class="sam-overlay"><div class="sam-loading"></div></div>`);
				// $('#field_text_2427494984').css('display','none');
        }
        catch{
          console.log("");
        }
				let exchangrate = <?= $exchangrate ?>;
				$('.page-id-6959 div#product-275 .sam_weight_wcpa_form_item').css('display','none');
				$('#Hatem-button').on("click",function() {
					Hatem_link =$('#InputHatemForm').val();
					if(Hatem_link !=""){
					$('.Row-buyForMeProduct').css('display',"flex");
					$('.Hatem-test-buyForMe-Section').css('display','none')
					}
					$('#<?= $inputid ?>').val(Hatem_link);
					// if ($('#<?= $inputid ?>').val()==""){
					// 	// location.alert("من فضلك ادخل رابط المنتج");
					// 	return 0;
					// }
          let hatem_img = document.getElementsByClassName("hatem-gallary");
        let hatem_img_h4 = hatem_img[0].getElementsByTagName('h4');

        $('.wcpa_field').not( "#<?= $inputid ?>"  ).not("#field_wcpa-text-1686379357937").val("");
        // let CheckFiled = document.getElementsByClassName("wcpa_field");
        // for (let i=1; i<CheckFiled.length; i++){
        //   CheckFiled[i].innerText="";
        // }
          $('.Hatem-from-img').remove();
					let overlay = $('.sam-overlay');
          let  hatm_gallay_class = $('.hatem-gallary');
          let hatem_sam_weights = $('.sam-weights');
					overlay.css('display','block');
					if (hatem_img_h4.length>0){
          hatem_img_h4[0].innerText= "صورة المنتج";
					}



					jQuery.ajax({
						type: 'POST',
						url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
						data: {
							action: 'product_scrapper',
							url: $('#<?= $inputid ?>').val()
						},
						success: function(response) {
              // $('.wcpa_form_outer').removeAttr('data-wcpa');

              // $('#field_wcpa-text-1652220613172').removeAttr('required');
							overlay.css('display','none');

							const responseKeys = Object.keys(response).filter((key) => !key.includes("-input"));


							//Here for Image

							let hiddenHatem =$("#field_wcpa-text-1686035897837.wcpa_field");
							// hiddenHatem.after("<h1>From File</h1>");
							if (responseKeys.hasOwnProperty('error')) {
								alert(response['error']);
								return ;
							}

							console.log( response );

              hatm_gallay_class.css('display','flex');
              hatem_sam_weights.css('display','flex');

							responseKeys.forEach(( currentValue , index , array ) => {

								if(currentValue=='hatem-img'){


									hiddenHatem.val(response[currentValue]);
                  Hatem_mobile_img =response[currentValue];
                  $('#<?= $inputid ?>').after(`<div class="Hatem-from-img"><img src="${Hatem_mobile_img}" height="300" width="300" alt="عذرا لم نستطع تحميل الصورة"></div>`);
                  									// $('#' + response[currentValue + '-input']).attr("src", response[currentValue].replace(/(<([^>]+)>)/gi, ""));
								}

								// console.log(`CurrentValue: `+currentValue);
								let inputlower	= '';
								let inputtype 	= $('#' + response[currentValue + '-input']).prop("tagName");

								if ( inputtype !== undefined ) {
									inputlower = inputtype.toLowerCase();
								}
              //   let ProductNameBeforeCart = $("#text-1680042894702").val();
							// 	if(ProductNameBeforeCart == ""){

						 	//  alert("من فضلك حاول مره اخري و التأكد من صحة رابط المنتج");
							// location.reload();
							// 	}


								if ( inputlower == 'input' ) {


									var ratepr = 1.0;

									if ( currentValue == '<?= $exchangselector ?>' ) {

										if ( exchangrate ) {

        						   			       let PurePrice = response[currentValue];
        						   			         PurePrice = PurePrice.replace('₺','');

                                          						  if(PurePrice[1] || PurePrice[2]  =='.'){
                                          						      PurePrice = PurePrice.replace('.','');
                                       							     }

                                      					let LastPrice = parseFloat(PurePrice);
                                       					     ratepr = LastPrice * exchangrate;
																											$('.page-id-6959 div#product-275 .sam_weight_wcpa_form_item').css('display','block');
										}

										var newrateptr = ratepr + (ratepr * (<?= $bankperc ?> / 100))
										$('#' + response[currentValue + '-input']).val("$"+newrateptr.toFixed(2));
										// $('#' + response[currentValue + '-input']).attr(('value'),("$"+newrateptr.toFixed(2)));



									} else {

										var text = response[currentValue].replace(/(<([^>]+)>)/gi, "");


										$('#' + response[currentValue + '-input']).val( text );
										// $('#' + response[currentValue + '-input']).attr('value', text );

									}

								} else if ( inputlower == 'img' ){



									$('#' + response[currentValue + '-input']).attr("src", response[currentValue].replace(/(<([^>]+)>)/gi, ""));

								} else {

									$('#' + response[currentValue + '-input']).html(response[currentValue].replace(/(<([^>]+)>)/gi, ""));

								}
              });



                  if( $('#field_wcpa-text-1683671671166').val()=="" && ( $('#field_wcpa-text-1678106207042').val()==""  || $('#field_wcpa-text-1678106207042').val()=="$NaN") ){
                    // location.replace("https://sawyancom.com/product/%d8%ae%d8%af%d9%85%d8%a9-%d8%a7%d8%b4%d8%aa%d8%b1%d9%8a%d9%84%d9%8a-%d9%88-%d8%a7%d8%b4%d8%ad%d9%86-%d9%8a%d8%af%d9%88%d9%8a/");

                  }


						},
						error: function( jqXHR , status , error ) {
							overlay.css('display','none');
              hatem_sam_weights.css('display','flex');
              // location.replace("https://sawyancom.com/product/%d8%ae%d8%af%d9%85%d8%a9-%d8%a7%d8%b4%d8%aa%d8%b1%d9%8a%d9%84%d9%8a-%d9%88-%d8%a7%d8%b4%d8%ad%d9%86-%d9%8a%d8%af%d9%88%d9%8a/");





						}
					})





					let Add_to_Cart_btn = $(".single_add_to_cart_button");



          // ===============Test===========================



          // let Button_Cloned_Add_To_Cart = "<button class='single_add_to_cart_button'id='Hatem-sec-add-to-cart-scrap'> < "
          // Add_to_Cart_btn.after(Button_Cloned_Add_To_Cart);

          // ===============Test===========================

					let color = $("#field_wcpa-text-1652377268636").val();
					let Size = $('#field_text-9839131957').val();
					let Hatemweight = $("#field_text_2427494984").val();
					let counter = 0;
					Add_to_Cart_btn.click(function(){
						if ($("#field_wcpa-text-1652377268636").val()==""){
							counter++;

							hatemWaring("من فضلك ادخل لون المنتج و في حالة لا يوجد قم بكتابة موحد");

							$("#field_wcpa-text-1652377268636").css("outline","3px solid red")
							setTimeout(function(){
							$(".Hatem-waring").remove();
						}, 4000);

						$("#field_wcpa-text-1652377268636").focusout(function(){
							if($("#field_wcpa-text-1652377268636").val() !=""){
							$("#field_wcpa-text-1652377268636").css("outline","none");
							}
						});

						}
						else if ($("#field_text-9839131957").val()==""){

							counter++;
							hatemWaring("من فضلك ادخل  حجم المنتج و في حالة لا يوجد قم بكتابة موحد");
							$("#field_text-9839131957").css("outline","3px solid red")
							setTimeout(function(){
							$(".Hatem-waring").remove();
						}, 4000);

						$("#field_text-9839131957").focusout(function(){
							if($("#field_text-9839131957").val() !=""){
							$("#field_text-9839131957").css("outline","none");
							}
						});
						}

						// else if ($("#field_text_2427494984").val()==""){

						// 	// console.log(Hatemweight);
						// 	counter++;
						// 	hatemWaring("من فضلك اختر تصنيف المنتج");
						// 	//location.href = "#";
						// 	//location.href = "#field_text-9839131957";
						// 	$("#field_text_2427494984").css("outline","3px solid red")
						// 	setTimeout(function(){
						// 	$(".Hatem-waring").remove();
						// }, 4000);

						// $("#field_text_2427494984").focusout(function(){
						// 	if($("#field_text_2427494984").val() !=""){
						// 	$("#field_text_2427494984").css("outline","none");
						// 	}
						// });
						// }

					});
										// 	if (counter==0){
					// 		counter++;
					// 		hatemWaring("حدث خطأ يرجي الضغط علي اضافة الي السلة مره أخري");
					// 		setTimeout(function(){
					// 		$(".Hatem-waring").remove();
					// 	}, 3000);
					// 	}
						// $(".single_add_to_cart_button").att('class','loading');
					function hatemWaring(text){
						$('.Row-buyForMeProduct').append(`<div id="TestLoaction" style="scroll-behavior:smooth;" class='Hatem-waring'><span>${text}</span></div>`);
					}

				});




	//		}); //Hatem-Test

			})(jQuery);
		</script>
		<?php

	}

}


// if ( is_user_logged_in() ) {

// 	$current_user = wp_get_current_user();
// 	$url .= '&User_signIn=1&User_name='.$current_user->user_login;
// 	$url .= '&User_id='.$current_user->ID;
// 	$url .= '&User_phone='.get_user_meta( get_current_user_id() , 'phone', true);

// 	// echo $current_user ->ID;
// }

// function testHatem

add_action('wp_footer', 'hatemtest' );

function hatemtest(){



	if ( is_user_logged_in() ) {

			// $current_user = wp_get_current_user();
			// $url .= '&User_signIn=1&User_name='.$current_user->user_login;
			// $url .= '&User_id='.$current_user->ID;
			// $url .= '&User_phone='.get_user_meta( get_current_user_id() , 'phone', true);


		// echo "hatem";

			?><script>

				(function($){

				let User_ID_Hatem =  `<?php echo wp_get_current_user()->ID?>`;
        let  Value = $('#hatem-filed-copy1').val(`İnönü, Belde Cd., 34510 Beylikdüzü Osb/Esenyurt/İstanbul, Türkiye Box:${User_ID_Hatem}`);

        $('#hatem-copy-1').click(function(){
          HatemCopyTrue();
          navigator.clipboard.writeText(`İnönü, Belde Cd., 34510 Beylikdüzü Osb/Esenyurt/İstanbul, Türkiye Box:${User_ID_Hatem}`);
        });





				})(jQuery);

  function HatemCopyTrue(){
  let HatemCopy = document.getElementsByClassName("hatem-Copy-true");
  HatemCopy[0].style.display="flex";
  setTimeout(function(){
    HatemCopy[0].style.display="none";
  },2000);
}
			</script>
			<?php

	}
  else{
    ?><script>
				(function($){


				let User_ID_Hatem =  "Sign in for Code";

        let  Value = $('#hatem-filed-copy1').val(`İnönü, Belde Cd., 34510 Beylikdüzü Osb/Esenyurt/İstanbul, Türkiye Box:${User_ID_Hatem}`);

        $('#hatem-copy-1').click(function(){
          navigator.clipboard.writeText(`İnönü, Belde Cd., 34510 Beylikdüzü Osb/Esenyurt/İstanbul, Türkiye Box:${User_ID_Hatem}`);
        });

				})(jQuery);




			</script>
			<?php
  }

}