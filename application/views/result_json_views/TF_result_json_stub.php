{
	"questionIsOpen": <?=($question_model->question_is_open==false)?"false":"true"?>,
	"label": ["results"],
	        "values": [
					<? $i=0; foreach($binnedResults as $binnedEntryKey=>$binnedEntry) {
						
						$outputStringArray[$i] = "{\n";
						$outputStringArray[$i] .= '"label": "' . $binnedEntryKey . '",' . "\n";
						$outputStringArray[$i] .= '"values": [' . $binnedEntry . ']' . "\n";
						$outputStringArray[$i] .= "}\n";
						$i++;
					} 
					echo join($outputStringArray, ",");
					?>
			]
}