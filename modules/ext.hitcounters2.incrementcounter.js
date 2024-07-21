$( function () {
	var restApi = new mw.Rest();
	var pageID = mw.config.get( 'wgArticleId' );
	if ( pageID > 0 ) {
		restApi.post( '/hitcounters2/v0/increment/' + pageID, {
			increment: true
		} );
	}
} )
