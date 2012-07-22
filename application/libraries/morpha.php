<?php

/*
 * Adapted from lex to php by Dingcheng Li, PhD student in computational linguistics at
 * University of Minnesota. He is also working as a IT research fellow at OIT of CLA, UMN.
 * morpha.lex - morphological analyser / lemmatiser

 Copyright (c) 1995-2001 University of Sheffield, University of Sussex
 All rights reserved.

 Redistribution and use of source and derived binary forms are
 permitted provided that:
 - they are not  used in commercial products
 - the above copyright notice and this paragraph are duplicated in
 all such forms
 - any documentation, advertising materials, and other materials
 related to such distribution and use acknowledge that the software
 was developed by Kevin Humphreys <kwh@dcs.shef.ac.uk> and John
 Carroll <john.carroll@cogs.susx.ac.uk> and Guido Minnen
 <Guido.Minnen@cogs.susx.ac.uk> and refer to the following related
 publication:

 Guido Minnen, John Carroll and Darren Pearce. 2000. Robust, Applied
 Morphological Generation. In Proceedings of the First International
 Natural Language Generation Conference (INLG), Mitzpe Ramon, Israel.
 201-208.

 The name of University of Sheffield may not be used to endorse or
 promote products derived from this software without specific prior
 written permission.

 This software is provided "as is" and without any express or
 implied warranties, including, without limitation, the implied
 warranties of merchantibility and fitness for a particular purpose.

 If you make any changes, the authors would appreciate it
 if you sent them details of what you have done.

 Covers the English productive affixes:

 -s	  plural of nouns, 3rd sing pres of verbs
 -ed	  past tense
 -en    past participle
 -ing	  progressive of verbs

 Compilation: flex -i -8 -Cfe -omorpha.yy.c morpha.lex
 gcc -o morpha morpha.yy.c
 CAVEAT: In order to be able to get the morphological analyser to return
 results immediately when used via unix pipes the flex and gcc command
 line options have to be adapted to:
 flex -i -omorpha.yy.c morpha.lex
 gcc -Dinteractive -o morpha morpha.yy.c

 Usage:       morpha [options:actuf verbstem-file] < file.txt
 N.B. A file with a list of verb stems that allow for
 consonant doubling in British English (called 'verbstem.list')
 is expected to be present in the same directory as morpha

 Options: a this option ensures that affixes are output. It is not
 relevant for the generator (morphg)
 c this option ensures that casing is left untouched
 wherever possible
 t this option ensures that tags are output; N.B. if
 the option 'u' is set and the input text is tagged
 the tag will always be output even if this option
 is not set
 u this option should be used when the input file is
 untagged
 f a file with a list of verb stems that allow for
 consonant doubling in British English (called
 'verbstem.list') is expected
 to be present in the same directory as morpha; using
 this option it is possible to specify a different file,
 i.e., 'verbstem-file'

 Kevin Humphreys <kwh@dcs.shef.ac.uk>
 original version: 30/03/95 - quick hack for LaSIE in the MUC6 dry-run
 revised: 06/12/95 - run stand-alone for Gerald Gazdar's use
 revised: 20/02/96 - for VIE, based on reports from Gerald Gazdar

 John Carroll <John.Carroll@cogs.susx.ac.uk>
 revised: 21/03/96 - made handling of -us and -use more accurate
 revised: 26/06/96 - many more exceptions from corpora and MRDs

 Guido Minnen <Guido.Minnen@cogs.susx.ac.uk>
 revised: 03/12/98 - normal form and  different treatment of
 consonant doubling introduced in order to
 support automatic reversal; usage of
 external list of verb
 stems (derived from the BNC) that allow
 for consonant doubling in British English
 introduced
 revised: 19/05/99 - improvement of option handling
 revised: 03/08/99 - introduction of option -f; adaption of
 normal form to avoid the loss of case
 information
 revised: 01/09/99 - changed from normal form to compiler
 directives format
 revised: 06/10/99 - addition of the treatment of article
 and sentence initial markings,
 i.e. <ai> and <si> markings at the
 beginning of a word.  Modification of
 the scanning of the newline symbol
 such that no problems arise in
 interactive mode. Extension of the
 list of verbstems allowing for
 consonant doubling and incorporation
 of exception lists based on the CELEX
 lexical database.
 revised: 02/12/99 - incorporated data extracted from the
 CELEX lexical databases
 revised: 07/06/00 - adaption of Makefile to enable various
 Flex optimizations

 John Carroll <John.Carroll@cogs.susx.ac.uk>
 revised: 25/01/01 - new version of inversion program,
 associated changes to directives; new
 C preprocessor flag 'interactive'

 Diana McCarthy <dianam@cogs.susx.ac.uk>
 revised: 23/02/02 - fixed bug reading in verbstem file.

 John Carroll <J.A.Carroll@sussex.ac.uk>
 revised: 28/08/03 - fixes to -o(e) and -ff(e) words, a few
 more irregulars, preferences for generating
 alternative irregular forms.

 Exception lists are taken from WordNet 1.5, the CELEX lexical
 database (Copyright Centre for Lexical Information; Baayen,
 Piepenbrock and Van Rijn; 1993) and various other corpora and MRDs.

 Further exception lists are taken from the CELEX lexical database
 (Copyright Centre for Lexical Information; Baayen, Piepenbrock and
 Van Rijn; 1993).

 Many thanks to Tim Baldwin, Chris Brew, Bill Fisher, Gerald Gazdar,
 Dale Gerdemann, Adam Kilgarriff and Ehud Reiter for suggested
 improvements.

 WordNet> WordNet 1.5 Copyright 1995 by Princeton University.
 WordNet> All rights reseved.
 WordNet>
 WordNet> THIS SOFTWARE AND DATABASE IS PROVIDED "AS IS" AND PRINCETON
 WordNet> UNIVERSITY MAKES NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR
 WordNet> IMPLIED.  BY WAY OF EXAMPLE, BUT NOT LIMITATION, PRINCETON
 WordNet> UNIVERSITY MAKES NO REPRESENTATIONS OR WARRANTIES OF MERCHANT-
 WordNet> ABILITY OR FITNESS FOR ANY PARTICULAR PURPOSE OR THAT THE USE
 WordNet> OF THE LICENSED SOFTWARE, DATABASE OR DOCUMENTATION WILL NOT
 WordNet> INFRINGE ANY THIRD PARTY PATENTS, COPYRIGHTS, TRADEMARKS OR
 WordNet> OTHER RIGHTS.
 WordNet>
 WordNet> The name of Princeton University or Princeton may not be used in
 WordNet> advertising or publicity pertaining to distribution of the software
 WordNet> and/or database.  Title to copyright in this software, database and
 WordNet> any associated documentation shall at all times remain with
 WordNet> Princeton Univerisy and LICENSEE agrees to preserve same.
 *
 */
include "options_st.php";

$options = new options_st();
class morpha {
	//basic regular expressions:
	public $A = "/[A-z0-9_]+/";
	public $V = "/[aeiou]/";
	public $VY = "/[aeiouy]/";
	public $C =   "/[bcdfghjklmnpqrstvwxyz]/";
	public $CXY = "/[bcdfghjklmnpqrstvwxz]/";
	public $CXY2 = "/bb|cc|dd|ff|gg|hh|jj|kk|ll|mm|nn|pp|qq|rr|ss|tt|vv|ww|xx|zz/";
	public $S2 = "/ss|zz/";
	public $S= "/[sxz]|([cs]h)/";
	public $PRE = "/be|ex|in|mis|pre|pro|re/";
	public $EDING="/ed|ing/";
	public $ESEDING="/es|ed|ing/";

	public $G = "/[^[:space:]_<>]/";
	public $GMinus = "/[^[:space:]_<>-]/";
	public $SKIP = "/[[:space:]]/";
	//regular expression for verbs:
	//note: in the following symbolcs, if I use CXY2, it refers to above $CXY2. If I use cxy2, it refers to $CXY
	//but repeat twice. Similarly, for S2 and s2.
	public $Aworks = "/[A-z0-9_]+-works/";
	public $Acxyzed ="/[A-z0-9_]+[bcdfghjklmnpqrstvwxz]zed/";
	public $Acxyzing = "/[A-z0-9_]+[bcdfghjklmnpqrstvwxz]zing/";
	public $Avyzed = "/[A-z0-9_]*[aeiouy]zed/";
	public $Avyzing = "/[A-z0-9_]+[aeiouy]zing/";
	public $Aused = "/[A-z0-9_]+used/";
	public $Ausing = "/[A-z0-9_]+using/";
	public $AS2ed = "/(ss|zz)ed/";
	public $AS2ing = "/(ss|zz)ing/";
	public $Cvlled = "/[bcdfghjklmnpqrstvwxyz]+[aeiou]lled/";
	public $Cvlling = "/[bcdfghjklmnpqrstvwxyz]+[aeiou]lling/";
	public $AcvCXY2ed = "/[A-z0-9_]+[bcdfghjklmnpqrstvwxyz][aeiou](bb|cc|dd|ff|gg|hh|jj|kk|ll|mm|nn|pp|qq|rr|ss|tt|vv|ww|xx|zz)ed/";
	public $AcvCXY2ing = "/[A-z0-9_]+[bcdfghjklmnpqrstvwxyz][aeiou](bb|cc|dd|ff|gg|hh|jj|kk|ll|mm|nn|pp|qq|rr|ss|tt|vv|ww|xx|zz)ing/";
	public $Cxyed = "/[bcdfghjklmnpqrstvwxz]+ed/";
	public $Precvnged = "/(be|ex|in|mis|pre|pro|re)[bcdfghjklmnpqrstvwxyz][aeiou]nged/";
	public $Aicked = "/[A-z0-9_]+icked/";
	public $Acined = "/[A-z0-9_]*[bcdfghjklmnpqrstvwxyz]ined/";
	public $Acvnpwxed = "/[A-z0-9_]*[bcdfghjklmnpqrstvwxyz][aeiou][npwx]ed/";
	public $PrecoredPre = "/(be|ex|in|mis|pre|pro|re)[bcdfghjklmnpqrstvwxyz]+ored(be|ex|in|mis|pre|pro|re)/";
	public $Actored = "/[A-z0-9_]+ctored/";
	public $Acclntored = "/[A-z0-9_]*[bcdfghjklmnpqrstvwxyz][clnt]ored/";
	public $Aeored = "/[A-z0-9_]+[eo]red/";
	public $Acied ="/[A-z0-9_]+[bcdfghjklmnpqrstvwxyz]ied/";
	public $Aquvced = "/[A-z0-9_]*qu[aeiou][bcdfghjklmnpqrstvwxyz]ed/";
	public $Auvded = "/[A-z0-9_]+u[aeiou]ded/";
	public $Acleted = "/[A-z0-9_]*[bcdfghjklmnpqrstvwxyz]leted/";
	public $Preceited = "/(be|ex|in|mis|pre|pro|re)[bcdfghjklmnpqrstvwxyz]/";
	public $Aeited = "/[A-z0-9_]*[ei]ted/";
	public $Precxy2eated = "/(be|ex|in|mis|pre|pro|re)[bcdfghjklmnpqrstvwxz]{2}eated/";
	public $Avcxy2eated = "/[A-z0-9_]*[aeiou]([bcdfghjklmnpqrstvwxz]{2})eated/";
	public $Aeoeated = "/[A-z0-9_]+[eo]ated/";
	public $Avated = "/[A-z0-9_]+[aeiou]ated/";
	public $Av2cgsved = "/[A-z0-9_]+[aeiou]{2}[cgsv]ed/";
	public $Av2ced = "/[A-z0-9_]+[aeiou]{2}[bcdfghjklmnpqrstvwxyz]ed/";
	public $Arwled = "/[A-z0-9_]+[rw]led/";
	public $Athed = "/[A-z0-9_]+thed/";
	public $Aued = "/[A-z0-9_]+ued/";
	public $Acxycglsved = "/[A-z0-9_]+[bcdfghjklmnpqrstvwxz][cglsv]ed/";
	//the following rule is added by myself, if correct, I need to add es, ing as well.
	public $Acglsved = "/[A-z0-9_]+[cglsv]ed/";
	public $Acxy2ed = "/[A-z0-9_]+[bcdfghjklmnpqrstvwxz]{2}ed/";
	public $Avy2ed = "/[A-z0-9_]+[aeiouy]{2}ed/";
	public $Aed = "/[aeiouy]ed/";
	public $Cxying = "/[bcdfghjklmnpqrstvwxz]ing/";
	public $Precvnging = "/(be|ex|in|mis|pre|pro|re)[bcdfghjklmnpqrstvwxyz][aeiou]nging/";
	public $Aicking = "/[A-z0-9_]+icking/";
	public $Acining = "/[A-z0-9_]*[bcdfghjklmnpqrstvwxyz]ining/";
	public $Acvnpwxing = "/[A-z0-9_]*[bcdfghjklmnpqrstvwxyz][aeiou][npwx]ing/";
	public $Aquvcing = "/[A-z0-9_]*qu[aeiou][bcdfghjklmnpqrstvwxyz]ing/";
	public $Auvding = "/[A-z0-9_]+u[aeiou][bcdfghjklmnpqrstvwxyz]ding/";
	public $Acleting = "/[A-z0-9_]*[bcdfghjklmnpqrstvwxyz]leting/";
	public $Preceiting = "/(be|ex|in|mis|pre|pro|re)[bcdfghjklmnpqrstvwxyz][ei]ting/";
	public $Aeiting = "/[A-z0-9_]+[ei]ting/";
	public $Aprecxy2eating = "/[A-z0-9_]*(be|ex|in|mis|pre|pro|re)[bcdfghjklmnpqrstvwxz]{2}eating/";
	public $Avcxy2eating ="/[A-z0-9_]*[aeiou][bcdfghjklmnpqrstvwxz]{2}eating/";
	public $Aeoating = "/[A-z0-9_]+[eo]ating/";
	public $Avating = "/[A-z0-9_]+[aeiou]ating/";
	public $Av2cgsving = "/[A-z0-9_]*[aeiou]{2}[cgsv]ing/";
	public $Av2cing = "/[A-z0-9_]*[aeiou]{2}[bcdfghjklmnpqrstvwxyz]ing/";
	public $Arwling = "/[A-z0-9_]+[rw]ling/";
	public $Athing = "/[A-z0-9_]+thing/";
	public $Acxycglsving ="/[A-z0-9_]+[bcdfghjklmnpqrstvwxz][cglsv]ing/";
	public $Acxy2ing = "/[A-z0-9_]+[bcdfghjklmnpqrstvwxz]{2}ing/";
	public $Auing="/[A-z0-9_]+uing/";
	public $Avy2ing = "/[A-z0-9_]+[aeiouy]{2}ing/";
	public $Aying = "/[A-z0-9_]+ying/";
	public $Acxyoing = "/[A-z0-9_]*[bcdfghjklmnpqrstvwxz]oing/";
	public $PREcoring = "/(be|ex|in|mis|pre|pro|re)[bcdfghjklmnpqrstvwxyz]+oring/" ;
	public $Actoring = "/[A-z0-9_]+ctoring/";
	public $Accltoring = "/[A-z0-9_]+[bcdfghjklmnpqrstvwxyz][clt]oring/";
	public $Aeoring = "/[A-z0-9_]+[eo]ing/";
	public $Aing = "/[A-z0-9_]+ing/";
	public $AhlmpousEseding = "/[A-z0-9_]*[hlmp]ous(es|ed|ing)/";
	public $AafusEseding = "/[A-z0-9_]*[af]us(es|ed|ing)/";

	//regualr expressions for nouns:
	public $As = "/[A-z0-9_]+s/";
	public $As1 = "/[A-z0-9_.].s./";
	public $As2="/[A-z0-9_.].'s./";
	public $As3="/[A-z0-9_.]s./";
	public $Amen="/[A-z0-9_]*men/";
	public $Awives="/[A-z0-9_]*wives/";
	public $Azoa="/[A-z0-9_]+zoa/";
	public $Aiia="/[A-z0-9_]+iia/";
	public $Aemnia="/[A-z0-9_]+e[mn]ia/";
	public $Aia="/[A-z0-9_]+ia/";
	public $Ala="/[A-z0-9_]+la/";
	public $Ai="/[A-z0-9_]+i/";
	public $Aae="/[A-z0-9_]+ae/";
	public $Aata="/[A-z0-9_]+ata/";

	//regular expressions for nouns plus verbs
	public $Aussssiseed = "/[A-z0-9_]+(us|ss|sis|eed)/";
	public $Avses = "/[A-z0-9_]*[aeiou]ses/";
	public $Acxyzes = "/[A-z0-9_]+[bcdfghjklmnpqrstvwxz]zes/";
	public $Avyzes = "/[A-z0-9_]*[aeiouy]zes/";
	public $AS2es = "/[A-z0-9_]+(ss|zz)es/";
	public $Avrses = "/[A-z0-9_]+[aeiou]rses/";
	public $Aonses = "/[A-z0-9_]+onses/";
	public $ASes = "/[A-z0-9_]+([sxz]|([cs]h))es/";
	public $Athes = "/[A-z0-9_]+thes/";
	public $Acxycglsves = "/[A-z0-9_]+[bcdfghjklmnpqrstvwxz][cglsv]es/";
	public $ACies = "/[A-z0-9_]+[bcdfghjklmnpqrstvwxyz]ies/";
	public $Acxyoes = "/[A-z0-9_]*[bcdfghjklmnpqrstvwxz]oes/";

	public $GMinusMore = "/[^[:space:]_<>]+/";
	public $GMinusMore2 = "/[^[:space:]_<>]+-/";
	//for line 5035
	public $Ahedra = "/[A-z0-9_]+hedra/";
	public $Aitis = "/[A-z0-9_]+itis/";
	public $Aphobia = "/[A-z0-9_]*phobia/";
	public $Aphilia = "/[A-z0-9_]+philia/";


	public $Aabuses = "/[A-z0-9_]*-abuses/";
	public $Auses = "/[A-z0-9_]*-uses/";
	public $Ahlmpouses = "/[A-z0-9_]*[hlmp]ouses/";
	public $Aafuses = "/[A-z0-9_]*[af]uses/";

	public $Ametres = "/[A-z0-9_]*metres/";
	public $Alitres = "/[A-z0-9_]*litres/";
	public $Aettes = "/[A-z0-9_]+ettes/";
	public $Ashoes = "/[A-z0-9_]*shoes/";
	/*
	 * @param array $D document corpus as array of strings
	 */
	function lemmatize_inputs($D) {
		//print("in lemmatize_input");


			$this->num_docs = count($D);

			// zero array containing document terms
			$doc_terms = array();
			// simplified word tokenization process
			$doc_terms = array_keys($D);
			// here is where the indexing of terms to document locations happens
			$num_terms = count($doc_terms);
			$resultArray = array();
			for($term_position=0; $term_position < $num_terms; $term_position++) {
				//print($doc_terms[$term_position]);

				$word = $doc_terms[$term_position];

				//print $word;
				$pos = null;
				$state = "any";
				$resultArray[] = $this->lemmatize_words($word,$pos,$state);
				
				//$term = strtolower($doc_terms[$term_position]);
				//$this->corpus_terms[$term][]=array($doc_num, $term_position);
			}
			return join($resultArray, " ");
			
	}

	/*
	 * Show Documents
	 *
	 * Helper function that shows the contents of your corpus documents.
	 *
	 * @param array $D document corpus as array of strings
	 */
	function show_docs($D) {
		$ndocs = count($D);
		for($doc_num=0; $doc_num < $ndocs; $doc_num++) {
			?>
<p>Document #<?php echo ($doc_num+1); ?>:<br />
			<?php echo $D[$doc_num]; ?></p>
			<?php
		}
	}

	/*
	 * can and will not always modal so can be inflected
	 */

	/**
	 * Enter description here ...
	 * @param unknown_type $pos
	 * @param unknown_type $state
	 */
	function lemmatize_words($word,$pos,$state) {




		if(($pos=="verb")){
			self::scanVerb($word);
		}elseif(($pos=="noun")){
			self::scanNoun($word);
		}
		elseif((($pos=="noun") & ($pos == "verb"))){
			self::scanVerbNoun($word);
		}elseif ($state=="any") {
			$processed = self::scanVerb($word);
			if(!$processed){
				$processed = self::scanNoun($word);
				if(!$processed){
					$processed = self::scanVerbNoun($word);
				}
			}
			
			
		}
		elseif($state=="scan"){
			if(strcmp($word,"were")==0 & strcmp($pos,"VBDR")==0){
				return self::stem($word,4,"be","ed");
				break;
			}else if(strcmp($word,"was")==0 & strcmp($pos,"VBDZ")==0){
				return self::stem($word, 3,"be","ed");
				break;
			}else if(strcmp($word,"am")==0 & strcmp($pos,"VBM")==0){
				return self::stem($word, 2,"be","");
				break;
			}else if(strcmp($word,"are")==0 & strcmp($pos,"VBR")==0){
				return self::stem($word, 3,"be","");
				break;
			}else if(strcmp($word,"is")==0 & strcmp($pos,"VBZ")==0){
				return self::stem($word, 2,"be","s");
				break;
			}else if(strcmp($word,"'d")==0 & strcmp($pos,"VH")==0){
				return self::stem($word,2,"have","ed");
				break;    /* disprefer */
			}else if(strcmp($word,"'d")==0 & strcmp($pos,"VM")==0){
				return self::stem($word,2,"would","");
				break;
			}else if(strcmp($word,"'s")==0 & strcmp($pos,"VBZ")==0){
				return self::stem($word,2,"be","s");
				break;
			}else if(strcmp($word,"'s")==0 & strcmp($pos,"VDZ")==0){
				return self::stem($word,2,"do","s");
				break;       /* disprefer */
			}else if(strcmp($word,"'s")==0 & strcmp($pos,"VHZ")==0){
				return self::stem($word,2,"have","s");
				break;     /* disprefer */
			}else if(strcmp($word,"'s")==0 & strcmp($pos,"$")==0){
				return self::stem($word,2,"'s","");
				break;
			}else if(strcmp($word,"'s")==0 & strcmp($pos,"POS")==0){
				return self::stem($word,2,"'s","");
				break;
			}else if(strcmp($word,"'s")==0 & strcmp($pos,"CSA")==0){
				return self::stem($word, 2,"as","");
				break;
			}else if(strcmp($word,"'s")==0 & strcmp($pos,"CJS")==0){
				return self::stem($word, 2,"as","");
				break;
			}else if(strcmp($word,"not")==0 & strcmp($pos,"XX")==0){
				return self::stem($word, 3,"not","");
				break;
			}else if(strcmp($word,"ai")==0 & strcmp($pos,"VH")==0){
				return self::stem($word, 2,"be","");
				break;
			}else if(strcmp($word,"ca")==0 & strcmp($pos,"VM")==0){
				return self::stem($word, 2,"can","");
				break;
			}else if(strcmp($word,"sha")==0 & strcmp($pos,"VM")==0){
				return self::stem($word, 3,"shall","");
				break;
			}else if(strcmp($word,"wo")==0 & strcmp($pos,"VM")==0){
				return self::stem($word, 2,"will","");
				break;      /* disprefer */
			}else if(strcmp($word,"n't")==0 & strcmp($pos,"XX")==0){
				return self::stem($word, 3,"not","");
				break;       /* disprefer */
			}else if(strcmp($word,"him")==0 & strcmp($pos,"PPH01")==0){
				return self::stem($word, 3,"he","");
				break;
			}else if(strcmp($word,"her")==0 & strcmp($pos,"PPH01")==0){
				return self::stem($word,3,"she","");
				break;
			}else if(strcmp($word,"them")==0 & strcmp($pos,"PPH02")==0){
				return self::stem($word,4,"they","");
				break;
			}else if(strcmp($word,"me")==0 & strcmp($pos,"PPIS1")==0){
				return self::stem($word, 2,"I","");
				break;
			}else if(strcmp($word,"us")==0 & strcmp($pos,"PPI02")==0){
				return self::stem($word,2,"we","");
				break;
			}else if(strcmp($word,"I")==0 & strcmp($pos,"PPIS1")==0){
				return (proper_name_stem($word));
				break;
			}else if(strcmp($word,"him")==0 & strcmp($pos,"PNP")==0){
				return self::stem($word, 3,"he","");
				break;
			}else if(strcmp($word,"her")==0 & strcmp($pos,"PNP")==0){
				return self::stem($word,3,"she","");
				break;
			}else if(strcmp($word,"them")==0 & strcmp($pos,"PNP")==0){
				return self::stem($word,4,"they","");
				break;
			}else if(strcmp($word,"me")==0 & strcmp($pos,"PNP")==0){
				return self::stem($word, 2,"I","");
				break;
			}else if(strcmp($word,"us")==0 & strcmp($pos,"PNP")==0){
				return self::stem($word,2,"we","");
				break;
			}else if(strcmp($word,"I")==0 & strcmp($pos,"PNP")==0){
				return self::proper_name_stem($word);
				break;
			}

		}
	}

