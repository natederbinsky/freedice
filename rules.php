<?php
	
	require 'common/private/lib/start.inc.php';
	$page_info['title'] = 'Dice Game Rules';
?>
	
	<div class="section">
		<div class="body">
			<p>Taken a bit from the Wikipedia article on <a href="http://en.wikipedia.org/wiki/Dudo" target="_blank">Perudo</a> &amp; <a href="http://en.wikipedia.org/wiki/Liar's_dice" target="_blank">Liar's dice</a>, but modified to simplify, use our terminology, and our rules.</p>
		</div>
	</div>

	<div class="section">
		<div class="title">
			Game play
		</div>
		<div class="body">
			<p>The dice game is a game of who can last the longest. Each player begins with five dice, and the goal of the game is to be the last player remaining that still has dice. The game is played in rounds consisting of increasing bids, and each round ends with one player losing a die (except in one special circumstance). Each player's dice are concealed from the other players under a cup.</p>
		</div>
	</div>

	<div class="section">
		<div class="title">
			Game mechanics
		</div>
		<div class="body">
			<p>The players sit in a circle, and one player is selected to begin the first round.</p>
			<p>A round begins by every player rolling their dice under their cup, such that the dice are randomly rolled but concealed. Players may look at the dice under their own cup, but must not change their rolls.</p>
			<p>Each round begins with a bid, and play proceeds clockwise. After a bid is made, it becomes the next player's turn to either bid or take some another action (discussed below). Play continues as players make bids or take actions in turn, and the round ends when a player makes a challenge or exact action. </p>
			<p>Play continues until only one player has dice remaining, at which point that player is declared the winner.</p>
		</div>
	</div>

	<div class="section">
		<div class="title">
			Actions
		</div>
		<div class="body">
			<p>The central component of the dice game is the bid, in which a player claims that a certain number of dice exist under all players' cups. A bid is made initially to begin a round, as well as with the raise action. A bid consists of both a number of dice and a rank: for example, "three fives" is a claim that there are at least three dice that have been rolled as fives (the rank).</p>
			<p>After a bid is made, a subsequent bid must increase the bid. Bids can be increasing in two ways: either by dice rank or by number of dice. If a bid increases in dice rank, the number of dice may remain constant. For example, "three fours" can be increased to "three fives" or "three sixes". If a bid increases the number of dice, then the rank of dice can either be changed or kept the same. For example, "three fours" could be followed by "four fours" OR "four threes."</p>
			<p>
				Example bid sequences:
				<ul>
					<li>LEGAL:	1. "three fours"	2. "three sixes"	(bid increased dice rank)</li>
					<li>LEGAL:	1. "three fours"	2. "four twos"	(bid increased number of dice, so rank can change)</li>
					<li>LEGAL: 	1. "three fives"	2. "five fives"	(bid increased number of dice)</li>
					<li>ILLEGAL:	1. "three fives"	2. "two sixes"	(rank increased but number of dice cannot be decreased)</li>
					<li>ILLEGAL: 1. "three fives"	2. "three fours"	(either rank or number of dice must increase)</li>
				</ul>
			</p>
			<p>
				On a player's turn, they must take one of the following actions:
				<ol>
					<li><span style="font-style: italic">Bid</span>: This action is taken by the first player to act in a round. The player makes an initial bid.</li>
					<li><span style="font-style: italic">Raise</span>: The player increases the bid, according to the rules of bidding as described above.</li>
					<li><span style="font-style: italic">Raise, Push, &amp; Reroll</span>: A variant of the raise action is for the player to reveal a proper subset of the dice in their cup (e.g. place 2 dice showing "fives" on the table next to their cup), reroll the remainder of the dice in the cup, and make a raised bid (e.g. "six fives"). For the remainder of the round, the dice that were revealed remain in play but now all players can see them. Once a die is pushed, it cannot be rerolled as part of a Raise, Push, and Reroll. There must always be at least one die remaining to be rerolled (not all dice can be pushed). Thus, if a player has only one die under their cup, they cannot do a Raise and Push, and Reroll.</li>
					<li><span style="font-style: italic">Pass</span>: A player with two or more dice can pass. On a pass, the current bid remains the same and the player passing does not make a bid. A pass is valid if all of the player's dice (including those pushed and those under the cup) are exactly the same face value (such as when the player has two fives). A player can pass only once with a given set of dice - but a player can pass again after a reroll.</li>
					<li><span style="font-style: italic">Challenge</span>: The challenge action disputes either a bid or a pass action. When a challenge is made, the player making the action specifies whether they are challenging the current bid or a pass (they can only challenge a pass if one has been made, of course!). When a challenge is made, all dice under cups are revealed.
						<ul>
							<li>Challenging a bid: If current bid is valid (i.e. there are at least the number of dice that have been bid), then the challenging player loses a die. If the current bid is invalid, then the player that made the invalid bid loses a die. The player that lost a die then begins the next round of play.</li>
							<li>Challenging a pass: If all of the challenged player's dice show identical rank, the pass is valid and the challenging player loses a die; otherwise the pass is invalid, and the player that passed loses a die. When the last action was a pass, the preceding bid or pass can also be challenged. If the last two actions were passes, the preceding bid or pass cannot be challenged.</li>
						</ul>
					</li>
					<li><span style="font-style: italic">Exact</span>: If a player takes the exact action, all dice are immediately revealed. If the bid is exactly valid (there are exactly the number of dice of the bid rank present, no more and no less) then the exacting player gains a die back (if they have lost any). If the exact action fails, the exacting player loses a die. Each player only gets one exact action PER GAME. </li>
				</ol>
			</p>
		</div>
	</div>

	<div class="section">
		<div class="title">
			Ones
		</div>
		<div class="body">
			<p>Dice showing the rank of one are wild. When checking the number of dice for a challenge or an exact (but not passes), ones are counted as the dice face that was bid. Passes must be natural with all of the dice being the same rank.</p>
			<p>Because ones contribute to the counts of all other dice, there are special rules for bidding ones. You can increase a bid by bidding ones by dividing the quantity of dice by two, rounding up if it's necessary. For example, "six twos" can be followed by "three ones" and "eleven fives" by "six ones" (11/2 = 5.5, then, 6). Also, you can bid following a one bid by doubling and adding one to the quantity of dice. Example: "Four ones" can be followed by "Nine (anything)" (2*4 + 1 = 9) or "two ones" by "5 (anything)" (2*2 + 1 = 5). Obviously, you can increase "three ones" into "four ones" as normally. Thus, the lowest bid is "one two" followed by "one three",  "one four", "one five", "one six", "two twos", ... "two sixes", "one one", "three twos", ...</p>
		</div>
	</div>

	<div class="section">
		<div class="title">
			Special rules
		</div>
		<div class="body">
			<p>When a player has lost all but one of his dice, there are special rules for one round of play.</p>
			<p>In special rules, ones are NOT wild, and players that currently have only a single die are the only players that can make bids to change rank. Any player possessing more than one die can only change the number of dice in a bid. Thus, if the initial bid is "two threes", then the next player (assuming they have more than one die) must also bid  "threes": "three threes", "four threes", or so on. Players with only one die may bid as per the normal rules on bidding. During special rules, bidding ones is no different than bidding for other ranks, and one bids are the lowest bids of a given number. Thus, "one one" is the lowest bid, and "two ones" is the next highest bid after "one six".</p>
			<p>Special rules apply only the first round that a player has one die. On subsequent rounds, play continues under normal rules (unless special rules are triggered by another player losing all but one die). Note that if a player has one die, takes a successful exact action and gains a die, then subsequently loses a die, special rules are NOT triggered, as the player has already triggered one round of special rules in the game.</p>
		</div>
	</div>

<?php
	require 'common/private/lib/end.inc.php';
?>
