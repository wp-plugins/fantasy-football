<?php
/**
 * @package Fantasy Football
 */
/**
 * Plugin Name: Fantasy Football
 * Plugin URI: http://www.fantasyfootballnerd.com/wordpress
 * Description: Put the award-winning fantasy football rankings and projections from FantasyFootballNerd.com on your website. Automatically updated. Perfect for any fantasy football related website. 
 * Version: 1.0.0
 * Author: TayTech, LLC
 * Author URI: http://www.fantasyfootballnerd.com
 * Text Domain: FantasyFootballNerd.com
 * Domain Path: 
 * Network: 
 * License: GPLv2
 */
 /*  Copyright 2015  TayTech, LLC  (email : nerd@fantasyfootballnerd.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
/**
 * Add our shortcodes 
**/
add_shortcode('ffn_draft_rankings', 'ffnDraftRankings');
add_shortcode('ffn_draft_projections', 'ffnDraftProjections');
add_shortcode('ffn_nfl_schedule', 'ffnSchedule');
add_shortcode('ffn_injuries', 'ffnInjuries');
add_shortcode('ffn_byes', 'ffnByes');
add_shortcode('ffn_weather', 'ffnWeather');
add_shortcode('ffn_auction', 'ffnAuction');
add_shortcode('ffn_depth_charts', 'ffnDepthCharts');
add_shortcode('ffn_weekly_rankings', 'ffnWeeklyRankings');
add_shortcode('ffn_weekly_projections', 'ffnWeeklyProjections');
add_shortcode('ffn_defensive_rankings', 'ffnDefensiveRankings');
add_shortcode('ffn_gameday_inactives', 'ffnGamedayInactives');

/**
 * Add our CSS and Javascript
**/
wp_enqueue_style('ffncss', plugins_url('fantasy-football/css/ffn.css'),array(),'20150428');



/**
 * Display the draft rankings from FantasyFootballNerd.com
 *
 * @param array $params An array of values provided in the shortcode. Expecting 'ppr' => 1 if PPR is being requested
**/
function ffnDraftRankings($params){
	if (isset($params['ppr']) && $params['ppr'] == '1'){$isPPR = true;}else{$isPPR = false;}
	//-- Get the rankings --
	if ($isPPR){$params = array('ppr' => '1');}else{$params = array();}
	$data = ffnCallAPI('draft-rankings', 'xml', $params);
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		echo "<table class='ffntable ffntable-striped'><thead><tr><th>Rank</th><th>Player</th><th>Team</th><th>Pos</th><th>Pos Rank</th><th>Bye</th></tr></thead><tbody>";
		foreach ($doc->getElementsByTagName("Player") AS $player)
			{
			echo "<tr><td>" . $player->getElementsByTagName('overallRank')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('position')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('positionRank')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('byeWeek')->item(0)->nodeValue . "</td></tr>";
			}
		echo "</tbody></table>";
		}
}

