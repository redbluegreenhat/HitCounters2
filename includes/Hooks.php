<?php

namespace MediaWiki\Extension\HitCounters2;

use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\GetMagicVariableIDsHook;
use MediaWiki\Hook\ParserGetVariableValueSwitchHook;

class Hooks implements
	BeforePageDisplayHook,
	GetMagicVariableIDsHook,
	ParserGetVariableValueSwitchHook
{
	public function onBeforePageDisplay( $out, $skin ): void {
		$out->addModules( 'ext.hitcounters2.incrementcounter' );
	}

	public function onGetMagicVariableIDs( &$variableIDs ) {
		$variableIDs[] = 'numberofhits';
	}

	public function onParserGetVariableValueSwitch( $parser, &$variableCache, $magicWordId, &$ret, $frame ) {
		if ( $magicWordId === 'numberofhits' ) {
			$dbr = $this->connectionProvider->getReplicaDatabase();
			$pageID = $frame->getTitle()->getId();
			$hitCount = $dbr->newSelectQueryBuilder()
				->select( [ 'hc_hits' ] )
				->from( 'hc2_hitcounter' )
				->where( [ 'hc_pageid' => $pageID ])
				->caller( __METHOD__ )->fetchField();
			if ( !$hitCount ) {
				// Page has not yet been recorded in the database
				$ret = 0;
				return;
			}
			$ret = (int)$hitCount;
		}
	}
}
