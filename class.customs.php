<?php

	/*
	* PHP Version 5.6.37
	* 宁波跨境贸易电子商务平台电商数据交换接口
	* Compiler : 陳哲儒
	* phone : 095669080
	*
	* $test = new customs;
	* $user =  $test->function();
	* private   $user_id 帳號 
	* private   $enterprise 电商企业名称
	* private   $enterprise_code 电商企业代码
	* private   $store_name 购物网站名称 PS:這個可不給 不影響方便確認
	* private   $store_code 购物网站代码
	* private   $oto_store_code OTO店鋪代碼
	* private   $order_shop 店铺代码
	* private   $user_key  申請服務的私鑰
	* private   $URL_base   串接的網址
	* private   $customs_array 關口名稱 與 輸出關口代碼
	* private   $xml_head 這個不須變動 為輸出 xmlstr 的前綴參數
	* private   $pay_code 支付方式 與 輸出代碼
	*/ 
	//該function 下 http_tax_page 是輸出網頁 其他輸出xml 需呼叫 xml_transform_array 轉為陣列模式或是自行轉成需要的型態
class customs{
	 private  $user_id  = "XXXXXXXXXX";
	 private  $enterprise = "XXXXXXXXXXXXXXXXXX";	//公司
	 private  $enterprise_code = "XXXXXXXXX";				//公司代碼
	 private  $store_name = "XXXXXXX";						//購物網站名稱
	 private  $store_code = "XXXX";							//購物網站代碼
	 private  $oto_store_code = "XXXXXX";					//OTO店鋪代碼
	 private  $order_shop = "XXXXXXX";						//店铺代码
	 private  $user_key =  "XXXXXXXXXXXXXXXXXXXXXXXXXXXX";
	 private  $URL_base = "https://api2.kjb2c.com/dsapi/dsapi.do";
	 private  $customs_array=array("栎社机场"=>"3109","北仑保税区"=>"3105","空港保税物流中心"=>"3115","梅山保税区"=>"3117","慈溪保税区"=>"3113");

	 
	 
	 private  $xml_head = '<?xml version="1.0" encoding="UTF-8" ?>';
	 private  $pay_code = array("银联在线"=>"01","支付宝"=>"02","盛付通"=>"03","建设银行"=>"04","中国银行"=>"05","易付宝"=>"06","农业银行宁波分行"=>"07","京东网银在线"=>"08",
							   "国际支付宝"=>"09","甬易支付"=>"10","富友支付"=>"11","连连支付"=>"12","财付通（微信支付）"=>"13","快钱"=>"14","网易宝"=>"15","银盈通支付"=>"16",
							   "鄞州银行"=>"17","智惠支付"=>"18","拉卡拉"=>"19","北京银联"=>"20","杭州银行（网银）"=>"21","银联网络"=>"22","重庆易极付"=>"23","易宝支付"=>"24",
							   "广州银联"=>"25","上海银联"=>"26","通联支付"=>"27","首信易支付"=>"28","浙江银商"=>"29","百度钱包"=>"31","易票联支付"=>"32","招商银行"=>"33",
							   "平安付"=>"34","联动优势"=>"35","易联支付"=>"36","四川商通"=>"37","高汇通"=>"38","开联通"=>"39","钱宝科技"=>"40","云商优付"=>"41","智付"=>"42",
							   "爱农"=>"43","翼支付"=>"44","上海汇付"=>"45","现代金控"=>"46","宝付"=>"47","交通银行宁波分行"=>"48","汇元银通"=>"49","汇元银通"=>"50","工商银行"=>"51");
	
	/*
	*  xml_transform_array方便將使用下列除了 http_tax_page(他回傳一個網頁) 
	*  以外的function 傳回值由xml格式轉為陣列格式
	*  input xml 格式 
	*  output array 格式
	*/


	function xml_transform_array($xml_original){	//將輸出職轉成陣列
		$xml = simplexml_load_string($xml_original);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		return $array;
	}
		/*
		* funtion import_orders API-进口订单
		* 透過串接api 來申報寧波海關的進口訂單
		* input  $customs_name 稅關名稱 如: 北仑保税区 或是 栎社机场
		* input  $status 新增 or 修改訂單 （0=新增，1=更新）
		* input  $orders_array 
		*	{
		*	 必填 	<orders部分>	create_time 訂單建立時間,order_number 訂單號, shipping_fee 運費, total_amount 總價(买家实付金额=實際付出金額), buyer_account 购物网站买家账号,	
		*							tax_amount 稅額(總共稅率), tariff_tax 關稅, value_add_tax 增值稅, consumption_tax 消費稅(這四個稅額只收金額預設免稅0),
		*							orders_weight 訂單總重量(給公克)(原先要自動跑商品但可能有額外的包裝) ,buyer_id_card 購買人身分證 ,buyer_name 購買人姓名,
		*			 
		*	
		*	 非必填	<orders部分>	package_flag 是否组合装标识（0=不是，1=是）,insurance_fee 保价费（无保价费时请设置0）
		*							buyer_tel 購買人手機號, buyer_email 購買人email, 
		*			 				discount{  [0]=>array{[pro_amount]=>xx [pro_description]=> xx} //折扣金額 折扣說明 }
		*			 				orders_memo_01 订单备用字段01, orders_memo_02, 订单备用字段02, orders_memo_03 订单备用字段03,
		*	 
		*
		*	特別注意:				pay_method==财付通 且 order_seq_number 與 payment_number不同則須填写商户机构号
		*	
		*	必填	<Pay>			pay_method 支付方式, pay_time 支付時間, payment_number 支付单号（与支付机构交互的流水号）,order_seq_number 商家送支付机构订单交易号（如无，请与支付单号一致(大多都有並且是order_number)）, pay_method 支付方式(以pay_code 有的公司為主),		
		*							
		*	非必舔 	<Pay>			pay_id_card 支付身分證 pay_name 支付姓名
		*		
		*	條件必填<Pay>			mer_id 银联在线商户号 PS:如果pay_method是银联在线則必填		
		*	
		* 
		*	必填	<Logistics>	 	shipping_number 運貨單,shipping_company 快遞公司, receiver_name 收件者姓名,
		*							receiver_province 收件地址省分,receiver_city 收件地址市鄉鎮,receiver_area 收件地址區,
		*							receiver_address 收件者地址,receiver_tel 收件者手機
		* 
		* 	非必填	<Logistics>		orders_name 貨物名稱, shipping_memo 物流备注,zip_code 郵編區號,
									shipping_add_message_01 物流备用字段01（大头笔信息）,shipping_add_message_02 物流备用字段02（物流附加信息）,
									shipping_add_message_03 物流备用字段03（物流附加信息）
		*	
		*		 oto_code OTO店铺代码(這個坐在上面),
		*	}
		* input  $producct_array
		*	{	
		*		因為一張訂單商品不只一個因此使用二維陣列 [0]=>array{	[customs_no]=>"",[customs_name]=>""	} 等
		*		特別注意:	customs_no 货号 空港保税物流中心（关区代码：3115）有两种清关模式（北仑保税、空港保税）商品备案时，需选择清关模式 不同清关模式的货号不能在同一订单申报。
		*		必填	customs_no 货号（跨境平台商品备案时产生的唯一编码）, customs_name 商品名称, quantity 數量, unit 计量单位（需与商品备案时的单位一致）,
		*				price 商品单价, 
		*
		*		非必填	product_memo_01 商品备用字段01, product_memo_02 商品备用字段02, product_memo_03 商品备用字段03,
		*			
		*
		*	}
		* input	條件必填 $Mft_number 申报单号 如果 $status 為 1 更新 則必須填否則不用
		*	TybType 固定給1(总署版) 固定值
		*
		*
		* output xml
		*
		*	<Message>
		*		<Header>
		*			<Result> T：操作成功；F：操作失败 </Result>
		*			<ResultMsg> 结果描述（操作失败时必需）</ResultMsg>
		*			<MftNo> 申报单号（审核成功返回）</MftNo>
		*		</Header>
		*	</Message>
		*
		*/

