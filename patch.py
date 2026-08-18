import re

with open('assets/app.js', 'r', encoding='utf-8') as f:
    c = f.read()

old_block = r'''	\( function \(\) \{
		var host = \$\( '#rs-resume' \);

		if \( ! host \) \{
			return;
		\}

		var map = readPositions\(\);
		var keys = Object\.keys\( map \);
		var last = keys\.length \? map\[ keys\[ keys\.length - 1 \] \] : null;

		/\* Entries from before titles were stored have nothing to show\. \*/
		if \( ! last \|\| 'number' === typeof last \|\| ! last\.n \|\| ! last\.u \) \{
			return;
		\}

		var id = keys\[ keys\.length - 1 \]\.slice\( 1 \);

		host\.innerHTML =
			'<div class="rs-resume">' \+
			'<a class="rs-resume__link" href="' \+ escapeHtml\( last\.u \) \+
			'" data-rs-post="' \+ escapeHtml\( id \) \+ '">' \+
			'<span class="rs-resume__label">আপনি পড়ছিলেন</span>' \+
			'<span class="rs-resume__title">' \+ escapeHtml\( last\.n \) \+ '</span>' \+
			'</a>' \+
			'<button class="rs-resume__close" type="button" aria-label="সরিয়ে দিন">&times;</button>' \+
			'</div>';

		\$\( '\.rs-resume__close', host \)\.addEventListener\( 'click', function \(\) \{
			/\* Dropped rather than hidden: they have said they are not
			   going back to it\. \*/
			var fresh = readPositions\(\);

			delete fresh\[ 'p' \+ id \];
			store\( POS_KEY, JSON\.stringify\( fresh \) \);

			host\.innerHTML = '';
		\} \);
	\}\(\) \);'''

new_block = '''	function renderResume() {
		var host = $( '#rs-resume' );
		if ( ! host ) { return; }

		var map = readPositions();
		var keys = Object.keys( map );

		var targetKey = null;
		var currentCat = parseInt( cfg.catId, 10 ) || 0;
		if ( currentCat > 0 ) {
			for ( var i = keys.length - 1; i >= 0; i-- ) {
				var entry = map[ keys[ i ] ];
				if ( entry && entry.c === currentCat ) {
					targetKey = keys[ i ];
					break;
				}
			}
		}

		if ( ! targetKey && keys.length > 0 ) {
			targetKey = keys[ keys.length - 1 ];
		}

		var last = targetKey ? map[ targetKey ] : null;

		if ( ! last || 'number' === typeof last || ! last.n || ! last.u ) {
			host.innerHTML = '';
			return;
		}

		var id = targetKey.slice( 1 );

		host.innerHTML =
			'<div class="rs-resume">' +
			'<a class="rs-resume__link" href="' + escapeHtml( last.u ) +
			'" data-rs-post="' + escapeHtml( id ) + '">' +
			'<span class="rs-resume__label">আপনি পড়ছিলেন</span>' +
			'<span class="rs-resume__title">' + escapeHtml( last.n ) + '</span>' +
			'</a>' +
			'<button class="rs-resume__close" type="button" aria-label="সরিয়ে দিন">&times;</button>' +
			'</div>';

		$( '.rs-resume__close', host ).addEventListener( 'click', function () {
			var fresh = readPositions();
			delete fresh[ 'p' + id ];
			store( POS_KEY, JSON.stringify( fresh ) );
			host.innerHTML = '';
		} );
	}
	renderResume();'''

c = re.sub(old_block.replace('\n', r'\r?\n'), new_block, c, flags=re.MULTILINE)

with open('assets/app.js', 'w', encoding='utf-8') as f:
    f.write(c)
