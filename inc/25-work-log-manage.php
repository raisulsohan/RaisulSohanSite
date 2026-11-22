<?php
/**
 * Keeping the Work Log from the page itself: what someone who may manage it
 * sees on /work/ while signed in.
 *
 * The page then lists every piece, hidden ones too (marked), with Hide or
 * Show, Edit and Delete under each card, in the pop-up player and in the
 * editor view, and a bar to add a piece or narrow the list to the shown or
 * the hidden ones. /work/?visitor=1 shows that same person the page exactly
 * as a visitor gets it. Everything goes through the routes of
 * inc/24-work-log-admin.php, which check the right again.
 *
 * A visitor gets none of this: no markup, no script, no hidden piece. The
 * page is sent uncached to anyone who may manage it (rs_work_log_render()),
 * so no cache can hand this version to a visitor.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Whether this visitor may manage the log and is not looking at it as a visitor. */
function rs_work_log_manage() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only chooses a view.
	return rs_wla_can() && empty( $_GET['visitor'] );
}

/**
 * The pieces the page lists: the shown ones, and for someone managing the
 * log the hidden ones too.
 *
 * @return array[]
 */
function rs_work_log_page_items() {
	return rs_work_log_manage() ? rs_work_log_all() : rs_work_log_items();
}

/** The stylesheet, in the page's head. */
function rs_work_log_manage_head() {
	if ( ! rs_wla_can() ) {
		return;
	}
	// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- A standalone page without wp_head().
	printf( '<link rel="stylesheet" href="%s">' . "\n", esc_url( RS_URI . '/assets/work-log-manage.min.css?ver=' . RS_VERSION ) );
}

/**
 * The bar under the page's own controls.
 *
 * @param int $all    Every piece.
 * @param int $hidden The hidden ones.
 */
function rs_work_log_manage_bar( $all, $hidden ) {
	if ( ! rs_work_log_manage() ) {
		return;
	}
	?>
	<div class="wrap adm-bar" id="admBar">
		<button type="button" class="adm-btn adm-btn--acc" id="admAdd"><?php echo esc_html( rs_wla_t( '+ কাজ যোগ করুন', '+ Add a piece' ) ); ?></button>
		<div class="seg adm-seg" role="group" aria-label="<?php echo esc_attr( rs_wla_t( 'কোনগুলো দেখবেন', 'Which pieces to list' ) ); ?>">
			<button type="button" data-vis="all" aria-pressed="true"><?php echo esc_html( rs_wla_t( 'সব', 'All' ) ); ?> <b data-n="all"><?php echo esc_html( rs_bn_digits( $all ) ); ?></b></button>
			<button type="button" data-vis="shown" aria-pressed="false"><?php echo esc_html( rs_wla_t( 'পেজে আছে', 'Shown' ) ); ?> <b data-n="shown"><?php echo esc_html( rs_bn_digits( $all - $hidden ) ); ?></b></button>
			<button type="button" data-vis="hidden" aria-pressed="false"><?php echo esc_html( rs_wla_t( 'লুকানো', 'Hidden' ) ); ?> <b data-n="hidden"><?php echo esc_html( rs_bn_digits( $hidden ) ); ?></b></button>
		</div>
		<span class="adm-note"><?php echo esc_html( rs_wla_t( 'এই বারটা শুধু আপনি দেখছেন', 'Only you see this bar' ) ); ?></span>
		<a class="adm-btn" href="<?php echo esc_url( add_query_arg( 'visitor', '1', rs_work_log_url() ) ); ?>"><?php echo esc_html( rs_wla_t( 'ভিজিটরের মতো দেখুন', 'View as a visitor' ) ); ?></a>
		<a class="adm-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=rs-work-log' ) ); ?>"><?php echo esc_html( rs_wla_t( 'ড্যাশবোর্ড', 'Dashboard' ) ); ?></a>
	</div>
	<?php
}

/** The fields an added and an edited piece share. */
function rs_work_log_manage_fields() {
	?>
	<label class="span2"><?php echo esc_html( rs_wla_t( 'নাম (ইংরেজি)', 'Title (English)' ) ); ?> <input type="text" name="title" required></label>
	<label class="span2"><?php echo esc_html( rs_wla_t( 'নাম (বাংলা, না দিলে ইংরেজিটা যাবে)', 'Title (Bengali; the English is used if empty)' ) ); ?> <input type="text" name="title_bn"></label>
	<label><?php echo esc_html( rs_wla_t( 'তারিখ', 'Date' ) ); ?> <input type="date" name="date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" required></label>
	<label><?php echo esc_html( rs_wla_t( 'ধরন', 'Kind' ) ); ?>
		<select name="kind">
			<?php foreach ( rs_work_log_kinds() as $k => $l ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $k, 'anim' ); ?>><?php echo esc_html( rs_wla_t( $l['bn'], $l['en'] ) ); ?></option>
			<?php endforeach; ?>
		</select></label>
	<label class="span2"><?php echo esc_html( rs_wla_t( 'এক লাইনের বর্ণনা (বাংলা)', 'One line about it (Bengali)' ) ); ?> <input type="text" name="desc_bn"></label>
	<label class="span2"><?php echo esc_html( rs_wla_t( 'এক লাইনের বর্ণনা (ইংরেজি, না দিলে বাংলাটা যাবে)', 'One line about it (English; the Bengali is used if empty)' ) ); ?> <input type="text" name="desc_en"></label>
	<label class="check span2"><input type="checkbox" name="client"> <?php echo esc_html( rs_wla_t( 'ক্লায়েন্টের জন্য করা', 'Made for a client' ) ); ?></label>
	<?php
}

