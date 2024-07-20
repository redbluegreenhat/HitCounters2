<?php

namespace MediaWiki\Extension\HitCounters2\Specials;

use ManualLogEntry;
use MediaWiki\SpecialPage\FormSpecialPage;
use Wikimedia\Rdbms\IConnectionProvider;

class SpecialResetHitCounter extends FormSpecialPage {

	private $connectionProvider;

	private $page;

	public function __construct(
		IConnectionProvider $connectionProvider
	) {
		$this->connectionProvider = $connectionProvider;
		parent::__construct( 'ResetHitCounter', 'hitcounters2-resetcounter' );
	}

	public function getGroupName() {
		return 'wiki';
	}

	public function doesWrites() {
		return 'true';
	}

	public function getDisplayFormat() {
		return 'ooui';
	}

	public function requiresUnblock() {
		return true;
	}

	protected function getFormFields() {
		return [
			'page' => [
				'type' => 'title',
				'default' => '',
				'label-message' => 'hitcounters2-enterpage',
				'name' => 'user',
				'required' => true,
			],
			'reason' => [
				'type' => 'text',
				'default' => '',
				'label-message' => 'hitcounters2-enterreason',
				'name' => 'reason',
				'required' => true,
			],
		];
	}

	public function onSubmit( array $formData ) {
		$pageID = Title::newFromTextThrow( $formData['page'] )->getId();
		if ( $pageID === 0 ) {
			return [ 'hitcounters2-resethitcounter-pageidzero' ];
		}
		$dbw = $this->connectionProvider->getPrimaryDatabase();
		$dbw->newUpdateQueryBuilder()
			->update( 'hc2_hitcounter' )
			->set( [ 'hc_hits' => 0 ] )
			->where( [ 'hc_pageid' => $pageID ] )
			->caller( __METHOD__ )->execute();
		if ( $dbw->affectedRows() === 0 ) {
			// Trying to reset the hit counter for a page not yet in the table
			return [ 'hitcounters2-resethitcounter-pagenotintable' ];
		}
		$this->page = $formData['page'];
		return true;
	}

	public function onSuccess() {
		$this->getOutput()->addWikiMsg( 'hitcounters2-resethitcounter-success', $this->page );
	}
}