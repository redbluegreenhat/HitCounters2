<?php

namespace MediaWiki\Extension\HitCounters2;

use ManualLogEntry;
use MediaWiki\Hook\GetMagicVariableIDsHook;
use MediaWiki\Hook\ParserGetVariableValueSwitchHook;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use Wikimedia\Rdbms\IConnectionProvider;

class Hooks implements
	BeforePageDisplayHook,
	GetMagicVariableIDsHook,
	PageDeleteCompleteHook,
	ParserGetVariableValueSwitchHook,
{

	private $connectionProvider;

	public function __construct(
		IConnectionProvider $connectionProvider
	) {
		$this->connectionProvider = $connectionProvider;
	}

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
				->where( [ 'hc_pageid' => $pageID ] )
				->caller( __METHOD__ )->fetchField();
			if ( !$hitCount ) {
				// Page has not yet been recorded in the database
				$ret = 0;
				return;
			}
			$ret = (int)$hitCount;
		}
	}

	public function onPageDeleteComplete(
		ProperPageIdentity $page,
		Authority $deleter,
		string $reason,
		int $pageID,
		RevisionRecord $deletedRev,
		ManualLogEntry $logEntry,
		int $archivedRevisionCount
	) {
		/*
		 * Check if the page ID exists in the hc2_hitcounter table.
		 * If so, delete it.
		 */
		$dbw = $this->connectionProvider->getPrimaryDatabase();
		$pageExists = $dbw->newSelectQueryBuilder()
			->select( [ 'hc_hits' ] )
			->from( 'hc2_hitcounter' )
			->where( [ 'hc_pageid' => $pageID ] )
			->caller( __METHOD__ )->fetchField();
		if ( !$pageExists ) {
			// Nothing to do
		} else {
			$dbw->newDeleteQueryBuilder()
				->deleteFrom( 'hc2_hitcounter' )
				->where( [ 'hc_pageid' => $pageID ] )
				->caller( __METHOD__ )->execute();
		}
	}
}