/**
 * Display the draft projections from FantasyFootballNerd.com
 *
 * @param array $params An array of values provided in the shortcode. Expecting 'position' => 'QB' if QB projections are being requested. Otherwise RB, WR, TE, K, DEF
**/
function ffnDraftProjections($params){
	if (isset($params['position']) && in_array(strtoupper($params['position']), array('QB','RB','WR','TE','K','DEF'))){$position = strtoupper($params['position']);}else{$position = 'QB';}
	//-- Get the projections --
	$params = array('position' => $position);
	$data = ffnCallAPI('draft-projections', 'xml', $params);
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		echo "<table class='ffntable ffntable-striped'><thead>";
		if ($position == 'QB'){echo "<tr><th>Player</th><th>Team</th><th>Comp</th><th>Pass Yds</th><th>Pass TD</th><th>Int</th><th>Ru Yds</th><th>Ru TD</th><th>Fan Pts</th></tr>";}
		if ($position == 'RB'){echo "<tr><th>Player</th><th>Team</th><th>Ru Att</th><th>Ru Yds</th><th>Ru TD</th><th>Rec</th><th>Rec Yds</th><th>Rec TD</th><th>Fum</th><th>Fan Pts</th></tr>";}
		if ($position == 'WR'){echo "<tr><th>Player</th><th>Team</th><th>Rec</th><th>Rec Yds</th><th>Rec TD</th><th>Ru Att</th><th>Ru Yds</th><th>Ru TD</th><th>Fum</th><th>Fan Pts</th></tr>";}
		if ($position == 'TE'){echo "<tr><th>Player</th><th>Team</th><th>Rec</th><th>Rec Yds</th><th>Rec TD</th><th>Ru Att</th><th>Ru Yds</th><th>Ru TD</th><th>Fum</th><th>Fan Pts</th></tr>";}
		if ($position == 'K'){echo "<tr><th>Player</th><th>Team</th><th>FG</th><th>XP</th><th>Fan Pts</th></tr>";}
		if ($position == 'DEF'){echo "<tr><th>Team</th><th>Sacks</th><th>Int</th><th>TD</th><th>Sp Tm TD</th><th>Fan Pts</th></tr>";}
		echo "</thead><tbody>";
		foreach ($doc->getElementsByTagName("Player") AS $player)
			{
			if ($position == 'QB'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('completions')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('passingYards')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('passingTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('passingInt')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushYards')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fantasyPoints')->item(0)->nodeValue . "</td></tr>";}
			if ($position == 'RB'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushAtt')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushYards')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rec')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recYards')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fumbles')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fantasyPoints')->item(0)->nodeValue . "</td></tr>";}
			if ($position == 'WR'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rec')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recYards')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushAtt')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushYards')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fumbles')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fantasyPoints')->item(0)->nodeValue . "</td></tr>";}
			if ($position == 'TE'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rec')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recYards')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushAtt')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushYards')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fumbles')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fantasyPoints')->item(0)->nodeValue . "</td></tr>";}
			if ($position == 'K'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fg')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('xp')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fantasyPoints')->item(0)->nodeValue . "</td></tr>";}
			if ($position == 'DEF'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('sacks')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('interceptions')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('TD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('specialTeamTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fantasyPoints')->item(0)->nodeValue . "</td></tr>";}
			}
		echo "</tbody></table>";
		}
}

/**
 * Display the NFL Schedule
 *
**/
function ffnSchedule(){
	$data = ffnCallAPI('schedule', 'xml');
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		$currentWeek = $doc->getElementsByTagName("CurrentWeek")->item(0)->nodeValue;
		$week = 0;
		echo "<table class='ffntable ffntable-striped'><thead><tr><th>Game Date</th><th>Home Team</th><th>Away Team</th><th>Kickoff</th><th>TV</th></tr></thead><tbody>";
		foreach ($doc->getElementsByTagName("Game") AS $game)
			{
			if ($game->getAttribute("gameWeek") != $week){ $week = $game->getAttribute("gameWeek"); echo "<tr><td colspan='5' class='ffnbold ffncenter'>Week $week</td></tr>"; }
			echo "<tr><td>" . date("D, M j, Y", strtotime($game->getAttribute("gameDate"))) . "</td><td>" . $game->getAttribute("homeTeam") . "</td><td>" . $game->getAttribute("awayTeam") . "</td><td>" . $game->getAttribute("gameTimeET") . " ET</td><td>" . $game->getAttribute("tvStation") . "</td></tr>";
			}
		echo "</tbody></table>";
		}
}

