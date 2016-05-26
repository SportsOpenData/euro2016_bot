<?php
/**
* Telegram Bot example for Italian Museums of DBUnico Mibact Lic. CC-BY
* @author Francesco Piero Paolicelli @piersoft
*/
//include("settings_t.php");
include("Telegram.php");

class mainloop{

	const MAX_LENGTH = 4096;

	function start($telegram,$update)
	{

		date_default_timezone_set('Europe/Rome');
		$today = date("Y-m-d H:i:s");

		$text = $telegram->Text();
		$chat_id = $update["message"] ["chat"]["id"];
		$user_id=$update["message"]["from"]["id"];
		$location=$update["message"]["location"];
		$reply_to_msg=$update["message"]["reply_to_message"];

		$this->shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
		$db = NULL;

	}

	//gestisce l'interfaccia utente
 	function shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
	{
		date_default_timezone_set('Europe/Rome');
		$today = date("Y-m-d H:i:s");

		$text = cleanContent($text, '@sportsop_bot');

		switch($content) {
			case "Gironi":
			case "gironi":
			case "/gironi":
				getGironi($telegram, $chat_id);
				break;
			case "Live":
			case "live":
			case "/live":
				getLive($telegram, $chat_id);
				break;
			case "/domani":
			case "domani":
			case "Domani":
				getTomorrow($telegram, $chat_id);
				break;
			case "/start":
			case "info":
			case "©️info":
				getDefaultException($telegram, $chat_id);
				break;
			default:
				getDefaultException($telegram, $chat_id);
				break;
		}
	}

