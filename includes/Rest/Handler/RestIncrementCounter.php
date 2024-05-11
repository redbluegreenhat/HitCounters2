<?php

namespace MediaWiki\Extension\HitCounters2\Rest\Handler;

use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\Validator\JsonBodyValidator;
use MediaWiki\Rest\Validator\UnsupportedContentTypeBodyValidator;
use Wikimedia\MessageValue\MessageValue;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\IConnectionProvider;
use Wikimedia\Rdbms\UpdateQueryBuilder;

/*
 * Increments the hit counter for the specified page ID
 * POST /hitcounters2/v0/increment/{id}
 */
class RestIncrementCounter extends SimpleHandler {

	private $connectionProvider;

	private $wikiPageFactory;

	public function __construct(
		IConnectionProvider $connectionProvider,
		WikiPageFactory $wikiPageFactory
	) {
		$this->connectionProvider = $connectionProvider;
		$this->wikiPageFactory = $wikiPageFactory;
	}

	public function run( int $pageID ) {
		$body = $this->getValidatedBody();
		if ( $pageID === 0 ) {
			return $this->getResponseFactory()->createLocalizedHttpError( 400, new MessageValue( 'hitcounters2-rest-pageidzero' ) );
		}
		if ( $pageID !== $body['id'] ) {
			return $this->getResponseFactory()->createLocalizedHttpError( 400, new MessageValue( 'hitcounters2-rest-pageidmismatch' ) );
		}
		if ( !$this->wikiPageFactory->newFromId( $pageID ) ) {
			return $this->getResponseFactory()->createLocalizedHttpError( 400, new MessageValue( 'hitcounters2-rest-pagedoesnotexist' ) );
		}

		$dbw = $this->connectionProvider->getPrimaryDatabase();

		$dbw->newUpdateQueryBuilder()
			->update( 'hc2_hitcounter' )
			->set( ['hc_hits=hc_hits+1' ] )
			->where( [ 'hc_pageid' => $pageID ] )
			->caller( __METHOD__ )->execute();

		if ( $dbw->affectedRows() == 0  ) {
			$dbw->newInsertQueryBuilder()
				->insertInto( 'hc2_hitcounter' )
				->row( [
					'hc_pageid' => $pageID,
					'hc_hits' => 1,
				] )
				->caller( __METHOD__ )->execute();
		}
	}

	public function needsWriteAccess() {
		return true;
	}

	public function requireSafeAgainstCsrf() {
		/*
		 * The REST API, by default, is really just intended for usage by OAuth applications
		 * While we could use \MediaWiki\Rest\TokenAwareHandlerTrait to allow it to accept CSRF tokens, we arguably don't need to here
		 * This just increments a number in the database, we don't care who did it, or even if it was done via a CSRF attack
		 *
		 * The above comment must be kept in mind when adding any other functions to this endpoint
		 */
		return false;
	}

	public function getParamSettings() {
		return 'id' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

	public function getBodyValidator( $contentType ) {
		if ( $contentType !== 'application/json' ) {
			return new UnsupportedContentTypeBodyValidator( $contentType );
		}
		return new JsonBodyValidator( [
			'id' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
			],
		] );
	}
}