/**
 * Display the official NFL injury report
 *
 * @param array $params An array of values provided in the shortcode. Expecting 'week' => 5 for a specific week to return. If no week specified, will return current week
**/
function ffnInjuries($params){
	if (isset($params['week'])){$week = (int)$week; if ($week < 0 || $week > 17){unset($week);} }
	if (!isset($week))
		{
		$data = ffnCallAPI('schedule', 'xml');
		$doc = DOMDocument::loadXML($data);
		if ($doc->getElementsByTagName("Error")->length > 0){ echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>"; }else{ $week = $doc->getElementsByTagName("CurrentWeek")->item(0)->nodeValue; }
		unset($doc, $data);
		}
	if (isset($week)){$params = array('week' => $week);}else{$params = array();}
	$data = ffnCallAPI('injuries', 'xml', $params);
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		foreach ($doc->getElementsByTagName("Team") AS $t)
			{
			$team = $t->getAttribute("code");
			echo "<div class='ffnpanel'><img src='http://www.fantasyfootballnerd.com/images/teams_small2/" . $team . ".png' alt='" . ffnGetTeamName($team, true) . "' title='" . ffnGetTeamName($team, true) . "'> " . ffnGetTeamName($team, true) . " Week $week Injuries</div>";
			echo "<table class='ffntable ffntable-striped'><thead><tr><th>Player</th><th>Pos</th><th>Injury</th><th>Practice<br>Status</th><th>Game<br>Status</th><th>Updated</th></tr></thead><tbody>";
			foreach ($t->getElementsByTagName("Player") AS $player)
				{
				echo "<tr><td>" . $player->getAttribute('playerName') . "</td><td>" . $player->getAttribute('position') . "</td><td>" . $player->getAttribute('injury') . "</td><td>" . $player->getAttribute('practiceStatus') . "</td><td>" . $player->getAttribute('gameStatus') . "</td><td>" . date("M j", strtotime($player->getAttribute('lastUpdate'))) . "</td></tr>";
				if ($player->getAttribute('notes') != ''){echo "<tr><td colspan='6' class='ffnnotes'>* " . $player->getAttribute('notes') . "</td></tr>";}
				}
			echo "</tbody></table>";
			}
		}
}

/**
 * Display the NFL Bye week schedule
 *
**/
function ffnByes(){
	$data = ffnCallAPI('byes', 'xml');
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		echo "<table class='ffntable ffntable-striped'><thead><tr><th>Week</th><th>Team on Bye</th></tr></thead><tbody>";
		foreach ($doc->getElementsByTagName("Week") AS $w)
			{
			$week = $w->getAttribute("number");
			foreach ($w->getElementsByTagName("Team") AS $team)
				{
				echo "<tr><td>$week</td><td><img src='http://www.fantasyfootballnerd.com/images/teams_small2/" . $team->getAttribute("code") . ".png' '> " . $team->getAttribute("name") . "</td></tr>";
				}
			}
		echo "</tbody></table>";
		}
}

/**
 * Display the upcoming week's NFL weather forecast
 *
**/
function ffnWeather(){
	$data = ffnCallAPI('weather', 'xml');
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		foreach ($doc->getElementsByTagName("Game") AS $game)
			{
			if ($game->getElementsByTagName("isDome")->item(0)->nodeValue == '1'){$wxImg = $game->getElementsByTagName("domeImg")->item(0)->nodeValue;}else{$wxImg = $game->getElementsByTagName("largeImg")->item(0)->nodeValue;}
			echo "<div class='ffnpanel ffnpanel-default ffntop'>
			  <div class='ffnpanel-heading ffncenter'><img border='0' src='http://www.fantasyfootballnerd.com/images/teams_small2/" . $game->getElementsByTagName("awayTeam")->item(0)->nodeValue . ".png' /> " . ffnGetTeamName($game->getElementsByTagName("awayTeam")->item(0)->nodeValue, true) . " @ <img src='http://www.fantasyfootballnerd.com/images/teams_small2/" . $game->getElementsByTagName("homeTeam")->item(0)->nodeValue . ".png' /> " . ffnGetTeamName($game->getElementsByTagName("homeTeam")->item(0)->nodeValue, true) . "</div>
			  <div class='ffnpanel-body'>
				<div class='ffnrow'>
					<div class='ffncol-md-6 ffncol-xs-12'>" . $game->getElementsByTagName("forecast")->item(0)->nodeValue . "<br><strong>High:</strong> " . $game->getElementsByTagName("high")->item(0)->nodeValue . "<br><strong>Low:</strong> " . $game->getElementsByTagName("low")->item(0)->nodeValue . "<br><strong>Wind:</strong> " . (int)$game->getElementsByTagName("windSpeed")->item(0)->nodeValue . " mph</div>
					<div class='ffncol-md-6 ffncol-xs-12 ffnright'><img src='$wxImg' alt='" . $game->getElementsByTagName("forecast")->item(0)->nodeValue . "' title='" . $game->getElementsByTagName("forecast")->item(0)->nodeValue . "' /><br><br>Week " . $game->getElementsByTagName("gameWeek")->item(0)->nodeValue . "<br>" . date("D, M j", strtotime($game->getElementsByTagName("gameDate")->item(0)->nodeValue)) . "</div>
				</div>
			  </div>
			</div>";
			}
		}
}