/** The dialogs and the script, at the end of the page; as a visitor, only the way back. */
function rs_work_log_manage_tail() {
	if ( ! rs_wla_can() ) {
		return;
	}

	if ( ! rs_work_log_manage() ) {
		printf(
			'<a class="adm-back" href="%s">%s</a>' . "\n",
			esc_url( rs_work_log_url() ),
			esc_html( rs_wla_t( 'ভিজিটর ভিউ চলছে · নিয়ন্ত্রণে ফিরুন', 'Visitor view · back to the controls' ) )
		);
		return;
	}
	?>
<dialog class="adm-dlg" id="admAddDlg" aria-labelledby="admAddH">
	<form class="adm-form" id="admAddForm">
		<h2 id="admAddH"><?php echo esc_html( rs_wla_t( 'নতুন কাজ যোগ করুন', 'Add a piece' ) ); ?></h2>
		<p><?php echo esc_html( rs_wla_t( 'একটা mp4 ভিডিও দিন, অথবা কাজের পুরো ফোল্ডারটা zip করে, অথবা একটা html ফাইল। বড় ফাইলও চলবে।', 'Choose an mp4 video, the piece\'s whole folder as a ZIP, or a single HTML file. Large files are fine.' ) ); ?></p>
		<label class="span2"><?php echo esc_html( rs_wla_t( 'ফাইল', 'File' ) ); ?> <input type="file" name="file" accept=".zip,.html,.htm,.mp4,.webm,.m4v,.mov" required></label>
		<?php rs_work_log_manage_fields(); ?>
		<label class="span2"><?php echo esc_html( rs_wla_t( 'পোস্টার ছবি (না দিলে ভিডিও থেকে নেওয়া হবে)', 'Poster image (taken from the video if empty)' ) ); ?> <input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp"></label>
		<label class="check span2"><input type="checkbox" name="hidden"> <?php echo esc_html( rs_wla_t( 'আপাতত লুকিয়ে রাখুন', 'Keep it hidden for now' ) ); ?></label>
		<div class="adm-foot span2">
			<span class="adm-msg" aria-live="polite"></span>
			<progress max="100" value="0" hidden></progress>
			<button type="button" class="adm-btn" data-close><?php echo esc_html( rs_wla_t( 'বাদ দিন', 'Cancel' ) ); ?></button>
			<button type="submit" class="adm-btn adm-btn--acc"><?php echo esc_html( rs_wla_t( 'যোগ করুন', 'Add' ) ); ?></button>
		</div>
	</form>
</dialog>

<dialog class="adm-dlg" id="admEditDlg" aria-labelledby="admEditH">
	<form class="adm-form" id="admEditForm">
		<h2 id="admEditH"><?php echo esc_html( rs_wla_t( 'কাজটা বদলান', 'Edit the piece' ) ); ?></h2>
		<?php rs_work_log_manage_fields(); ?>
		<div class="adm-foot span2">
			<span class="adm-msg" aria-live="polite"></span>
			<button type="button" class="adm-btn" data-close><?php echo esc_html( rs_wla_t( 'বাদ দিন', 'Cancel' ) ); ?></button>
			<button type="submit" class="adm-btn adm-btn--acc"><?php echo esc_html( rs_wla_t( 'সেভ করুন', 'Save' ) ); ?></button>
		</div>
	</form>
</dialog>

<dialog class="adm-dlg adm-dlg--s" id="admDelDlg" aria-labelledby="admDelH">
	<div class="adm-form">
		<h2 id="admDelH"><?php echo esc_html( rs_wla_t( 'কাজটা মুছে ফেলবেন?', 'Delete this piece?' ) ); ?></h2>
		<p><b id="admDelName"></b><br><span id="admDelInfo"></span></p>
		<p class="adm-warn"><?php echo esc_html( rs_wla_t( 'সাইট থেকে এর সব ফাইল মুছে যাবে, আর ফেরানো যাবে না। শুধু পেজ থেকে সরাতে চাইলে লুকান।', 'All its files go from the site for good. To only take it off the page, hide it instead.' ) ); ?></p>
		<div class="adm-foot span2">
			<span class="adm-msg" aria-live="polite"></span>
			<button type="button" class="adm-btn" data-close><?php echo esc_html( rs_wla_t( 'বাদ দিন', 'Cancel' ) ); ?></button>
			<button type="button" class="adm-btn" id="admDelHide"><?php echo esc_html( rs_wla_t( 'শুধু লুকান', 'Only hide it' ) ); ?></button>
			<button type="button" class="adm-btn adm-btn--bad" id="admDelYes"><?php echo esc_html( rs_wla_t( 'মুছে ফেলুন', 'Delete' ) ); ?></button>
		</div>
	</div>
</dialog>

<div class="adm-toast" id="admToast" role="status" hidden></div>

<script>window.RS_WL = <?php echo wp_json_encode( rs_work_log_admin_config() ); ?>;</script>
	<?php
	foreach ( array( 'work-log-api', 'work-log-manage' ) as $name ) {
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- A standalone page without wp_footer().
		printf( '<script src="%s"></script>' . "\n", esc_url( RS_URI . '/assets/' . $name . '.min.js?ver=' . RS_VERSION ) );
	}
}
