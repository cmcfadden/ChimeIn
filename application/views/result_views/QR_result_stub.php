<script type="text/javascript" src="<?=base_url()?>assets/js/wordCloud.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/js/jquery.contextMenu.js"></script>
<link rel="stylesheet" href="<?=base_url()?>assets/css/jquery.contextMenu.css" type="text/css" media="screen" />
<script language="javascript" type="text/javascript" src="<?=base_url()?>assets/js/json2.js"></script>

<ul id="cloudMenuThing" class="contextMenu" style="display:none;"><li><a href="#filterNumbers">Filter Numbers</a></li></ul>

<script>
	var updateTimer;
	var word_filter = '';
	var exclude_list = new Array();
	var exclude_array = new Array();
	var groupDroppedItem;
	var groupTargetItem;
	var filterNumbers = false;

	
	j(document).ready(function() {		
		updateTimer = setInterval("updateResultView();", 5000);
		j("#CIword_cloud").contextMenu({
			menu: 'cloudMenuThing'
		},
		function(action, el, pos) {
		if(action == "filterNumbers") {
			filterNumbersFromCloud();
		}}
		);
	
	});
	
	function buildContextMenu(lemma, wordArray, targetElement) {
		
		if(j("#context"+lemma).length ==0) {
			j("#cloudMenu").after("<ul id=context" + lemma + " class=\"contextMenu\" style='display:none;'></ul>");			
		}
		else {
			j("#context"+lemma).empty();
		}
		

		j.each(wordArray["sourceWords"], function(index,value) {
			j("#context"+lemma).append("<li><a href='#" + value + "'>" + value + "</a></li>");
		});
		j("#context"+lemma).append("<li class=\"separator Exclude\"><a href='#exclude'>Exclude</a></li>");
		j("#context"+lemma).append("<li class=\"\"><a href='#destroy'>Destroy Group</a></li>");
		
		
		j(targetElement).contextMenu({
				      menu: 'context'+lemma
		    },
		    function(action, el, pos) {
				if(action == "destroy") {
					destroyGroup(j(el).attr('id'));
				}
				else if(action == "exclude") {
					excludeWordFromCloud(j(el).attr('id'));								
				}
				else {
					removeWordFromGroup(j(el).attr('id'), action);
				}

		    });
	}
	
	
	function destroyGroup(targetElement) {

		j.ajax({
			type: 'POST',
			url: "<?= site_url('result/destroyGroup')?>",
			data: {groupName : targetElement.replace("group_g_", ""), question_id : "<?= $question_model->question_id ?>"},
			success: function (response) {
				clearTimeout(updateTimer);
				updateResultView();

			}
		});
	}
	
	function removeWordFromGroup(groupName, wordName) {
		j.ajax({
			type: 'POST',
			url: "<?= site_url('result/removeWordFromGroup')?>",
			data: {groupName : groupName.replace("group_g_", ""), wordName: wordName, question_id : "<?= $question_model->question_id ?>"},
			success: function (response) {
				clearTimeout(updateTimer);
				updateResultView();

			}
		});
	}
	
	function dropEvent(droppedItem, targetItem) {

		groupDroppedItem=droppedItem;
		groupTargetItem=targetItem;
		j("#"+targetItem).removeClass("CIgroup_hover"); 
		if(targetItem.search("group_g_") != -1) { // adding a new item to a group
			updateGroup(groupTargetItem, groupDroppedItem);
			
		}
		else { //creating a new group
			if(groupTargetItem != "CIword_cloud") {
				j("#field_group_name").val("");
				j.fancybox({'href':"#create_group_name"});
			}
		}
	}
	
	
	function dropHover(targetItem) {
		j("#"+targetItem).addClass("CIgroup_hover");
	}
	
	function dropLeave(targetItem) {

		j("#"+targetItem).removeClass("CIgroup_hover");
	}
	
	function createGroup() {
		j.fancybox.close();
		groupName = j("#field_group_name").val();
		groupItem1 = groupDroppedItem.replace("word_", "");
		groupItem2 = groupTargetItem.replace("word_", "");
		


		j.ajax({
			type: 'POST',
			url: "<?= site_url('result/createGroup')?>",
			data: {groupName : groupName, groupItem1: groupItem1, groupItem2: groupItem2, question_id : "<?= $question_model->question_id ?>"},
			success: function (response) {
				clearTimeout(updateTimer);
				updateResultView();

			}
		});

		
	}
	
	function updateGroup(targetGroup, droppedItem) {
	
		j.ajax({
			type: 'POST',
			url: "<?= site_url('result/updateGroup')?>",
			data: {groupName: targetGroup.replace("group_g_", ""), newItem: droppedItem.replace("word_", ""), question_id : "<?= $question_model->question_id ?>"},
			success: function (response) {
				clearTimeout(updateTimer);
				updateResultView();

			}
		});
		
	}
	
	
	function showGroupMembers(lemma) {
		

		
	}
	
	function updateResultView()
	{
		var result_array = {};

		j.each(j(".result_node"), function(i,v){
			num_str = j(v).attr('id');
			num_str = num_str.replace("result_id", "");
			num_str = parseInt(num_str);
			result_array[i] = num_str;
		});

		result_array = JSON.stringify(result_array);

		j.ajax({
						type: 'POST',
						url: "<?= site_url('result/updateResultForFRAjax')?>",
						data: {json_data : result_array, question_id : "<?= $question_model->question_id ?>", selected_word: word_filter, exclude_list: JSON.stringify(exclude_list), filterNumbers: filterNumbers },
						success: function (response) 
										{
											clearInterval(updateTimer);

											var response_array = eval("(" + response + ")");

											if(response_array['results_to_add_string'] != "")
											{
												j("#FR_all_results_list").prepend(response_array['results_to_add_string']);
											}
											
											for(add_response in response_array['results_to_add'])
											{
												element_to_add = "#result_id"+response_array['results_to_add'][add_response];
												j(element_to_add).hide();
											}
											
											for(add_response in response_array['results_to_add'])
											{
												element_to_add = "#result_id"+response_array['results_to_add'][add_response];
												j(element_to_add).slideDown('slow', function(){
													j(this).effect("highlight", {color : "#C2D9C2"}, 1000);
												});
											}
											
											
											myWordCloud.addWordsWithArray(response_array['sortedArray']);
											
											if(myWordCloud.removedWords[word_filter]) {
												j("#CIword_cloud").children().removeClass("highlightWordInCloud");
												showResponsesWithWord();
											}
											
											
											updateTimer = setInterval("updateResultView();", 5000);
										},
						async: true
					}); 
	}
	
	function excludeWordFromCloud(word) {
		var originalWord = word;
		var exclusionType;

		if(originalWord.indexOf("word_") != -1) {
			var tempWordArray = myWordCloud.wordCloudArray[originalWord.replace("word_","")]["sourceWords"];			
			exclusionType="word";
		}
		else if(originalWord.indexOf("group_") != -1) {
			var tempWordArray = myWordCloud.wordCloudArray[originalWord.replace("group_","")]["sourceWords"];	
			var groupName = myWordCloud.wordCloudArray[originalWord.replace("group_","")]["groupTitle"];
			exclusionType="group";
		}


		exclude_list = j.merge(exclude_list, tempWordArray);
		exclude_array[word] = tempWordArray;

		clearTimeout(updateTimer);
		updateResultView();
		
		
		if(exclusionType=="word") {
			var insertText = "<span class=\"exclude_hover\" id=\"exclude_"+word+"\"><a href=\"#\" onClick=\"removeExclusion('"+word+"'); return false;\">" + tempWordArray[0] + "<span>[x]</span></a></span>";			
		}
		else if(exclusionType=="group") {
			var insertText = "<span class=\"exclude_hover\" id=\"exclude_"+word+"\"><a href=\"#\" onClick=\"removeExclusion('"+word+"'); return false;\">" + groupName + "<span>[x]</span></a></span>";
		}

		
		j('#exclusion_list').append(insertText);
		if(!j("#exclusion_list").is(":visible")) {
			j("#exclusion_list").slideDown();			
		}

	}
	
	function filterNumbersFromCloud() {
		filterNumbers = true;
		clearTimeout(updateTimer);
		updateResultView();
	}
	
	
	
	function highlightRelated(word) {
		if(typeof(myWordCloud.wordCloudArray[word]) != "undefined") {

			if(typeof(myWordCloud.wordCloudArray[word]["firstOrder"]) != "undefined") {
				j.each(myWordCloud.wordCloudArray[word]["firstOrder"], function(index, value) {
					if(value!=word) {
						j("#word_" +value).addClass("highlightFirstOrderWordInCloud");
					}

				});
			}
		
			if(typeof(myWordCloud.wordCloudArray[word]["secondOrder"]) != "undefined") {
				j.each(myWordCloud.wordCloudArray[word]["secondOrder"], function(index, value) {
					if(value!=word) {
						j("#word_" +value).addClass("highlightSecondOrderWordInCloud");
					}

				});
			}
		}
	}
	
	function clearRelated(word) {

				
				j("#CIword_cloud").children().removeClass("highlightFirstOrderWordInCloud");
				j("#CIword_cloud").children().removeClass("highlightSecondOrderWordInCloud");
			
			}
	
	function removeExclusion(word) {
		var originalWord = word;
	

		var tempWordArray = exclude_array[word];
	

		var new_exclude_list = new Array();
		j.each(exclude_list, function(index, value) {
			if(j.inArray(value, tempWordArray) == -1) {
				new_exclude_list.push(value);
			}
			
		});
		exclude_list=new_exclude_list;

		if(exclude_list.length == 0) {
			j("#exclusion_list").slideUp(function() {
				j('#exclude_'+word).remove();
					clearTimeout(updateTimer);
					updateResultView();
			});
		}
		else {
			j('#exclude_'+word).remove();
			clearTimeout(updateTimer);
			updateResultView();
		}

	
		
	}
	
	function showResponsesWithWord(word)
	{
		word_filter = word;
		j.ajax({
						type: 'POST',
						url: "<?= site_url('result/getResultsWithWordAjax')?>",
						data: {question_id : <?= $question_model->question_id ?> , selected_word : word, exclude_list: JSON.stringify(exclude_list)},
						success: function(response) 
										{

											j("#FR_all_results_list").html(eval("("+response+")"));
											if(word != "") {
													j("#CIword_cloud").children().removeClass("highlightWordInCloud");
												j("#word_" + word).addClass("highlightWordInCloud");
											}else {
												j("#CIword_cloud").children().removeClass("highlightWordInCloud");
											}	
										},
						async: true
					});
	}
	
	function toggleListHeight() {
		if(j("#FR_all_results").height() == 800) {
			j("#FR_all_results").height(300);
			j("#toggleListHeightLink").text("[+]");
		}
		else {
			j("#toggleListHeightLink").text("[-]");
			j("#FR_all_results").height(800);
		}
		return false;
	}
	
	