/**
 * Display the auction values for fantasy football auction drafts
 *
 * @param array $params An array of values provided in the shortcode. Expecting 'ppr' => 1 if PPR is being requested
**/
function ffnAuction($params){
	if (isset($params['ppr']) && $params['ppr'] == '1'){$isPPR = true;}else{$isPPR = false;}
	//-- Get the rankings --
	if ($isPPR){$params = array('ppr' => '1');}else{$params = array();}
	$data = ffnCallAPI('auction', 'xml', $params);
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		echo "<table class='ffntable ffntable-striped'><thead><tr><th>Price</th><th>Player</th><th>Team</th><th>Min Price</th><th>Max Price</th></tr></thead><tbody>";
		foreach ($doc->getElementsByTagName("Player") AS $player)
			{
			echo "<tr><td>\$" . $player->getAttribute('avgPrice') . "</td><td>" . $player->getAttribute('displayName') . "</td><td>" . $player->getAttribute('team') . "</td><td>\$" . $player->getAttribute("minPrice") . "</td><td>\$" . $player->getAttribute("maxPrice") . "</td></tr>";
			}
		echo "</tbody></table><p>*Prices are based upon a \$200 budget</p>";
		}
}

/**
 * Display the depth charts for all NFL teams
 *
**/
function ffnDepthCharts(){
	$data = ffnCallAPI('depth-charts', 'xml');
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		foreach ($doc->getElementsByTagName("Team") AS $team)
			{
			echo "<div class='ffnpanel ffnpanel-default ffntop'>
			  <div class='ffnpanel-heading ffncenter'><img border='0' src='http://www.fantasyfootballnerd.com/images/teams_small2/" . $team->getAttribute('code') . ".png' /> " . $team->getAttribute('name') . "</div>
			  <div class='ffnpanel-body'>";
			foreach ($team->getElementsByTagName('Position') AS $pos)
				{
				echo "<div class='ffnrow'>
						<div class='ffncol-md-2 ffncol-xs-2'>" . $pos->getAttribute("code") . "</div>
						<div class='ffncol-md-10 ffncol-xs-10'><ol>";
						foreach ($pos->getElementsByTagName('Player') AS $player){ echo "<li>" . $player->getAttribute('playerName') . "</li>"; }
						echo "</ol></div>";
				echo "</div>";
				}
			echo "</div></div>";
			}
		}
}

/**
 * Display the weekly rankings from FantasyFootballNerd.com
 *
 * @param array $params An array of values provided in the shortcode. Example: 'position' => 'RB', 'week' => '3', 'ppr' => 1
**/
function ffnWeeklyRankings($params){
	if (isset($params['ppr']) && $params['ppr'] == '1'){$isPPR = '1';}else{$isPPR = '0';}
	if (isset($params['position']) && in_array(strtoupper($params['position']), array('QB','RB','WR','TE','K','DEF'))){$position = $params['position'];}else{$position = 'QB';}
	if (isset($params['week']) && $params['week'] > 0 && $params['week'] <= 17){$week = $params['week'];}else{$week = 0;}
	if ($week < 1)
		{
		$data = ffnCallAPI('schedule', 'xml');
		$doc = DOMDocument::loadXML($data);
		if ($doc->getElementsByTagName("Error")->length > 0){ echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>"; }else{ $week = $doc->getElementsByTagName("CurrentWeek")->item(0)->nodeValue; }
		unset($doc, $data);
		}
	//-- Get the rankings --
	$params = array('position' => $position, 'week' => $week, 'ppr' => $isPPR);
	$data = ffnCallAPI('weekly-rankings', 'xml', $params);
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		echo "<table class='ffntable ffntable-striped'><thead><tr><th>Rank</th><th>Player</th><th>Team</th><th>Pos</th></tr></thead><tbody>";
		$r = 1;
		foreach ($doc->getElementsByTagName("Player") AS $player)
			{
			echo "<tr><td>" . $r . "</td><td>" . $player->getElementsByTagName('name')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('position')->item(0)->nodeValue . "</td></tr>";
			$r++;
			}
		echo "</tbody></table>";
		}
}