	function getGironi($telegram, $chat_id) {

		$json_string = file_get_contents("http://soccer.sportsopendata.net/v1/leagues/uefa-euro-2016/seasons/16/standings");
		$parsed_json = json_decode($json_string, true);
		$count = 0;
		$countl = array();
		$temp_c1="\n";
		foreach($parsed_json['data']['standings'] as $data=>$csv1){

			$temp_c1 .= $data."\n";
			foreach($csv1 as $keyval=>$team){
				$temp_c1 .= getFlag($team['team'])." ".$team['team']." ".$team['position']."\n";
			}
			$temp_c1 .="----\n";

		}
		$chunks = str_split($temp_c1, MAX_LENGTH);
		foreach($chunks as $chunk)
		{
			$forcehide=$telegram->buildForceReply(true);
			//chiedo cosa sta accadendo nel luogo
			$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);

			$telegram->sendMessage($content);
		}
		$log=$today. ",gironi," .$chat_id. "\n";
		create_keyboard($telegram,$chat_id);
		return true;
	}

	function getLive($telegram, $chat_id) {

		// Cerco i rounds disponibili per il torneo
		$json_string = file_get_contents("http://soccer.sportsopendata.net/v1/leagues/uefa-euro-2016/seasons/16/rounds");
		$parsed_json = json_decode($json_string, true);
		foreach($parsed_json['data']['rounds'] as $key=>$round){
			$json_round_string = file_get_contents("http://soccer.sportsopendata.net/v1/leagues/uefa-euro-2016/seasons/16/rounds/".$round['round_slug']."/matches?date=today");
			$parsed_round_json = json_decode($json_round_string, true);
			$count = 0;
			$countl = 0;
			$temp_c1="\n";
			$option=array();
			foreach($parsed_round_json['data']['matches'] as $data=>$csv1){

				$from = strtotime($csv1['date_match']);
				$to = strtotime($csv1['date_match']+90*60);
				if ($today >= $from && $today <= $to) {
					$temp_c1 .="⚽️ ".$csv1['home']['team']."-".$csv1['away']['team']." : ".$csv1['match_result']."\n";
					$countl++;
				}
			}
		}

		// Se temp_c1 è vuoto allora rispondi che non ci sono match
		if($temp_c1 == "\n" || $temp_c1 == ""){
			$temp_c1 .= "Nessun Match Trovato per la giornata di oggi!";
		}

		$chunks = str_split($temp_c1, MAX_LENGTH);
		foreach($chunks as $chunk) {
			$forcehide=$telegram->buildForceReply(true);
			$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
		}

		create_keyboard($telegram,$chat_id);
		return true;
	}

	function getTomorrow($telegram, $chat_id) {
	
		// Cerco i rounds disponibili per il torneo
		$json_string = file_get_contents("http://soccer.sportsopendata.net/v1/leagues/uefa-euro-2016/seasons/16/rounds");
		$parsed_json = json_decode($json_string, true);
		foreach($parsed_json['data']['rounds'] as $key=>$round){
			$json_round_string = file_get_contents("http://soccer.sportsopendata.net/v1/leagues/uefa-euro-2016/seasons/16/rounds/".$round['round_slug']."/matches?date=tomorrow");
			$parsed_round_json = json_decode($json_round_string, true);
			$count = 0;
			$countl = 0;
			$temp_c1="\n";
			$option=array();
			foreach($parsed_round_json['data']['matches'] as $data=>$csv1){

				$from = strtotime($csv1['date_match']);
				$to = strtotime($csv1['date_match']+90*60);
				if ($today >= $from && $today <= $to) {
					$temp_c1 .="⚽️ ".$csv1['home']['team']."-".$csv1['away']['team']." : ".$csv1['match_result']."\n";
					$countl++;
				}
			}
		}

		// Se temp_c1 è vuoto allora rispondi che non ci sono match
		if($temp_c1 == "\n" || $temp_c1 == ""){
			$temp_c1 .= "Nessun Match Trovato per la giornata di domani!";
		}

		$chunks = str_split($temp_c1, MAX_LENGTH);
		foreach($chunks as $chunk) {
			$forcehide=$telegram->buildForceReply(true);
			$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
		}

		create_keyboard($telegram,$chat_id);
		return true;
	}


	function getDefaultException($telegram, $chat_id) {
	
		$reply = "SPORTS OPEN DATA (http://sportsopendata.net/)\nSports Open Data è un’associazione culturale senza scopo di lucro che si pone l'obiettivo di fornire dati statistici in ambito sportivo in modalità open data attraverso delle API Rest.";
		$reply .="\nQuesto bot è stato realizzato durante il raduno Spaghetti openData 2016 a Trento da Paolo Riva, Piersoft e Alice Giorgio";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		create_keyboard($telegram,$chat_id);
		return true;

	}

	function getFlag($team) {

		switch($team) {
			case "France":
				$flag = "🇫🇷";
				break;
			case "Romania":
				$flag = "🇷🇴";
				break;
			case "Albania":
				$flag = "🇦🇱";
				break;
			case "Switzerland":
				$flag = "🇨🇭";
				break;
			case "Wales":
				$flag = "🏳";
				break;
			case "Slovakia":
				$flag = "🇸🇰";
				break;
			case "England":
				$flag = "🏳";
				break;
			case "Russia":
				$flag = "🇷🇺";
				break;
			case "Poland":
				$flag = "🇵🇱";
				break;
			case "Northern Ireland":
				$flag = "🏳";
				break;
			case "Germany":
				$flag = "🇩🇪";
				break;
			case "Ukraine":
				$flag = "🇺🇦";
				break;
			case "Croatia":
				$flag = "🇭🇷";
				break;
			case "Turkey":
				$flag = "🇹🇷";
				break;
			case "Spain":
				$flag = "🇪🇸";
				break;
			case "Czech Republic":
				$flag = "🇨🇿";
				break;
			case "Belgium":
				$flag = "🇧🇪";
				break;
			case "Republic of Ireland":
				$flag = "🇮🇪";
				break;
			case "Sweden":
				$flag = "🇸🇪";
				break;
			case "Italy":
				$flag = "🇮🇹";
				break;
			case "Austria":
				$flag = "🇦🇹";
				break;
			case "Hungary":
				$flag = "🇭🇺";
				break;
			case "Portugal":
				$flag = "🇵🇹";
				break;
			case "Iceland":
				$flag = "🇮🇸";
				break;
			default:
				$flag = "";
				break;
		}

		return $flag;
	}

	// Crea la tastiera
	function create_keyboard($telegram, $chat_id)
	{
		$option = array(array("🚩 Gironi","⚽️ Live","⏰ Domani"),array("©️info"));
		$keyb = $telegram->buildKeyBoard($option, $onetime=true);
		$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[fai la tua scelta]");
		$telegram->sendMessage($content);
	}

	function cleanContent($content, $telegramBot) {

	if (strpos($content,$telegramBot) !== false){
		$content=str_replace($telegramBot." ","",$content);
		$content=str_replace($telegramBot,"",$content);
	}
	if (strpos($content,"⚽️") !== false) $content=str_replace("⚽️ ","",$content);
	if (strpos($content,"🚩") !== false) $content=str_replace("🚩 ","",$content);
	if (strpos($content,"⏰") !== false) $content=str_replace("⏰ ","",$content);
	
	return $content;
	
}

}

?>