	function Options($op) {
		return $options->$op;
	}

	function up8($char) {
		if(('a'<=$char & $char<='z')|('\xE0'<=$char & $char<='\xFE' & $char!='xF7')){
			return $char-('a'-'A');
		}else{
			return $char;
		}
	}

	function scmp($a,$b) {
		$i=0;
		while ($d=up8($a($i)-$b($i))==0 & $a[$i]!=0) {
			$i++;
		}
		return $d;
	}

	function vcmp($a,$b) {
		return scmp($a,$b);
	}


	public $verbstem_n = 0;
	public $verbstem_list;

	function in_verbstem_list($a) {
		return $verbstem_n>0 &
		bsearch($a,$verbstem_list,$verbstem_n,vcmp($a));
	}

	function downcase($text,$len) {
		for ($i = 0; $i < $len; $i++) {
			if (isupper($text[$i])) {
				$text[$i]='a'+($text[$i]-'A');
			}
		};
	}

	function capitalise($text,$len) {
		if(islower($text[0])){
			$text[0]='A'+($text[0]-'a');
		}
		for ($i = 1; $i <$len; $i++) {
			if(isupper($text[i])){
				$text[$i]='a'+($text[$i]-'A');
			}
		}
	}


	function stem($yytext,$del,$add,$affix) {
		$yyleng = strlen($yytext);
		$stem_length = $yyleng - $del;
		$i = 0;

		//if(Options($change_case)) { downcase($yytext, $stem_length); }
		if($yytext!=""){
//			print substr($yytext,0,$stem_length);
//			printf("%s", $add);
//			print "\t";
		}
		
		//for ( ;$i < $stem_length; ){

		//putchar($yytext[$i++]);
		//}
		if (!$add[0] == '\0') //printf("%s", $add);
		//if(Options($print_affixes)) { printf("+%s", $affix); }
		return (1);
	}

	function condub_stem($yytext)
	{
		$yyleng = strlen($yytext);
		$stem_length = $yyleng - $del;
		//$d;

		//if(Options($change_case)) { downcase($yytext, $stem_length); }

		$d = $yytext[$stem_length - 1];
		if ($del > 0) { $yytext[$stem_length - 1] = '\0'; }

		//if ($in_verbstem_list($yytext))
		if(in_array($yytext,$in_verbstem))
		{//printf("%s", $yytext); 
		}
		else
		{//printf("%s%c", $yytext, $d); 
		}

		//if(Options($print_affixes)) { printf("+%s", $affix); }

		return(1);
	}

	function semi_reg_stem($yytext, $del,$add) {
		$yyleng = strlen($yytext);
		$stem_length = 0;
		$i = 0;
		$affix;
		strtolower($yytext);
		//if(Options($change_case)) { downcase($yytext, $stem_length); }
		if ($yytext[$yyleng-1] == 's' ||
		$yytext[$yyleng-1] == 'S')
		{$stem_length = $yyleng - 2 - $del;
		$affix = "s";
		}
		if ($yytext[$yyleng-1] == 'd' ||
		$yytext[$yyleng-1] == 'D')
		{$stem_length = $yyleng - 2 - $del;
		$affix = "ed";
		}
		if ($yytext[$yyleng-1] == 'g' ||
		$yytext[$yyleng-1] == 'G')
		{$stem_length = $yyleng - 3 - $del;
		$affix = "ing";
		}
		//for ( ;$i < $stem_length; )
		//{putchar($yytext[$i++]);
		//}
		
		if($yytext!=""){
			//print substr($yytext,0,$stem_length);
		 	//printf("%s ", $add);
			//print "\t";
		}
		
		//if(Options(print_affixes))
		//{
		//printf("+%s", $affix);
		//}

		return (1);
	}

	function proper_name_stem($yytext) {
		//if(Options($change_case)) { capitalise($yytext, $yyleng); }
		$yyleng = strlen($yytext);
		//echo $yytext." ".$yyleng." ";
//		echo $yytext." ";
		return(1);
	}

	function common_noun_stem($yytext) {
		//if(Options($change_case)) { downcase($yytext, $yyleng); }
		$yyleng = strlen($yytext);
//		echo $yytext." ";
		return true;
	}

	/* the +ed/+en form is the same as the stem */

	function null_stem($yytext) {
		return self::common_noun_stem($yytext);
	}

	/* the +ed/+en form is the same as the stem */
	function xnll_stem($yytext) {
		return self::common_noun_stem($yytext);
	}

	/* all inflected forms are the same as the stem */

	function ynull_stem($yytext) {
		return self::common_noun_stem($yytext);
	}

	/* this form is actually the stem so don't apply any generic analysis rules */

	function cnull_stem($yytext) {
		return self::common_noun_stem($yytext);
	}

	function read_verbstem($fn) {
		$n;$i;$j;$fs;

		$file = fopen($fn, "r");

		if ($file == NULL) fprintf(stderr, "File with consonant doubling verb stems not found (\"%s\").\n", fn);
		else
		{ while (1){
			$fs = fscanf($file, " %n%63s%n", $i, $w, $j);
			if ($fs == 0 || $fs == EOF) break;
			if ($verbstem_n == $n)
			$verbstem_list[$verbstem_n] = $w;
		}
		fclose($file);
		//qsort(verbstem_list, verbstem_n, sizeof(char*), &vcmp);
		}
	}

	function read_verbstem_file($argv, $maxbuff,$arg,$i){
		$ok = 1;

		if (strlen($argv[$arg]+$i) > $maxbuff)
		{
			fprintf(stderr, "Argument to option f too long\n");
			$ok = 0;
		}
		else self::read_verbstem($argv[$arg]);
		return ok;
	}

	function get_option($argc,$argv,$options,$arg,$i) {
		$aa = $arg;
		$ii=$i;
		$opt;
		$letter;
		if($aa>($argc-1)){
			$arg=$aa;
			$i=0;
			return 0;
		}

		if($argsv[$aa][$ii]==0){
			$arg=$aa+1;
			$ii=0;
			return 0;
		}

		if($aa>($argc-1)){
			$arg=$aa;
			$i=0;
			return 0;
		}
		do
		{if ($aa == 1 && $ii == 0 && $argv[$aa][$ii] == '-') $ii += 1;
		$letter = $argv[$aa][$ii++];
		if (($opt = strchr($options, $letter)) == NULL)
		{fprintf(stderr, "Unknown option '%c' ignored\n", $letter);
		}
		else
		{
			break;
		}
		} while(TRUE);
		$arg = $aa;
		$i   = $ii;
		return $letter;
	}

	function set_up_options($argc,$argv) {
		$opt;
		$arg = 1;
		$i=0;
		$opt_string = "actuf:"; /* don't need : now */

		/* Initialize options */
		UnSetOption(print_affixes);
		SetOption(change_case);
		UnSetOption(tag_output);
		UnSetOption(fspec);

		$state = "scan";

		while (($opt = get_option($argc, $argv, $opt_string, $arg, $i)) != 0){
			switch (opt){
				case 'a':
					SetOption(print_affixes);
					break;
				case 'c':
					UnSetOption(change_case);
					break;
				case 't':
					SetOption(tag_output);
					break;
				case 'u':
					$state = any;
					break;
				case 'f':
					SetOption(fspec);
					break;
			}
		}

		if (Option($fspec)) {
			if (arg > (argc - 1)) fprintf(stderr, "File with consonant doubling verb stems not specified\n");
			else { read_verbstem_file($argv, $MAXSTR, $arg, $i);}
		}
		else self::read_verbstem("verbstem.list");
	}

