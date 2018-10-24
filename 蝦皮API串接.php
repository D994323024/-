<?php 



 $TW_shopee = array(
						"shop_id" => xxxxxxxx,
						"partner_id" => xxxxxxx,
						"shopee_key" => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
							);           

function shopee_escrow_details($shopee_date,$shopee_order_id){	//GetEscrowDetails

		$url = "https://partner.shopeemobile.com/api/v1/orders/my_income";
		$time = time();	//時間戳
		$data = array(
			"ordersn"=>$shopee_order_id,
			"partner_id"=>$shopee_date['partner_id'],
			"shopid"=>$shopee_date['shop_id'],
			"timestamp"=>$time
		);
		
		$encode_data=json_encode($data);//轉json
		$authorization=hash_hmac('sha256', $url.'|'.$encode_data,$shopee_date['shopee_key']);

		$curl = curl_init();	
		if(strstr($url,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: '.$authorization
		)
	);
		curl_setopt($curl,CURLOPT_URL, $url); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_POSTFIELDS, $encode_data); // 將post資料塞入
		$response=curl_exec($curl); // 執行網頁
		$err = curl_error($curl); //確認是否有回報錯誤
		curl_close($curl); // 關閉網頁
		
		if($err){
			return $err;
		}else{
			return $response;
		}	
	}
	/*
	$shopee_orders_details_json = shopee_escrow_details($TW_shopee,"18102210014CP9G");
	$shopee_orders_details=json_decode($shopee_orders_details_json,true);
	print_r($shopee_orders_details);
	*/

function shopee_getorder_details($shopee_date,$shopee_order_id_list){	//GetOrderDetails

		$url = "https://partner.shopeemobile.com/api/v1/orders/detail";
		$time = time();	//時間戳
		$data = array(
			"ordersn_list"=>$shopee_order_id_list,
			"partner_id"=>$shopee_date['partner_id'],
			"shopid"=>$shopee_date['shop_id'],
			"timestamp"=>$time
		);
		
		$encode_data=json_encode($data);//轉json
		$authorization=hash_hmac('sha256', $url.'|'.$encode_data,$shopee_date['shopee_key']);

		$curl = curl_init();	
		if(strstr($url,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: '.$authorization
		)
	);
		curl_setopt($curl,CURLOPT_URL, $url); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_POSTFIELDS, $encode_data); // 將post資料塞入
		$response=curl_exec($curl); // 執行網頁
		$err = curl_error($curl); //確認是否有回報錯誤
		curl_close($curl); // 關閉網頁
		
		if($err){
			return $err;
		}else{
			return $response;
		}	
	}
	/*
	$list = array("18102312154K8ES","181023075446YH4","181023051644KHN");
	$shopee_getorder_details_json = shopee_getorder_details($TW_shopee,$list);
	$shopee_getorder_details=json_decode($shopee_getorder_details_json,true);
	print_r($shopee_getorder_details);
	*/
	
function shopee_getorder_status($shopee_date,$order_status,$create_time_from=0,$create_time_to=0,$pagination_entries_per_page=0,$pagination_offset=0){	//GetOrdersByStatus

		$url = "https://partner.shopeemobile.com/api/v1/orders/get";
		$time = time();	//時間戳
		$data = array(
			"order_status"=>$order_status,
			"create_time_from"=>$create_time_from,
			"create_time_to"=>$create_time_to,
			"pagination_entries_per_page"=>$pagination_entries_per_page,
			"pagination_offset"=>$pagination_offset,
			"partner_id"=>$shopee_date['partner_id'],
			"shopid"=>$shopee_date['shop_id'],
			"timestamp"=>$time
		);
		
		$encode_data=json_encode($data);//轉json
		$authorization=hash_hmac('sha256', $url.'|'.$encode_data,$shopee_date['shopee_key']);

		$curl = curl_init();	
		if(strstr($url,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: '.$authorization
		)
	);
		curl_setopt($curl,CURLOPT_URL, $url); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_POSTFIELDS, $encode_data); // 將post資料塞入
		$response=curl_exec($curl); // 執行網頁
		$err = curl_error($curl); //確認是否有回報錯誤
		curl_close($curl); // 關閉網頁
		
		if($err){
			return $err;
		}else{
			return $response;
		}	
	}	
	/*
	
	$shopee_getorder_status_json = shopee_getorder_status($TW_shopee,$list);
	$shopee_getorder_status=json_decode($shopee_getorder_status_json,true);
	print_r($shopee_getorder_status);
	
	
	*/
	
	
function shopee_getorder_list($shopee_date,$create_time_from=0,$create_time_to=0,$update_time_from=0,$update_time_to=0,$pagination_entries_per_page=0,$pagination_offset=0){	//GetOrdersList

		$url = "https://partner.shopeemobile.com/api/v1/orders/basics";
		$time = time();	//時間戳
		$data = array(
			"create_time_from"=>$create_time_from,
			"create_time_to"=>$create_time_to,
			"update_time_from"=>$update_time_from,
			"update_time_to"=>$update_time_to,
			"pagination_entries_per_page"=>$pagination_entries_per_page,
			"pagination_offset"=>$pagination_offset,
			"partner_id"=>$shopee_date['partner_id'],
			"shopid"=>$shopee_date['shop_id'],
			"timestamp"=>$time
		);
		
		$encode_data=json_encode($data);//轉json
		$authorization=hash_hmac('sha256', $url.'|'.$encode_data,$shopee_date['shopee_key']);

		$curl = curl_init();	
		if(strstr($url,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: '.$authorization
		)
	);
		curl_setopt($curl,CURLOPT_URL, $url); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_POSTFIELDS, $encode_data); // 將post資料塞入
		$response=curl_exec($curl); // 執行網頁
		$err = curl_error($curl); //確認是否有回報錯誤
		curl_close($curl); // 關閉網頁
		
		if($err){
			return $err;
		}else{
			return $response;
		}	
	}	
	
	/*
	$shopee_getorder_list_json = shopee_getorder_list($TW_shopee,$list);
	$shopee_getorder_list=json_decode($shopee_getorder_list_json,true);
	print_r($shopee_getorder_list);	
	
	*/
	
	
	
	
	
	
	
	
	
	
	
	
	


?>