/**
 * Display the weekly projections from FantasyFootballNerd.com
 *
 * @param array $params An array of values provided in the shortcode. Expecting 'position' => 'QB' if QB projections are being requested. Otherwise RB, WR, TE, K, DEF. Also 'week' => '4' if requesting projections for week 4.
**/
function ffnWeeklyProjections($params){
	if (isset($params['position']) && in_array(strtoupper($params['position']), array('QB','RB','WR','TE','K','DEF'))){$position = strtoupper($params['position']);}else{$position = 'QB';}
	if (isset($params['week']) && $params['week'] > 0 && $params['week'] <= 17){$week = $params['week'];}else{$week = 0;}
	if ($week < 1)
		{
		$data = ffnCallAPI('schedule', 'xml');
		$doc = DOMDocument::loadXML($data);
		if ($doc->getElementsByTagName("Error")->length > 0){ echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>"; }else{ $week = $doc->getElementsByTagName("CurrentWeek")->item(0)->nodeValue; }
		unset($doc, $data);
		}
	//-- Get the projections --
	$params = array('position' => $position, 'week' => $week);
	$data = ffnCallAPI('weekly-projections', 'xml', $params);
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		echo "<table class='ffntable ffntable-striped'><thead>";
		if ($position == 'QB'){echo "<tr><th>Player</th><th>Team</th><th>Comp</th><th>Pass Yds</th><th>Pass TD</th><th>Int</th><th>Ru Yds</th><th>Ru TD</th></tr>";}
		if ($position == 'RB'){echo "<tr><th>Player</th><th>Team</th><th>Ru Att</th><th>Ru Yds</th><th>Ru TD</th><th>Rec</th><th>Rec Yds</th><th>Rec TD</th><th>Fum</th></tr>";}
		if ($position == 'WR'){echo "<tr><th>Player</th><th>Team</th><th>Rec</th><th>Rec Yds</th><th>Rec TD</th><th>Ru Att</th><th>Ru Yds</th><th>Ru TD</th><th>Fum</th></tr>";}
		if ($position == 'TE'){echo "<tr><th>Player</th><th>Team</th><th>Rec</th><th>Rec Yds</th><th>Rec TD</th><th>Ru Att</th><th>Ru Yds</th><th>Ru TD</th><th>Fum</th></tr>";}
		if ($position == 'K'){echo "<tr><th>Player</th><th>Team</th><th>FG</th><th>XP</th></tr>";}
		if ($position == 'DEF'){echo "<tr><th>Team</th><th>Sacks</th><th>Int</th><th>TD</th><th>Pts Allowed</th><th>Yds Allowed</th></tr>";}
		echo "</thead><tbody>";
		foreach ($doc->getElementsByTagName("Player") AS $player)
			{
			if ($position == 'QB'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('passCmp')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('passYds')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('passTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('passInt')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushYds')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushTD')->item(0)->nodeValue . "</td></tr>";}
			if ($position == 'RB'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushAtt')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushYds')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('receptions')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recYds')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fumblesLost')->item(0)->nodeValue . "</td></tr>";}
			if ($position == 'WR'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('receptions')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recYds')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushAtt')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushYds')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fumblesLost')->item(0)->nodeValue . "</td></tr>";}
			if ($position == 'TE'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('receptions')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recYds')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('recTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushAtt')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushYds')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('rushTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fumblesLost')->item(0)->nodeValue . "</td></tr>";}
			if ($position == 'K'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('team')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('fg')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('xp')->item(0)->nodeValue . "</td></tr>";}
			if ($position == 'DEF'){echo "<tr><td>" . $player->getElementsByTagName('displayName')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('defSack')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('defInt')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('defTD')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('defPA')->item(0)->nodeValue . "</td><td>" . $player->getElementsByTagName('defYdsAllowed')->item(0)->nodeValue . "</td></tr>";}
			}
		echo "</tbody></table>";
		}
}