	function scanVerb($word) {
		preg_match($this->EDING,$word,$edingAffix);
		preg_match($this->ESEDING,$word,$esedingAffix);
		preg_match($this->Acxyzed,$word,$matchzed);
		preg_match($this->Acxyzing,$word,$matchzing);
		preg_match($this->Avyzed,$word,$matchAvyzed);
		preg_match($this->Avyzing,$word,$matchAvyzing);
		preg_match($this->Aused,$word,$matchused);
		preg_match($this->Ausing,$word,$matchusing);
		preg_match($this->AS2ed,$word,$matchAs2ed);
		preg_match($this->AS2ing,$word,$matchAs2ing);
		preg_match($this->Cvlled,$word,$matchlled);
		preg_match($this->Cvlling,$word,$matchlling);
		preg_match($this->AcvCXY2ed,$word,$matchCxy2ed);
		preg_match($this->AcvCXY2ing,$word,$matchCxy2ing);
		preg_match($this->Cxyed,$word,$matchCxyed);
		preg_match($this->Precvnged,$word,$matchcvnged);
		preg_match($this->Aicked,$word,$matchAicked);
		preg_match($this->Acined,$word,$matchAcined);
		preg_match($this->Acvnpwxed,$word,$matchAcvnpwxed);
		preg_match($this->PrecoredPre,$word,$matchPrecoredPre);
		preg_match($this->Actored,$word,$matchActored);
		preg_match($this->Acclntored,$word,$matchAcclntored);


		preg_match($this->Aeored,$word,$matchAeored);
		preg_match($this->Acied,$word,$matchAcied);
		preg_match($this->Aquvced,$word,$matchAquvced);
		preg_match($this->Auvded,$word,$matchAuvded);
		preg_match($this->Acleted,$word,$matchAcleted);
		preg_match($this->Preceited,$word,$matchPreceited);
		preg_match($this->Aeited,$word,$matchAeited);
		preg_match($this->Precxy2eated,$word,$matchPrecxy2eated);
		preg_match($this->Avcxy2eated,$word,$matchAvcxy2eated);
		preg_match($this->Aeoeated,$word,$matchAeoeated);
		preg_match($this->Avated,$word,$matchAvated);
		preg_match($this->Av2cgsved,$word,$matchAv2cgsved);
		preg_match($this->Av2ced,$word,$matchAv2ced);
		preg_match($this->Arwled ,$word,$matchArwled);
		preg_match($this->Athed,$word,$matchAthed);
		preg_match($this->Aued,$word,$matchAued);
		preg_match($this->Acxycglsved,$word,$matchAcxycglsved);
		preg_match($this->Acglsved,$word,$matchAcglsved);
		
		preg_match($this->Acxy2ed,$word,$matchAcxy2ed);
		preg_match($this->Avy2ed,$word,$matchAvy2ed);
		preg_match($this->Aed,$word,$matchAed);

		preg_match($this->Cxying,$word,$matchCxying);
		preg_match($this->Precvnging,$word,$matchPrecvnging);
		preg_match($this->Aicking,$word,$matchAicking);
		preg_match($this->Acining,$word,$matchAcining);
		preg_match($this->Acvnpwxing,$word,$matchAcvnpwxing);
		preg_match($this->Aquvcing,$word,$matchAquvcing);
		preg_match($this->Auvding,$word,$matchAuvding);
		preg_match($this->Acleting,$word,$matchAcleting);
		preg_match($this->Preceiting,$word,$matchPreceiting);
		preg_match($this->Aeiting,$word,$matchAeiting);
		preg_match($this->Aprecxy2eating,$word,$matchAprecxy2eating);
		preg_match($this->Avcxy2eating,$word,$matchAvcxy2eating);
		preg_match($this->Aeoating,$word,$matchAeoating);
		preg_match($this->Avating,$word,$matchAvating);
		preg_match($this->Av2cgsving,$word,$matchAv2cgsving);
		preg_match($this->Av2cing,$word,$matchAv2cing);
		preg_match($this->Arwling,$word,$matchArwling);
		preg_match($this->Athing,$word,$matchAthing);
		preg_match($this->Acxycglsving,$word,$matchAcxycglsving);
		preg_match($this->Acxy2ing,$word,$matchAcxy2ing);

		preg_match($this->Auing,$word,$matchAuing);
		preg_match($this->Avy2ing,$word,$matchAvy2ing);
		preg_match($this->Aying,$word,$matchAying);
		preg_match($this->Acxyoing,$word,$matchAcxyoing);
		preg_match($this->PREcoring,$word,$matchPREcoring);
		preg_match($this->Actoring,$word,$matchActoring);
		preg_match($this->Accltoring,$word,$matchAccltoring);
		preg_match($this->Aeoring,$word,$matchAeoring);
		preg_match($this->Aing,$word,$matchAing);
		preg_match($this->AhlmpousEseding,$word,$matchAhlmpousEseding);
		preg_match($this->AafusEseding,$word,$matchAafusEseding);

		switch ($word) {
			case "shall":
				return self::ynull_stem($word);
				break;
			case "would":
				return self::ynull_stem($word);
				break;
			case "may":
				return self::ynull_stem($word);
				break;
			case "might":
				return self::ynull_stem($word);
				break;
			case "ought":
				return self::ynull_stem($word);
				break;
			case "should":
				return self::ynull_stem($word);
				break;
			case "am":
				return self::stem($word, 2,"be","");
				break;
			case "are":
				return self::stem($word,3,"be","");
				break;
			case "is":
				return self::stem($word,2,"be","s");
				break;
			case "was":
				return self::stem($word, 3,"be","ed");
				break;
			case "wast":
				return self::stem($word,4,"be","ed");
				break;
			case "wert":
				return self::stem($word,4,"be","ed");
				break;
			case "were":
				return self::stem($word,4,"be","ed");
				break;
			case "being":
				return self::stem($word,5,"be","ed");
				break;
			case "been":
				return self::stem($word,4,"be","en");
				break;
			case "had":
				return self::stem($word,3,"have","ed");
				break;
			case "has":
				return self::stem($word,3,"have","s");
				break;
			case "hath":
				return self::stem($word,4,"have","s");
				break;
			case "does":
				return self::stem($word,4,"do","s");
				break;
			case "did":
				return self::stem($word,3,"do","ed");
				break;
			case "done":
				return self::stem($word,4,"do","en");
				break;
			case "didst":
				return self::stem($word,5,"do","ed");
				break;
			case "'ll":
				return self::stem($word,3,"will","");
				break;
			case "'m":
				return self::stem($word, 2,"be","");
				break;
			case "'re":
				return self::stem($word,3,"be","");
				break;
			case "'ve":
				return self::stem($word,3,"have","");
				break;
			case "beat":
			case "browbeat":
				return self::stem($word, 2,"","en");
			case "beat":
			case "beset":
			case "bet":
			case "broadcast":
			case "browbeat":
			case "burst":
			case "cost":
			case "cut":
			case "hit":
			case "let":
			case "set":
			case "shed":
			case "shut":
			case "slit":
			case "split":
			case "put":
			case "quit":
			case "spread":
			case "sublet":
			case "spred":
			case "thrust":
			case "upset":
			case "hurt":
			case "bust":
			case "cast":
			case "forecast":
			case "inset":
			case "miscast":
			case "mishit":
			case "misread":
			case "offset":
			case "outbit":
			case "overbid":
			case "preset":
			case "read":
			case "recast":
			case "reset":
			case "telcast":
			case "typecast":
			case "typeset":
			case "underbid":
			case "undercut":
			case "wed":
			case "wet":
				return self::null_stem($word);
				break;
			case "aches":
				return self::stem($word, 2,"e","s");
				break;
			case "aped":
				return self::stem($word,2,"e","ed");
				break;
			case "biases":
			case "canvases":
				return self::stem($word,2,"","s");
				break;
			case "caddied":
			case "vied":
				return self::stem($word,2,"e","ed");
				break;
			case "caddying":
			case "vying":
				return self::stem($word,4,"ie","ing");
				break;
			case "cooees":
				return self::stem($word,2, "e", "s");
				break;
			case "cooeed":
				return self::stem($word,3, "ee", "ed");
				break;
			case "eyed":
				return self::stem($word,2, "e", "ed");
				break;
			case "dyed":
				return self::stem($word,2, "e", "ed");
				break;
			case "eyeing":
				return self::stem($word,3, "", "ing");
				break;
			case "eying":
				return self::stem($word,3, "e", "ing");
				break;
			case "dying":
				return self::stem($word,4, "ie", "ing");
				break;
			case "gelded":
				return self::stem($word, 2, "", "ed");
				break;
			case "gilded":
				return self::stem($word, 2, "", "ed");
				break;
			case "outvied":
				return self::stem($word,2, "e", "ed");
				break;
			case "hied":
				return self::stem($word,2, "e", "ed");
				break;
			case "ourlay":
				return self::stem($word,2, "ie", "ed");
				break;
			case "rebound":
				return self::stem($word,4, "ind", "ed");
				break;
			case "plummets":
				return self::stem($word,1, "", "s");
				break;
			case "queueing":
				return self::stem($word,3, "", "ing");
				break;
			case "stomachs":
				return self::stem($word,1, "", "s");
				break;
			case "trammels":
				return self::stem($word,1, "", "s");
				break;
			case "tarmacked":
				return self::stem($word,3, "", "ed");
				break;
			case "transfixed":
				return self::stem($word, 2, "", "ed");
				break;
			case "underlay":
				return self::stem($word,2, "ie", "ed");
				break;
			case "overlay":
				return self::stem($word,2, "ie", "ed");
				break;
			case "overflown":
				return self::stem($word,3, "y", "ed");
				break;
			case "relaid":
				return self::stem($word,3, "ay", "ed");
				break;
			case "shat":
				return self::stem($word,3, "hit", "ed");
				break;
			case "bereft":
				return self::stem($word,3, "eave", "ed");
				break;
			case "clave":
				return self::stem($word,3, "eave", "ed");
				break;
			case "wrought":
				return self::stem(6, "ork", "ed");
				break;
			case "durst":
				return self::stem($word,4, "are", "ed");
				break;
			case "foreswore":
				return self::stem($word,3, "ear", "ed");
				break;
			case "outfought":
				return self::stem(5, "ight", "ed");
				break;
			case "garotting":
				return self::stem($word,3, "e", "ing");
				break;
			case "shorn":
				return self::stem($word,3, "ear", "en");
				break;
			case "spake":
				return self::stem($word,3, "eak", "ed");
				break;
			case "analyses":
			case "paralyses":
			case "caches":
			case "browses":
			case "glimpses":
			case "collapses":
			case "eclipses":
			case "elapses":
			case "lapses":
			case "traipses":
			case "relapses":
			case "pulses":
			case "repulses":
			case "cleanses":
			case "rinses":
			case "recompenses":
			case "condenses":
			case "dispenses":
			case "incenses":
			case "licenses":
			case "senses":
			case "tenses":
				return self::stem($word,1, "", "s");
				break;
			case "cached":
				return self::stem($word,2, "e", "ed");
				break;
			case "caching":
				return self::stem($word,3, "e", "ing");
				break;
			case "tun".$edingAffix[0]:
			case "gangren".$edingAffix[0]:
			case "wan".$edingAffix[0]:
			case "grip".$edingAffix[0]:
			case "unit".$edingAffix[0]:
			case "coher".$edingAffix[0]:
			case "comper".$edingAffix[0]:
			case "rever".$edingAffix[0]:
			case "semaphor".$edingAffix[0]:
			case "commun".$edingAffix[0]:
			case "reunit".$edingAffix[0]:
			case "dynamit".$edingAffix[0]:
			case "superven".$edingAffix[0]:
			case "telephon".$edingAffix[0]:
			case "ton".$edingAffix[0]:
			case "aton".$edingAffix[0]:
			case "bon".$edingAffix[0]:
			case "phon".$edingAffix[0]:
			case "plan".$edingAffix[0]:
			case "profan".$edingAffix[0]:
			case "importun".$edingAffix[0]:
			case "enthron".$edingAffix[0]:
			case "elop".$edingAffix[0]:
			case "interlop".$edingAffix[0]:
			case "sellotap".$edingAffix[0]:
			case "sideswip".$edingAffix[0]:
			case "slop".$edingAffix[0]:
			case "scrap".$edingAffix[0]:
			case "mop".$edingAffix[0]:
			case "lop".$edingAffix[0]:
			case "expung".$edingAffix[0]:
			case "lung".$edingAffix[0]:
			case "past".$edingAffix[0]:
			case "premier".$edingAffix[0]:
			case "rang".$edingAffix[0]:
			case "secret".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
			case "unroll".$edingAffix[0]:
			case "unscroll".$edingAffix[0]:
			case "whang".$edingAffix[0]:
			case "bath".$edingAffix[0]:
			case "billet".$edingAffix[0]:
			case "collar".$edingAffix[0]:
			case "ballot".$edingAffix[0]:
			case "earth".$edingAffix[0]:
			case "fathom".$edingAffix[0]:
			case "fillet".$edingAffix[0]:
			case "mortar".$edingAffix[0]:
			case "parrot".$edingAffix[0]:
			case "profit".$edingAffix[0]:
			case "ransom".$edingAffix[0]:
			case "slang".$edingAffix[0]:
				return self::semi_reg_stem($word,0, "");
			case "disunited":
			case "aquaplaned":
			case "enplaned":
			case "revenged":
			case "riposted":
			case "seined":
				return self::stem($word,2, "e", "ed");
			case "toping":
				return self::stem($word,3, "e", "ing");
			case "distills":
			case "fulfills":
			case "appalls":
				return self::stem($word,2, "", "s");
			case "overcalled":
			case "miscalled":
				return self::stem($word, 2, "", "ed");

			case "catcalling":
				return self::stem($word,3,"","ing");
				break;
			case "catcalling":
			case "squalling":
				return self::stem($word,3,"","ing");
				break;
			case "browbeating":
			case "axing":
			case "dubbining":
				return self::stem($word,3,"","ing");
				break;
			case "summonses":
				return self::stem($word,2,"","s");
				break;
			case "putted":
				return self::stem($word,2,"","ed");
				break;
			case "summonsed":
				return self::stem($word,2,"","ed");
				break;
			case "sugared":
			case "tarmacadamed":
			case "beggared":
			case "betrothed":
			case "boomeranged":
			case "chagrined":
			case "envenomed":
			case "miaoued":
			case "pressganged":
				return self::stem($word,2,"","ed");
				break;
			case "abode" :
				return self::stem($word,3,"ide","ed");
				break;        /* en */
			case "abought" :
				return self::stem(5,"y","ed");
				break;        /* en */
			case "abyes" :
				return self::stem($word,2,"","s");
				break;
			case "addrest" :
				return self::stem($word,3,"ess","ed");
				break;      /* en */ /* disprefer */
			case "ageing" :
				return self::stem($word,4,"e","ing");
				break;
			case "agreed" :
				return self::stem($word,3,"ee","ed");
				break;        /* en */
			case "anted" :
				return self::stem($word,3,"te","ed");
				break;         /* en */
			case "antes" :
				return self::stem($word,2,"e","s");
				break;
			case "arisen" :
				return self::stem($word,3,"se","en");
				break;
			case "arose" :
				return self::stem($word,3,"ise","ed");
				break;
			case "ate" :
				return self::stem($word,3,"eat","ed");
				break;
			case "awoke" :
				return self::stem($word,3,"ake","ed");
				break;
			case "awoken" :
				return self::stem($word,4,"ake","en");
				break;
			case "backbit" :
				return self::stem($word,3,"bite","ed");
				break;
			case "backbiting" :
				return self::stem($word,4,"te","ing");
				break;
			case "backbitten" :
				return self::stem($word,3,"e","en");
				break;
			case "backslid" :
				return self::stem($word,3,"lide","ed");
				break;
			case "backslidden" :
				return self::stem($word,3,"e","en");
				break;
			case "bad" :
				return self::stem($word,3,"bid","ed");
				break;          /* disprefer */
			case "bade" :
				return self::stem($word,3,"id","ed");
				break;
			case "bandieds" :
				return self::stem($word,4,"y","s");
				break;
			case "became" :
				return self::stem($word,3,"ome","ed");
				break;       /* en */
			case "befallen" :
				return self::stem($word,3,"l","en");
				break;
			case "befalling" :
				return self::stem($word,4,"l","ing");
				break;
			case "befell" :
				return self::stem($word,3,"all","ed");
				break;
			case "began" :
				return self::stem($word,3,"gin","ed");
				break;
			case "begat" :
				return self::stem($word,3,"get","ed");
				break;        /* disprefer */
			case "begirt" :
				return self::stem($word,3,"ird","ed");
				break;       /* en */
			case "begot" :
				return self::stem($word,3,"get","ed");
				break;
			case "begotten" :
				return self::stem(5,"et","en");
				break;
			case "begun" :
				return self::stem($word,3,"gin","en");
				break;
			case "beheld" :
				return self::stem($word,3,"old","ed");
				break;
			case "beholden" :
				return self::stem($word,3,"d","en");
				break;
			case "benempt" :
				return self::stem($word,4,"ame","ed");
				break;      /* en */
			case "bent" :
				return self::stem($word,3,"end","ed");
				break;         /* en */
			case "besought" :
				return self::stem(5,"eech","ed");
				break;    /* en */
			case "bespoke" :
				return self::stem($word,3,"eak","ed");
				break;
			case "bespoken" :
				return self::stem($word,4,"eak","en");
				break;
			case "bestrewn" :
				return self::stem($word,3,"ew","en");
				break;
			case "bestrid" :
				return self::stem($word,3,"ride","ed");
				break;     /* disprefer */
			case "bestridden" :
				return self::stem($word,3,"e","en");
				break;
			case "bestrode" :
				return self::stem($word,3,"ide","ed");
				break;
			case "betaken" :
				return self::stem($word,3,"ke","en");
				break;
			case "bethought" :
				return self::stem(5,"ink","ed");
				break;    /* en */
			case "betook" :
				return self::stem($word,3,"ake","ed");
				break;
			case "bidden" :
				return self::stem($word,3,"","en");
				break;
			case "bit" :
				return self::stem($word,3,"bite","ed");
				break;
			case "biting" :
				return self::stem($word,4,"te","ing");
				break;
			case "bitten" :
				return self::stem($word,3,"e","en");
				break;
			case "bled" :
				return self::stem($word,3,"leed","ed");
				break;        /* en */
			case "blest" :
				return self::stem($word,3,"ess","ed");
				break;        /* en */ /* disprefer */
			case "blew" :
				return self::stem($word,3,"low","ed");
				break;
			case "blown" :
				return self::stem($word,3,"ow","en");
				break;
			case "bogged-down" :
				return self::stem(8,"-down","ed");
				break; /* en */
			case "bogging-down" :
				return self::stem(9,"-down","ing");
				break;
			case "bogs-down" :
				return self::stem(6,"-down","s");
				break;
			case "boogied" :
				return self::stem($word,3,"ie","ed");
				break;       /* en */
			case "boogies" :
				return self::stem($word,2,"e","s");
				break;
			case "bore" :
				return self::stem($word,3,"ear","ed");
				break;
			case "borne" :
				return self::stem($word,4,"ear","en");
				break;        /* disprefer */
			case "born" :
				return self::stem($word,3,"ear","en");
				break;
			case "bought" :
				return self::stem(5,"uy","ed");
				break;        /* en */
			case "bound" :
				return self::stem($word,4,"ind","ed");
				break;        /* en */
			case "breastfed" :
				return self::stem($word,3,"feed","ed");
				break;   /* en */
			case "bred" :
				return self::stem($word,3,"reed","ed");
				break;        /* en */
			case "breid" :
				return self::stem($word,3,"ei","ed");
				break;         /* en */
			case "bringing" :
				return self::stem($word,4,"g","ing");
				break;
			case "broke" :
				return self::stem($word,3,"eak","ed");
				break;
			case "broken" :
				return self::stem($word,4,"eak","en");
				break;
			case "brought" :
				return self::stem(5,"ing","ed");
				break;      /* en */
			case "built" :
				return self::stem($word,3,"ild","ed");
				break;        /* en */
			case "burnt" :
				return self::stem($word,3,"rn","ed");
				break;         /* en */ /* disprefer */
			case "bypast" :
				return self::stem($word,3,"ass","ed");
				break;       /* en */ /* disprefer */
			case "came" :
				return self::stem($word,3,"ome","ed");
				break;         /* en */
			case "caught" :
				return self::stem($word,4,"tch","ed");
				break;       /* en */
			case "chassed" :
				return self::stem($word,3,"se","ed");
				break;       /* en */
			case "chasseing" :
				return self::stem($word,4,"e","ing");
				break;
			case "chasses" :
				return self::stem($word,2,"e","s");
				break;
			case "chevied" :
				return self::stem(5,"ivy","ed");
				break;      /* en */ /* disprefer */
			case "chevies" :
				return self::stem(5,"ivy","s");
				break;       /* disprefer */
			case "chevying" :
				return self::stem(6,"ivy","ing");
				break;    /* disprefer */
			case "chid" :
				return self::stem($word,3,"hide","ed");
				break;        /* disprefer */
			case "chidden" :
				return self::stem($word,3,"e","en");
				break;        /* disprefer */
			case "chivvied" :
				return self::stem($word,4,"y","ed");
				break;       /* en */
			case "chivvies" :
				return self::stem($word,4,"y","s");
				break;
			case "chivvying" :
				return self::stem(5,"y","ing");
				break;
			case "chose" :
				return self::stem($word,3,"oose","ed");
				break;
			case "chosen" :
				return self::stem($word,3,"ose","en");
				break;
			case "clad" :
				return self::stem($word,3,"lothe","ed");
				break;       /* en */
			case "cleft" :
				return self::stem($word,3,"eave","ed");
				break;       /* en */ /* disprefer */
			case "clept" :
				return self::stem($word,3,"epe","ed");
				break;        /* en */ /* disprefer */
			case "clinging" :
				return self::stem($word,4,"g","ing");
				break;
			case "clove" :
				return self::stem($word,3,"eave","ed");
				break;
			case "cloven" :
				return self::stem($word,4,"eave","en");
				break;
			case "clung" :
				return self::stem($word,3,"ing","ed");
				break;        /* en */
			case "countersank" :
				return self::stem($word,3,"ink","ed");
				break;
			case "countersunk" :
				return self::stem($word,3,"ink","en");
				break;
			case "crept" :
				return self::stem($word,3,"eep","ed");
				break;        /* en */
			case "crossbred" :
				return self::stem($word,3,"reed","ed");
				break;   /* en */
			case "curettes" :
				return self::stem($word,3,"","s");
				break;
			case "curst" :
				return self::stem($word,3,"rse","ed");
				break;        /* en */ /* disprefer */
			case "dealt" :
				return self::stem($word,3,"al","ed");
				break;         /* en */
			case "decreed" :
				return self::stem($word,3,"ee","ed");
				break;       /* en */
			case "degases" :
				return self::stem($word,2,"","s");
				break;
			case "deleing" :
				return self::stem($word,4,"e","ing");
				break;
			case "disagreed" :
				return self::stem($word,3,"ee","ed");
				break;     /* en */
			case "disenthralls" :
				return self::stem($word,2,"","s");
				break;     /* disprefer */
			case "disenthrals" :
				return self::stem($word,2,"l","s");
				break;
			case "dought" :
				return self::stem($word,4,"w","ed");
				break;         /* en */
			case "dove" :
				return self::stem($word,3,"ive","ed");
				break;         /* en */ /* disprefer */
			case "drank" :
				return self::stem($word,3,"ink","ed");
				break;
			case "drawn" :
				return self::stem($word,3,"aw","en");
				break;
			case "dreamt" :
				return self::stem($word,3,"am","ed");
				break;        /* en */
			case "dreed" :
				return self::stem($word,3,"ee","ed");
				break;         /* en */
			case "drew" :
				return self::stem($word,3,"raw","ed");
				break;
			case "driven" :
				return self::stem($word,3,"ve","en");
				break;
			case "drove" :
				return self::stem($word,3,"ive","ed");
				break;
			case "drunk" :
				return self::stem($word,3,"ink","en");
				break;
			case "dug" :
				return self::stem($word,3,"dig","ed");
				break;          /* en */
			case "dwelt" :
				return self::stem($word,3,"ell","ed");
				break;        /* en */
			case "eaten" :
				return self::stem($word,3,"t","en");
				break;
			case "emceed" :
				return self::stem($word,3,"ee","ed");
				break;        /* en */
			case "enwound" :
				return self::stem($word,4,"ind","ed");
				break;      /* en */
			case "facsimileing" :
				return self::stem($word,4,"e","ing");
				break;
			case "fallen" :
				return self::stem($word,3,"l","en");
				break;
			case "fed" :
				return self::stem($word,3,"feed","ed");
				break;         /* en */
			case "fell" :
				return self::stem($word,3,"all","ed");
				break;
			case "felt" :
				return self::stem($word,3,"eel","ed");
				break;         /* en */
			case "filagreed" :
				return self::stem($word,3,"ee","ed");
				break;     /* en */
			case "filigreed" :
				return self::stem($word,3,"ee","ed");
				break;     /* en */
			case "fillagreed" :
				return self::stem($word,3,"ee","ed");
				break;    /* en */
			case "fled" :
				return self::stem($word,3,"lee","ed");
				break;         /* en */
			case "flew" :
				return self::stem($word,3,"ly","ed");
				break;
			case "flinging" :
				return self::stem($word,4,"g","ing");
				break;
			case "floodlit" :
				return self::stem($word,3,"light","ed");
				break;   /* en */
			case "flown" :
				return self::stem($word,3,"y","en");
				break;
			case "flung" :
				return self::stem($word,3,"ing","ed");
				break;        /* en */
			case "flyblew" :
				return self::stem($word,3,"low","ed");
				break;
			case "flyblown" :
				return self::stem($word,3,"ow","en");
				break;
			case "forbade" :
				return self::stem($word,3,"id","ed");
				break;
			case "forbad" :
				return self::stem($word,3,"bid","ed");
				break;       /* disprefer */
			case "forbidden" :
				return self::stem($word,3,"","en");
				break;
			case "forbore" :
				return self::stem($word,3,"ear","ed");
				break;
			case "forborne" :
				return self::stem($word,4,"ear","en");
				break;
			case "fordid" :
				return self::stem($word,3,"do","ed");
				break;
			case "fordone" :
				return self::stem($word,3,"o","en");
				break;
			case "foredid" :
				return self::stem($word,3,"do","ed");
				break;
			case "foredone" :
				return self::stem($word,3,"o","en");
				break;
			case "foregone" :
				return self::stem($word,3,"o","en");
				break;
			case "foreknew" :
				return self::stem($word,3,"now","ed");
				break;
			case "foreknown" :
				return self::stem($word,3,"ow","en");
				break;
			case "foreran" :
				return self::stem($word,3,"run","ed");
				break;      /* en */
			case "foresaw" :
				return self::stem($word,3,"see","ed");
				break;
			case "foreseen" :
				return self::stem($word,3,"ee","en");
				break;
			case "foreshown" :
				return self::stem($word,3,"ow","en");
				break;
			case "forespoke" :
				return self::stem($word,3,"eak","ed");
				break;
			case "forespoken" :
				return self::stem($word,4,"eak","en");
				break;
			case "foretelling" :
				return self::stem($word,4,"l","ing");
				break;
			case "foretold" :
				return self::stem($word,3,"ell","ed");
				break;     /* en */
			case "forewent" :
				return self::stem($word,4,"go","ed");
				break;
			case "forgave" :
				return self::stem($word,3,"ive","ed");
				break;
			case "forgiven" :
				return self::stem($word,3,"ve","en");
				break;
			case "forgone" :
				return self::stem($word,3,"o","en");
				break;
			case "forgot" :
				return self::stem($word,3,"get","ed");
				break;
			case "forgotten" :
				return self::stem(5,"et","en");
				break;
			case "forsaken" :
				return self::stem($word,3,"ke","en");
				break;
			case "forsook" :
				return self::stem($word,3,"ake","ed");
				break;
			case "forspoke" :
				return self::stem($word,3,"eak","ed");
				break;
			case "forspoken" :
				return self::stem($word,4,"eak","en");
				break;
			case "forswore" :
				return self::stem($word,3,"ear","ed");
				break;
			case "forsworn" :
				return self::stem($word,3,"ear","en");
				break;
			case "forwent" :
				return self::stem($word,4,"go","ed");
				break;
			case "fought" :
				return self::stem(5,"ight","ed");
				break;      /* en */
			case "found" :
				return self::stem($word,4,"ind","ed");
				break;        /* en */
			case "freed" :
				return self::stem($word,3,"ee","ed");
				break;         /* en */
			case "fricasseed" :
				return self::stem($word,3,"ee","ed");
				break;    /* en */
			case "froze" :
				return self::stem($word,3,"eeze","ed");
				break;
			case "frozen" :
				return self::stem($word,4,"eeze","en");
				break;
			case "gainsaid" :
				return self::stem($word,3,"ay","ed");
				break;      /* en */
			case "gan" :
				return self::stem($word,3,"gin","en");
				break;
			case "garnisheed" :
				return self::stem($word,3,"ee","ed");
				break;    /* en */
			case "gases" :
				return self::stem($word,2,"","s");
				break;
			case "gave" :
				return self::stem($word,3,"ive","ed");
				break;
			case "geed" :
				return self::stem($word,3,"ee","ed");
				break;          /* en */
			case "gelt" :
				return self::stem($word,3,"eld","ed");
				break;         /* en */
			case "genned-up" :
				return self::stem(6,"-up","ed");
				break;    /* en */
			case "genning-up" :
				return self::stem(7,"-up","ing");
				break;
			case "gens-up" :
				return self::stem($word,4,"-up","s");
				break;
			case "ghostwriting" :
				return self::stem($word,4,"te","ing");
				break;
			case "ghostwritten" :
				return self::stem($word,3,"e","en");
				break;
			case "ghostwrote" :
				return self::stem($word,3,"ite","ed");
				break;
			case "gilt" :
				return self::stem($word,3,"ild","ed");
				break;         /* en */ /* disprefer */
			case "girt" :
				return self::stem($word,3,"ird","ed");
				break;         /* en */ /* disprefer */
			case "given" :
				return self::stem($word,3,"ve","en");
				break;
			case "gnawn" :
				return self::stem($word,3,"aw","en");
				break;
			case "gone" :
				return self::stem($word,3,"o","en");
				break;
			case "got" :
				return self::stem($word,3,"get","ed");
				break;
			case "gotten" :
				return self::stem(5,"et","en");
				break;
			case "graven" :
				return self::stem($word,3,"ve","en");
				break;
			case "greed" :
				return self::stem($word,3,"ee","ed");
				break;         /* en */
			case "grew" :
				return self::stem($word,3,"row","ed");
				break;
			case "gript" :
				return self::stem($word,3,"ip","ed");
				break;         /* en */ /* disprefer */
			case "ground" :
				return self::stem($word,4,"ind","ed");
				break;       /* en */
			case "grown" :
				return self::stem($word,3,"ow","en");
				break;
			case "guaranteed" :
				return self::stem($word,3,"ee","ed");
				break;    /* en */
			case "hacksawn" :
				return self::stem($word,3,"aw","en");
				break;
			case "hamstringing" :
				return self::stem($word,4,"g","ing");
				break;
			case "hamstrung" :
				return self::stem($word,3,"ing","ed");
				break;    /* en */
			case "handfed" :
				return self::stem($word,3,"feed","ed");
				break;     /* en */
			case "heard" :
				return self::stem($word,3,"ar","ed");
				break;         /* en */
			case "held" :
				return self::stem($word,3,"old","ed");
				break;         /* en */
			case "hewn" :
				return self::stem($word,3,"ew","en");
				break;
			case "hid" :
				return self::stem($word,3,"hide","ed");
				break;
			case "hidden" :
				return self::stem($word,3,"e","en");
				break;
			case "honied" :
				return self::stem($word,3,"ey","ed");
				break;        /* en */
			case "hove" :
				return self::stem($word,3,"eave","ed");
				break;        /* en */ /* disprefer */
			case "hung" :
				return self::stem($word,3,"ang","ed");
				break;         /* en */
			case "impanells" :
				return self::stem($word,2,"","s");
				break;
			case "inbred" :
				return self::stem($word,3,"reed","ed");
				break;      /* en */
			case "indwelling" :
				return self::stem($word,4,"l","ing");
				break;
			case "indwelt" :
				return self::stem($word,3,"ell","ed");
				break;      /* en */
			case "inlaid" :
				return self::stem($word,3,"ay","ed");
				break;        /* en */
			case "interbred" :
				return self::stem($word,3,"reed","ed");
				break;   /* en */
			case "interlaid" :
				return self::stem($word,3,"ay","ed");
				break;     /* en */
			case "interpled" :
				return self::stem($word,3,"lead","ed");
				break;   /* en */ /* disprefer */
			case "interwove" :
				return self::stem($word,3,"eave","ed");
				break;
			case "interwoven" :
				return self::stem($word,4,"eave","en");
				break;
			case "inwove" :
				return self::stem($word,3,"eave","ed");
				break;
			case "inwoven" :
				return self::stem($word,4,"eave","en");
				break;
			case "joint" :
				return self::stem($word,3,"in","ed");
				break;         /* en */ /* disprefer */
			case "kent" :
				return self::stem($word,3,"en","ed");
				break;          /* en */
			case "kept" :
				return self::stem($word,3,"eep","ed");
				break;         /* en */
			case "kneed" :
				return self::stem($word,3,"ee","ed");
				break;         /* en */
			case "knelt" :
				return self::stem($word,3,"eel","ed");
				break;        /* en */
			case "knew" :
				return self::stem($word,3,"now","ed");
				break;
			case "known" :
				return self::stem($word,3,"ow","en");
				break;
			case "laden" :
				return self::stem($word,3,"de","en");
				break;
			case "ladyfied" :
				return self::stem(5,"ify","ed");
				break;     /* en */
			case "ladyfies" :
				return self::stem(5,"ify","s");
				break;
			case "ladyfying" :
				return self::stem(6,"ify","ing");
				break;
			case "laid" :
				return self::stem($word,3,"ay","ed");
				break;          /* en */
			case "lain" :
				return self::stem($word,3,"ie","en");
				break;
			case "leant" :
				return self::stem($word,3,"an","ed");
				break;         /* en */ /* disprefer */
			case "leapt" :
				return self::stem($word,3,"ap","ed");
				break;         /* en */
			case "learnt" :
				return self::stem($word,3,"rn","ed");
				break;        /* en */
			case "led" :
				return self::stem($word,3,"lead","ed");
				break;         /* en */
			case "left" :
				return self::stem($word,3,"eave","ed");
				break;        /* en */
			case "lent" :
				return self::stem($word,3,"end","ed");
				break;         /* en */
			case "lit" :
				return self::stem($word,3,"light","ed");
				break;        /* en */
			case "lost" :
				return self::stem($word,3,"ose","ed");
				break;         /* en */
			case "made" :
				return self::stem($word,3,"ake","ed");
				break;         /* en */
			case "meant" :
				return self::stem($word,3,"an","ed");
				break;         /* en */
			case "met" :
				return self::stem($word,3,"meet","ed");
				break;         /* en */
			case "misbecame" :
				return self::stem($word,3,"ome","ed");
				break;    /* en */
			case "misdealt" :
				return self::stem($word,3,"al","ed");
				break;      /* en */
			case "misgave" :
				return self::stem($word,3,"ive","ed");
				break;
			case "misgiven" :
				return self::stem($word,3,"ve","en");
				break;
			case "misheard" :
				return self::stem($word,3,"ar","ed");
				break;      /* en */
			case "mislaid" :
				return self::stem($word,3,"ay","ed");
				break;       /* en */
			case "misled" :
				return self::stem($word,3,"lead","ed");
				break;      /* en */
			case "mispled" :
				return self::stem($word,3,"lead","ed");
				break;     /* en */ /* disprefer */
			case "misspelt" :
				return self::stem($word,3,"ell","ed");
				break;     /* en */ /* disprefer */
			case "misspent" :
				return self::stem($word,3,"end","ed");
				break;     /* en */
			case "mistaken" :
				return self::stem($word,3,"ke","en");
				break;
			case "mistook" :
				return self::stem($word,3,"ake","ed");
				break;      /* en */
			case "misunderstood" :
				return self::stem($word,3,"and","ed");
				break; /* en */
			case "molten" :
				return self::stem(5,"elt","en");
				break;
			case "mown" :
				return self::stem($word,3,"ow","en");
				break;
			case "outbidden" :
				return self::stem($word,3,"","en");
				break;       /* disprefer */
			case "outbred" :
				return self::stem($word,3,"reed","ed");
				break;     /* en */
			case "outdid" :
				return self::stem($word,3,"do","ed");
				break;
			case "outdone" :
				return self::stem($word,3,"o","en");
				break;
			case "outgone" :
				return self::stem($word,3,"o","en");
				break;
			case "outgrew" :
				return self::stem($word,3,"row","ed");
				break;
			case "outgrown" :
				return self::stem($word,3,"ow","en");
				break;
			case "outlaid" :
				return self::stem($word,3,"ay","ed");
				break;       /* en */
			case "outran" :
				return self::stem($word,3,"run","ed");
				break;       /* en */
			case "outridden" :
				return self::stem($word,3,"e","en");
				break;
			case "outrode" :
				return self::stem($word,3,"ide","ed");
				break;
			case "outselling" :
				return self::stem($word,4,"l","ing");
				break;
			case "outshone" :
				return self::stem($word,3,"ine","ed");
				break;     /* en */
			case "outshot" :
				return self::stem($word,3,"hoot","ed");
				break;     /* en */
			case "outsold" :
				return self::stem($word,3,"ell","ed");
				break;      /* en */
			case "outstood" :
				return self::stem($word,3,"and","ed");
				break;     /* en */
			case "outthought" :
				return self::stem(5,"ink","ed");
				break;   /* en */
			case "outwent" :
				return self::stem($word,4,"go","ed");
				break;       /* en */
			case "outwore" :
				return self::stem($word,3,"ear","ed");
				break;
			case "outworn" :
				return self::stem($word,3,"ear","en");
				break;
			case "overbidden" :
				return self::stem($word,3,"","en");
				break;      /* disprefer */
			case "overblew" :
				return self::stem($word,3,"low","ed");
				break;
			case "overblown" :
				return self::stem($word,3,"ow","en");
				break;
			case "overbore" :
				return self::stem($word,3,"ear","ed");
				break;
			case "overborne" :
				return self::stem($word,4,"ear","en");
				break;
			case "overbuilt" :
				return self::stem($word,3,"ild","ed");
				break;    /* en */
			case "overcame" :
				return self::stem($word,3,"ome","ed");
				break;     /* en */
			case "overdid" :
				return self::stem($word,3,"do","ed");
				break;
			case "overdone" :
				return self::stem($word,3,"o","en");
				break;
			case "overdrawn" :
				return self::stem($word,3,"aw","en");
				break;
			case "overdrew" :
				return self::stem($word,3,"raw","ed");
				break;
			case "overdriven" :
				return self::stem($word,3,"ve","en");
				break;
			case "overdrove" :
				return self::stem($word,3,"ive","ed");
				break;
			case "overflew" :
				return self::stem($word,3,"ly","ed");
				break;      /* en */
			case "overgrew" :
				return self::stem($word,3,"row","ed");
				break;
			case "overgrown" :
				return self::stem($word,3,"ow","en");
				break;
			case "overhanging" :
				return self::stem($word,4,"g","ing");
				break;
			case "overheard" :
				return self::stem($word,3,"ar","ed");
				break;     /* en */
			case "overhung" :
				return self::stem($word,3,"ang","ed");
				break;     /* en */
			case "overlaid" :
				return self::stem($word,3,"ay","ed");
				break;      /* en */
			case "overlain" :
				return self::stem($word,3,"ie","en");
				break;
			case "overlies" :
				return self::stem($word,2,"e","s");
				break;
			case "overlying" :
				return self::stem($word,4,"ie","ing");
				break;
			case "overpaid" :
				return self::stem($word,3,"ay","ed");
				break;      /* en */
			case "overpast" :
				return self::stem($word,3,"ass","ed");
				break;     /* en */
			case "overran" :
				return self::stem($word,3,"run","ed");
				break;      /* en */
			case "overridden" :
				return self::stem($word,3,"e","en");
				break;
			case "overrode" :
				return self::stem($word,3,"ide","ed");
				break;
			case "oversaw" :
				return self::stem($word,3,"see","ed");
				break;
			case "overseen" :
				return self::stem($word,3,"ee","en");
				break;
			case "overselling" :
				return self::stem($word,4,"l","ing");
				break;
			case "oversewn" :
				return self::stem($word,3,"ew","en");
				break;
			case "overshot" :
				return self::stem($word,3,"hoot","ed");
				break;    /* en */
			case "overslept" :
				return self::stem($word,3,"eep","ed");
				break;    /* en */
			case "oversold" :
				return self::stem($word,3,"ell","ed");
				break;     /* en */
			case "overspent" :
				return self::stem($word,3,"end","ed");
				break;    /* en */
			case "overspilt" :
				return self::stem($word,3,"ill","ed");
				break;    /* en */ /* disprefer */
			case "overtaken" :
				return self::stem($word,3,"ke","en");
				break;
			case "overthrew" :
				return self::stem($word,3,"row","ed");
				break;
			case "overthrown" :
				return self::stem($word,3,"ow","en");
				break;
			case "overtook" :
				return self::stem($word,3,"ake","ed");
				break;
			case "overwound" :
				return self::stem($word,4,"ind","ed");
				break;    /* en */
			case "overwriting" :
				return self::stem($word,4,"te","ing");
				break;
			case "overwritten" :
				return self::stem($word,3,"e","en");
				break;
			case "overwrote" :
				return self::stem($word,3,"ite","ed");
				break;
			case "paid" :
				return self::stem($word,3,"ay","ed");
				break;          /* en */
			case "partaken" :
				return self::stem($word,3,"ke","en");
				break;
			case "partook" :
				return self::stem($word,3,"ake","ed");
				break;
			case "peed" :
				return self::stem($word,3,"ee","ed");
				break;          /* en */
			case "pent" :
				return self::stem($word,3,"en","ed");
				break;          /* en */ /* disprefer */
			case "pled" :
				return self::stem($word,3,"lead","ed");
				break;        /* en */ /* disprefer */
			case "prepaid" :
				return self::stem($word,3,"ay","ed");
				break;       /* en */
			case "prologs" :
				return self::stem($word,2,"gue","s");
				break;
			case "proven" :
				return self::stem($word,3,"ve","en");
				break;
			case "pureed" :
				return self::stem($word,3,"ee","ed");
				break;        /* en */
			case "quartersawn" :
				return self::stem($word,3,"aw","en");
				break;
			case "queued" :
				return self::stem($word,3,"ue","ed");
				break;        /* en */
			case "queues" :
				return self::stem($word,2,"e","s");
				break;
			case "queuing" :
				return self::stem($word,4,"ue","ing");
				break;      /* disprefer */
			case "ran" :
				return self::stem($word,3,"run","ed");
				break;          /* en */
			case "rang" :
				return self::stem($word,3,"ing","ed");
				break;
			case "rarefied" :
				return self::stem($word,3,"y","ed");
				break;       /* en */
			case "rarefies" :
				return self::stem($word,3,"y","s");
				break;
			case "rarefying" :
				return self::stem($word,4,"y","ing");
				break;
			case "razeed" :
				return self::stem($word,3,"ee","ed");
				break;
			case "rebuilt" :
				return self::stem($word,3,"ild","ed");
				break;      /* en */
			case "recced" :
				return self::stem($word,3,"ce","ed");
				break;        /* en */
			case "red" :
				return self::stem($word,3,"red","ed");
				break;          /* en */
			case "redid" :
				return self::stem($word,3,"do","ed");
				break;
			case "redone" :
				return self::stem($word,3,"o","en");
				break;
			case "refereed" :
				return self::stem($word,3,"ee","ed");
				break;      /* en */
			case "reft" :
				return self::stem($word,3,"eave","ed");
				break;        /* en */
			case "remade" :
				return self::stem($word,3,"ake","ed");
				break;       /* en */
			case "repaid" :
				return self::stem($word,3,"ay","ed");
				break;        /* en */
			case "reran" :
				return self::stem($word,3,"run","ed");
				break;        /* en */
			case "resat" :
				return self::stem($word,3,"sit","ed");
				break;        /* en */
			case "retaken" :
				return self::stem($word,3,"ke","en");
				break;
			case "rethought" :
				return self::stem(5,"ink","ed");
				break;    /* en */
			case "retook" :
				return self::stem($word,3,"ake","ed");
				break;
			case "rewound" :
				return self::stem($word,4,"ind","ed");
				break;      /* en */
			case "rewriting" :
				return self::stem($word,4,"te","ing");
				break;
			case "rewritten" :
				return self::stem($word,3,"e","en");
				break;
			case "rewrote" :
				return self::stem($word,3,"ite","ed");
				break;
			case "ridden" :
				return self::stem($word,3,"e","en");
				break;
			case "risen" :
				return self::stem($word,3,"se","en");
				break;
			case "riven" :
				return self::stem($word,3,"ve","en");
				break;
			case "rode" :
				return self::stem($word,3,"ide","ed");
				break;
			case "rose" :
				return self::stem($word,3,"ise","ed");
				break;
			case "rove" :
				return self::stem($word,3,"eeve","ed");
				break;        /* en */
			case "rung" :
				return self::stem($word,3,"ing","en");
				break;
			case "said" :
				return self::stem($word,3,"ay","ed");
				break;          /* en */
			case "sang" :
				return self::stem($word,3,"ing","ed");
				break;
			case "sank" :
				return self::stem($word,3,"ink","ed");
				break;
			case "sat" :
				return self::stem($word,3,"sit","ed");
				break;          /* en */
			case "saw" :
				return self::stem($word,3,"see","ed");
				break;
			case "sawn" :
				return self::stem($word,3,"aw","en");
				break;
			case "seen" :
				return self::stem($word,3,"ee","en");
				break;
			case "sent" :
				return self::stem($word,3,"end","ed");
				break;         /* en */
			case "sewn" :
				return self::stem($word,3,"ew","en");
				break;
			case "shaken" :
				return self::stem($word,3,"ke","en");
				break;
			case "shaven" :
				return self::stem($word,3,"ve","en");
				break;
			case "shent" :
				return self::stem($word,3,"end","ed");
				break;        /* en */
			case "shewn" :
				return self::stem($word,3,"ew","en");
				break;
			case "shod" :
				return self::stem($word,3,"hoe","ed");
				break;         /* en */
			case "shone" :
				return self::stem($word,3,"ine","ed");
				break;        /* en */
			case "shook" :
				return self::stem($word,3,"ake","ed");
				break;
			case "shot" :
				return self::stem($word,3,"hoot","ed");
				break;        /* en */
			case "shown" :
				return self::stem($word,3,"ow","en");
				break;
			case "shrank" :
				return self::stem($word,3,"ink","ed");
				break;
			case "shriven" :
				return self::stem($word,3,"ve","en");
				break;
			case "shrove" :
				return self::stem($word,3,"ive","ed");
				break;
			case "shrunk" :
				return self::stem($word,3,"ink","en");
				break;
			case "shrunken" :
				return self::stem(5,"ink","en");
				break;     /* disprefer */
			case "sightsaw" :
				return self::stem($word,3,"see","ed");
				break;
			case "sightseen" :
				return self::stem($word,3,"ee","en");
				break;
			case "ski'd" :
				return self::stem($word,3,"i","ed");
				break;          /* en */
			case "skydove" :
				return self::stem($word,3,"ive","ed");
				break;      /* en */
			case "slain" :
				return self::stem($word,3,"ay","en");
				break;
			case "slept" :
				return self::stem($word,3,"eep","ed");
				break;        /* en */
			case "slew" :
				return self::stem($word,3,"lay","ed");
				break;
			case "slid" :
				return self::stem($word,3,"lide","ed");
				break;
			case "slidden" :
				return self::stem($word,3,"e","en");
				break;
			case "slinging" :
				return self::stem($word,4,"g","ing");
				break;
			case "slung" :
				return self::stem($word,3,"ing","ed");
				break;        /* en */
			case "slunk" :
				return self::stem($word,3,"ink","ed");
				break;        /* en */
			case "smelt" :
				return self::stem($word,3,"ell","ed");
				break;        /* en */ /* disprefer */
			case "smit" :
				return self::stem($word,3,"mite","ed");
				break;
			case "smiting" :
				return self::stem($word,4,"te","ing");
				break;
			case "smitten" :
				return self::stem($word,3,"e","en");
				break;
			case "smote" :
				return self::stem($word,3,"ite","ed");
				break;        /* en */ /* disprefer */
			case "sold" :
				return self::stem($word,3,"ell","ed");
				break;         /* en */
			case "soothsaid" :
				return self::stem($word,3,"ay","ed");
				break;     /* en */
			case "sortied" :
				return self::stem($word,3,"ie","ed");
				break;       /* en */
			case "sorties" :
				return self::stem($word,2,"e","s");
				break;
			case "sought" :
				return self::stem(5,"eek","ed");
				break;       /* en */
			case "sown" :
				return self::stem($word,3,"ow","en");
				break;
			case "spat" :
				return self::stem($word,3,"pit","ed");
				break;         /* en */
			case "sped" :
				return self::stem($word,3,"peed","ed");
				break;        /* en */
			case "spellbound" :
				return self::stem($word,4,"ind","ed");
				break;   /* en */
			case "spelt" :
				return self::stem($word,3,"ell","ed");
				break;        /* en */ /* disprefer */
			case "spent" :
				return self::stem($word,3,"end","ed");
				break;        /* en */
			case "spilt" :
				return self::stem($word,3,"ill","ed");
				break;        /* en */ /* disprefer */
			case "spoilt" :
				return self::stem($word,3,"il","ed");
				break;        /* en */
			case "spoke" :
				return self::stem($word,3,"eak","ed");
				break;
			case "spoken" :
				return self::stem($word,4,"eak","en");
				break;
			case "spotlit" :
				return self::stem($word,3,"light","ed");
				break;    /* en */
			case "sprang" :
				return self::stem($word,3,"ing","ed");
				break;
			case "springing" :
				return self::stem($word,4,"g","ing");
				break;
			case "sprung" :
				return self::stem($word,3,"ing","en");
				break;
			case "spun" :
				return self::stem($word,3,"pin","ed");
				break;         /* en */
			case "squeegeed" :
				return self::stem($word,3,"ee","ed");
				break;     /* en */
			case "stank" :
				return self::stem($word,3,"ink","ed");
				break;
			case "stinging" :
				return self::stem($word,4,"g","ing");
				break;
			case "stole" :
				return self::stem($word,3,"eal","ed");
				break;
			case "stolen" :
				return self::stem($word,4,"eal","en");
				break;
			case "stood" :
				return self::stem($word,3,"and","ed");
				break;        /* en */
			case "stove" :
				return self::stem($word,3,"ave","ed");
				break;        /* en */
			case "strewn" :
				return self::stem($word,3,"ew","en");
				break;
			case "stridden" :
				return self::stem($word,3,"e","en");
				break;
			case "stringing" :
				return self::stem($word,4,"g","ing");
				break;
			case "striven" :
				return self::stem($word,3,"ve","en");
				break;
			case "strode" :
				return self::stem($word,3,"ide","ed");
				break;
			case "strove" :
				return self::stem($word,3,"ive","ed");
				break;
			case "strown" :
				return self::stem($word,3,"ow","en");
				break;
			case "struck" :
				return self::stem($word,3,"ike","ed");
				break;       /* en */
			case "strung" :
				return self::stem($word,3,"ing","ed");
				break;       /* en */
			case "stuck" :
				return self::stem($word,3,"ick","ed");
				break;        /* en */
			case "stung" :
				return self::stem($word,3,"ing","ed");
				break;        /* en */
			case "stunk" :
				return self::stem($word,3,"ink","en");
				break;
			case "sung" :
				return self::stem($word,3,"ing","en");
				break;
			case "sunk" :
				return self::stem($word,3,"ink","en");
				break;
			case "sunken" :
				return self::stem(5,"ink","en");
				break;       /* disprefer */
			case "swam" :
				return self::stem($word,3,"wim","ed");
				break;
			case "swept" :
				return self::stem($word,3,"eep","ed");
				break;        /* en */
			case "swinging" :
				return self::stem($word,4,"g","ing");
				break;
			case "swollen" :
				return self::stem(5,"ell","en");
				break;
			case "swore" :
				return self::stem($word,3,"ear","ed");
				break;
			case "sworn" :
				return self::stem($word,3,"ear","en");
				break;
			case "swum" :
				return self::stem($word,3,"wim","en");
				break;
			case "swung" :
				return self::stem($word,3,"ing","ed");
				break;        /* en */
			case "taken" :
				return self::stem($word,3,"ke","en");
				break;
			case "taught" :
				return self::stem(5,"each","ed");
				break;      /* en */
			case "taxying" :
				return self::stem($word,4,"i","ing");
				break;       /* disprefer */
			case "teed" :
				return self::stem($word,3,"ee","ed");
				break;          /* en */
			case "thought" :
				return self::stem(5,"ink","ed");
				break;      /* en */
			case "threw" :
				return self::stem($word,3,"row","ed");
				break;
			case "thriven" :
				return self::stem($word,3,"ve","en");
				break;       /* disprefer */
			case "throve" :
				return self::stem($word,3,"ive","ed");
				break;       /* disprefer */
			case "thrown" :
				return self::stem($word,3,"ow","en");
				break;
			case "tinged" :
				return self::stem($word,3,"ge","ed");
				break;        /* en */
			case "tingeing" :
				return self::stem($word,4,"e","ing");
				break;
			case "tinging" :
				return self::stem($word,4,"ge","ing");
				break;      /* disprefer */
			case "told" :
				return self::stem($word,3,"ell","ed");
				break;         /* en */
			case "took" :
				return self::stem($word,3,"ake","ed");
				break;
			case "tore" :
				return self::stem($word,3,"ear","ed");
				break;
			case "torn" :
				return self::stem($word,3,"ear","en");
				break;
			case "tramels" :
				return self::stem($word,3,"mel","s");
				break;       /* disprefer */
			case "transfixt" :
				return self::stem($word,3,"ix","ed");
				break;     /* en */ /* disprefer */
			case "tranship" :
				return self::stem($word,3,"ship","ed");
				break;    /* en */
			case "trod" :
				return self::stem($word,3,"read","ed");
				break;
			case "trodden" :
				return self::stem(5,"ead","en");
				break;
			case "typewriting" :
				return self::stem($word,4,"te","ing");
				break;
			case "typewritten" :
				return self::stem($word,3,"e","en");
				break;
			case "typewrote" :
				return self::stem($word,3,"ite","ed");
				break;
			case "unbent" :
				return self::stem($word,3,"end","ed");
				break;       /* en */
			case "unbound" :
				return self::stem($word,4,"ind","ed");
				break;      /* en */
			case "unclad" :
				return self::stem($word,3,"lothe","ed");
				break;     /* en */
			case "underbought" :
				return self::stem(5,"uy","ed");
				break;   /* en */
			case "underfed" :
				return self::stem($word,3,"feed","ed");
				break;    /* en */
			case "undergirt" :
				return self::stem($word,3,"ird","ed");
				break;    /* en */
			case "undergone" :
				return self::stem($word,3,"o","en");
				break;
			case "underlaid" :
				return self::stem($word,3,"ay","ed");
				break;     /* en */
			case "underlain" :
				return self::stem($word,3,"ie","en");
				break;
			case "underlies" :
				return self::stem($word,2,"e","s");
				break;
			case "underlying" :
				return self::stem($word,4,"ie","ing");
				break;
			case "underpaid" :
				return self::stem($word,3,"ay","ed");
				break;     /* en */
			case "underselling" :
				return self::stem($word,4,"l","ing");
				break;
			case "undershot" :
				return self::stem($word,3,"hoot","ed");
				break;   /* en */
			case "undersold" :
				return self::stem($word,3,"ell","ed");
				break;    /* en */
			case "understood" :
				return self::stem($word,3,"and","ed");
				break;   /* en */
			case "undertaken" :
				return self::stem($word,3,"ke","en");
				break;
			case "undertook" :
				return self::stem($word,3,"ake","ed");
				break;
			case "underwent" :
				return self::stem($word,4,"go","ed");
				break;
			case "underwriting" :
				return self::stem($word,4,"te","ing");
				break;
			case "underwritten" :
				return self::stem($word,3,"e","en");
				break;
			case "underwrote" :
				return self::stem($word,3,"ite","ed");
				break;
			case "undid" :
				return self::stem($word,3,"do","ed");
				break;
			case "undone" :
				return self::stem($word,3,"o","en");
				break;
			case "unfroze" :
				return self::stem($word,3,"eeze","ed");
				break;
			case "unfrozen" :
				return self::stem($word,4,"eeze","en");
				break;
			case "unlaid" :
				return self::stem($word,3,"ay","ed");
				break;        /* en */
			case "unlearnt" :
				return self::stem($word,3,"rn","ed");
				break;      /* en */
			case "unmade" :
				return self::stem($word,3,"ake","ed");
				break;       /* en */
			case "unrove" :
				return self::stem($word,3,"eeve","ed");
				break;      /* en */
			case "unsaid" :
				return self::stem($word,3,"ay","ed");
				break;        /* en */
			case "unslinging" :
				return self::stem($word,4,"g","ing");
				break;
			case "unslung" :
				return self::stem($word,3,"ing","ed");
				break;      /* en */
			case "unspoke" :
				return self::stem($word,3,"eak","ed");
				break;
			case "unspoken" :
				return self::stem($word,4,"eak","en");
				break;
			case "unstringing" :
				return self::stem($word,4,"g","ing");
				break;
			case "unstrung" :
				return self::stem($word,3,"ing","ed");
				break;     /* en */
			case "unstuck" :
				return self::stem($word,3,"ick","ed");
				break;      /* en */
			case "unswore" :
				return self::stem($word,3,"ear","ed");
				break;
			case "unsworn" :
				return self::stem($word,3,"ear","en");
				break;
			case "untaught" :
				return self::stem(5,"each","ed");
				break;    /* en */
			case "unthought" :
				return self::stem(5,"ink","ed");
				break;    /* en */
			case "untrod" :
				return self::stem($word,3,"read","ed");
				break;
			case "untrodden" :
				return self::stem(5,"ead","en");
				break;
			case "unwound" :
				return self::stem($word,4,"ind","ed");
				break;      /* en */
			case "upbuilt" :
				return self::stem($word,3,"ild","ed");
				break;      /* en */
			case "upheld" :
				return self::stem($word,3,"old","ed");
				break;       /* en */
			case "uphove" :
				return self::stem($word,3,"eave","ed");
				break;      /* en */
			case "upped" :
				return self::stem($word,3,"","ed");
				break;           /* en */
			case "upping" :
				return self::stem($word,4,"","ing");
				break;
			case "uprisen" :
				return self::stem($word,3,"se","en");
				break;
			case "uprose" :
				return self::stem($word,3,"ise","ed");
				break;
			case "upsprang" :
				return self::stem($word,3,"ing","ed");
				break;
			case "upspringing" :
				return self::stem($word,4,"g","ing");
				break;
			case "upsprung" :
				return self::stem($word,3,"ing","en");
				break;
			case "upswept" :
				return self::stem($word,3,"eep","ed");
				break;      /* en */
			case "upswinging" :
				return self::stem($word,4,"g","ing");
				break;
			case "upswollen" :
				return self::stem(5,"ell","en");
				break;    /* disprefer */
			case "upswung" :
				return self::stem($word,3,"ing","ed");
				break;      /* en */
			case "visaed" :
				return self::stem($word,3,"a","ed");
				break;         /* en */
			case "visaing" :
				return self::stem($word,4,"a","ing");
				break;
			case "waylaid" :
				return self::stem($word,3,"ay","ed");
				break;
			case "waylain" :
				return self::stem($word,3,"ay","en");
				break;
			case "went" :
				return self::stem($word,4,"go","ed");
				break;
			case "wept" :
				return self::stem($word,3,"eep","ed");
				break;         /* en */
			case "whipsawn" :
				return self::stem($word,3,"aw","en");
				break;
			case "winterfed" :
				return self::stem($word,3,"feed","ed");
				break;   /* en */
			case "wiredrawn" :
				return self::stem($word,3,"aw","en");
				break;
			case "wiredrew" :
				return self::stem($word,3,"raw","ed");
				break;
			case "withdrawn" :
				return self::stem($word,3,"aw","en");
				break;
			case "withdrew" :
				return self::stem($word,3,"raw","ed");
				break;
			case "withheld" :
				return self::stem($word,3,"old","ed");
				break;     /* en */
			case "withstood" :
				return self::stem($word,3,"and","ed");
				break;    /* en */
			case "woke" :
				return self::stem($word,3,"ake","ed");
				break;
			case "woken" :
				return self::stem($word,4,"ake","en");
				break;
			case "won" :
				return self::stem($word,3,"win","ed");
				break;          /* en */
			case "wore" :
				return self::stem($word,3,"ear","ed");
				break;
			case "worn" :
				return self::stem($word,3,"ear","en");
				break;
			case "wound" :
				return self::stem($word,4,"ind","ed");
				break;        /* en */
			case "wove" :
				return self::stem($word,3,"eave","ed");
				break;
			case "woven" :
				return self::stem($word,4,"eave","en");
				break;
			case "wringing" :
				return self::stem($word,4,"g","ing");
				break;
			case "writing" :
				return self::stem($word,4,"te","ing");
				break;
			case "written" :
				return self::stem($word,3,"e","en");
				break;
			case "wrote" :
				return self::stem($word,3,"ite","ed");
				break;
			case "wrung" :
				return self::stem($word,3,"ing","ed");
				break;        /* en */
			case "ycleped" :
				return self::stem(7,"clepe","ed");
				break;    /* en */ /* disprefer */
			case "yclept" :
				return self::stem(6,"clepe","ed");
				break;     /* en */ /* disprefer */

			case "cryed" :
				return self::stem($word,3,"y","ed");
				break;          /* en */ /* disprefer */
			case "forted" :
				return self::stem($word,3,"te","ed");
				break;        /* en */
			case "forteing" :
				return self::stem($word,4,"e","ing");
				break;
			case "picknicks" :
				return self::stem($word,2,"","s");
				break;
			case "resold" :
				return self::stem($word,3,"ell","ed");
				break;       /* en */
			case "retold" :
				return self::stem($word,3,"ell","ed");
				break;       /* en */
			case "retying" :
				return self::stem($word,4,"ie","ing");
				break;
			case "singed" :
				return self::stem($word,3,"ge","ed");
				break;        /* en */
			case "singeing" :
				return self::stem($word,4,"e","ing");
				break;
			case "trecked" :
				return self::stem($word,4,"k","ed");
				break;        /* en */
			case "trecking" :
				return self::stem(5,"k","ing");
				break;

				break;
			case "buffetts" :
				return self::stem($word,2,"","s");
				break;
			case "plummetts" :
				return self::stem($word,2,"","s");
				break;        /* disprefer */
			case "gunslung" :
				return self::stem($word,3,"ing","ed");
				break;     /* en */
			case "gunslinging" :
				return self::stem($word,4,"g","ing");
				break;

			case "bussed" :
				return self::stem($word,3,"","ed");
				break;          /* en */
			case "bussing" :
				return self::stem($word,4,"","ing");
				break;

			case "ach".$edingAffix[0]   :
				return self::semi_reg_stem($word,0,"e");
				break;
			case "accustom".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "blossom".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "boycott".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "catalog".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case $this->PRE."creat".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "finess".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "interfer".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case $this->PRE."rout".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "tast".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "wast".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "acquitt".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "ante".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "arc".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "arck".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;           /* disprefer */
			case "banquet".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "barrel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "bedevil".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "beguil".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "bejewel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "bevel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "bias".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "biass".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "bivouack".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "buckram".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "bushel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "canal".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "cancel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;         /* disprefer */
			case "carol".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "cavil".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "cbel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "cbell".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;          /* disprefer */
			case "channel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "chisel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "clep".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "cloth".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "coiff".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "concertina".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "conga".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "coquett".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "counsel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;        /* disprefer */
			case "croquet".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "cudgel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "cupel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "debuss".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "degass".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "devil".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "diall".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "disembowel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "dishevel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "drivel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "duell".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "embuss".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "empanel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "enamel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "equal".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "equall".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;         /* disprefer */
			case "equipp".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "flannel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "frivol".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "frolick".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "fuell".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "funnel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "gambol".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "gass".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "gell".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "glace".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "gravel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "grovel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "gypp".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "hansel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "hatchel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "hocus-pocuss".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "hocuss".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "housel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "hovel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "impanel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "initiall".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "jewel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "kennel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "kernel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "label".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "laurel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "level".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "libel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "marshal".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "marvel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "medal".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "metal".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "mimick".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "misspell".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "model".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;          /* disprefer */
			case "nickel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "nonpluss".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "outgass".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "outgeneral".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "overspill".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "pall".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "panel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "panick".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "parallel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "parcel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "pedal".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "pencil".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "physick".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "picnick".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "pistol".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "polka".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "pommel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "precancel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;      /* disprefer */
			case "prolog".$edingAffix[0]:
				return self::semi_reg_stem(0,"ue");
				break;
			case "pummel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "quarrel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "quipp".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "quitt".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "ravel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "recce".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "refuell".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "revel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "rival".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "roquet".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "rowel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "samba".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "saute".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "shellack".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "shovel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "shrivel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "sick".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "signal".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;         /* disprefer */
			case "ski".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "snafu".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "snivel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "sol-fa".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "spancel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "spiral".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "squatt".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "squibb".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "squidd".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "stencil".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "subpoena".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "subtotal".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;       /* disprefer */
			case "swivel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "symbol".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "symboll".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;        /* disprefer */
			case "talc".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "talck".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;          /* disprefer */
			case "tassel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "taxi".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "tinsel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "total".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;          /* disprefer */
			case "towel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "traffick".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "tramel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "tramell".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;        /* disprefer */
			case "travel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;         /* disprefer */
			case "trowel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "tunnel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "uncloth".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "unkennel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "unravel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "upswell".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "victuall".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "vitrioll".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "viva".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "yodel".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "dies":
			case "ties":
			case "lies":
			case "unties":
			case "belies":
			case "hogties":
			case "stymies" :
				return self::stem($word,2,"e","s");
				break; /* en */
			case "died":
			case "tied":
			case "lied":
			case "untied":
			case "belied":
			case "hogtied":
			case "stymied" :
				return self::stem($word,2,"e","ed");
				break; /* en */
			case "dying":
			case "tying":
			case "lying":
			case "untying":
			case "belying":
			case "hogtying":
			case "stymying" :
				return self::stem($word,4,"ie","ing");
				break; /* en */
			case "bias":
				return self::cnull_stem($word);
				break;
			case "canvas":
				return self::cnull_stem($word);
				break;
			case "canvas".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "embed":
				return self::cnull_stem($word);
				break;                         /* disprefer */
			case "focuss".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "gas":
				return self::cnull_stem($word);
				break;
			case "picknick".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "adher".$edingAffix[0]:
			case "ador".$edingAffix[0]:
			case "attun".$edingAffix[0]:
			case "bast".$edingAffix[0]:
			case "bor".$edingAffix[0]:
			case "can".$edingAffix[0]:
			case "centr".$edingAffix[0]:
			case "cit".$edingAffix[0]:
			case "compet".$edingAffix[0]:
			case "cop".$edingAffix[0]:
			case "complet".$edingAffix[0]:
			case "concret".$edingAffix[0]:
			case "condon".$edingAffix[0]:
			case "contraven".$edingAffix[0]:
			case "conven".$edingAffix[0]:
			case "cran".$edingAffix[0]:
			case "delet".$edingAffix[0]:
			case "delineat".$edingAffix[0]:
			case "dop".$edingAffix[0]:
			case "drap".$edingAffix[0]:
			case "dron".$edingAffix[0]:
			case "escap".$edingAffix[0]:
			case "excit".$edingAffix[0]:
			case "fort".$edingAffix[0]:
			case "gap".$edingAffix[0]:
			case "gazett".$edingAffix[0]:
			case "grop".$edingAffix[0]:
			case "hon".$edingAffix[0]:
			case "hop".$edingAffix[0]:
			case "ignit".$edingAffix[0]:
			case "ignor".$edingAffix[0]:
			case "incit".$edingAffix[0]:
			case "interven".$edingAffix[0]:
			case "inton".$edingAffix[0]:
			case "invit".$edingAffix[0]:
			case "landscap".$edingAffix[0]:
			case "manoeuvr".$edingAffix[0]:
			case "nauseat".$edingAffix[0]:
			case "normalis".$edingAffix[0]:
			case "outmanoeuvr".$edingAffix[0]:
			case "overaw".$edingAffix[0]:
			case "permeat".$edingAffix[0]:
			case "persever".$edingAffix[0]:
			case "pip".$edingAffix[0]:
			case "por".$edingAffix[0]:
			case "postpon".$edingAffix[0]:
			case "prun".$edingAffix[0]:
			case "rap".$edingAffix[0]:
			case "recit".$edingAffix[0]:
			case "reshap".$edingAffix[0]:
			case "rop".$edingAffix[0]:
			case "shap".$edingAffix[0]:
			case "shor".$edingAffix[0]:
			case "snor".$edingAffix[0]:
			case "snip".$edingAffix[0]:
			case "ston".$edingAffix[0]:
			case "tap".$edingAffix[0]:
			case "wip".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "ape".$edingAffix[0]:
			case "augur".$edingAffix[0]:
			case "belong".$edingAffix[0]:
			case "berth".$edingAffix[0]:
			case "burr".$edingAffix[0]:
			case "conquer".$edingAffix[0]:
			case "egg".$edingAffix[0]:
			case "forestall".$edingAffix[0]:
			case "froth".$edingAffix[0]:
			case "install".$edingAffix[0]:
			case "lacquer".$edingAffix[0]:
			case "martyr".$edingAffix[0]:
			case "mouth".$edingAffix[0]:
			case "murmur".$edingAffix[0]:
			case "pivot".$edingAffix[0]:
			case "preceed".$edingAffix[0]:
			case "prolong".$edingAffix[0]:
			case "purr".$edingAffix[0]:
			case "quell".$edingAffix[0]:
			case "recall".$edingAffix[0]:
			case "refill".$edingAffix[0]:
			case "remill".$edingAffix[0]:
			case "resell".$edingAffix[0]:
			case "retell".$edingAffix[0]:
			case "smooth".$edingAffix[0]:
			case "throng".$edingAffix[0]:
			case "twang".$edingAffix[0]:
			case "unearth".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case "buffet"."t".$edingAffix[0]:
			case "plummet"."t".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "gunsling":
				return self::cnull_stem($word);
				break;
			case "hamstring":
				return self::cnull_stem($word);
				break;
			case "shred":
				return self::cnull_stem($word);
				break;
			case "unfocuss".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "accret".$edingAffix[0]:
			case "clon".$edingAffix[0]:
			case "deplet".$edingAffix[0]:
			case "dethron".$edingAffix[0]:
			case "dup".$edingAffix[0]:
			case "excret".$edingAffix[0]:
			case "expedit".$edingAffix[0]:
			case "extradit".$edingAffix[0]:
			case "fet".$edingAffix[0]:
			case "finetun".$edingAffix[0]:
			case "gor".$edingAffix[0]:
			case "hing".$edingAffix[0]:
			case "massacr".$edingAffix[0]:
			case "obsolet".$edingAffix[0]:
			case "reconven".$edingAffix[0]:
			case "recreat".$edingAffix[0]:
			case "recus".$edingAffix[0]:
			case "reignit".$edingAffix[0]:
			case "swip".$edingAffix[0]:
			case "videotap".$edingAffix[0]:
			case "zon".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "backpedal".$edingAffix[0]:
			case "bankroll".$edingAffix[0]:
			case "bequeath".$edingAffix[0]:
			case "blackball".$edingAffix[0]:
			case "bottom".$edingAffix[0]:
			case "clang".$edingAffix[0]:
			case "debut".$edingAffix[0]:
			case "doctor".$edingAffix[0]:
			case "eyeball".$edingAffix[0]:
			case "factor".$edingAffix[0]:
			case "imperil".$edingAffix[0]:
			case "landfill".$edingAffix[0]:
			case "margin".$edingAffix[0]:
			case "multihull".$edingAffix[0]:
			case "occur".$edingAffix[0]:
			case "overbill".$edingAffix[0]:
			case "pilot".$edingAffix[0]:
			case "prong".$edingAffix[0]:
			case "pyramid".$edingAffix[0]:
			case "reinstall".$edingAffix[0]:
			case "relabel".$edingAffix[0]:
			case "remodel".$edingAffix[0]:
			case "snowball".$edingAffix[0]:
			case "socall".$edingAffix[0]:
			case "squirrel".$edingAffix[0]:
			case "stonewall".$edingAffix[0]:
			case "wrong".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break; /* disprefer */
			case "chor".$edingAffix[0]:
			case "sepulchr".$edingAffix[0]:
			case "silhouett".$edingAffix[0]:
			case "telescop".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "subpena".$edingAffix[0]:
			case "suds".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;

			case "imbed" :
				return self::cnull_stem($word);
				break;
			case "precis" :
				return self::cnull_stem($word);
				break;
			case "precis".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break;
			case $this->A."-us".$edingAffix[0]:
			case "abus".$edingAffix[0]:
			case "accus".$edingAffix[0]:
			case "amus".$edingAffix[0]:
			case "arous".$edingAffix[0]:
			case "bemus".$edingAffix[0]:
			case "carous".$edingAffix[0]:
			case "contus".$edingAffix[0]:
			case "disabus".$edingAffix[0]:
			case "disus".$edingAffix[0]:
			case "dous".$edingAffix[0]:
			case "enthus".$edingAffix[0]:
			case "excus".$edingAffix[0]:
			case "grous".$edingAffix[0]:
			case "misus".$edingAffix[0]:
			case "mus".$edingAffix[0]:
			case "overus".$edingAffix[0]:
			case "perus".$edingAffix[0]:
			case "reus".$edingAffix[0]:
			case "rous".$edingAffix[0]:
			case "sous".$edingAffix[0]:
			case "us".$edingAffix[0]:
			case $matchAhlmpousEseding[0]:
			case $matchAafusEseding[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
				/* -o / -oe */

			case "bastinadoed":
			case "buncoed":
			case "bunkoed":
			case "carbonadoed":
			case "contangoed":
			case "crescendoed":
			case "dittoed":
			case "echoed":
			case "embargoed":
			case "frescoed":
			case "halloed":
			case "haloed":
			case "lassoed":
			case "nielloed":
			case "radioed":
			case "soloed":
			case "stilettoed":
			case "stuccoed":
			case "tally-hoed":
			case "tangoed":
			case "torpedoed":
			case "vetoed":
			case "zeroed" :
				return self::stem($word,2,"","ed");
				break;    /* en */
			case "ko'd" :
				return self::stem($word,3,"o","ed");
				break;           /* en */
			case "ko'ing" :
				return self::stem($word,4,"","ing");
				break;
			case "ko's" :
				return self::stem($word,2,"","s");
				break;
			case "tally-ho'd" :
				return self::stem($word,3,"","ed");
				break;     /* en */ /* disprefer */
			case "canoes":
			case "hoes":
			case "outwoes":
			case "rehoes":
			case $this->A."shoes":
			case "tiptoes":
			case "toes":
				return self::stem($word, 1,"","s");
				break;


			case "echoes":
			case "foregoes":
			case "forgoes":
			case "goes":
			case "outdoes":
			case "overdoes":
			case "redoes":
			case "torpedoes":
			case "undergoes":
			case "undoes":
			case "vetoes" :
				return self::stem($word,2,"","s");
				break;


			case "adz".$esedingAffix[0]:
			case "bronz".$esedingAffix[0]:
				return self::semi_reg_stem($word,0,"e");
				break;
			case "quiz"."z".$edingAffix[0]:
			case "whiz"."z".$edingAffix[0]:
				return self::semi_reg_stem($word,1,"");
				break;
			case "quiz".$edingAffix[0]:
			case "whiz".$edingAffix[0]:
				return self::semi_reg_stem($word,0,"");
				break; /* disprefer */


			case $this->A."used":
				return self::stem($word,2,"","ed");
				break; /* en */
			case $this->A."using":
				return self::stem($word,3,"","ing");
				break;

			case $matchzed[0]:
				return self::stem($word,2,"","ed");
				break;     /* en */
			case $matchAvyzed[0]:
				return self::stem($word,1,"","ed");
				break;     /* en */
			case $matchAs2ed[0]:
				return self::stem($word,2,"","ed");
				break;     /* en */
			case $matchzing[0]:
				return self::stem($word,3,"","ing");
				break;
			case $matchAvyzing[0]:
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAs2ing[0]:
				return self::stem($word,3,"","ing");
				break;
			case $matchlled[0]:
				return self::stem($word,2,"","ed");
				break;     /* en */
			case $matchlling[0]:
				return self::stem($word,3,"","ing");
				break;
			case $matchCxy2ed[0] :
				return condub_stem($word,2,"","ed");
				break; /* en */
			case $matchCxy2ing[0] :
				return condub_stem($word,3,"","ing");
				break;
			case $matchCxyed[0]  :
				return self::cnull_stem($word);
				break;
			case $matchcvnged[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */
			case $matchAicked[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */
			case $matchAcined[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAcvnpwxed[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */ /* disprefer */
			case $matchPrecoredPre[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchActored[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */ /* disprefer */
			case $matchAcclntored[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAeored[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */
			case $matchAcied[0]  :
				return self::stem($word, 3,"y","ed");
				break;  /* en */
			case $matchAquvced[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAuvded[0]  :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAcleted[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchPreceited[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAeited[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */
			case $matchPrecxy2eated[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */
			case $matchAvcxy2eated[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAeoeated[0]  :
				return self::stem($word,2,"","ed");
				break;   /* en */
			case $matchAvated[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAv2cgsved[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAv2ced[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */
			case $matchArwled[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */
			case $matchAthed[0]  :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAued[0]  :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAcxycglsved[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAcglsved[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */
			case $matchAcxy2ed[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */
			case $matchAvy2ed[0] :
				return self::stem($word,2,"","ed");
				break;   /* en */
			case $matchAed[0] :
				return self::stem($word,2,"e","ed");
				break;  /* en */

			case $matchCxying[0] :
				return self::cnull_stem($word);
				break;
			case $matchPrecvnging[0]   :
				return self::stem($word,3,"","ing");
				break;
			case $matchAicking[0]  :
				return self::stem($word,3,"","ing");
				break;
			case $matchAcining[0] :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAcvnpwxing[0] :
				return self::stem($word,3,"","ing");
				break;  /* disprefer */
			case $matchAquvcing[0]  :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAuvding[0]  :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAcleting[0]  :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchPreceiting[0]  :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAeiting[0] :
				return self::stem($word,3,"","ing");
				break;
			case $matchAprecxy2eating[0] :
				return self::stem($word,3,"","ing");
				break;
			case $matchAvcxy2eating[0] :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAeoating[0]  :
				return self::stem($word,3,"","ing");
				break;
			case $matchAvating[0]   :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAv2cgsving[0]  :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAv2cing[0]  :
				return self::stem($word,3,"","ing");
				break;
			case $matchArwling[0]  :
				return self::stem($word,3,"","ing");
				break;
			case $matchAthing[0]  :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAcxycglsving[0]  :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAcxy2ing[0] :
				return self::stem($word,3,"","ing");
				break;
			case $matchAuing[0]   :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAvy2ing[0]   :
				return self::stem($word,3,"","ing");
				break;
			case $matchAying[0]  :
				return self::stem($word,3,"","ing");
				break;
			case $matchAcxyoing[0] :
				return self::stem($word,3,"","ing");
				break;
			case $matchPREcoring[0] :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchActoring[0]  :
				return self::stem($word,3,"","ing");
				break;  /* disprefer */
			case $matchAccltoring[0]  :
				return self::stem($word, 3,"e","ing");
				break;
			case $matchAeoring[0]  :
				return self::stem($word,3,"","ing");
				break;
			case $matchAing[0]  :
				return self::stem($word, 3,"e","ing");
				break;


			default:
				;
				break;
		}

	}

	function scanNoun($word) {
		preg_match($this->Aworks,$word,$matchworks);

		preg_match($this->As1,$word,$matchAs1);
		preg_match($this->As2,$word,$matchAs2);
		preg_match($this->As3,$word,$matchAs3);
		preg_match($this->Amen,$word,$matchAmen);
		preg_match($this->Awives,$word,$matchAwives);
		preg_match($this->Azoa,$word,$matchAzoa);
		preg_match($this->Aiia,$word,$matchAiia);
		preg_match($this->Aemnia,$word,$matchAemnia);
		preg_match($this->Aia,$word,$matchAia);
		preg_match($this->Ala,$word,$matchAla);
		preg_match($this->Ai,$word,$matchAi);

		preg_match($this->Aae,$word,$matchAae);
		preg_match($this->Aata,$word,$matchAata);


		preg_match($this->Ahedra,$word,$matchAhedra);
		preg_match($this->Aitis,$word,$matchAitis);
		preg_match($this->Aphobia,$word,$matchAphobia);
		preg_match($this->Aphilia,$word,$matchAphilia);

		preg_match($this->Aabuses,$word,$matchAabuses);

		preg_match($this->Ahlmpouses,$word,$matchAhlmpouses);
		preg_match($this->Aafuses,$word,$matchAafuses);

		preg_match($this->Ametres,$word,$matchAmetres);
		preg_match($this->Alitres,$word,$matchAlitres);
		preg_match($this->Ashoes,$word,$matchAshoes);
		preg_match($this->Aettes,$word,$matchAettes);
		preg_match($this->Auses,$word,$matchAuses);
		switch ($word) {
			case "corpses" :
				return self::stem($word, 1,"","s");
				break;

			case "biases" :
				return self::stem($word,2,"","s");
				break;
			case "biscotti" :
				return self::stem($word,2,"to","s");
				break;
			case "bookshelves" :
				return self::stem($word,3,"f","s");
				break;
			case "palazzi" :
				return self::stem($word,2,"zo","s");
				break;
			case "daises" :
				return self::stem($word,2,"","s");
				break;
			case "reguli" :
				return self::stem($word,2,"lo","s");
				break;
			case "steppes" :
				return self::stem($word,2,"e","s");
				break;
			case "obsequies" :
				return self::stem($word,3,"y","s");
				break;

			case "canvases" :
				return self::stem($word,2,"","s");
				break;
			case "carcases" :
				return self::stem($word, 1,"","s");
				break;
			case "lenses" :
				return self::stem($word,2,"","s");

			case "ABCs" :
				return self::stem($word,4,"ABC","s");
				break;
			case "bacteria" :
				return self::stem($word,1,"um","s");
				break;
			case "loggias" :
				return self::stem($word, 1,"","s");
				break;
			case "bases"   :
				return self::stem($word,2,"is","s");
				break;
			case "schemata" :
				return self::stem($word,2,"","s");
				break;

			case "curiae":
			case "formulae":
			case "vertebrae":
			case "larvae":
			case "ulnae":
			case "alumnae" :
				return self::stem($word, 1,"","s");
				break;
			case "beldames":
			case "bosses":
			case "cruxes":
			case "larynxes":
			case "sphinxes":
			case "trellises":
			case "yeses":
			case "atlases" :
				return self::stem($word,2,"","s");
				break;
			case "alumni":
			case "loci":
			case "thrombi":
			case "tarsi":
			case "streptococci":
			case "stimuli":
			case "solidi":
			case "radii":
			case "magi":
			case "cumuli":
			case "bronchi":
			case "bacilli" :
				return self::stem($word,1,"us","s");
				break;
			case "Brahmans":
			case "Germans":
			case "dragomans":
			case "ottomans":
			case "shamans":
			case "talismans":
			case "Normans":
			case "Pullmans":
			case "Romans" :
				return self::stem($word, 1,"","s");
				break;
			case "Czechs":
			case "diptychs":
			case "Sassenachs":
			case "abdomens":
			case "alibis":
			case "arias":
			case "bandits":
			case "begonias":
			case "bikinis":
			case "caryatids":
			case "colons":
			case "cornucopias":
			case "cromlechs":
			case "cupolas":
			case "dryads":
			case "eisteddfods":
			case "encyclopaedias":
			case "epochs":
			case "eunuchs":
			case "flotillas":
			case "gardenias":
			case "gestalts":
			case "gondolas":
			case "hierarchs":
			case "hoses":
			case "impediments":
			case "koalas":
			case "lochs":
			case "manias":
			case "manservants":
			case "martinis":
			case "matriarchs":
			case "monarchs":
			case "oligarchs":
			case "omens":
			case "parabolas":
			case "pastorales":
			case "patriarchs":
			case "peas":
			case "peninsulas":
			case "pfennigs":
			case "phantasmagorias":
			case "pibrochs":
			case "polys":
			case "reals":
			case "safaris":
			case "saris":
			case "specimens":
			case "standbys":
			case "stomachs":
			case "swamis":
			case "taxis":
			case "techs":
			case "toccatas":
			case "triptychs":
			case "villas":
			case "yogis":
			case "zlotys":
				return self::stem($word, 1,"","s");
				break;
			case "asylums":
			case "sanctums":
			case "rectums":
			case "plums":
			case "pendulums":
			case "mausoleums":
			case "hoodlums":
			case "forums" :
				return self::stem($word, 1,"","s");
				break;
			case "Bantu":
			case "Bengalese":
			case "Beninese":
			case "Boche":
			case "Burmese":
			case "Chinese":
			case "Congolese":
			case "Gabonese":
			case "Guyanese":
			case "Japanese":
			case "Javanese":
			case "Lebanese":
			case "Maltese":
			case "Olympics":
			case "Portuguese":
			case "Senegalese":
			case "Siamese":
			case "Singhalese":
			case "Sinhalese":
			case "Sioux":
			case "Sudanese":
			case "Swiss":
			case "Taiwanese":
			case "Togolese":
			case "Vietnamese":
			case "aircraft":
			case "anopheles":
			case "apparatus":
			case "asparagus":
			case "barracks":
			case "bellows":
			case "bison":
			case "bluefish":
			case "bob":
			case "bourgeois":
			case "bream":
			case "brill":
			case "butterfingers":
			case "carp":
			case "catfish":
			case "chassis":
			case "chub":
			case "cod":
			case "codfish":
			case "coley":
			case "contretemps":
			case "corps":
			case "crawfish":
			case "crayfish":
			case "crossroads":
			case "cuttlefish":
			case "dace":
			case "dice":
			case "dogfish":
			case "doings":
			case "dory":
			case "downstairs":
			case "eldest":
			case "finnan":
			case "firstborn":
			case "fish":
			case "flatfish":
			case "flounder":
			case "fowl":
			case "fry":
			case "fries":

			case $matchworks[0]:
			case "gasworks":
			case "glassworks":
			case "globefish":
			case "goldfish":
			case "grand":
			case "gudgeon":
			case "gulden":
			case "haddock":
			case "hake":
			case "halibut":
			case "headquarters":
			case "herring":
			case "hertz":
			case "horsepower":
			case "hovercraft":
			case "hundredweight":
			case "ironworks":
			case "jackanapes":
			case "kilohertz":
			case "kurus":
			case "kwacha":
			case "ling":
			case "lungfish":
			case "mackerel":
			case "means":
			case "megahertz":
			case "moorfowl":
			case "moorgame":
			case "mullet":
			case "offspring":
			case "pampas":
			case "parr":
			case "patois":
			case "pekinese":
			case "penn'orth":
			case "perch":
			case "pickerel":
			case "pike":
			case "pince-nez":
			case "plaice":
			case "precis":
			case "quid":
			case "rand":
			case "rendezvous":
			case "revers":
			case "roach":
			case "roux":
			case "salmon":
			case "samurai":
			case "series":
			case "shad":
			case "sheep":
			case "shellfish":
			case "smelt":
			case "spacecraft":
			case "species":
			case "starfish":
			case "stockfish":
			case "sunfish":
			case "superficies":
			case "sweepstakes":
			case "swordfish":
			case "tench":
			case "tope":
			case "triceps":
			case "trout":
			case "tuna":
			case "tunafish":
			case "tunny":
			case "turbot":
			case "undersigned":
			case "veg":
			case "waterfowl":
			case "waterworks":
			case "waxworks":
			case "whiting":
			case "wildfowl":
			case "woodworm":
			case "yen" :
				return self::xnull_stem($word);
				break;
			case "Aries":
				return self::stem($word,1,"s","s");
				break;
			case "Pisces":
				return self::stem($word,1,"s","s");
				break;
			case "Bengali":
				return self::stem($word,1,"i","s");
				break;
			case "Somali":
				return self::stem($word,1,"i","s");
				break;
			case "cicatrices":
				return self::stem($word,3,"x","s");
				break;
			case "cachous":
				return self::stem($word, 1,"","s");
				break;
			case "confidantes":
				return self::stem($word, 1,"","s");
				break;
			case "weltanschauungen":
				return self::stem($word,2,"","s");
				break;
			case "apologetics":
				return self::stem($word, 1,"","s");
				break;
			case "dues":
				return self::stem($word, 1,"","s");
				break;
			case "whirrs":
				return self::stem($word,2,"","s");
				break;
			case "emus":
				return self::stem($word, 1,"","s");
				break;
			case "equities":
				return self::stem($word,3,"y","s");
				break;
			case "ethics":
				return self::stem($word, 1,"","s");
				break;
			case "extortions":
				return self::stem($word, 1,"","s");
				break;
			case "folks":
				return self::stem($word, 1,"","s");
				break;
			case "fumes":
				return self::stem($word, 1,"","s");
				break;
			case "fungi":
				return self::stem($word,1,"us","s");
				break;
			case "ganglia":
				return self::stem($word,1,"on","s");
				break;
			case "gnus":
				return self::stem($word, 1,"","s");
				break;
			case "goings":
				return self::stem($word, 1,"","s");
				break;
			case "groceries":
				return self::stem($word,3,"y","s");
				break;
			case "gurus":
				return self::stem($word, 1,"","s");
				break;
			case "halfpence":
				return self::stem($word,2,"ny","s");
				break;
			case "hostilities":
				return self::stem($word,3,"y","s");
				break;
			case "hysterics":
				return self::stem($word, 1,"","s");
				break;
			case "impromptus":
				return self::stem($word, 1,"","s");
				break;
			case "incidentals":
				return self::stem($word, 1,"","s");
				break;
			case "jujus":
				return self::stem($word, 1,"","s");
				break;
			case "landaus":
				return self::stem($word, 1,"","s");
				break;
			case "loins":
				return self::stem($word, 1,"","s");
				break;
			case "mains":
				return self::stem($word, 1,"","s");
				break;
			case "menus":
				return self::stem($word, 1,"","s");
				break;
			case "milieus":
				return self::stem($word, 1,"","s");
				break;           /* disprefer */
			case "mockers":
				return self::stem($word, 1,"","s");
				break;
			case "morals":
				return self::stem($word, 1,"","s");
				break;
			case "motions":
				return self::stem($word, 1,"","s");
				break;
			case "mus":
				return self::stem($word, 1,"","s");
				break;
			case "nibs":
				return self::stem($word, 1,"","s");
				break;
			case "ninepins":
				return self::stem($word, 1,"","s");
				break;
			case "nippers":
				return self::stem($word, 1,"","s");
				break;
			case "oilskins":
				return self::stem($word, 1,"","s");
				break;
			case "overtones":
				return self::stem($word, 1,"","s");
				break;
			case "parvenus":
				return self::stem($word, 1,"","s");
				break;
			case "plastics":
				return self::stem($word, 1,"","s");
				break;
			case "polemics":
				return self::stem($word, 1,"","s");
				break;
			case "races":
				return self::stem($word, 1,"","s");
				break;
			case "refreshments":
				return self::stem($word, 1,"","s");
				break;
			case "reinforcements":
				return self::stem($word, 1,"","s");
				break;
			case "reparations":
				return self::stem($word, 1,"","s");
				break;
			case "returns":
				return self::stem($word, 1,"","s");
				break;
			case "rheumatics":
				return self::stem($word, 1,"","s");
				break;
			case "rudiments":
				return self::stem($word, 1,"","s");
				break;
			case "sadhus":
				return self::stem($word, 1,"","s");
				break;
			case "shires":
				return self::stem($word, 1,"","s");
				break;
			case "shivers":
				return self::stem($word, 1,"","s");
				break;
			case "sis":
				return self::stem($word, 1,"","s");
				break;
			case "spoils":
				return self::stem($word, 1,"","s");
				break;
			case "stamens":
				return self::stem($word, 1,"","s");
				break;
			case "stays":
				return self::stem($word, 1,"","s");
				break;
			case "subtitles":
				return self::stem($word, 1,"","s");
				break;
			case "tares":
				return self::stem($word, 1,"","s");
				break;
			case "thankyous":
				return self::stem($word, 1,"","s");
				break;
			case "thews":
				return self::stem($word, 1,"","s");
				break;
			case "toils":
				return self::stem($word, 1,"","s");
				break;
			case "tongs":
				return self::stem($word, 1,"","s");
				break;
			case "Hindus":
				return self::stem($word, 1,"","s");
				break;
			case "ancients":
				return self::stem($word, 1,"","s");
				break;
			case "bagpipes":
				return self::stem($word, 1,"","s");
				break;
			case "bleachers":
				return self::stem($word, 1,"","s");
				break;
			case "buttocks":
				return self::stem($word, 1,"","s");
				break;
			case "commons":
				return self::stem($word, 1,"","s");
				break;
			case "Israelis":
				return self::stem($word, 1,"","s");
				break;
			case "Israeli":
				return self::stem($word,1,"i","s");
				break;          /* disprefer */
			case "dodgems":
				return self::stem($word, 1,"","s");
				break;
			case "causeries":
				return self::stem($word, 1,"","s");
				break;
			case "quiches":
				return self::stem($word, 1,"","s");
				break;
			case "rations":
				return self::stem($word, 1,"","s");
				break;
			case "recompenses":
				return self::stem($word, 1,"","s");
				break;
			case "rinses":
				return self::stem($word, 1,"","s");
				break;
			case "lieder":
				return self::stem($word,2,"","s");
				break;
			case "passers-by":
				return self::stem($word,4,"-by","s");
				break;
			case "prolegomena":
				return self::stem($word,1,"on","s");
				break;
			case "signore":
				return self::stem($word,1,"a","s");
				break;
			case "nepalese":
				return self::stem($word,1,"e","s");
				break;
			case "algae":
				return self::stem($word, 1,"","s");
				break;
			case "clutches":
				return self::stem($word,2,"","s");
				break;
			case "continua":
				return self::stem($word,1,"um","s");
				break;
			case "diggings":
				return self::stem($word, 1,"","s");
				break;
			case "K's":
				return self::stem($word,2,"","s");
				break;
			case "seychellois":
				return self::stem($word,1,"s","s");
				break;
			case "afterlives":
				return self::stem($word,3,"fe","s");
				break;
			case "avens":
				return self::stem($word,1,"s","s");
				break;
			case "axes":
				return self::stem($word,2,"is","s");
				break;
			case "bonsai":
				return self::stem($word,1,"i","s");
				break;
			case "coypus":
				return self::stem($word, 1,"","s");
				break;
			case "duodena":
				return self::stem($word,1,"um","s");
				break;
			case "genii":
				return self::stem($word,1,"e","s");
				break;
			case "leaves":
				return self::stem($word,3,"f","s");
				break;
			case "mantelshelves":
				return self::stem($word,3,"f","s");
				break;
			case "meninges":
				return self::stem($word,3,"x","s");
				break;
			case "moneybags":
				return self::stem($word,1,"s","s");
				break;
			case "obbligati":
				return self::stem($word,1,"o","s");
				break;
			case "orchises":
				return self::stem($word,2,"","s");
				break;
			case "palais":
				return self::stem($word,1,"s","s");
				break;
			case "pancreases":
				return self::stem($word,2,"","s");
				break;
			case "phalanges":
				return self::stem($word,3,"x","s");
				break;
			case "portcullises":
				return self::stem($word,2,"","s");
				break;
			case "pubes":
				return self::stem($word,1,"s","s");
				break;
			case "pulses":
				return self::stem($word, 1,"","s");
				break;
			case "ratlines":
				return self::stem($word,2,"","s");
				break;
			case "signori":
				return self::stem($word, 1,"","s");
				break;
			case "spindle-shanks":
				return self::stem($word,1,"s","s");
				break;
			case "substrata":
				return self::stem($word,1,"um","s");
				break;
			case "woolies":
				return self::stem($word,3,"ly","s");
				break;
			case "moggies":
				return self::stem($word,3,"y","s");
				break;
			case "ghillies":
			case "groupies":
			case "honkies":
			case "meanies":
			case "roadies":
			case "shorties":
			case "smoothies":
			case "bookies":
			case "cabbies":
			case "hankies":
			case "tootsies":
			case "toughies":
			case "trannies":
				return self::stem($word,2,"e","s");
				break;
			case "christmases":
			case "judases":
				return self::stem($word,2,"","s");
				break;
			case "flambeaus":
			case "plateaus":
			case "portmanteaus":
			case "tableaus":
			case "beaus":
			case "bureaus":
			case "trousseaus":
				return self::stem($word,2,"u","s");
				break; /* disprefer */
			case "maharajahs":
			case "rajahs":
			case "mynahs":
			case "mullahs" :
				return self::stem($word,2,"","s");
				break;
			case "Boches":
			case "apocalypses":
			case "apses":
			case "arses":
			case "avalanches":
			case "backaches":
			case "tenses":
			case "relapses":
			case "barouches":
			case "brioches":
			case "cloches":
			case "collapses":
			case "copses":
			case "creches":
			case "crevasses":
			case "douches":
			case "eclipses":
			case "expanses":
			case "expenses":
			case "finesses":
			case "glimpses":
			case "gouaches":
			case "heartaches":
			case "impasses":
			case "impulses":
			case "lapses":
			case "manses":
			case "microfiches":
			case "mousses":
			case "nonsenses":
			case "pastiches":
			case "pelisses":
			case "posses":
			case "prolapses":
			case "psyches":
				return self::stem($word, 1,"","s");
				break;
			case "addenda" :
				return self::stem($word,2,"dum","s");
				break;
			case "adieux" :
				return self::stem($word,2,"u","s");
				break;
			case "aides-de-camp" :
				return self::stem($word,9,"-de-camp","s");
				break;
			case "aliases" :
				return self::stem($word,2,"","s");
				break;
			case "alkalies" :
				return self::stem($word,2,"","s");
				break;
			case "alti" :
				return self::stem($word,2,"to","s");
				break;
			case "amanuenses" :
				return self::stem($word,2,"is","s");
				break;
			case "analyses" :
				return self::stem($word,2,"is","s");
				break;
			case "anthraces" :
				return self::stem($word,3,"x","s");
				break;
			case "antitheses" :
				return self::stem($word,2,"is","s");
				break;
			case "aphides" :
				return self::stem($word,3,"s","s");
				break;
			case "apices" :
				return self::stem($word,4,"ex","s");
				break;
			case "appendices" :
				return self::stem($word,3,"x","s");
				break;
			case "arboreta" :
				return self::stem($word,2,"tum","s");
				break;
			case "atlantes" :
				return self::stem($word,4,"s","s");
				break;        /* disprefer */
			case "aurar" :
				return self::stem($word,5,"eyrir","s");
				break;
			case "automata" :
				return self::stem($word,2,"ton","s");
				break;
			case "axises" :
				return self::stem($word,2,"","s");
				break;           /* disprefer */
			case "bambini" :
				return self::stem($word,2,"no","s");
				break;
			case "bandeaux" :
				return self::stem($word,2,"u","s");
				break;
			case "banditti" :
				return self::stem($word,2,"","s");
				break;         /* disprefer */
			case "bassi" :
				return self::stem($word,2,"so","s");
				break;
			case "beaux" :
				return self::stem($word,2,"u","s");
				break;
			case "beeves" :
				return self::stem($word,3,"f","s");
				break;
			case "bicepses" :
				return self::stem($word,2,"","s");
				break;
			case "bijoux" :
				return self::stem($word,2,"u","s");
				break;
			case "billets-doux" :
				return self::stem($word,6,"-doux","s");
				break;
			case "boraces" :
				return self::stem($word,3,"x","s");
				break;
			case "bossies" :
				return self::stem($word,3,"","s");
				break;          /* disprefer */
			case "brainchildren" :
				return self::stem($word,3,"","s");
				break;
			case "brothers-in-law" :
				return self::stem($word,8,"-in-law","s");
				break;
			case "buckteeth" :
				return self::stem($word,4,"ooth","s");
				break;
			case "bunde" :
				return self::stem($word,2,"d","s");
				break;
			case "bureaux" :
				return self::stem($word,2,"u","s");
				break;
			case "cacti" :
				return self::stem($word,1,"us","s");
				break;
			case "calves" :
				return self::stem($word,3,"f","s");
				break;
			case "calyces" :
				return self::stem($word,3,"x","s");
				break;
			case "candelabra" :
				return self::stem($word,2,"rum","s");
				break;
			case "capricci" :
				return self::stem($word,2,"cio","s");
				break;      /* disprefer */
			case "caribous" :
				return self::stem($word,2,"u","s");
				break;
			case "carides" :
				return self::stem($word,4,"yatid","s");
				break;     /* disprefer */
			case "catalyses" :
				return self::stem($word,2,"is","s");
				break;
			case "cerebra" :
				return self::stem($word,2,"rum","s");
				break;
			case "cervices" :
				return self::stem($word,3,"x","s");
				break;
			case "chateaux" :
				return self::stem($word,2,"u","s");
				break;
			case "children" :
				return self::stem($word,3,"","s");
				break;
			case "chillies" :
				return self::stem($word,2,"","s");
				break;
			case "chrysalides" :
				return self::stem($word,3,"s","s");
				break;
			case "chrysalises" :
				return self::stem($word,2,"","s");
				break;      /* disprefer */
			case "ciceroni" :
				return self::stem($word,2,"ne","s");
				break;
			case "cloverleaves" :
				return self::stem($word,3,"f","s");
				break;
			case "coccyges" :
				return self::stem($word,3,"x","s");
				break;
			case "codices" :
				return self::stem($word,4,"ex","s");
				break;
			case "colloquies" :
				return self::stem($word,3,"y","s");
				break;
			case "colones" :
				return self::stem($word,2,"","s");
				break;          /* disprefer */
			case "concertanti" :
				return self::stem($word,2,"te","s");
				break;
			case "concerti" :
				return self::stem($word,2,"to","s");
				break;
			case "concertini" :
				return self::stem($word,2,"no","s");
				break;
			case "conquistadores" :
				return self::stem($word,2,"","s");
				break;
			case "consortia" :
				return self::stem($word,1,"um","s");
				break;
			case "contralti" :
				return self::stem($word,2,"to","s");
				break;
			case "corpora" :
				return self::stem($word,3,"us","s");
				break;
			case "corrigenda" :
				return self::stem($word,2,"dum","s");
				break;
			case "cortices" :
				return self::stem($word,4,"ex","s");
				break;
			case "crescendi" :
				return self::stem($word,2,"do","s");
				break;      /* disprefer */
			case "crises" :
				return self::stem($word,2,"is","s");
				break;
			case "criteria" :
				return self::stem($word,2,"ion","s");
				break;
			case "cruces" :
				return self::stem($word,3,"x","s");
				break;          /* disprefer */
			case "culs-de-sac" :
				return self::stem($word,8,"-de-sac","s");
				break;
			case "cyclopes" :
				return self::stem($word,2,"s","s");
				break;
			case "cyclopses" :
				return self::stem($word,2,"","s");
				break;        /* disprefer */
			case "data" :
				return self::stem($word,2,"tum","s");
				break;
			case "daughters-in-law" :
				return self::stem($word,8,"-in-law","s");
				break;
			case "desiderata" :
				return self::stem($word,2,"tum","s");
				break;
			case "diaereses" :
				return self::stem($word,2,"is","s");
				break;
			case "diaerses" :
				return self::stem($word,3,"esis","s");
				break;     /* disprefer */
			case "dialyses" :
				return self::stem($word,2,"is","s");
				break;
			case "diathses" :
				return self::stem($word,3,"esis","s");
				break;
			case "dicta" :
				return self::stem($word,2,"tum","s");
				break;
			case "diereses" :
				return self::stem($word,2,"is","s");
				break;
			case "dilettantes" :
				return self::stem($word,2,"e","s");
				break;
			case "dilettanti" :
				return self::stem($word,2,"te","s");
				break;     /* disprefer */
			case "divertimenti" :
				return self::stem($word,2,"to","s");
				break;
			case "dogteeth" :
				return self::stem($word,4,"ooth","s");
				break;
			case "dormice" :
				return self::stem($word,3,"ouse","s");
				break;
			case "dryades" :
				return self::stem($word,2,"","s");
				break;          /* disprefer */
			case "dui" :
				return self::stem($word,2,"uo","s");
				break;            /* disprefer */
			case "duona" :
				return self::stem($word,2,"denum","s");
				break;       /* disprefer */
			case "duonas" :
				return self::stem($word,3,"denum","s");
				break;      /* disprefer */
			case "tutus" :
				return self::stem($word, 1,"","s");
				break;
			case "vicissitudes" :
				return self::stem($word, 1,"","s");
				break;
			case "virginals" :
				return self::stem($word, 1,"","s");
				break;
			case "volumes" :
				return self::stem($word, 1,"","s");
				break;
			case "zebus" :
				return self::stem($word, 1,"","s");
				break;
			case "dwarves" :
				return self::stem($word,3,"f","s");
				break;
			case "eisteddfodau" :
				return self::stem($word,2,"","s");
				break;     /* disprefer */
			case "ellipses" :
				return self::stem($word,2,"is","s");
				break;
			case "elves" :
				return self::stem($word,3,"f","s");
				break;
			case "emphases" :
				return self::stem($word,2,"is","s");
				break;
			case "epicentres" :
				return self::stem($word,2,"e","s");
				break;
			case "epiglottides" :
				return self::stem($word,3,"s","s");
				break;
			case "epiglottises" :
				return self::stem($word,2,"","s");
				break;     /* disprefer */
			case "errata" :
				return self::stem($word,2,"tum","s");
				break;
			case "exegeses" :
				return self::stem($word,2,"is","s");
				break;
			case "eyeteeth" :
				return self::stem($word,4,"ooth","s");
				break;
			case "fathers-in-law" :
				return self::stem($word,8,"-in-law","s");
				break;
			case "feet" :
				return self::stem($word,3,"oot","s");
				break;
			case "fellaheen" :
				return self::stem($word,3,"","s");
				break;
			case "fellahin" :
				return self::stem($word,2,"","s");
				break;         /* disprefer */
			case "femora" :
				return self::stem($word,3,"ur","s");
				break;
			case "flagstaves" :
				return self::stem($word,3,"ff","s");
				break;     /* disprefer */
			case "flambeaux" :
				return self::stem($word,2,"u","s");
				break;
			case "flatfeet" :
				return self::stem($word,3,"oot","s");
				break;
			case "fleurs-de-lis" :
				return self::stem($word,8,"-de-lis","s");
				break;
			case "fleurs-de-lys" :
				return self::stem($word,8,"-de-lys","s");
				break;
			case "flyleaves" :
				return self::stem($word,3,"f","s");
				break;
			case "fora" :
				return self::stem($word,2,"rum","s");
				break;          /* disprefer */
			case "forcipes" :
				return self::stem($word,4,"eps","s");
				break;
			case "forefeet" :
				return self::stem($word,3,"oot","s");
				break;
			case "fulcra" :
				return self::stem($word,2,"rum","s");
				break;
			case "gallowses" :
				return self::stem($word,2,"","s");
				break;
			case "gases" :
				return self::stem($word,2,"","s");
				break;
			case "gasses" :
				return self::stem($word,3,"","s");
				break;           /* disprefer */
			case "gateaux" :
				return self::stem($word,2,"u","s");
				break;
			case "geese" :
				return self::stem($word,4,"oose","s");
				break;
			case "gemboks" :
				return self::stem($word,4,"sbok","s");
				break;
			case "genera" :
				return self::stem($word,3,"us","s");
				break;
			case "geneses" :
				return self::stem($word,2,"is","s");
				break;
			case "gentlemen-at-arms" :
				return self::stem($word,10,"an-at-arms","s");
				break;
			case "gestalten" :
				return self::stem($word,2,"","s");
				break;        /* disprefer */
			case "glissandi" :
				return self::stem($word,2,"do","s");
				break;
			case "glottides" :
				return self::stem($word,3,"s","s");
				break;       /* disprefer */
			case "glottises" :
				return self::stem($word,2,"","s");
				break;
			case "godchildren" :
				return self::stem($word,3,"","s");
				break;
			case "goings-over" :
				return self::stem($word,6,"-over","s");
				break;
			case "grandchildren" :
				return self::stem($word,3,"","s");
				break;
			case "halves" :
				return self::stem($word,3,"f","s");
				break;
			case "hangers-on" :
				return self::stem($word,4,"-on","s");
				break;
			case "helices" :
				return self::stem($word,3,"x","s");
				break;
			case "hooves" :
				return self::stem($word,3,"f","s");
				break;
			case "hosen" :
				return self::stem($word,2,"e","s");
				break;           /* disprefer */
			case "hypotheses" :
				return self::stem($word,2,"is","s");
				break;
			case "iambi" :
				return self::stem($word,2,"b","s");
				break;
			case "ibices" :
				return self::stem($word,4,"ex","s");
				break;         /* disprefer */
			case "ibises" :
				return self::stem($word,2,"","s");
				break;           /* disprefer */
			case "impedimenta" :
				return self::stem($word,2,"t","s");
				break;     /* disprefer */
			case "indices" :
				return self::stem($word,4,"ex","s");
				break;
			case "intagli" :
				return self::stem($word,2,"lio","s");
				break;       /* disprefer */
			case "intermezzi" :
				return self::stem($word,2,"zo","s");
				break;
			case "interregna" :
				return self::stem($word,2,"num","s");
				break;
			case "irides" :
				return self::stem($word,3,"s","s");
				break;          /* disprefer */
			case "irises" :
				return self::stem($word,2,"","s");
				break;
			case "is" :
				return self::stem($word,2,"is","s");
				break;
			case "jacks-in-the-box" :
				return self::stem($word,12,"-in-the-box","s");
				break;
			case "kibbutzim" :
				return self::stem($word,2,"","s");
				break;
			case "knives" :
				return self::stem($word,3,"fe","s");
				break;
			case "kohlrabies" :
				return self::stem($word,2,"","s");
				break;
			case "kronen" :
				return self::stem($word,2,"e","s");
				break;          /* disprefer */
			case "kroner" :
				return self::stem($word,2,"e","s");
				break;
			case "kronor" :
				return self::stem($word,2,"a","s");
				break;
			case "kronur" :
				return self::stem($word,2,"a","s");
				break;          /* disprefer */
			case "kylikes" :
				return self::stem($word,3,"x","s");
				break;
			case "ladies-in-waiting" :
				return self::stem($word,14,"y-in-waiting","s");
				break;
			case "larynges" :
				return self::stem($word,3,"x","s");
				break;        /* disprefer */
			case "latices" :
				return self::stem($word,4,"ex","s");
				break;
			case "leges" :
				return self::stem($word,3,"x","s");
				break;
			case "libretti" :
				return self::stem($word,2,"to","s");
				break;
			case "lice" :
				return self::stem($word,3,"ouse","s");
				break;
			case "lire" :
				return self::stem($word,2,"ra","s");
				break;
			case "lives" :
				return self::stem($word,3,"fe","s");
				break;
			case "loaves" :
				return self::stem($word,3,"f","s");
				break;
			case "loggie" :
				return self::stem($word,2,"ia","s");
				break;         /* disprefer */
			case "lustra" :
				return self::stem($word,2,"re","s");
				break;
			case "lyings-in" :
				return self::stem($word,4,"-in","s");
				break;
			case "macaronies" :
				return self::stem($word,2,"","s");
				break;
			case "maestri" :
				return self::stem($word,2,"ro","s");
				break;
			case "mantes" :
				return self::stem($word,2,"is","s");
				break;
			case "mantises" :
				return self::stem($word,2,"","s");
				break;         /* disprefer */
			case "markkaa" :
				return self::stem($word,2,"a","s");
				break;
			case "marquises" :
				return self::stem($word,2,"","s");
				break;
			case "masters-at-arms" :
				return self::stem($word,9,"-at-arms","s");
				break;
			case "matrices" :
				return self::stem($word,3,"x","s");
				break;
			case "matzoth" :
				return self::stem($word,2,"","s");
				break;
			case "mausolea" :
				return self::stem($word,2,"eum","s");
				break;      /* disprefer */
			case "maxima" :
				return self::stem($word,2,"mum","s");
				break;
			case "memoranda" :
				return self::stem($word,2,"dum","s");
				break;
			case "men-at-arms" :
				return self::stem($word,10,"an-at-arms","s");
				break;
			case "men-o'-war" :
				return self::stem($word,9,"an-of-war","s");
				break; /* disprefer */
			case "men-of-war" :
				return self::stem($word,9,"an-of-war","s");
				break;
			case "menservants" :
				return self::stem($word,10,"anservant","s");
				break; /* disprefer */
			case "mesdemoiselles" :
				return self::stem($word,13,"ademoiselle","s");
				break;
			case "messieurs" :
				return self::stem($word,8,"onsieur","s");
				break;
			case "metatheses" :
				return self::stem($word,2,"is","s");
				break;
			case "metropolises" :
				return self::stem($word,2,"","s");
				break;
			case "mice" :
				return self::stem($word,3,"ouse","s");
				break;
			case "milieux" :
				return self::stem($word,2,"u","s");
				break;
			case "minima" :
				return self::stem($word,2,"mum","s");
				break;
			case "momenta" :
				return self::stem($word,2,"tum","s");
				break;
			case "monies" :
				return self::stem($word,3,"ey","s");
				break;
			case "monsignori" :
				return self::stem($word,2,"r","s");
				break;
			case "mooncalves" :
				return self::stem($word,3,"f","s");
				break;
			case "mothers-in-law" :
				return self::stem($word,8,"-in-law","s");
				break;
			case "naiades" :
				return self::stem($word,2,"","s");
				break;
			case "necropoleis" :
				return self::stem($word,3,"is","s");
				break;    /* disprefer */
			case "necropolises" :
				return self::stem($word,2,"","s");
				break;
			case "nemeses" :
				return self::stem($word,2,"is","s");
				break;
			case "novelle" :
				return self::stem($word,2,"la","s");
				break;
			case "oases" :
				return self::stem($word,2,"is","s");
				break;
			case "obloquies" :
				return self::stem($word,3,"y","s");
				break;
			case $matchAhedra[0]:
				return self::stem($word,2,"ron","s");
				break;
			case "optima" :
				return self::stem($word,2,"mum","s");
				break;
			case "ora" :
				return self::stem($word,2,"s","s");
				break;
			case "osar" :
				return self::stem($word,2,"","s");
				break;             /* disprefer */
			case "ossa" :
				return self::stem($word,2,"","s");
				break;             /* disprefer */
			case "ova" :
				return self::stem($word,2,"vum","s");
				break;
			case "oxen" :
				return self::stem($word,2,"","s");
				break;
			case "paralyses" :
				return self::stem($word,2,"is","s");
				break;
			case "parentheses" :
				return self::stem($word,2,"is","s");
				break;
			case "paris-mutuels" :
				return self::stem($word,9,"-mutuel","s");
				break;
			case "pastorali" :
				return self::stem($word,2,"le","s");
				break;      /* disprefer */
			case "patresfamilias" :
				return self::stem($word,11,"erfamilias","s");
				break;
			case "pease" :
				return self::stem($word,2,"","s");
				break;            /* disprefer */
			case "pekingese" :
				return self::stem($word,4,"ese","s");
				break;     /* disprefer */
			case "pelves" :
				return self::stem($word,2,"is","s");
				break;         /* disprefer */
			case "pelvises" :
				return self::stem($word,2,"","s");
				break;
			case "pence" :
				return self::stem($word,2,"ny","s");
				break;
			case "penes" :
				return self::stem($word,2,"is","s");
				break;          /* disprefer */
			case "penises" :
				return self::stem($word,2,"","s");
				break;
			case "penknives" :
				return self::stem($word,3,"fe","s");
				break;
			case "perihelia" :
				return self::stem($word,2,"ion","s");
				break;
			case "pfennige" :
				return self::stem($word,2,"g","s");
				break;        /* disprefer */
			case "pharynges" :
				return self::stem($word,3,"x","s");
				break;
			case "phenomena" :
				return self::stem($word,2,"non","s");
				break;
			case "philodendra" :
				return self::stem($word,2,"ron","s");
				break;
			case "pieds-a-terre" :
				return self::stem($word,9,"-a-terre","s");
				break;
			case "pineta" :
				return self::stem($word,2,"tum","s");
				break;
			case "plateaux" :
				return self::stem($word,2,"u","s");
				break;
			case "plena" :
				return self::stem($word,2,"num","s");
				break;
			case "pocketknives" :
				return self::stem($word,3,"fe","s");
				break;
			case "portmanteaux" :
				return self::stem($word,2,"u","s");
				break;
			case "potlies" :
				return self::stem($word,4,"belly","s");
				break;
			case "praxes" :
				return self::stem($word,2,"is","s");
				break;         /* disprefer */
			case "praxises" :
				return self::stem($word,2,"","s");
				break;
			case "proboscides" :
				return self::stem($word,3,"s","s");
				break;     /* disprefer */
			case "proboscises" :
				return self::stem($word,2,"","s");
				break;
			case "prostheses" :
				return self::stem($word,2,"is","s");
				break;
			case "protozoa" :
				return self::stem($word,2,"oan","s");
				break;
			case "pudenda" :
				return self::stem($word,2,"dum","s");
				break;
			case "putti" :
				return self::stem($word,2,"to","s");
				break;
			case "quanta" :
				return self::stem($word,2,"tum","s");
				break;
			case "quarterstaves" :
				return self::stem($word,3,"ff","s");
				break;
			case "reales" :
				return self::stem($word,2,"","s");
				break;           /* disprefer */
			case "recta" :
				return self::stem($word,2,"tum","s");
				break;         /* disprefer */
			case "referenda" :
				return self::stem($word,2,"dum","s");
				break;
			case "reis" :
				return self::stem($word,2,"al","s");
				break;           /* disprefer */
			case "rondeaux" :
				return self::stem($word,2,"u","s");
				break;
			case "rostra" :
				return self::stem($word,2,"rum","s");
				break;
			case "runners-up" :
				return self::stem($word,4,"-up","s");
				break;
			case "sancta" :
				return self::stem($word,2,"tum","s");
				break;        /* disprefer */
			case "sawboneses" :
				return self::stem($word,2,"","s");
				break;
			case "scarves" :
				return self::stem($word,3,"f","s");
				break;
			case "scherzi" :
				return self::stem($word,2,"zo","s");
				break;        /* disprefer */
			case "scrota" :
				return self::stem($word,2,"tum","s");
				break;
			case "secretaries-general" :
				return self::stem($word,11,"y-general","s");
				break;
			case "selves" :
				return self::stem($word,3,"f","s");
				break;
			case "sera" :
				return self::stem($word,2,"rum","s");
				break;          /* disprefer */
			case "seraphim" :
				return self::stem($word,2,"","s");
				break;
			case "sheaves" :
				return self::stem($word,3,"f","s");
				break;
			case "shelves" :
				return self::stem($word,3,"f","s");
				break;
			case "simulacra" :
				return self::stem($word,2,"rum","s");
				break;
			case "sisters-in-law" :
				return self::stem($word,8,"-in-law","s");
				break;
			case "soli" :
				return self::stem($word,2,"lo","s");
				break;           /* disprefer */
			case "soliloquies" :
				return self::stem($word,3,"y","s");
				break;
			case "sons-in-law" :
				return self::stem($word,8,"-in-law","s");
				break;
			case "spectra" :
				return self::stem($word,2,"rum","s");
				break;
			case "sphinges" :
				return self::stem($word,3,"x","s");
				break;        /* disprefer */
			case "splayfeet" :
				return self::stem($word,3,"oot","s");
				break;
			case "sputa" :
				return self::stem($word,2,"tum","s");
				break;
			case "stamina" :
				return self::stem($word,3,"en","s");
				break;        /* disprefer */
			case "stelae" :
				return self::stem($word,2,"e","s");
				break;
			case "stepchildren" :
				return self::stem($word,3,"","s");
				break;
			case "sterna" :
				return self::stem($word,2,"num","s");
				break;
			case "strata" :
				return self::stem($word,2,"tum","s");
				break;
			case "stretti" :
				return self::stem($word,2,"to","s");
				break;
			case "summonses" :
				return self::stem($word,2,"","s");
				break;
			case "swamies" :
				return self::stem($word,2,"","s");
				break;          /* disprefer */
			case "swathes" :
				return self::stem($word,2,"","s");
				break;
			case "synopses" :
				return self::stem($word,2,"is","s");
				break;
			case "syntheses" :
				return self::stem($word,2,"is","s");
				break;
			case "tableaux" :
				return self::stem($word,2,"u","s");
				break;
			case "taxies" :
				return self::stem($word,2,"","s");
				break;           /* disprefer */
			case "teeth" :
				return self::stem($word,4,"ooth","s");
				break;
			case "tempi" :
				return self::stem($word,2,"po","s");
				break;
			case "tenderfeet" :
				return self::stem($word,3,"oot","s");
				break;
			case "testes" :
				return self::stem($word,2,"is","s");
				break;
			case "theses" :
				return self::stem($word,2,"is","s");
				break;
			case "thieves" :
				return self::stem($word,3,"f","s");
				break;
			case "thoraces" :
				return self::stem($word,3,"x","s");
				break;
			case "titmice" :
				return self::stem($word,3,"ouse","s");
				break;
			case "tootses" :
				return self::stem($word,2,"","s");
				break;
			case "torsi" :
				return self::stem($word,2,"so","s");
				break;          /* disprefer */
			case "tricepses" :
				return self::stem($word,2,"","s");
				break;        /* disprefer */
			case "triumviri" :
				return self::stem($word,2,"r","s");
				break;
			case "trousseaux" :
				return self::stem($word,2,"u","s");
				break;      /* disprefer */
			case "turves" :
				return self::stem($word,3,"f","s");
				break;
			case "tympana" :
				return self::stem($word,2,"num","s");
				break;
			case "ultimata" :
				return self::stem($word,2,"tum","s");
				break;
			case "vacua" :
				return self::stem($word,2,"uum","s");
				break;         /* disprefer */
			case "vertices" :
				return self::stem($word,4,"ex","s");
				break;
			case "vertigines" :
				return self::stem($word,4,"o","s");
				break;
			case "virtuosi" :
				return self::stem($word,2,"so","s");
				break;
			case "vortices" :
				return self::stem($word,4,"ex","s");
				break;
			case "wagons-lits" :
				return self::stem($word,6,"-lit","s");
				break;
			case "weirdies" :
				return self::stem($word,2,"e","s");
				break;
			case "werewolves" :
				return self::stem($word,3,"f","s");
				break;
			case "wharves" :
				return self::stem($word,3,"f","s");
				break;
			case "whippers-in" :
				return self::stem($word,4,"-in","s");
				break;
			case "wolves" :
				return self::stem($word,3,"f","s");
				break;
			case "woodlice" :
				return self::stem($word,3,"ouse","s");
				break;
			case "yogin" :
				return self::stem($word,2,"i","s");
				break;           /* disprefer */
			case "zombies" :
				return self::stem($word,2,"e","s");
				break;

			case $matchAmetres[0]:
			case $matchAlitres[0]:
			case $matchAettes[0]:
			case "acres":
			case "Aussies":
			case "budgies":
			case "catastrophes":
			case "centres":
			case "cliches":
			case "commies":
			case "coolies":
			case "curies":
			case "demesnes":
			case "employees":
			case "evacuees":
			case "fibres":
			case "headaches":
			case "hordes":
			case "magpies":
			case "manoeuvres":
			case "moggies":
			case "moustaches":
			case "movies":
			case "nighties":
			case "programmes":
			case "queues":
			case "sabres":
			case "sorties":
			case "tastes":
			case "theatres":
			case "timbres":
			case "titres":
			case "wiseacres":
				return self::stem($word, 1,"","s");
				break;
			case "burnurns":
				return self::stem($word, 1,"","s");
				break;
			case "carriageways":
				return self::stem($word, 1,"","s");
				break;
			case "cills":
				return self::stem($word, 1,"","s");
				break;
			case "umbrellas":
			case "utopias":
				return self::stem($word, 1,"","s");
				break;
			case $matchAitis[0]:
			case "abdomen":
			case "acacia":
			case "achimenes":
			case "alibi":
			case "alkali":
			case "ammonia":
			case "amnesia":
			case "anaesthesia":
			case "anesthesia":
			case "aria":
			case "arris":
			case "asphyxia":
			case "aspidistra":
			case "aubrietia":
			case "axis":
			case "begonia":
			case "bias":
			case "bikini":
			case "cannula":
			case "canvas":
			case "chili":
			case "chinchilla":
			case "Christmas":
			case "cornucopia":
			case "cupola":
			case "cyclamen":
			case "diabetes":
			case "diphtheria":
			case "dysphagia":
			case "encyclopaedia":
			case "ennui":
			case "escallonia":
			case "ferris":
			case "flotilla":
			case "forsythia":
			case "ganglia":
			case "gas":
			case "gondola":
			case "grata":
			case "guerrilla":
			case "haemophilia":
			case "hysteria":
			case "inertia":
			case "insignia":
			case "iris":
			case "khaki":
			case "koala":
			case "lens":
			case "macaroni":
			case "manilla":
			case "mania":
			case "mantis":
			case "martini":
			case "matins":
			case "memorabilia":
			case "metropolis":
			case "moa":
			case "morphia":
			case "nostalgia":
			case "omen":
			case "pantometria":
			case "parabola":
			case "paraphernalia":
			case "pastis":
			case "patella":
			case "patens":
			case "pelvis":
			case "peninsula":
			case "phantasmagoria":
			case "pneumonia":
			case "polyuria":
			case "portcullis":
			case "pyrexia":
			case "regalia":
			case "safari":
			case "salami":
			case "sari":
			case "saturnalia":
			case "spaghetti":
			case "specimen":
			case "subtopia":
			case "suburbia":
			case "syphilis":
			case "taxi":
			case "toccata":
			case "trellis":
			case "tutti":
			case "umbrella":
			case "utopia":
			case "villa":
			case "zucchini":
				return self::cnull_stem($word);
				break;
			case "acumen":
			case "Afrikaans":
			case "aphis":
			case "brethren":
			case "caries":
			case "confetti":
			case "contretemps":
			case "dais":
			case "debris":
			case "extremis":
			case "gallows":
			case "hors":
			case "hovis":
			case "hustings":
			case "innards":
			case "isosceles":
			case "maquis":
			case "minutiae":
			case "molasses":
			case "mortis":
			case "patois":
			case "pectoris":
			case "plumbites":
			case "series":
			case "tares":
			case "tennis":
			case "turps":
				return self::xnull_stem($word);
				break;
			case "accoutrements":
			case "aerodynamics":
			case "aeronautics":
			case "aesthetics":
			case "algae":
			case "amends":
			case "annals":
			case "arrears":
			case "assizes":
			case "auspices":
			case "backwoods":
			case "bacteria":
			case "banns":
			case "battlements":
			case "bedclothes":
			case "belongings":
			case "billiards":
			case "binoculars":
			case "bitters":
			case "blandishments":
			case "bleachers":
			case "blinkers":
			case "blues":
			case "breeches":
			case "brussels":
			case "clothes":
			case "clutches":
			case "commons":
			case "confines":
			case "contents":
			case "credentials":
			case "crossbones":
			case "damages":
			case "dealings":
			case "dentures":
			case "depths":
			case "devotions":
			case "diggings":
			case "doings":
			case "downs":
			case "dues":
			case "dynamics":
			case "earnings":
			case "eatables":
			case "eaves":
			case "economics":
			case "electrodynamics":
			case "electronics":
			case "entrails":
			case "environs":
			case "equities":
			case "ethics":
			case "eugenics":
			case "filings":
			case "finances":
			case "folks":
			case "footlights":
			case "fumes":
			case "furnishings":
			case "genitals":
			case "glitterati":
			case "goggles":
			case "goods":
			case "grits":
			case "groceries":
			case "grounds":
			case "handcuffs":
			case "headquarters":
			case "histrionics":
			case "hostilities":
			case "humanities":
			case "hydraulics":
			case "hysterics":
			case "illuminations":
			case "italics":
			case "jeans":
			case "jitters":
			case "kinetics":
			case "knickers":
			case "latitudes":
			case "leggings":
			case "likes":
			case "linguistics":
			case "lodgings":
			case "loggerheads":
			case "mains":
			case "manners":
			case "mathematics":
			case "means":
			case "measles":
			case "media":
			case "memoirs":
			case "metaphysics":
			case "mockers":
			case "motions":
			case "multimedia":
			case "munitions":
			case "news":
			case "nutria":
			case "nylons":
			case "oats":
			case "odds":
			case "oils":
			case "oilskins":
			case "optics":
			case "orthodontics":
			case "outskirts":
			case "overalls":
			case "pants":
			case "pantaloons":
			case "papers":
			case "paras":
			case "paratroops":
			case "particulars":
			case "pediatrics":
			case "phonemics":
			case "phonetics":
			case "physics":
			case "pincers":
			case "plastics":
			case "politics":
			case "proceeds":
			case "proceedings":
			case "prospects":
			case "pyjamas":
			case "rations":
			case "ravages":
			case "refreshments":
			case "regards":
			case "reinforcements":
			case "remains":
			case "respects":
			case "returns":
			case "riches":
			case "rights":
			case "savings":
			case "scissors":
			case "seconds":
			case "semantics":
			case "shades":
			case "shallows":
			case "shambles":
			case "shorts":
			case "singles":
			case "slacks":
			case "specifics":
			case "spectacles":
			case "spoils":
			case "statics":
			case "statistics":
			case "summons":
			case "supplies":
			case "surroundings":
			case "suspenders":
			case "takings":
			case "teens":
			case "telecommunications":
			case "tenterhooks":
			case "thanks":
			case "theatricals":
			case "thermodynamics":
			case "tights":
			case "toils":
			case "trappings":
			case "travels":
			case "troops":
			case "tropics":
			case "trousers":
			case "tweeds":
			case "underpants":
			case "vapours":
			case "vicissitudes":
			case "vitals":
			case "wages":
			case "wanderings":
			case "wares":
			case "whereabouts":
			case "whites":
			case "winnings":
			case "withers":
			case "woollens":
			case "workings":
			case "writings":
			case "yes":
				return self::xnll_stem($word);
				break;
			case "boaties":
			case "bonhomies":
			case "clippies":
			case "creepies":
			case "dearies":
			case "droppies":
			case "gendarmeries":
			case "girlies":
			case "goalies":
			case "haddies":
			case "kookies":
			case "kyries":
			case "lambies":
			case "lassies":
			case "maries":
			case "menageries":
			case "petties":
			case "reveries":
			case "snotties":
			case "sweeties":
				return self::stem($word, 1,"","s");
				break;

			case "beasties":
			case "brownies":
			case "caches":
			case "cadres":
			case "calories":
			case "champagnes":
			case "colognes":
			case "cookies":
			case "druggies":
			case "eateries":
			case "emigres":
			case "emigrees":
			case "employees":
			case "freebies":
			case "genres":
			case "kiddies":
			case "massacres":
			case "moonies":
			case "neckties":
			case "niches":
			case "prairies":
			case "softies":
			case "toothpastes":
			case "willies":
				return self::stem($word, 1,"","s");
				break;
			case $matchAphobia[0]:
			case "accompli":
			case "aegis":
			case "alias":
			case "anorexia":
			case "anti":
			case "artemisia":
			case "ataxia":
			case "beatlemania":
			case "blini":
			case "cafeteria":
			case "capita":
			case "cola":
			case "coli":
			case "deli":
			case "dementia":
			case "downstairs":
			case "upstairs":
			case "dyslexia":
			case "jakes":
			case "dystopia":
			case "encyclopedia":
			case "estancia":
			case "euphoria":
			case "euthanasia":
			case "fracas":
			case "fuss":
			case "gala":
			case "gorilla":
			case "GI":
			case "habeas":
			case "haemophilia":
			case "hemophilia":
			case "hoopla":
			case "hula":
			case "impatiens":
			case "informatics":
			case "intelligentsia":
			case "jacuzzi":
			case "kiwi":
			case "mafia":
			case "magnolia":
			case "malaria":
			case "maquila":
			case "marginalia":
			case "megalomania":
			case "mercedes":
			case "militia":
			case "mufti":
			case "muni":
			case "olympics":
			case "pancreas":
			case "paranoia":
			case "pastoris":
			case "pastrami":
			case "pepperoni":
			case "pepsi":
			case "pi":
			case "piroghi":
			case "pizzeria":
			case "pneumocystis":
			case "potpourri":
			case "proboscis":
			case "rabies":
			case "reggae":
			case "regimen":
			case "rigatoni":
			case "salmonella":
			case "sarsaparilla":
			case "semen":
			case "ski":
			case "sonata":
			case "spatula":
			case "stats":
			case "subtilis":
			case "sushi":
			case "tachyarrhythmia":
			case "tachycardia":
			case "tequila":
			case "tetris":
			case "thrips":
			case "timpani":
			case "tsunami":
			case "vaccinia":
			case "vanilla":
				return self::cnull_stem($word);
				break;
			case "acrobatics":
			case "athletics":
			case "basics":
			case "betters":
			case "bifocals":
			case "bowels":
			case "briefs":
			case "checkers":
			case "cognoscenti":
			case "denims":
			case "doldrums":
			case "dramatics":
			case "dungarees":
			case "ergonomics":
			case "genetics":
			case "gravitas":
			case "gymnastics":
			case "hackles":
			case "haves":
			case "hubris":
			case "ides":
			case "incidentals":
			case "ironworks":
			case "jinks":
			case "leavings":
			case "leftovers":
			case "logistics":
			case "makings":
			case "microelectronics":
			case "miniseries":
			case "mips":
			case "mores":
			case "oodles":
			case "pajamas":
			case "pampas":
			case "panties":
			case "payola":
			case "pickings":
			case "plainclothes":
			case "pliers":
			case "ravings":
			case "reparations":
			case "rudiments":
			case "scads":
			case "splits":
			case "stays":
			case "subtitles":
			case "sunglasss":
			case "sweepstakes":
			case "tatters":
			case "toiletries":
			case "tongs":
			case "trivia":
			case "tweezers":
			case "vibes":
			case "waterworks":
			case "woolens":
				return self::xnull_stem($word);
				break;
			case "biggies":
			case "bourgeoisies":
			case "bries":
			case "camaraderies":
			case "chinoiseries":
			case "coteries":
			case "doggies":
			case "genies":
			case "hippies":
			case "junkies":
			case "lingeries":
			case "moxies":
			case "preppies":
			case "rookies":
			case "yuppies" :
				return self::stem($word, 1,"","s");
				break;
			case $matchAphilia[0]:
			case "fantasia":
			case "Feis":
			case "Gras":
			case "Mardi":
				return self::cnull_stem($word);
				break;
			case "calisthenics":
			case "heroics":
			case "rheumatics":
			case "victuals":
			case "wiles":
				return self::xnull_stem($word);
				break;
			case "aunties":
			case "anomies":
			case "coosies":
			case "quickies":
				return self::stem($word, 1,"","s");
				break;
			case "absentia":
			case "bourgeois":
			case "pecunia":
			case "Syntaxis":
			case "uncia":
				return self::cnull_stem($word);
				break;
			case "apologetics":
			case "goings":
			case "outdoors":
				return self::xnull_stem($word);
				break;
			case "collies" :
				return self::stem($word, 1,"","s");
				break;

			case "assagai":
			case "borzoi":
			case "calla":
			case "camellia":
			case "campanula":
			case "cantata":
			case "caravanserai":
			case "cedilla":
			case "cognomen":
			case "copula":
			case "corolla":
			case "cyclopaedia":
			case "dahlia":
			case "dhoti":
			case "dolmen":
			case "effendi":
			case "fibula":
			case "fistula":
			case "freesia":
			case "fuchsia":
			case "guerilla":
			case "hadji":
			case "hernia":
			case "houri":
			case "hymen":
			case "hyperbola":
			case "hypochondria":
			case "inamorata":
			case "kepi":
			case "kukri":
			case "mantilla":
			case "monomania":
			case "nebula":
			case "ovata":
			case "pergola":
			case "petunia":
			case "pharmacopoeia":
			case "phi":
			case "poinsettia":
			case "primula":
			case "rabbi":
			case "scapula":
			case "sequoia":
			case "sundae":
			case "tarantella":
			case "tarantula":
			case "tibia":
			case "tombola":
			case "topi":
			case "tortilla":
			case "uvula":
			case "viola":
			case "wisteria":
			case "zinnia":
				return self::cnull_stem($word);
				break;
			case "tibiae":
			case "nebulae":
			case "uvulae" :
				return self::stem($word, 1,"","s");
				break; /* disprefer */
			case "arrases":
			case "clitorises":
			case "mugginses":
				return self::stem($word,2,"","s");
				break;
			case "alms":
			case "biceps":
			case "calends":
			case "elevenses":
			case "eurhythmics":
			case "faeces":
			case "forceps":
			case "jimjams":
			case "jodhpurs":
			case "menses":
			case "secateurs":
			case "shears":
			case "smithereens":
			case "spermaceti":
			case "suds":
			case "trews":
			case "triceps":
			case "underclothes":
			case "undies":
			case "vermicelli":
				return self::xnull_stem($word);
				break;
			case "albumen":
			case "alopecia":
			case "ambergris":
			case "amblyopia":
			case "ambrosia":
			case "analgesia":
			case "aphasia":
			case "arras":
			case "asbestos":
			case "asia":
			case "assegai":
			case "astrophysics":
			case "aubrietia":
			case "aula":
			case "avoirdupois":
			case "beriberi":
			case "bitumen":
			case "broccoli":
			case "cadi":
			case "callisthenics":
			case "collywobbles":
			case "curia":
			case "cybernetics":
			case "cyclops":
			case "cyclopedia":
			case "dickens":
			case "dietetics":
			case "dipsomania":
			case "dyspepsia":
			case "epidermis":
			case "epiglottis":
			case "erysipelas":
			case "fascia":
			case "finis":
			case "fives":
			case "fleur-de-lis":
			case "geophysics":
			case "geriatrics":
			case "glottis":
			case "haggis":
			case "hara-kiri":
			case "herpes":
			case "hoop-la":
			case "ibis":
			case "insomnia":
			case "kleptomania":
			case "kohlrabi":
			case "kris":
			case "kumis":
			case "litchi":
			case "litotes":
			case "loggia":
			case "magnesia":
			case "man-at-arms":
			case "manila":
			case "marquis":
			case "master-at-arms":
			case "mattins":
			case "melancholia":
			case "minutia":
			case "muggins":
			case "mumps":
			case "mi":
			case "myopia":
			case "necropolis":
			case "neuralgia":
			case "nibs":
			case "numismatics":
			case "nymphomania":
			case "obstetrics":
			case "okapi":
			case "onomatopoeia":
			case "ophthalmia":
			case "paraplegia":
			case "patchouli":
			case "paterfamilias":
			case "penis":
			case "piccalilli":
			case "praxis":
			case "precis":
			case "prophylaxis":
			case "pyrites":
			case "raffia":
			case "revers":
			case "rickets":
			case "rounders":
			case "rubella":
			case "saki":
			case "salvia":
			case "sassafras":
			case "sawbones":
			case "scabies":
			case "schnapps":
			case "scintilla":
			case "scrofula":
			case "sepia":
			case "stamen":
			case "si":
			case "swami":
			case "testis":
			case "therapeutics":
			case "tiddlywinks":
			case "verdigris":
			case "wadi":
			case "wapiti":
			case "yogi":
				return self::cnull_stem($word);
				break;
			case "aeries":
			case "birdies":
			case "bogies":
			case "caddies":
			case "cock-a-leekies":
			case "collies":
			case "corries":
			case "cowries":
			case "dixies":
			case "eyries":
			case "faeries":
			case "gaucheries":
			case "gillies":
			case "knobkerries":
			case "laddies":
			case "mashies":
			case "mealies":
			case "menageries":
			case "organdies":
			case "patisseries":
			case "pinkies":
			case "pixies":
			case "stymies":
			case "talkies":
				return self::stem($word, 1,"","s");
				break;
			case "humans":
				return self::stem($word, 1,"","s");
				break;
			case "slums"                  :
				return self::stem($word, 1,"","s");
				break;

			case $matchAabuses[0]:
			case $matchAuses[0]:
			case "abuses":
			case "burnouses":
			case "cayuses":
			case "chanteuses":
			case "chartreuses":
			case "chauffeuses":
			case "cruses":
			case "disuses":
			case "excuses":
			case "grouses":
			case "hypotenuses":
			case "masseuses":
			case "misuses":
			case "muses":
			case "Ouses":
			case "overuses":
			case "poseuses":
			case "recluses":
			case "reuses":
			case "ruses":
			case "uses":
			case $matchAhlmpouses[0]:
			case $matchAafuses[0]:
				return self::stem($word, 1,"","s");
				break;
			case "ablutions":
			case "adenoids":
			case "aerobatics":
			case "afters":
			case "astronautics":
			case "atmospherics":
			case "bagpipes":
			case "ballistics":
			case "bell-bottoms":
			case "belles-lettres":
			case "blinders":
			case "bloomers":
			case "butterfingers":
			case "buttocks":
			case "bygones":
			case "cahoots":
			case "castanets":
			case "clappers":
			case "dodgems":
			case "dregs":
			case "duckboards":
			case "edibles":
			case "eurythmics":
			case "externals":
			case "extortions":
			case "falsies":
			case "fisticuffs":
			case "fleshings":
			case "fleur-de-lys":
			case "fours":
			case "gentleman-at-arms":
			case "geopolitics":
			case "giblets":
			case "gleanings":
			case "handlebars":
			case "heartstrings":
			case "homiletics":
			case "housetops":
			case "hunkers":
			case "hydroponics":
			case "kalends":
			case "knickerbockers":
			case "lees":
			case "lei":
			case "lieder":
			case "literati":
			case "loins":
			case "meanderings":
			case "meths":
			case "muniments":
			case "necessaries":
			case "nines":
			case "ninepins":
			case "nippers":
			case "nuptials":
			case "orthopaedics":
			case "paediatrics":
			case "phonics":
			case "polemics":
			case "pontificals":
			case "prelims":
			case "pyrotechnics":
			case "ravioli":
			case "rompers":
			case "ructions":
			case "scampi":
			case "scrapings":
			case "serjeant-at-arms":
			case "shires":
			case "smalls":
			case "steelworks":
			case "sweepings":
			case "vespers":
			case "virginals":
			case "waxworks":
				return self::xnull_stem($word);
				break;
			case "cannabis":
			case "corgi":
			case "envoi":
			case "hi-fi":
			case "kwela":
			case "lexis":
			case "muesli":
			case "sheila":
			case "ti":
			case "yeti":
				return self::cnull_stem($word);
				break;

			case "mounties":
			case "brasseries":
			case "grannies":
			case "koppies":
			case "rotisseries":
				return self::stem($word, 1,"","s");
				break;

			case "cantharis"   :
				return self::stem($word,1,"de","s");
				break;
			case "chamois"     :
				return self::stem($word,1,"x","s");
				break;
			case "submatrices" :
				return self::stem($word,3,"x","s");
				break;
			case "mafiosi"     :
				return self::stem($word,1,"o","s");
				break;
			case "pleura"      :
				return self::stem($word,1,"on","s");
				break;
			case "vasa"        :
				return self::stem($word, 1,"","s");
				break;
			case "antipasti"   :
				return self::stem($word,1,"o","s");
				break;

				/* redundant in analysis; but in generation e.g. buffalo+s -> buffaloes */
			case "co's":
			case "do's":
			case "ko's":
			case "no's"  :
				return self::stem($word,2,"","s");
				break;

			case "aloes":
			case "archfoes":
			case "canoes":
			case "does":
			case "felloes":
			case "floes":
			case "foes":
			case "hammertoes":
			case "hoes":
			case "icefloes":
			case "mistletoes":
			case "oboes":
			case "roes":
			case $matchAshoes[0]:
			case "sloes":
			case "throes":
			case "tiptoes":
			case "toes":
			case "voes":
			case "woes" :
				return self::stem($word, 1,"","s");
				break;

			case "antiheroes":
			case "buffaloes":
			case "dingoes":
			case "dominoes":
			case "echoes":
			case "goes":
			case "grottoes":
			case "heroes":
			case "innuendoes":
			case "mangoes":
			case "matoes":
			case "mosquitoes":
			case "mulattoes":
			case "potatoes":
			case "peccadilloes":
			case "pentominoes":
			case "superheroes":
			case "tomatoes":
			case "tornadoes":
			case "torpedoes":
			case "vetoes":
			case "volcanoes":
				return self::stem($word,2,"","s");
				break;

				/* -os / -oses */
				
			case "tornedos":
			case "throes":
				return self::xnull_stem($word);
				break;
			case "bathos":
			case "cross-purposes":
			case "kudos":
				return self::xnull_stem($word);
				break;
			case "cos"                               :
				return self::cnull_stem($word);
				break;

			case "chaos":
			case "cosmos":
			case "ethos":
			case "parados":
			case "pathos":
			case "rhinoceros":
			case "tripos":
			case "thermos":
			case "OS":
			case "reredos":
				return self::cnull_stem($word);
				break;
			case "chaoses":
			case "cosmoses":
			case "ethoses":
			case "paradoses":
			case "pathoses":
			case "rhinoceroses":
			case "triposes":
			case "thermoses":
			case "OSes":
			case "reredoses" :
				return self::stem($word,2,"","s");
				break;

			case "anastomoses":
			case "apotheoses":
			case "arterioscleroses":
			case "asbestoses":
			case "celluloses":
			case "dermatoses":
			case "diagnoses":
			case "diverticuloses":
			case "exostoses":
			case "hemicelluloses":
			case "histocytoses":
			case "hypnoses":
			case "meioses":
			case "metamorphoses":
			case "metempsychoses":
			case "mitoses":
			case "neuroses":
			case "prognoses":
			case "psychoses":
			case "salmonelloses":
			case "symbioses":
			case "scleroses":
			case "stenoses":
			case "symbioses":
			case "synchondroses":
			case "treponematoses":
			case "zoonoses" :
				return self::stem($word,2,"is","s");
				break;

			case "pharoses"  :
				return self::stem($word,4,"isee","s");
				break;  /* disprefer */

				/* -zes */

			case "adzes":
			case "bronzes"       :
				return self::stem($word, 1,"","s");
				break;
			case "fezzes":
			case "quizzes"        :
				return self::stem($word,3,"","s");
				break;
			case "fezes":
			case "quizes"         :
				return self::stem($word,2,"","s");
				break;      /* disprefer */
			case "pp.":
				return self::stem($word,2,".","s");
				break;
			case "m.p.s.":
				return self::stem($word,6,"m.p.","s");
				break;
			case "cons.":
			case "miss.":
			case "mrs.":
			case "ms.":
			case "n-s.":
			case "pres.":
			case "ss.":
				return self::cnull_stem($word);
				break;

			case $matchAs1[0]:
				return self::cnull_stem($word);
				break;
			case $matchAs2[0]:
				return self::stem($word,4,".","s");
				break; /* disprefer */

			case $matchAs3[0]:
				return self::stem($word,2,".","s");
				break;

			case $matchAmen[0]:
				return self::stem($word,2,"an","s");
				break;
			case $matchAwives[0]:
				return self::stem($word,3,"fe","s");
				break;
			case $matchAzoa[0]:
				return self::stem($word,1,"on","s");
				break;
			case $matchAiia[0]:
				return self::stem($word,2,"um","s");
				break; /* disprefer */
			case $matchAemnia[0]:
				return self::cnull_stem($word);
				break;
			case $matchAia[0]:
				return self::stem($word,1,"um","s");
				break; /* disprefer */
			case $matchAla[0]:
				return self::stem($word,1,"um","s");
				break;
			case $matchAi[0]:
				return self::stem($word,1,"us","s");
				break; /* disprefer */
			case $matchAae[0]:
				return self::stem($word,2,"a","s");
				break; /* disprefer */
			case $matchAata[0]:
				return self::stem($word,3,"a","s");
				break; /* disprefer */

			default:
				;
				break;
		}

	}


	function scanVerbNoun($word) {
		preg_match($this->Auses,$word,$matchAuses);
		preg_match($this->Aussssiseed,$word,$matchAussssiseed);
		preg_match($this->Avses,$word,$matchAvses);
		preg_match($this->Acxyzes,$word,$matchAcxyzes);
		preg_match($this->Avyzes,$word,$matchAvyzes);
		preg_match($this->AS2es,$word,$matchAS2es);
		preg_match($this->Avrses,$word,$matchAvrses);
		preg_match($this->Aonses,$word,$matchAonses);
		preg_match($this->ASes,$word,$matchASes);
		preg_match($this->Athes,$word,$matchAthes);
		preg_match($this->Acxycglsves,$word,$matchAcxycglsves);
		preg_match($this->Aettes,$word,$matchAettes);
		preg_match($this->ACies,$word,$matchACies);
		preg_match($this->Acxyoes,$word,$matchAcxyoes);
		preg_match($this->GMinusMore,$word,$matchGMinusMore);
		preg_match($this->GMinusMore2,$word,$matchGMinusMore2);
		preg_match($this->As,$word,$matchAs);
		preg_match($this->SKIP,$word,$matchSkip);
		switch ($word) {
			case "busses" :
				return self::stem($word,3,"","s");
				break;

			case "hocus-pocusses" :
				return self::stem($word,3,"","s");
				break;
			case "hocusses" :
				return self::stem($word,3,"","s");
				break;

			case $matchAuses[0]:
				return self::stem($word,2,"","s");
				break;
			case "his":
			case "hers":
			case "theirs":
			case "ours":
			case "yours":
			case "as":
			case "its":
			case "this":
			case "during":
			case "something":
			case "nothing":
			case "anything":
			case "everything":
				return self::cnull_stem($word);
				break;
			case $matchAussssiseed[0]:
				return self::cnull_stem($word);
				break;
			case $matchAvses[0]:
				return self::stem($word, 1,"","s");
				break;
			case $matchAcxyzes[0]:
				return self::stem($word,2,"","s");
				break;
			case $matchAvyzes[0]:
				return self::stem($word, 1,"","s");
				break;
			case $matchAS2es[0]:
				return self::stem($word,2,"","s");
				break;
			case $matchAvrses[0]:
				return self::stem($word, 1,"","s");
				break;
			case $matchAonses[0]:
				return self::stem($word, 1,"","s");
				break;
			case $matchASes[0]:
				return self::stem($word,2,"","s");
				break;
			case $matchAthes[0]:
				return self::stem($word, 1,"","s");
				break;
			case $matchAcxycglsves[0]:
				return self::stem($word, 1,"","s");
				break;
			case $matchAettes[0]:
				return self::stem($word, 1,"","s");
				break;
			case $matchACies[0]:
				return self::stem($word,3,"y","s");
				break;
			case $matchAcxyoes[0]:
				return self::stem($word,2,"","s");
				break;  /* disprefer */
			case $matchAs[0]:
				return self::stem($word, 1,"","s");
				break;

			case $matchGMinusMore[0]:
				return self::common_noun_stem($word);
				//return yylex();
				break;
			case $matchGMinusMore2[0]:
				return self::common_noun_stem($word);
				break;
			case $matchSkip[0]:
				return self::common_noun_stem($word);
				break;
			default:
				;
				break;
		}

	}




	/*
	 * Show Index
	 *
	 * Helper function that outputs inverted index in a standard format.
	 */
	function show_index() {
		// sort by key for alphabetically ordered output
		ksort($this->words);
		// output a representation of the inverted index
		foreach($this->words AS $term => $doc_locations) {
			echo "<b>$term:</b> ";
			foreach($doc_locations AS $doc_location)
			echo "{".$doc_location[DOC_ID].", ".$doc_location[TERM_POSITION]."} ";
			echo "<br />";
		}
	}

}

?>





















