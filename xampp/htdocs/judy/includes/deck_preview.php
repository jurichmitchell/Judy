	<a href="deck.php?deck_id=<?php echo "{$deck_info['deck_id']}"; ?>">
		<fieldset class="deckPreview">
			<table>
				<tr>
					<td rowspan="2">
						<div class="cardHoldingDiv">
							<?php
							echo "<div class=\"fixedRatioCardPreview\"><div class=\"cardPreviewText\">{$deck_info['preview_card']}</div></div>\n\t\t";
							?>
						</div>
					</td>
					<td>
						<p class="deckName"><?php echo "{$deck_info['deck_name']}"; ?></p>
						<p class="deckSubText">Created by: <?php echo "{$deck_info['username']}"; ?></p>
						<p class="deckSubText">Uploaded:
							<?php
							$date = date_create($deck_info['upload_date']);
							echo date_format($date,"F d, Y"); 
							?>
						</p>
					</td>
				</tr>
				<tr>
					<!--<td style="width:100%;" valign="bottom"><p class="deckSubText">RATING</p></td>-->
					<td style="width:100%;" valign="bottom"><p class="deckSubText">
					<?php
					// Generate query to get average of this deck's ratings
					$query = "SELECT AVG(rating) as avg FROM rating WHERE deck_id='{$deck_info['deck_id']}'";
					$rating_results = mysqli_query($dbc, $query);
					if ($rating_results && mysqli_num_rows($rating_results) != 0) {
						echo "AVG. RATING: " . mysqli_fetch_array($rating_results, MYSQLI_ASSOC)['avg'];
					}
					// NOT REACHED FOR SOME REASON
					else {
						echo "DECK UNRATED";
					}
					?>
					</p></td>
				</tr>
			</table>
		</fieldset>
	</a>