/**
 * Display the defensive rankings for all NFL teams
 *
**/
function ffnDefensiveRankings(){
	$data = ffnCallAPI('defense-rankings', 'xml');
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		foreach ($doc->getElementsByTagName("Defense") AS $team)
			{
			echo "<div class='ffnpanel ffnpanel-default ffntop'>
			  <div class='ffnpanel-heading ffncenter'><img border='0' src='http://www.fantasyfootballnerd.com/images/teams_small2/" . $team->getAttribute('team') . ".png' /> " . $team->getAttribute('teamName') . "</div>
			  <div class='ffnpanel-body'>
				<div class='ffnrow'>
					<div class='ffncol-md-6 ffncol-xs-6'>
						<p><strong>Defensive Rankings</strong></p>
						<p>Total Yds/Gm: " . ffnordinal($team->getAttribute('ypgRank')) . "<br>Pass Yds/Gm: " . ffnordinal($team->getAttribute('pypgRank')) . "<br>Rush Yds/Gm: " . ffnordinal($team->getAttribute('rypgRank')) . "<br>Points Allowed/Gm: " . ffnordinal($team->getAttribute('ppgRank')) . "</p>
					</div>
					<div class='ffncol-md-6 ffncol-xs-6'>
						<p><strong>Yards Allowed Per Game</strong></p>
						<p>Total: " . $team->getAttribute('yardsPerGame') . "<br>Passing: " . $team->getAttribute('passingYardsPerGame') . "<br>Rushing: " . $team->getAttribute('rushingYardsPerGame') . "<br>Points Allowed: " . $team->getAttribute('pointsPerGame') . "</p>
					</div>
				</div>
			  </div>
			  </div>";
			}
		}
}

