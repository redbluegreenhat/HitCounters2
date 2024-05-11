$( function () {
	var restApi = new mw.Rest();
	restApi.post( '/hitcounters2/v0/increment/' + mw.config.get( 'wgArticleId' ), {
		increment: true
	} );
} )