	function import_orders($customs_name,$status,$orders_array,$producct_array,$Mft_number=""){ //未測試
		//API-进口订单  稅關名稱,狀態(新增給0 更新給1),訂單陣列,商品陣列,申报单号
		if($status=="1" && empty($Mft_number)){
			return "更新必須要填入申报单号!!";
			exit;
		}	
		$msgtype = "cnec_jh_order";
		$date = date("Y-m-d H:i:s");
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		$producct_xml="";	// 商品明細
		$promotions_xml="";	// 優惠	
		$pay_code = $this->pay_code[$orders_array['pay_method']]; //支付的中文轉成代碼
		
		/*  <Goods>   貨物 start  */
		foreach($producct_array as $key => $value){		//run 商品明細
			$amount = $value['quantity']*$value['price'];
			$producct_xml .="<Detail><ProductId>{$value['customs_no']}</ProductId><GoodsName>{$value['customs_name']}</GoodsName><Qty>{$value['quantity']}</Qty><Unit>{$value['unit']}</Unit><Price>{$value['price']}</Price><Amount>{$amount}</Amount><DtRemark01>{$value['product_memo_01']}</DtRemark01><DtRemark02>{$value['product_memo_02']}</DtRemark02><DtRemark03>{$value['product_memo_03']}</DtRemark03></Detail>";
		}
		$producct_xml = "<Goods>{$producct_xml}</Goods>";
		/*  <Goods>   貨物 end */
	
		$tax_amount = empty($orders_array['tax_amount'])?0:round($orders_array['tax_amount'],2);					//稅額(總共稅率)(取小數點第二位)		
		$tariff_tax = empty($orders_array['tariff_tax'])?0:round($orders_array['tariff_tax'],2);					//tariff_tax 關稅(取小數點第二位)
		$value_add_tax = empty($orders_array['value_add_tax'])?0:round($orders_array['value_add_tax'],2);			//value_add_tax 增值稅(取小數點第二位)
		$consumption_tax = empty($orders_array['consumption_tax'])?0:round($orders_array['consumption_tax'],2);	//consumption_tax 消費稅(取小數點第二位)
		$insurance_fee = empty($orders_array['insurance_fee'])?0:round($orders_array['insurance_fee'],2); //保價費
		$shipping_fee = empty($orders_array['shipping_fee'])?0:round($orders_array['shipping_fee'],2); //運費
		$orders_weight = round($orders_array['orders_weight'],2);//訂單總重量(取小數點第二位) 
		
		/* Promotions 優惠start */
		$total_discount = "0"; //總折扣
		if(!empty($orders_array['discount'])){
			foreach($orders_array['discount'] as $key => $value){
				$promotions_xml .="<Promotion><ProAmount>{$value['pro_amount']}</ProAmount><ProRemark>{$value['pro_description']}</ProRemark></Promotion>";
				$total_discount += $value['pro_amount'];
			}
		}
		/*
		if(empty($promotions_xml)){
			$promotions_xml .="<Promotion><ProAmount></ProAmount><ProRemark></ProRemark></Promotion>";
		}*/
		$promotions_xml = "<Promotions>{$promotions_xml}</Promotions>";			
 
		
		/* Promotions 優惠end */
		 
		/* orders 到Promotions 前的 start */
		$order_amount = round($orders_array['total_amount'],2);
		if($orders_array['buyer_name']==$orders_array['pay_name']){ //購買人與付款者是否相同
			$buyer_is_pay = "1";
		}else{
			$buyer_is_pay = "0";
		}	
		$orders_message = "<Operation>{$status}</Operation><TybType>1</TybType><MftNo>{$Mft_number}</MftNo><OrderShop>{$this->order_shop}</OrderShop><OTOCode>{$this->oto_store_code}</OTOCode>
		<OrderFrom>{$this->store_code}</OrderFrom><PackageFlag>{$orders_array['package_flag']}</PackageFlag><OrderNo>{$orders_array['order_number']}</OrderNo><PostFee>{$shipping_fee}</PostFee>
		<InsuranceFee>{$insurance_fee}</InsuranceFee><Amount>{$order_amount}</Amount><BuyerAccount>{$orders_array['buyer_account']}</BuyerAccount>
		<Phone>{$orders_array['buyer_tel']}</Phone><Email>{$orders_array['buyer_email']}</Email><TaxAmount>{$tax_amount}</TaxAmount><TariffAmount>{$tariff_tax}</TariffAmount>
		<AddedValueTaxAmount>{$value_add_tax}</AddedValueTaxAmount><ConsumptionDutyAmount>{$consumption_tax}</ConsumptionDutyAmount><GrossWeight>{$orders_weight}</GrossWeight>
		<DisAmount>{$total_discount}</DisAmount><BuyerIdnum>{$orders_array['buyer_id_card']}</BuyerIdnum><BuyerName>{$orders_array['buyer_name']}</BuyerName>
		<BuyerIsPayer>{$buyer_is_pay}</BuyerIsPayer><OdRemark01>{$orders_array['orders_memo_01']}</OdRemark01><OdRemark02>{$orders_array['orders_memo_02']}</OdRemark02><OdRemark03>{$orders_array['orders_memo_03']}</OdRemark03>";
		//<TybType> 給1因為都是總署版了,<PackageFlag> 直郵給0因為沒有組合裝的東西,購買人手機email非必要
		//我們公司只有增值稅(行郵稅 因此稅額就是行郵稅)	
		/* orders 到Promotions 前的end */
	
		/* Pay start */
		$pay_xml="<Pay><Paytime>{$orders_array['pay_time']}</Paytime><PaymentNo>{$orders_array['payment_number']}</PaymentNo><OrderSeqNo>{$orders_array['order_seq_number']}</OrderSeqNo><Source>{$pay_code}</Source>
		<Idnum>{$orders_array['pay_id_card']}</Idnum><Name>{$orders_array['pay_name']}</Name><MerId>{$orders_array['mer_id']}</MerId></Pay>";
		
		
		 
		
		/* Pay  end */
		
		/* Logistics 物流start */
		$Logistics = "<Logistics><LogisticsNo>{$orders_array['shipping_number']}</LogisticsNo><LogisticsName>{$orders_array['shipping_company']}</LogisticsName><Consignee>{$orders_array['receiver_name']}</Consignee><Province>{$orders_array['receiver_province']}</Province>
		<City>{$orders_array['receiver_city']}</City><District>{$orders_array['receiver_area']}</District><ConsigneeAddr>{$orders_array['receiver_address']}</ConsigneeAddr><ConsigneeTel>{$orders_array['receiver_tel']}</ConsigneeTel><MailNo>{$orders_array['zip_code']}</MailNo>
		<GoodsName>{$orders_array['orders_name']}</GoodsName><Default01>{$orders_array['shipping_memo']}</Default01><LgRemark01>{$orders_array['product_memo_01']}</LgRemark01><LgRemark02>{$orders_array['product_memo_02']}</LgRemark02><LgRemark03>{$orders_array['product_memo_03']}</LgRemark03></Logistics>";
		/* Logistics 物流end  */ 	
		
		
		
		$xml = $xml_head."<Message><Header><CustomsCode>{$this->enterprise_code}</CustomsCode><OrgName>{$this->enterprise}</OrgName><CreateTime>{$orders_array['create_time']}</CreateTime></Header><Body><Order>{$orders_message}{$promotions_xml}{$producct_xml}</Order>{$pay_xml}{$Logistics}</Body></Message>";		
		
		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();	
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
		$response=curl_exec($curl); // 執行網頁
		$err = curl_error($curl); //確認是否有回報錯誤
		curl_close($curl); // 關閉網頁
		
		if($err){
			return $err;
		}else{
			return $response;
		}		
	}
	/* function customs_order_query API-申报单状态查询（申报单号）
	*	<必要>
	*	input  $customs_name 稅關名稱 如: 北仑保税区 或是 栎社机场
	*	input  $customs_name 電報單號
	*
	*	output 
	*		<Message>
	*			<Header>
	*				<Result>T：操作成功；F：操作失败</Result>
	*				<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*			</Header>
	*			<Body>
	*				<Mft>
	*					<MftNo>申报单号</MftNo>
	*					<ManifestId>总署清单编号</ManifestId>
	*					<OrderNo>订单号</OrderNo>
	*					<LogisticsNo>运单号</LogisticsNo>				
	*					<PaySource>支付方式</PaySource>
	*					<LogisticsName>物流公司名称</LogisticsName>
	*					<CheckFlg>预校验标识(0=未通过,1=已通过)</CheckFlg>
	*					<CheckMsg>预校验描述</CheckMsg>
	*					<Status>申报单当前状态</Status> 
	*					<Unusual>申报单异常状态</Unusual> 01=已报海关：库存不足（该状态为海关异常单，通常为库存不足，具体情况看workflow备注）, 02=已报海关：总署人工审核
	*					<Result>描述</Result>
	*					<MftInfos>	 
	*						<MftInfo>	申报单明细节点可循环（显示申报单所有流转状态信息）
	*							<Status>流转状态</Status>	00=未申报, 01=库存不足, 02=发仓库配货, 03=仓库已配货11=已报检验检疫, 12=检验检疫放行,
															13=检验检疫审核未过, 14=检验检疫抽检, 21=已报海关, 22=海关单证放行, 23=海关单证审核未过,
															24=海关货物放行, 25=海关查验未过,99=已关闭
	*							<Result>描述</Result>
	*							<CreateTime>操作时间</CreateTime>
	*						</MftInfo>
	*					</MftInfos>
	*				</Mft>
	*			</Body>
	*		</Message>
	*/
	function customs_order_query($Mft_number,$customs_name){
		//API-申报单状态查询（申报单号）//電報單號,稅關名稱
		$msgtype = "cnec_jh_decl_byorder";
		$date = date("Y-m-d H:i:s");
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		$xml = $xml_head."<Message><Header><CustomsCode>{$this->enterprise_code}</CustomsCode><OrgName>{$this->enterprise}</OrgName><MftNo>{$Mft_number}</MftNo></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();	
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function query_order_update API-申报单状态查询 (根据状态更新时间)
	*	<必要>
	*	input $customs_name	稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $start_time	开始时间 如: 采用yyyy-MM-dd HH:mm:ss格式（例："2013-09-01 13:30:05"）		
	*	input $end_time		結束时间 如: 采用yyyy-MM-dd HH:mm:ss格式（例："2013-09-01 13:30:05"）	
	*	<非必要>
	*	input $page			指定查询页码(1/2/3...)，从1开始计算，每页1000条纪录	
	*	
	*	註釋:如果位填寫 $start_time 是以前現在12小時為主
	*		 如果位填寫 $end_time 是以前現在為主
	*
	*
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需</ResultMsg>
	*			<NextPage>是否存在下一页（T:是；F：否）</NextPage>
	*		</Header>
	*		<Body>
	*			<Mft>申报单节点可循环
	*				<MftNo>申报单号</MftNo>
	*				<ManifestId>总署清单编号</ManifestId>
	*				<OrderNo>订单号</OrderNo>
	*				<LogisticsNo>运单号</LogisticsNo>
	*				<CheckFlg>预校验标识(0=未通过,1=已通过)</CheckFlg>
	*				<CheckMsg>预校验描述</CheckMsg>
	*				<Status>申报单当前状态</Status>
	*				<Unusual>申报单异常状态</Unusual>	00=未申报, 01=库存不足, 02=发仓库配货, 03=仓库已配货,21=已报海关, 
														22=海关单证放行, 23=海关单证审核未过, 24=海关货物放行, 25=海关查验未过,
														25=海关查验未过, 99=已关闭
	*				<Result>描述</Result>
	*				<PaySource>支付方式</PaySource>
	*				<LogisticsName>物流公司名称</LogisticsName>
	*				<CreateTime>操作时间</CreateTime>
	*			</Mft>
	*		</Body>
	*	</Message>
	*/
	
	
	function query_order_update($customs_name,$start_time="",$end_time="",$page=""){
		//API-申报单状态查询 (根据状态更新时间) 關稅名稱,開始時間,結束時間,頁數(每頁1000筆) 如果未填寫開始時間則以12hour為主
		$msgtype = "cnec_jh_decl_byupdatetime";
		$date = date("Y-m-d H:i:s");
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼		
		if(empty($start_time)){
			$start_time = date("Y-m-d H:i:s", strtotime('-12 hour'));
		}
		if(empty($end_time)){
			$end_time = date("Y-m-d H:i:s");
		}
		$xml = $xml_head."<Message><Header><CustomsCode>{$this->enterprise_code}</CustomsCode><OrgName>{$this->enterprise}</OrgName><StartTime>{$start_time}</StartTime><EndTime>{$end_time}</EndTime><Page>{$page}</Page></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();	
		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function order_close	//API-进口订单关闭
	*	<必要>
	*	input $customs_name	稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $Mft_number 	電報單號
	*	input $reason		撤单原因
	*	input $return_time	关闭申请创建时间(yyyy-MM-dd HH:mm:ss) 
	*
	*	註釋: $reason未填寫或空值默認 消费者撤销
	*		  $end_time未填寫是以前現在時間為主
	*
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*		</Header>
	*	</Message>
	*/

	function order_close($customs_name,$Mft_number,$reason="消费者撤销",$return_time=""){//PS:未測試
		//API-进口订单关闭 //關稅名稱,電報單號,撤單原因(默認消費者撤銷),關閉時間(默認當下)
		$msgtype = "cnec_jh_cancel";
		$date = date("Y-m-d H:i:s");
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		if(empty($return_time)){
			$return_time = date("Y-m-d H:i:s");
		}
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		$xml = $xml_head."<Message><Header><CustomsCode>{$this->enterprise_code}</CustomsCode><OrgName>{$this->enterprise}</OrgName><CreateTime>{$return_time}</CreateTime></Header><Body><MftNo>{$Mft_number}</MftNo></Body><Reason>{$reason}</Reason></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);	
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function return_goods_application	API-退货申请
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $Mft_number		申报单号
	*	input $shipping_number	运单号
	*	input $reason			退货原因
	*	input $shipping_company	快递公司名称
	*	input $product_array{
	*		customs_no	貨號
	*		quantity	退货数量
	*	}
	*	input $return_time	退货申请创建时间(yyyy-MM-dd HH:mm:ss)
	*	
	*	註釋: 退货原因如为空，则默认为“消费者退货”,快递公司如为空，则默认为订单申报时所选择的快递公司,$return_time為空則以當下時間表示退貨時間
	*
	*	<非必要>
	*	input $order_number		销售退货单号(但還是建議填寫)
	*	
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*		</Header>
	*	</Message>
	*
	*/
	function return_goods_application($customs_name,$Mft_number,$shipping_number,$order_number,$reason="消费者退货",$shipping_company,$product_array,$return_time=""){//未測試 我們的關稅不需要使用這個API 直郵不需做
		//API-退货申请 //關稅名稱,申报单号,运单号,销售退货单号,退货原因,快递公司名称,$product_array(退貨商品相關 詳細下面有說明) ,退貨時間 如果未填寫則表示現在時間點
		//$product_array customs_no 貨號,quantity 退货数量
		if($customs_name=="栎社机场"){
			return "該關稅區不支援此服務";
		}
		$msgtype = "cnec_jh_rejdec";
		$date = date("Y-m-d H:i:s");
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		if(empty($return_time)){
			$return_time = date("Y-m-d H:i:s");
		}
		$return_good_xml = ""; //退貨商品
		foreach($product_array as $key => $value){
			$return_good_xml = "<Detail><ProductId>{$producct_array[$key][customs_no]}</ProductId><RejectedQty>{$producct_array[$key][quantity]}</RejectedQty></Detail>";
		}
		$return_good_xml = "<RejectedGoods>{$return_good_xml}</RejectedGoods>";
		$xml = $xml_head."<Message><Header><CustomsCode>{$this->enterprise_code}</CustomsCode><CreateTime>{$return_time}</CreateTime></Header><Body><RejectedInfo><MftNo>{$Mft_number}</MftNo><WaybillNo>{$shipping_number}</WaybillNo><Flag>00</Flag><OuterNo>{$order_number}</OuterNo><Reason>{$reason}</Reason><LogisticsName>{$shipping_company}</LogisticsName>{$return_good_xml}</RejectedInfo></Body></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);	
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function query_return_goods	API-退货状态查询
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $Mft_number		申报单号
	*	input $shipping_number	运单号
	*	
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*		</Header>
	*	<Body>
	*		<RejectedInfo>	退货节点可循环（该订单下所有退货流转信息）
	*			<RejectedNo>申报单号</RejectedNo>		
	*			<OrderNo>订单号</OrderNo>			
	*			<WaybillNo>运单号</WaybillNo>		
	*			<DeclTime>申报时间</DeclTime>	
	*			<Status>状态</Status>		（00-未申报 ,10-已申报 ,20-审核通过 ,30-审核未通过 ,40-已验收 ,50-验收未通过）		
	*			<StatusDec>状态描述</StatusDec>
	*			<RejStatus>当前状态</RejStatus>
	*		</RejectedInfo>
	*	</Body>
	*
	*
	*	
	*/
	
	
	
	function query_return_goods($customs_name,$Mft_number,$shipping_number){//未測試 我們的關稅不需要使用這個API  //直郵不須做
		//API-退货状态查询 //關稅名稱,申报单号,运单号
		//$product_array customs_no 貨號,quantity 退货数量
		if($customs_name=="栎社机场"){
			return "該關稅區不支援此服務";
		}
		$msgtype = "cnec_jh_rejser";
		$date = date("Y-m-d H:i:s");
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		if(empty($return_time)){
			$return_time = date("Y-m-d H:i:s");
		}
		$xml = $xml_head."<Message><Header><MftNo>{$Mft_number}</MftNo><WaybillNo>{$shipping_number}</WaybillNo><Flag>00</Flag></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);	
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function query_good	API-备案商品查询（根据货号查询）
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $customs_no		貨號
	*	input $product_sku		電商sku
	*
	*	PS:	$customs_no與$product_sku最少填一個就可以了
	*
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*		</Header>
	*		<Body>
	*			<ProductId>货号（跨境平台商品备案时产生的唯一编码）</ProductId>
	*			<GoodsName>商品中文名称</GoodsName>
	*			<GoodsEnName>商品英文名称</GoodsEnName>
	*			<TariffNo>进口税则号</TariffNo>
	*			<HsCode>HS编码</HsCode>
	*			<Weight>净重（千克）</Weight>
	*			<Property>规格型号</Property>
	*			<Brand>品牌</Brand>
	*			<OriginPlace>原产地</OriginPlace>
	*			<Unit>计量单位</Unit>
	*			<Tax>税率（小数显示，例：0.1=10%）审核通过的商品才有</Tax>
	*			<Status>海关状态（0=未申报,1=待审批,2=审批通过,3=审批不通过,9=锁定）</Status>
	*			<GjStatus>检验检疫状态（1=锁定,0=正常）</GjStatus>
	*			<Guse>用途</Guse>
	*			<Gcomposition>成分</Gcomposition>
	*			<Gfunction>功能</Gfunction>
	*			<Detail>商品描述</Detail>
	*			<DsSku>电商sku</DsSku>
	*			<DsSkuCode>商品条码</DsSkuCode>
	*			<Comments>商品备注</Comments>
	*			<WarehouseCode>仓库代码</WarehouseCode>
	*			<WarehouseName>仓库名称</WarehouseName>
	*			<BizType>清关模式（1=保税备货，2=保税集货，3=一般进口）</BizType>
	*			<Tariff>关税税率</Tariff>
	*			<AddedValueTax>增值税税率</AddedValueTax>
	*			<ConsumptionDuty>消费税税率</ConsumptionDuty>
	*			<LegalQty>法定数量</LegalQty>
	*			<LegalUnit>法定计量单位</LegalUnit>
	*			<LegalUnitCode>法定计量单位代码</LegalUnitCode>
	*			<SecondQty>第二数量</SecondQty>
	*			<SecondUnit>第二数量单位</SecondUnit>
	*			<SecondUnitCode>第二数量单位代码</SecondUnitCode>
	*		</Body>
	*	</Message>
	*
	*/
	function query_good($customs_name,$customs_no="",$product_sku=""){
		//API-备案商品查询（根据货号查询） //關稅名稱,貨號,電商sku  PS:貨號,電商至少填一,關稅必填
		$msgtype = "cnec_jh_getgoods";
		$date = date("Y-m-d H:i:s");
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		$xml = $xml_head."<Message><Header><CustomsCode>{$this->enterprise_code}</CustomsCode><OrgName>{$this->enterprise}</OrgName><ProductId>{$customs_no}</ProductId><DsSku>{$product_sku}</DsSku></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);	
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function query_route	API-物流动态查询
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $shipping_number	運貨單	
	*	input $shipping_company 快遞公司名稱
	*
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*		</Header>
	*		<Body>
	*			<Lgs>	运单流转状态（该运单下所有物流状态）
	*				<WaybillNo>运单号</WaybillNo>
	*				<Express>快递公司</Express>	yyyy/MM/dd HH:mm:ss
	*				<OperTime>处理时间</OperTime>
	*				<Status>状态</Status>
	*				<StatusDec>状态描述</StatusDec>
	*			</Lgs>
	*		</Body>
	*	</Message>
	*
	*/
	function query_route($customs_name,$shipping_number,$shipping_company){ 
		//API-物流动态查询 //關稅名稱,運貨單,快遞公司名稱
		$msgtype = "cnec_jh_route";
		$date = date("Y-m-d H:i:s");
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		$xml = $xml_head."<Message><Header><LogisticsName>{$shipping_company}</LogisticsName><WaybillNo>{$shipping_number}</WaybillNo></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();	
		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function query_telegram_detailed_update	API-申报单详情查询接口（根据更新时间）
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	ipnut $status			狀態(00=未申报, 01=库存不足, 11=已报检验检疫, 12=检验检疫放行, 13=检验检疫审核未过, 14=检验检疫抽检,
									 21=已报海关, 22=海关单证放行, 23=海关单证审核未过, 24=海关货物放行, 25=海关查验未过, 99=已关闭
	*	input $start_time		开始时间 如: 采用yyyy-MM-dd HH:mm:ss格式（例："2013-09-01 13:30:05"）		
	*	input $end_time			結束时间 如: 采用yyyy-MM-dd HH:mm:ss格式（例："2013-09-01 13:30:05"）	
	*
	*	註釋:	如果位填寫 $start_time 是以前現在12小時為主
	*		 	如果位填寫 $end_time 是以前現在為主
	*
	*	<非必要>
	*	input $platform_code 	平台代碼
	*	input $page			指定查询页码(1/2/3...)，从1开始计算，每页1000条纪录	
	*	
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需</ResultMsg>
	*			<NextPage>是否存在下一页（T:是；F：否）</NextPage>
	*		</Header>
	*		<Body>
	*			<Mft>		申报单节点可循环
	*				<MftNo>申报单号</MftNo>
	*				<ManifestId>总署清单编号</ManifestId>
	*				<BizType>业务模式（1=保税单，2=分销单）</BizType>
	*				<OrderNo>订单号</OrderNo>
	*				<Dspt>电商平台</Dspt>
	*				<DealDate>下单时间(yyyy-MM-dd HH:mm:ss)</DealDate>
	*				<TariffAmount>关税额（免税请设置0）</TariffAmount>
	*				<AddedValueTaxAmount>增值税额（免税请设置0）</AddedValueTaxAmount>
	*				<ConsumptionDutyAmount>消费税额（免税请设置0）</ConsumptionDutyAmount>
	*				<GrossWeight>毛重</GrossWeight>
	*				<InsuranceFee>保价费（无保价费时自动设置为0）</InsuranceFee>
	*				<Payment>金额</Payment>
	*				<TaxAmount>税额</TaxAmount>
	*				<PostFee>运费</PostFee>
	*				<DsStorer>店铺名称</DsStorer>
	*				<BuyerAccount>买家账号</BuyerAccount>
	*				<PackageWeight>包裹称重(流水线称重上报)</PackageWeight>
	*				<Province>省</Province>
	*				<City>市</City>
	*				<District>区</District>
	*				<ConsigneeAddr>收货地址（包含省、市、区）</ConsigneeAddr>
	*				<DisAmount>优惠金额</DisAmount>
	*				<Promotions>	订单优惠清单列表
	*					<Promotion> 节点可循环
	*						<ProAmount>优惠金额</ProAmount>
	*						<ProRemark>优惠信息说明</ProRemark>
	*					</Promotion>
	*				</Promotions>
	*				<Goods>
	*					<Detail> 商品明细节点可循环
	*						<ProductId>货号（跨境平台商品备案时产生的唯一编码）</ProductId>
	*						<GoodsName>商品名称</GoodsName>
	*						<Qty>数量</Qty>
	*						<DeclPrice>申报单价</DeclPrice>
	*						<DeclTotal>申报总价</DeclTotal>
	*						<CurrCode>币种</CurrCode>
	*					</Detail>
	*				</Goods>
	*			</Mft>
	*			<Mft>	....
	*			</Mft>
	*		</Body>
	*	</Message>
	*
	*/		
	function query_telegram_detailed_update($customs_name,$status,$start_time="",$end_time="",$platform_code="",$page=""){ //如果開始結束於狀態都給空值則以-12hour現在時間未申報為主
		//API-申报单详情查询接口（根据更新时间） //關稅名稱  ,狀態(00=未申报, 01=库存不足, 11=已报检验检疫, 12=检验检疫放行, 開始時間 ,截止時間 ,平台代碼,頁數
		//13=检验检疫审核未过,14=检验检疫抽检,21=已报海关, 22=海关单证放行, 23=海关单证审核未过, 24=海关货物放行, 25=海关查验未过, 99=已关闭)
		$msgtype = "cnec_mft_byupdatetime";
		$date = date("Y-m-d H:i:s");	
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		if(empty($start_time)){
			$start_time = date("Y-m-d H:i:s", strtotime('-12 hour'));
		}
		if(empty($end_time)){
			$end_time = date("Y-m-d H:i:s");
		}
		$xml = $xml_head."<Message><Header><CustomsCode>{$this->enterprise_code}</CustomsCode><OrgName>{$this->enterprise}</OrgName><StartTime>{$start_time}</StartTime><EndTime>{$end_time}</EndTime><PtCode>{$platform_code}</PtCode><Status>{$status}</Status><Page>{$page}</Page></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function query_tax_list	API-税单查询接口
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $Mft_number		申报单号
	*
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*		</Header>
	*		<Body>
	*			<OrderNo>订单号</OrderNo>
	*			<AgentName>电商企业</AgentName>
	*			<TariffAmount>总关税额</TariffAmount>
	*			<AddedValueTaxAmount>总增值税额</AddedValueTaxAmount>
	*			<ConsumptionDutyAmount>总消费税额</ConsumptionDutyAmount>
	*			<Cargos>
	*				<Cargo>		节点可循环
	*					<HsNumber>HS编码</HsNumber>
	*					<Gnum>商品项号</Gnum>
	*					<TaxAmount>税额</TaxAmount>
	*					<SubTariffAmount>对应关税额</SubTariffAmount>
	*					<SubAddedValueTaxAmount>对应增值税额</SubAddedValueTaxAmount>
	*					<SubConsumptionDutyAmount>对应消费税额</SubConsumptionDutyAmount>
	*				</Cargo>
	*			</Cargos>
	*		</Body>
	*	</Message>
	*
	*
	*/
	function query_tax_list($customs_name,$Mft_number){
		//API-税单查询接口 //關稅名稱 ,電報單號
		//13=检验检疫审核未过,14=检验检疫抽检,21=已报海关, 22=海关单证放行, 23=海关单证审核未过, 24=海关货物放行, 25=海关查验未过, 99=已关闭)
		$msgtype = "cnec_tax_list";
		$date = date("Y-m-d H:i:s");	
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		if(empty($start_time)){
			$start_time = date("Y-m-d H:i:s", strtotime('-12 hour'));
		}
		if(empty($end_time)){
			$end_time = date("Y-m-d H:i:s");
		}
		$xml = $xml_head."<Message><Header><MftNo>{$Mft_number}</MftNo></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function query_order	API-申报单号单查询
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $order_number		订单号
	*
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*			<MftNo>申报单号（查询成功返回）</MftNo>
	*		</Header>
	*	</Message>
	*
	*/
	function query_order($customs_name,$order_number){
		//API-申报单号单查询 //關稅名稱 ,订单号
		//13=检验检疫审核未过,14=检验检疫抽检,21=已报海关, 22=海关单证放行, 23=海关单证审核未过, 24=海关货物放行, 25=海关查验未过, 99=已关闭)
		$msgtype = "cnec_get_mftno";
		$date = date("Y-m-d H:i:s");	
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");	
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		if(empty($start_time)){
			$start_time = date("Y-m-d H:i:s", strtotime('-12 hour'));
		}
		if(empty($end_time)){
			$end_time = date("Y-m-d H:i:s");
		}
		$xml = $xml_head."<Message><Header><OrderFrom>{$this->store_code}</OrderFrom><OrderNo>{$order_number}</OrderNo></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function new_purchase_orders	API-采购单新增接口
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $Asn_array{		採購訂單相關參數
	*		<必填>
	*		warehouse_Code 仓库海关代码, po_number 採購單號, decl_number 報檢單號, 
	*
	*		<非必填>
	*		Asnwho 採購人員, Asnwho_phone 採購人員聯絡方式, expected_date 預計收貨時間, memo 備註,  
	*		entry_number 報關單號, sales_web 銷售網站, shipment 啟運港, ship_date 啟運時間, 
	*					
	*				}
	*	input $product_array{	商品相關參數	$product_array = array{[0]=>array{[customs_no]=>"XXX",[quantity]=>"XXX" }   }這樣
	*		<必填>
	*		customs_no 貨號, quantity 數量, expiration_time 保存期限, weight 重量, currency_value 幣值, currency 幣制(使用貨幣)
	*
	*		<非必填>
	*		product_memo 商品備註,
	*				}
	*
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*			<SerialNo>平台采购单流水号（成功返回）</SerialNo>
	*		</Header>
	*	</Message>
	*
	*/

	function new_purchase_orders($customs_name,$Asn_array,$product_array){ //未測試 PS:直郵不須做 寫爽的
		//API-采购单新增接口 //關稅名稱,$Asn_array 採購訂單相關參數,$product_array 商品相關參數
		
		$msgtype = "cnec_purchase_order";
		$date = date("Y-m-d H:i:s");	
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼
		if(empty($Asn_array['create_time'])){
			$create_time = date("Y-m-d H:i:s");
		}else{
			$create_time = $Asn_array['create_time'];
		}
		
		/*  採購單  start   */
		$po_xml = "";
		foreach($product_array as $key => $value){
			$po_xml = "<PoGood><ProductId>{$value['customs_no']}</ProductId><Qty>{$value['quantity']}</Qty><GoodsRemark>{$value['product_memo']}</GoodsRemark><ExpirationTime>{$value['expiration_time']}</ExpirationTime>
			<Weight>{$value['weight']}</Weight><CurrencyValue>{$value['currency_value']}</CurrencyValue><Currency>{$value['currency']}</Currency></PoGood>";
		}
		$po_xml = "<PoGoods>{$po_xml}</PoGoods>";
		/*  採購單  end   */
		
		/* orders start  */
		$order_xml = "<Asnwho>{$Asn_array['Asnwho']}</Asnwho><Asnwhophone>{$Asn_array['Asnwho_phone']}</Asnwhophone><Expected_date>{$Asn_array['expecteddate']}</Expecteddate><Remark>{$Asn_array['memo']}</Remark>
		<WarehouseCode>{$Asn_array['warehouse_Code']}</WarehouseCode><PoNo>{$Asn_array['po_number']}</PoNo><EntryNo>{$Asn_array['entry_number']}</EntryNo><DeclNo>{$Asn_array['decl_number']}</DeclNo><SalesSite>{$Asn_array['sales_web']}</SalesSite>
		<Shipment>{$Asn_array['shipment']}</Shipment><ShipDate>{$Asn_array['ship_date']}</ShipDate><OrderShop>{$this->order_shop}</OrderShop>";
		/* orders end    */
		
		$xml = $xml_head."<Message><Header><CustomsCode>{$this->enterprise_code}</CustomsCode><OrgName>{$this->enterprise}</OrgName><CreateTime>{$create_time}</CreateTime><Body><Order>{$order_xml}{$po_xml}</Order></Body></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function purchase_delete	API-采购单取消接口
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $serial_number	平台採購單流水號
	*	input $orders_number	電商原始採購單號(其實平台跟電商都是同一個訂單號碼)
	*	input $create_time		採購單建立時間
	*	
	*	註釋:如果沒給建立時間則以現在時間為主
	*
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*			<Success>true:成功，false:失败</Success>
	*		</Header>
	*	</Message>
	*
	*/
	
	function purchase_delete($customs_name,$serial_number,$orders_number,$create_time=""){	//未測試  PS:直郵不須做
		//API-采购单取消接口 //關稅名稱,平台採購單流水號,電商原始採購單號(其實平台跟電商都是同一個訂單號碼),採購單建立時間
		$msgtype = "cnec_purchase_cancel";
		$date = date("Y-m-d H:i:s");	
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		if(empty($create_time)){
			$create_time = date("Y-m-d H:i:s");
		}
		
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼
		$xml = $xml_head."<Message><Header><CustomsCode>{$this->enterprise_code}</CustomsCode><OrgName>{$this->enterprise}</OrgName><CreateTime>{$create_time}</CreateTime><Body><Order><SerialNo>{$serial_number}</SerialNo><PoNo>{$orders_number}</PoNo></Order></Body></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function product_register	API-商品备案接口
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $product_array{
	*	<必要>
	*	name 商品中文名称, eng_name 商品英文名称, hs_number HS编码, wight 公克, serving_size 規格型號, product_sold_country 原產地, brand 品牌,
	*	unit_name 規格, supplier 供應商, 
	*
	*
	*
	*	<非必要>
	*	import_number 进口税税则号, guse 用途, gcomposition 成分, gfunction 功能, intro 商品描述, gtin13 條碼, 
	*	prdouct_meno 商品備註, warehouse_code 仓库代码, biz_type 清关模式（1=保税备货，2=保税集货，3=一般進口）,
	*
	*
	*	<這段非必要但是個人認為是必要的>
	*	product_sku 電商sku, price 销售单价（元）(栎社机场填写), quantity 法定數量, second_quantity 第二数量,
	*
	*
	*	註釋: warehouse_code 仓库代码 电商在该关区下有多个仓库的需要指定其中一家仓库，具体仓库代码请联系客服或者对应仓库
	*		  biz_type 清关模式, 空港保税关区场合，请填写清关模式；其他关区，不需填写,
	*
	*
	*	<固定值>	<Flag>1<Flag>
	*  						}
	*
	*	input $photo{ 
	*	形式用$_FILES直接丟入但是必須以	product_photo 商品圖片(只有這個必要), label_photo 中文标签图片 , product_register (商品或生产企业取得的认证、注册、备案等资质),
	*									product_examine (商品取得的自由销售证明、第三方检验鉴定证书), prodcut_directions_data (产品说明的中文对照资料),
	*									product_warning  (消费警示), other_data (其他可提供的证明材料), product_composition 成分圖片
	*
	*	<必要> 
	*	product_photo 商品圖片(只有這個必要)
	*
	*	<非必要>
	*	label_photo 中文标签图片 , product_register (商品或生产企业取得的认证、注册、备案等资质),
	*	product_examine (商品取得的自由销售证明、第三方检验鉴定证书), prodcut_directions_data (产品说明的中文对照资料),
	*	product_warning  (消费警示),other_data (其他可提供的证明材料), product_composition 成分圖片
	*
	*	註釋:
	*	product_photo  商品圖片  對應 文件上傳hsfile欄位,(只有這是必要),
	*	label_photo (中文標籤文件名) 對應 文件上傳cnfile欄位,
	*	product_register (商品或生产企业取得的认证、注册、备案等资质) 對應 文件上傳attachfile1欄位,
	*	product_examine  (商品取得的自由销售证明、第三方检验鉴定证书) 對應 文件上傳attachfile2欄位,
	*	prodcut_directions_data (产品说明的中文对照资料)  對應 文件上傳attachfile3欄位,
	*	product_warning  (消费警示) 對應 文件上傳attachfile4欄位,
	*	other_data (其他可提供的证明材料) 對應 文件上傳attachfile5欄位,
	*	product_composition 成分 對應 文件上傳cpfile欄位
	*	
	*	
	*
	*
	*	}
	*
	*	output 
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*			<ProductId>系统自动生成货号</ProductId>
	*		</Header>
	*	</Message>
	*
	*
	*/
	
	function product_register($customs_name,$product_array,$photo){
		//API-商品备案接口 //(必要參數) 關稅名稱,商品陣列,$photo <-- 這東西就是 $_FILES

		$msgtype = "cnec_jh_hscode";
		$date = date("Y-m-d H:i:s");	
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼
		
		if($customs_name="空港保税物流中心" && empty($producct_array['biz_type'])){
			return "空港保税物流中心,必須填入清关模式";
			exit;
		}
		
		$photo_array();
		$wight =  round(($producct_array['wight']/1000),4);//保價費(取小數點第四位)
		
		$body_xml = "<Name>{$producct_array['name']}</Name><NameEn>{$producct_array['eng_name']}</NameEn><HsNumber>{$producct_array['hs_number']}</HsNumber><Weight>{$wight}</Weight><Property>{$producct_array['serving_size']}</Property>
					<Gproduction>{$producct_array['product_sold_country']}</Gproduction><Brand>{$producct_array['brand']}</Brand><Unit>{$producct_array['unit_name']}</Unit><Guse>{$producct_array['guse']}</Guse>
					<Gcomposition>{$product_array['gcomposition']}</Gcomposition><Gfunction>{$product_array['gfunction']}</Gfunction><Detail>{$product_array['intro']}</Detail><DsSku>{$product_array['product_sku']}</DsSku>
					<DsSkuCode>{$product_array['gtin13']}</DsSkuCode><Comments>{$product_array['prdouct_meno']}</Comments><WarehouseCode>{$product_array['warehouse_code']}</WarehouseCode><BizType>{$producct_array['biz_type']}</BizType>
					<Flag>1</Flag><Supplier>{$product_array['supplier']}</Supplier>";	// 這邊負責<body>中的<Name>到<Supplier> 的模塊
					
		$photo_xml= "";//這邊是負責<HsfileName>到<CpfileName>模塊
		$other_xml = "<LegalQty>{$producct_array['quantity']}</LegalQty><SecondQty></SecondQty>{$producct_array['second_quantity']}<Price>{$product_array['price']}</Price>";//剩下的模塊
		
		$photo_title = array("product_photo"=>"HsfileName","label_photo"=>"CnfileName","product_register"=>"AttachFile1Name","product_examine"=>"AttachFile2Name","prodcut_directions_data"=>"AttachFile3Name","product_warning"=>"AttachFile4Name",
							 "other_data"=>"AttachFile5Name","product_composition"=>"CpfileName");//key值對應 xml值
							 
		$photo_file = array("product_photo"=>"hsfile","cn_label_photo"=>"cnfile","product_register"=>"attachfile1","product_examine"=>"attachfile2","prodcut_directions_data"=>"attachfile3","product_warning"=>"attachfile4",
							"other_data"=>"attachfile5","product_composition"=>"cpfile");//key值對應 file傳送係數
		//做紀錄晚一點要使用
		$file = array();//這個陣列是儲存流文件使用
		
		
		foreach($photo as $key => $value){
			
			if(empty($photo['product_photo']['name'])){//先判斷是否有名子來確認是否上傳
				return "您沒有上傳商品圖片!!";
				exit;
			}  
			
			if(!empty($value['name']) && ($value['type']=="image/gif" || $value['type']=="image/png" || $value['type']=="image/jpeg" || $value['type']=="image/bmp")){//為圖檔並且有檔名(等於有圖)		
				$photo_xml .="<{$photo_title[$value]}>{$value['name']}<{$photo_title[$value]}>";		
				$new_file = new CURLFile($value['tmp_name'],$value['type'],$photo_file[$value]);
				array_push($file,$new_file);//加入一個新的array到 $file 後面
			}
			else if(empty($value['name'])){ //或是沒傳資料(因為除了商品圖片都非必要)
				
			}else{ //剩下為有檔案且不為空則非我需要的
				return "檔案名稱或是圖片格式不符合!!";
				exit;
			}	
			
		}
		
		
		
		$xml = $xml_head."<Message><Header><CustomsCode>{$this->enterprise_code}</CustomsCode><OrgName>{$this->enterprise}</OrgName></Header><Body>{$body_xml}{$photo_xml}{$other_xml}</Body></Message>";	
		
		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		
		$post = array($post,$file);//查看網路說流文件需要這樣弄
		
		$post = http_build_query($post);
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		//curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html")); //因為會限制到file 因此將他默認皆可
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function query_tax_price	API-税费查询接口
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $product_array{    商品陣列 如:  $product_array => array([0]=>array([customs_no]=>"",[product_name]=>"")) 這樣
	*	<必要>
	*	customs_no 货号（跨境平台商品备案时产生的唯一编码）, product_name 商品名称, quantity 数量, price 商品单价
	*					}
	*	input $shipping_fee 運費（无運費时请设置0）
	*	input $insurance_fee 保价费（无保价费时请设置0）
	*	
	*	註釋:如果不填入運費與保价费 會直接給0(無運費)(無保價費)
	*
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*		</Header>
	*		<Body>
	*			<TariffAmount>关税额</TariffAmount>
	*			<AddedValueTaxAmount>增值税额（七折后的税额）</AddedValueTaxAmount>
	*			<ConsumptionDutyAmount>消费税额（七折后的税额）</ConsumptionDutyAmount>
	*			<TaxAmount>总税额（七折后的税额）</TaxAmount> <--實際上這個是关税额+增值税额（七折后的税额）+消费税额（七折后的税额） 的值
	*		</Body>
	*	</Message>
	*	
	*
	*
	*/

	function query_tax_price($customs_name,$product_array,$shipping_fee="0",$insurance_fee="0"){	
		//API-税费查询接口 //關稅名稱 商品陣列,运费（无运费时请设置0）,保价费（无保价费时请设置0）
		//customs_no 货号（跨境平台商品备案时产生的唯一编码）, product_name 商品名称, quantity 数量, price 商品单价
		$msgtype = "cnec_tax_price";
		$date = date("Y-m-d H:i:s");	
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		$shipping_fee  =  round($shipping_fee,2);//保價費(取小數點第二位)
		$insurance_fee =  round($insurance_fee,2);//保價費(取小數點第二位)
		$product_xml="";
		
		foreach($product_array as $key=>$value){
			$product_xml.="<Detail><ProductId>{$value['customs_no']}</ProductId><GoodsName>{$value['product_name']}</GoodsName><Qty>{$value['quantity']}</Qty><Price>{$value['price']}</Price></Detail>";
		
		}	
		$product_xml="<Goods>{$product_xml}</Goods>";
		$xml = $xml_head."<Message><Header><PostFee>{$shipping_fee}</PostFee><InsuranceFee>{$insurance_fee}</InsuranceFee>{$product_xml}</Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function customs_filing_application	API-商品备案申报接口
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $customs_no		貨號（跨境平台商品备案时产生的唯一编码）
	*
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：操作成功；F：操作失败</Result>
	*			<ResultMsg>结果描述（操作失败时必需）</ResultMsg>
	*		</Header>
	*	</Message>
	*
	*/
	
	function customs_filing_application($customs_name,$customs_no){	//未測試
		//API-商品备案申报接口  //關稅名稱,貨號（跨境平台商品备案时产生的唯一编码）
		$msgtype = "cnec_product_decl";
		$date = date("Y-m-d H:i:s");	
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		$xml = $xml_head."<Message><Header><ProductId>{$customs_no}</ProductId></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function consumer_account	API-消费者姓名身份证查询
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $account			購物網站帳號
	*	
	*	output
	*	<Message>
	*		<Header>
	*			<Result>T：存在；F：不存在</Result>
	*		</Header>
	*		<Body>
	*			<Idnum>身份证号(前4位+(身份证长度-8)个‘*’号+后4位)</Idnum>
	*			<Name>姓名((姓名长度-1)个‘*’+最后一个字)</Name>
	*			<IsAuth>是否已认证（0=未认证 1=认证通过 2=认证未通过）</IsAuth>
	*		</Body>
	*	</Message>
	*
	*/
	
	function consumer_account($customs_name,$account){	//未測試
		//API-消费者姓名身份证查询  //關稅名稱,購物網站帳號
		$msgtype = "cnec_jh_account";
		$date = date("Y-m-d H:i:s");	
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		$xml = $xml_head."<Message><Header><OrderFrom>{$this->store_code}</OrderFrom><Account>{$account}</Account></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
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
	*	function http_tax_page	API-税单页面查看接口
	*	<必要>
	*	input $customs_name		稅關名稱 如: 北仑保税区 或是 栎社机场	
	*	input $Mft_number		申报单号
	*
	*	output
	*	輸出網頁
	*
	*/
	function http_tax_page($customs_name,$Mft_number){
		//API-税单页面查看接口  //關稅名稱,申报单号
		$msgtype = "cnec_tax_page";
		$date = date("Y-m-d H:i:s");	
		$sign = md5("{$this->user_id}{$this->user_key}{$date}");
		$customs = $this->customs_array[$customs_name]; //依照輸入的關稅口名稱變更代碼	
		$xml = $xml_head."<Message><Header><MftNo>{$Mft_number}</MftNo></Header></Message>";		
		$post=array("userid"=>$this->user_id,"timestamp"=>$date,"xmlstr"=>$xml,"sign"=>$sign,"msgtype"=>$msgtype,"customs"=>$customs);//要輸入的數值
		$post = http_build_query($post);
		$curl = curl_init();		
		if(strstr($this->URL_base,'https://')){//SSL POST
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/html"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("charset:utf-8"));
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Accept:text/html"));
		curl_setopt($curl,CURLOPT_URL, $this->URL_base); // 設定所要傳送網址
		curl_setopt($curl,CURLOPT_HEADER, false); // 不顯示網頁
		curl_setopt($curl,CURLOPT_POST,1); // 開啟回傳
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post); // 將post資料塞入
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
		$response=curl_exec($curl); // 執行網頁
		$err = curl_error($curl); //確認是否有回報錯誤
		curl_close($curl); // 關閉網頁
		
		if($err){
			return $err;
		}else{
			return $response;
		}		
	}
}
	/*
	$test = new customs;
	$customs_name = "栎社机场";
	*/
	
	/*
	//API-申报单状态查询（申报单号）
	$test = new customs;
	$order ="31092018I126632467";
	//$productId = "31091861J780001512";
	$user =  $test->customs_order_query($order,$customs_name);  
	$array = $test->xml_transform_array($user);
	print_r($array);
	*/
	/*
	$product_sid = "31091861J780001512";
	$sku = "";
	$user =  $test->query_good($product_sid,$sku,$customs_name);  
	$array = $test->xml_transform_array($user);
	print_r($array);
*/
/*
	$start_time = date("Y-m-d H:i:s", strtotime('-12 hour'));
	$end_time = date("Y-m-d H:i:s");
	
	$user =  $test->query_order_update($customs_name,$start_time,$end_time);  
	print_r($user);
	
	$array = $test->xml_transform_array($user);
	print_r($array);
	
	*/
	/*
	$LogisticsName = "顺丰速运";
	$WaybillNo = "92101805624";
	
	$user =  $test->query_route($customs_name,$WaybillNo,$LogisticsName);  
	print_r($user);
	
	$array = $test->xml_transform_array($user);
	print_r($array);
	*/
	/*
	$start_time = date("Y-m-d H:i:s", strtotime('-12 hour'));
	$end_time = date("Y-m-d H:i:s");
	$status = "00";
	
	$user =  $test->query_telegram_detailed_update($customs_name,$start_time,$end_time,$status);  
	
	$array = $test->xml_transform_array($user);
	print_r($array);
	*/
	/*
	$MftNo= "31092018I126632467";
	
	$user =  $test->query_tax_list($customs_name,$MftNo);  
	
	$array = $test->xml_transform_array($user);
	print_r($array);
	*/
	/*
	$OrderNo = "18090415472330";
	
	$user =  $test->query_order($customs_name,$OrderNo);  
	
	$array = $test->xml_transform_array($user);
	print_r($array);
	*/
	/*
	$customs_no ="31091861J780001512";
	$product_name = "胡老爹菓子工房-胡桃塔礼盒x2盒 ";
	$quantity = "2";
	$price = "170.8333";
	$shipping_fee ="100";
	
	$user =  $test->query_tax_price($customs_name,$customs_no,$product_name,$quantity,$price,$shipping_fee);  
	
	$array = $test->xml_transform_array($user);
	print_r($array);
	*/
	/*
	$Mft_number= "31092018I126632467";
	
	$user =  $test->http_tax_page($customs_name,$Mft_number);  
	
	echo $user;
	*/
	
	
?>