</script>


<ul id="cloudMenu" class="contextMenu" style="display:none;">
<li class="Exclude">
      <a href="#exclude">Exclude</a>
  </li>

</ul>






<div id="word_cloud_header">
<div id="clear_word_cloud" style="text-align: center;" onclick="showResponsesWithWord('')">ALL</div>
<div><h3 id="downloadFree"><?=anchor("result/view/". $question_model->question_id . "/false/true/false", "[View Results Table]")?> <?=anchor("result/view/". $question_model->question_id . "/false/false/true", "[Results Spreadsheet]")?></h3></div>
</div>
<div id="CIword_cloud">

	<script>
	
	var wordCloudArray=<?=json_encode($sortedArray)?>;

	var myWordCloud;
	j(document).ready(function () {
		myWordCloud = new WordCloud("#CIword_cloud");


		myWordCloud.addWordsWithArray(wordCloudArray);
	
	});



	</script>
	
</div>
<div id="exclusion_list">Excluding:</div>


<div id='FR_all_results'>
<a id="toggleListHeightLink" href="#" onClick="toggleListHeight(); 
return false; ">[+]</a>
	<ul id='FR_all_results_list'>
	<?foreach( $result_data as $response){?>
		<?if($question_model->anonymous){?>
			<li class='result_node' id='result_id<?=$response->RESULT_ID?>'>
			<span class="responseTime"><?= $response->CREATED_AT ?></span>
			<span class="responseContent"><?= strip_tags($response->RESULT_CONTENT)?></span>
			</li>
		<?}else{?>
			<li class='result_node' id='result_id<?=$response->RESULT_ID?>'>
				<? if($response->PER_ID == 0){ ?>
					<span class="responsePerson">Anonymous wrote: </span>
				<? }else{?>
					<span class="responsePerson"><?= personIdToName($response->PER_ID)?> wrote: </span>
				<? } ?>
			<span class="responseContent"><?= strip_tags($response->RESULT_CONTENT)?></span>
			</li>
		<? } ?>
	<? } ?>
	</ul>
</div>


<div id="create_group_name_wrapper" style="display:none;">
	<div id='create_group_name'>
		<p><label for="field_group_name">Enter a title for this group: </label></p>
		<input name="field_group_name" id="field_group_name"><input type=button onClick="createGroup();" value="save">
	</div>
</div>

