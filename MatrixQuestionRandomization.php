<?php
namespace Vanderbilt\MatrixQuestionRandomization;

class MatrixQuestionRandomization extends \ExternalModules\AbstractExternalModule{
	function redcap_survey_page(){
		$groupNames = $this->getProjectSetting('matrix-group-names');
		if($groupNames === null){
			// The user has not saved settings yet
			return;
		}

		foreach($groupNames as $groupName){
			?>
			<script>
				(function(){
					var header = $('#<?=$groupName?>-mtxhdr-tr')
					var questions = header.nextUntil(':not(tr[mtxgrp="<?=$groupName?>"])')

					// Hide the labels until we're done re-ordering them
					// (so the user doesn't see the previous order for a split second).
					var labels = questions.find('label:visible')
					labels.css('visibility', 'hidden')

					// The following must be added to the loop to execute after other pending tasks
					// to ensure it gets called after question numbers are initially set.
					// The questions are numbered incorrectly if we sort them beforehand.
					$(function(){
						var numbers = []
						questions.find('td.questionnummatrix').each(function(index, element){
							// Store the existing numbers in order.
							numbers.push($(element).html())
						})

						questions.sort(function () {
							// This effectively randomizes the order by returning a
							// random number between -0.5 and 0.5.
							return 0.5 - Math.random()
						}).each(function (index, element) {
							element = $(element)
							element.find('td.questionnummatrix').html(numbers.pop())
							element.insertAfter(header)
						})

						labels.css('visibility', 'visible')
					})
				})()
			</script>
			<?php
		}
	}
}