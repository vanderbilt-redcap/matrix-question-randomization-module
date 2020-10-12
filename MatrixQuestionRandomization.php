<?php
namespace Vanderbilt\MatrixQuestionRandomization;

class MatrixQuestionRandomization extends \ExternalModules\AbstractExternalModule{
	function redcap_survey_page(){
		$groupNames = $this->getProjectSetting('matrix-group-names');
		if($groupNames === null){
			// The user has not saved settings yet
			return;
		}

		?>
		<style>
			#questiontable tr.matrix-randomization-module-highligted-row td{
				background-color: #ffe6e6;
			}
		</style>
		<?php

		foreach($groupNames as $groupName){
			?>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.2.0/js.cookie.min.js" integrity="sha256-9Nt2r+tJnSd2A2CRUvnjgsD+ES1ExvjbjBNqidm9doI=" crossorigin="anonymous"></script>
			<script>
				(function(){
					// Copied from https://stackoverflow.com/questions/2450954/how-to-randomize-shuffle-a-javascript-array/2450976
					var shuffle = function(array) {
						var currentIndex = array.length, temporaryValue, randomIndex;

						// While there remain elements to shuffle...
						while (0 !== currentIndex) {

							// Pick a remaining element...
							randomIndex = Math.floor(Math.random() * currentIndex);
							currentIndex -= 1;

							// And swap it with the current element.
							temporaryValue = array[currentIndex];
							array[currentIndex] = array[randomIndex];
							array[randomIndex] = temporaryValue;
						}

						return array;
					}

					var header = $('#<?=$groupName?>-mtxhdr-tr')
					var questions = header.nextUntil(':not(tr[mtxgrp="<?=$groupName?>"])')

					// Hide the labels until we're done re-ordering them
					// (so the user doesn't see the previous order for a split second).
					var matrixTables = questions.find('table:visible')
					matrixTables.css('visibility', 'hidden')

					// The following must be added to the loop to execute after other pending tasks
					// to ensure it gets called after question numbers are initially set.
					// The questions are numbered incorrectly if we sort them beforehand.
					$(function(){
						var cookieName = 'matrix-question-randomization-module'
						if(<?=json_encode($_SERVER['REQUEST_METHOD'] === 'GET')?>){
							// Clear the randomization order when a new survey is loaded
							Cookies.remove(cookieName)
						}

						var randomizationCache = Cookies.getJSON(cookieName)
						if(!randomizationCache){
							randomizationCache = {}
						}

						var groupName = '' + <?=json_encode($groupName)?>;
						var currentMatrixOrder = randomizationCache[groupName]
						if(!currentMatrixOrder){
							currentMatrixOrder = []

							questions.each(function (index, element) {
								currentMatrixOrder.push(index)
							})

							shuffle(currentMatrixOrder)

							randomizationCache[groupName] = currentMatrixOrder
							Cookies.set(cookieName, randomizationCache, { expires: 1 })
						}

						var numbers = []
						questions.find('td.questionnummatrix').each(function(index, element){
							// Store the existing numbers in order.
							numbers.push($(element).html())
						})

						var wasRequiredFieldsMessageDisplayed = $('#reqPopup').length !== 0
						$(currentMatrixOrder).each(function (index, sortedIndex) {
							var row = $(questions[sortedIndex])
							row.find('td.questionnummatrix').html(numbers.pop())
							row.insertAfter(header)

							if( wasRequiredFieldsMessageDisplayed &&
								row.find('.requiredlabelmatrix').length !== 0 &&
								row.find('input:checked').length === 0
							){
								// The required fields dialog was displayed.
								// This field must be one of the reasons since it is required and unanswered.
								// Highlight this row.
								row.addClass('matrix-randomization-module-highligted-row')
							}
						})

						matrixTables.css('visibility', 'visible')
					})
				})()
			</script>
			<?php
		}
	}
}