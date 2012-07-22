
function WordCloud(targetId) {

	this.targetId = targetId;
	
	this.wordCloudArray = new Array();
	
	this.minFontSize = 12;
	this.maxFontSize = 60;
	this.alignToggle = 0;
	this.minCount;
	this.maxCount;
	this.colors = new Array("#90B88E", "#17615D");
	this.spread;
	this.initialBuild=true;
	this.removedWords = new Array();
	
	
	this.addWordsWithArray = function(wordArray) {


		this.minCount = wordArray['minIgnore'];
		this.maxCount = wordArray['maxIgnore'];
		delete wordArray.maxIgnore;
		delete wordArray.minIgnore;
		
		this.spread = this.maxCount-this.minCount;
		if(this.spread==0) {
			this.spread=1;
		}


		newWordCloudArray = new Array();		
		this.removedWords = new Array();
		
		
		for(e in wordArray) {
		


			if(e in this.wordCloudArray) {
				this.updateWord(e, wordArray[e]);
			}
			else {
				if(this.initialBuild) {
					this.addWord(e, wordArray[e], "sequential", false);									
				}
				else {
					this.addWord(e, wordArray[e], "random", true);									
				}
			}
	
			newWordCloudArray[e] = wordArray[e];
		}
		

		for(e in this.wordCloudArray) {
			if(!(e in newWordCloudArray) && !this.initialBuild) {

				this.removeWord(e, this.wordCloudArray[e]);
			}
		}
		
		
		this.wordCloudArray = newWordCloudArray;
		this.initialBuild=false;
		
	}
	
	this.addWord = function (lemma, wordArrayEntry, locationType, animate) {
		
		if(wordArrayEntry["type"] == "word") {
			var wordCount = wordArrayEntry["count"];
			var displayWord = wordArrayEntry["sourceWords"][0];
			var idPrefix = "word_";
		}
		else if(wordArrayEntry["type"] == "group") {
			var wordCount = wordArrayEntry["count"];
			var displayWord = wordArrayEntry["groupTitle"];
			var idPrefix = "group_";
		}

		
		var size = Math.floor(this.minFontSize+(wordCount - this.minCount) * (this.maxFontSize - this.minFontSize)/this.spread);

		if(this.alignToggle==0) {
			alignType = (1-(Math.floor(size)/60))*6;
		}
		else {
			alignType = (1-(Math.floor(size)/60))*-6;
		}
		
		
		if(wordArrayEntry["type"] == "word") {
			var insertText= '<span onMouseOver="highlightRelated(\'' + lemma + '\')" onMouseOut="clearRelated(\''+lemma+'\')" onclick="showResponsesWithWord(\'' + lemma +'\');"  class="wordCloud" style="display:none; vertical-align: ' + alignType + 'px; color: ' + this.colors[this.alignToggle] + '; font-size: ' + size + 'px" id="' + idPrefix + lemma + '">' + displayWord + '</span>'+"\n"		


		}
		else if(wordArrayEntry["type"] == "group") {
			var insertText= '<span onMouseOver="highlightRelated(\'' + lemma + '\');" onMouseOut="clearRelated(\''+lemma+'\')" onclick="showResponsesWithWord(\'' + lemma +'\');" class="wordCloud CIgroup_entry" style="display:none; vertical-align: ' + alignType + 'px; color: ' + this.colors[this.alignToggle] + '; font-size: ' + size + 'px" id="' +idPrefix + lemma + '">' + displayWord + '</span>'+"\n"

		}
		
				
		if(locationType == "sequential") {

			j(this.targetId).append(insertText);
		}
		if(locationType == "random") {
			var randomInsertionPoint = Math.ceil(Math.random() * j(targetId).children().length);
			if(randomInsertionPoint == 0) {
				randomInsertionPoint = 1;
			}
			j(this.targetId+">span:nth-child("+randomInsertionPoint+")").after(insertText);	
		}
		
		var elementToUpdate = "#" + idPrefix + lemma;
		
		
		if(wordArrayEntry["type"] == "word") {
			j(elementToUpdate).contextMenu({
				        menu: 'cloudMenu'
				    },
				    function(action, el, pos) {

						excludeWordFromCloud(j(el).attr('id'));

				    });
		}
		else {
			
			buildContextMenu(lemma, wordArrayEntry, elementToUpdate);
			
		
		}
		
		if(animate) {
			j(elementToUpdate).fadeIn('slow');
		}
		else {
			j(elementToUpdate).show();
		}
		
		// we have to destroy the context menu when we start a drag, otherwise it steals our events

		j(elementToUpdate).draggable({ containment: "parent", zIndex: 2700,  revert: true, 
			start:function(event,ui) { 

			},
			stop: function(event,ui) {

			}
			});
		
		j(elementToUpdate).droppable({  
			drop:function(event,ui) { 
				dropEvent(ui.draggable.attr("id"), event.target.id);

			},
			over: function(event,ui) {
				dropHover(event.target.id);
			},
			out: function(event,ui) {
				dropLeave(event.target.id);
			}
			
			});
		
		
		this.alignToggle++;
		if(this.alignToggle>=this.colors.length) {
			this.alignToggle=0;
		}
		
	}

	this.updateWord = function(lemma, wordEntryArray) {


		if(wordEntryArray["type"] == "word") {
			var idPrefix = "word_";
			var elementToUpdate = "#" + idPrefix + lemma;
		}
		else if(wordEntryArray["type"] == "group") {
			var idPrefix = "group_";
			var elementToUpdate = "#" + idPrefix + lemma;
			buildContextMenu(lemma, wordEntryArray, elementToUpdate);
		}


		var wordCount= wordEntryArray["count"];
		var size = Math.floor(this.minFontSize + (wordCount - this.minCount) * (this.maxFontSize-this.minFontSize)/this.spread);
		
	

		if(j(elementToUpdate).length>0) {

			var currentAlignStatus = j(elementToUpdate).css("vertical-align");
			if(currentAlignStatus.indexOf("-")>=0) {
				var alignStatus = (1-(Math.floor(size)/this.maxFontSize))*(-6);
			}
			else {
				var alignStatus = (1-(Math.floor(size)/this.maxFontSize))*6;
			}

			j(elementToUpdate).css("font-size",size+"px");
			j(elementToUpdate).css("vertical-alignStatus", alignStatus +"px");
		
		
		}
		
		
	}
	
	this.removeWord = function(lemma, wordEntryArray) {

		if(wordEntryArray["type"] == "word") {
			var idPrefix = "word_";
		}
		else if(wordEntryArray["type"] == "group") {
			var idPrefix = "group_";
		}


		var elementToUpdate = "#" + idPrefix +lemma;
		j(elementToUpdate).fadeOut('slow');
		this.removedWords[lemma]=true;
		
		
	}


}