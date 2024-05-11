<?php

namespace MediaWiki\Extension\HitCounters2;

use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\GetMagicVariableIDsHook;

class Hooks implements
	BeforePageDisplayHook,
	GetMagicVariableIDsHook
{
	public function onBeforePageDisplay( $out, $skin ): void {
		$out->addModules( 'ext.hitcounters2.incrementcounter.js' );
	}
	public function onGetMagicVariableIDs( &$variableIDs ) {
		$variableIDs[] = 'numberofhits';
	}
}
