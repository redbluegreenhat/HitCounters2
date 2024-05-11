<?php

namespace MediaWiki\Extension\HitCounters2;

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class Installer implements LoadExtensionSchemaUpdatesHook {

	public function onLoadExtensionSchemaUpdates( $updater ) {
		$dbType = $updater->getDB()->getType();
		$dir = __DIR__ . '/sql';

		$updater->addExtensionTable( 'hitcounters2_hitcounter', "$dir/$dbType/tables-generated.sql" );
	}
}