/**
 * List the Gameday Inactives
 *
 * @param array $params An array of values provided in the shortcode. Expecting 'week' => '4' if requesting inactives for week 4. If empty, will retrieve current week.
**/
function ffnGamedayInactives($params){
	if (isset($params['week']) && $params['week'] > 0 && $params['week'] <= 17){$week = $params['week'];}else{$week = 0;}
	if ($week < 1)
		{
		$data = ffnCallAPI('schedule', 'xml');
		$doc = DOMDocument::loadXML($data);
		if ($doc->getElementsByTagName("Error")->length > 0){ echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>"; }else{ $week = $doc->getElementsByTagName("CurrentWeek")->item(0)->nodeValue; }
		unset($doc, $data);
		}
	$params = array('week' => $week);
	$data = ffnCallAPI('inactives', 'xml', $params);
	$doc = DOMDocument::loadXML($data);
	if ($doc->getElementsByTagName("Error")->length > 0)
		{
		echo "<p>Error: " . $doc->getElementsByTagName("Error")->item(0)->nodeValue . "</p>";
		}else{
		echo "<table class='ffntable ffntable-striped'><thead><tr><th>Player</th><th>Team</th><th>Pos</th></tr></thead><tbody>";
		foreach ($doc->getElementsByTagName("Player") AS $player)
			{
			echo "<tr><td>" . $player->getAttribute('playerName') . "</td><td>" . $player->getAttribute('team') . "</td><td>" . $player->getAttribute('position') . "</td></tr>";
			}
		echo "</tbody></table>";
		}
}

/**
 * Utility function to call the FFN API
 *
 * @param string $service The service to call
 * @param string $format Either "xml" or "json". Default is xml
 * @param array $options Optional: An array where the values are the url options
**/
function ffnCallAPI($service, $format, $options = array()){
	//-- Build the URL --
	$apiKey = ffnGetKey();
	$url = 'http://www.fantasyfootballnerd.com/service/' . $service . '/' . $format . '/' . $apiKey;
	if (count($options) > 0){$url .= '/' . implode('/', $options);}
	$response = wp_remote_get($url);
	if (is_array($response)){
	  $header = $response['headers'];
	  $body = $response['body'];
	}
	if (!isset($body) && $format == 'xml'){$body = '<?xml version="1.0" encoding="UTF-8" ?><Error>No Response</Error>';}
	if (!isset($body) && $format == 'json'){$body = json_decode(array());}
	if (strtolower($apiKey) == 'test'){echo "<p>** TEST DATA **</p><p>To retrieve live data, please create a free API key. Instructions for working with the FFN WordPress plugin can be found <a href='http://www.fantasyfootballnerd.com/wordpress' target='_blank'>here</a>.</p>";}
	return $body;
}

/**
 * Utility function to get the FFN API Key
 *
**/
function ffnGetKey(){ $key = get_option('ffn_api_key', 'test'); if ($key == ''){$key = 'test';} return $key; }

/**
 * Utility function to get the name of the team
 *
 * @param string $team The team abbreviation
 * @param boolean $fullname OPTIONAL: Return the full name (ie, Green Bay Packers instead of just Green Bay)
**/
function ffnGetTeamName($team, $fullname = false){
	$teamNames = array("Arizona", "Atlanta", "Baltimore", "Buffalo", "Carolina", "Chicago", "Cincinnati", "Cleveland", "Dallas", "Denver", "Detroit", "Green Bay", "Houston", "Indianapolis", "Jacksonville", "Kansas City", "Miami", "Minnesota", "N.Y. Giants", "N.Y. Jets", "New England", "New Orleans", "Oakland", "Philadelphia", "Pittsburgh", "San Diego", "San Francisco", "Seattle", "St. Louis", "Tampa Bay", "Tennessee", "Washington");
	$fullTeamNames = array("Arizona Cardinals", "Atlanta Falcons", "Baltimore Ravens", "Buffalo Bills", "Carolina Panthers", "Chicago Bears", "Cincinnati Bengals", "Cleveland Browns", "Dallas Cowboys", "Denver Broncos", "Detroit Lions", "Green Bay Packers", "Houston Texans", "Indianapolis Colts", "Jacksonville Jaguars", "Kansas City Chiefs", "Miami Dolphins", "Minnesota Vikings", "N.Y. Giants", "N.Y. Jets", "New England Patriots", "New Orleans Saints", "Oakland Raiders", "Philadelphia Eagles", "Pittsburgh Steelers", "San Diego Chargers", "San Francisco 49ers", "Seattle Seahawks", "St. Louis Rams", "Tampa Bay Buccaneers", "Tennessee Titans", "Washington Redskins");
	$myNames = array("ARI", "ATL", "BAL", "BUF", "CAR", "CHI", "CIN", "CLE", "DAL", "DEN", "DET", "GB", "HOU", "IND", "JAC", "KC", "MIA", "MIN", "NYG", "NYJ", "NE", "NO", "OAK", "PHI", "PIT", "SD", "SF", "SEA", "STL", "TB", "TEN", "WAS");
	if (in_array($team, $myNames))
		{
		$key = array_search($team, $myNames);
		if ($fullname){	return $fullTeamNames[$key]; }else{ return $teamNames[$key]; }
		}
}

/**
 * Add ordinal to number
 *
**/
function ffnordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}

/**
 * Admin Menu
**/
if (is_admin()){ // admin actions
  add_action('admin_menu', 'ffn_create_menu');
  add_action('admin_init', 'register_ffnsettings');
}

function ffn_create_menu() { add_submenu_page('options-general.php', 'FFN Settings', 'FFN Settings', 'administrator', 'ffn_settings', 'ffn_settings_page'); }

function register_ffnsettings() { register_setting( 'ffn-settings-group', 'ffn_api_key' ); }

function ffn_settings_page() {
?>
<div class="wrap">
<h2>Fantasy Football Settings</h2>
<p>It's quick and easy to add fantasy football related content to your website, but you'll need to register for an API key on FantasyFootballNerd.com.</p>
<p><a href="http://www.fantasyfootballnerd.com/fantasy-football-api" target="_blank">Click here to request an API key.</a></p>
<p>If you do not use a live API key, a test key will be used; however, TEST DATA IS NOT CURRENT. It is merely used to show you what the data would look like.</p>
<p>*Please note that your API key from Fantasy Football Nerd (FFN) gives you access to the same content that your FFN account would give you access to.</p>
<form method="post" action="options.php">
    <?php settings_fields( 'ffn-settings-group' ); ?>
    <?php do_settings_sections( 'ffn-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Your API Key from FantasyFootballNerd.com</th>
        <td><input type="text" name="ffn_api_key" value="<?php echo esc_attr( get_option('ffn_api_key') ); ?>" /></td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
</div>
<?php } ?>