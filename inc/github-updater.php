<?php
/**
 * GitHub Theme Updater
 *
 * Checks a public GitHub repository for newer theme versions and plugs
 * into WordPress's native update system. When a new version is found
 * (by comparing the Version header in style.css), the standard
 * "Update Available" notice appears in Appearance > Themes and the
 * Dashboard > Updates screen. Clicking "Update Now" downloads the
 * zip from GitHub and installs it through WordPress's own upgrader.
 *
 * Usage:  require_once get_template_directory() . '/inc/github-updater.php';
 *
 * The updater reads the GitHub repo URL from the "Theme URI" header
 * in style.css (e.g. https://github.com/raisulsohan/RaisulSohanSite),
 * so there is nothing to configure manually.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RS_GitHub_Updater
 */
final class RS_GitHub_Updater {

	/** @var string  GitHub owner/repo, e.g. "raisulsohan/RaisulSohanSite". */
	private $repo;

	/** @var string  The theme's directory name (slug). */
	private $slug;

	/** @var string  Currently installed version. */
	private $current_version;

	/** @var string  Transient key for caching the remote version check. */
	private $transient_key;

	/**
	 * What GitHub said during this page load.
	 *
	 * Null means nobody has looked yet. Anything else — including false,
	 * meaning the question could not be answered — is a settled answer and
	 * is handed back without asking again.
	 *
	 * @var array|false|null
	 */
	private $remote = null;

	/**
	 * Boot the updater.
	 */
	public function __construct() {
		$theme = wp_get_theme();

		$this->slug            = $theme->get_stylesheet();
		$this->current_version = $theme->get( 'Version' );
		$this->transient_key   = 'rs_gh_update_' . $this->slug;

		/* Parse owner/repo from the Theme URI header.
		   Expected format: https://github.com/owner/repo  */
		$theme_uri = $theme->get( 'ThemeURI' );
		$path      = trim( (string) wp_parse_url( $theme_uri, PHP_URL_PATH ), '/' );

		if ( ! $path || substr_count( $path, '/' ) < 1 ) {
			return; // Not a valid GitHub URL — bail silently.
		}

		$this->repo = $path; // e.g. "raisulsohan/RaisulSohanSite"

		/* Hook into WordPress's update system (both get and set). */
		add_filter( 'site_transient_update_themes',         array( $this, 'check_update' ) );
		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_update' ) );
		add_filter( 'themes_api',                           array( $this, 'theme_info' ), 10, 3 );
		add_filter( 'upgrader_source_selection',            array( $this, 'fix_directory_name' ), 10, 4 );

		/* Clear cached data after an update completes. */
		add_action( 'upgrader_process_complete', array( $this, 'clear_transient' ), 10, 2 );
	}

	/* ------------------------------------------------------------------
	   1. Check for updates
	   ------------------------------------------------------------------ */

	/**
	 * Called by WordPress when checking or reading theme updates.
	 * Compares the GitHub version with the installed version.
	 *
	 * @param mixed $transient  The update_themes transient object.
	 * @return object
	 */
	public function check_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		$current_ver = ! empty( $transient->checked[ $this->slug ] )
			? $transient->checked[ $this->slug ]
			: $this->current_version;

		$remote = $this->get_remote_version();

		if (
			$remote &&
			! empty( $remote['version'] ) &&
			version_compare( $remote['version'], $current_ver, '>' )
		) {
			if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
				$transient->response = array();
			}

			$transient->response[ $this->slug ] = array(
				'theme'       => $this->slug,
				'new_version' => $remote['version'],
				'url'         => 'https://github.com/' . $this->repo,
				'package'     => $this->get_zip_url( isset( $remote['sha'] ) ? $remote['sha'] : '' ),
			);
		}

		return $transient;
	}

	/* ------------------------------------------------------------------
	   2. Theme information popup
	   ------------------------------------------------------------------ */

	/**
	 * Supplies data for the "View version X details" link that appears
	 * in the update notice.
	 *
	 * @param false|object|array $result
	 * @param string             $action
	 * @param object             $args
	 * @return false|object
	 */
	public function theme_info( $result, $action, $args ) {
		if ( 'theme_information' !== $action ) {
			return $result;
		}

		if ( ! isset( $args->slug ) || $args->slug !== $this->slug ) {
			return $result;
		}

		$remote = $this->get_remote_version();

		if ( ! $remote ) {
			return $result;
		}

		$theme = wp_get_theme( $this->slug );

		$info                = new stdClass();
		$info->name          = $theme->get( 'Name' );
		$info->slug          = $this->slug;
		$info->version       = $remote['version'];
		$info->author        = $theme->get( 'Author' );
		$info->homepage      = 'https://github.com/' . $this->repo;
		$info->download_link = $this->get_zip_url( isset( $remote['sha'] ) ? $remote['sha'] : '' );
		$info->sections      = array(
			'description' => $theme->get( 'Description' ),
			'changelog'   => sprintf(
				'<p><a href="%s" target="_blank">গিটহাবে সম্পূর্ণ চেঞ্জলগ দেখুন →</a></p>',
				esc_url( 'https://github.com/' . $this->repo . '/commits/main' )
			),
		);

		return $info;
	}

	/* ------------------------------------------------------------------
	   3. Fix the extracted directory name
	   ------------------------------------------------------------------ */

	/**
	 * GitHub zips extract to "RepoName-main/". WordPress expects the
	 * folder to match the theme slug exactly. This filter renames it.
	 *
	 * @param string       $source        Extracted directory path.
	 * @param string       $remote_source
	 * @param WP_Upgrader  $upgrader
	 * @param array        $args
	 * @return string|WP_Error
	 */
	public function fix_directory_name( $source, $remote_source, $upgrader, $args ) {
		global $wp_filesystem;

		/* Only act on our own theme. */
		if (
			! isset( $args['theme'] ) ||
			$args['theme'] !== $this->slug
		) {
			return $source;
		}

		$correct_dest = trailingslashit( $remote_source ) . $this->slug . '/';

		if ( $source === $correct_dest ) {
			return $source;
		}

		if ( $wp_filesystem->move( $source, $correct_dest ) ) {
			return $correct_dest;
		}

		return new WP_Error(
			'rename_failed',
			'GitHub zip ফোল্ডারের নাম পরিবর্তন করা যায়নি।'
		);
	}

	/* ------------------------------------------------------------------
	   4. Clear cache after update
	   ------------------------------------------------------------------ */

	/**
	 * Purge our transient so the next check fetches fresh data.
	 *
	 * @param WP_Upgrader $upgrader
	 * @param array       $options
	 */
	public function clear_transient( $upgrader, $options ) {
		if (
			isset( $options['action'], $options['type'] ) &&
			'update' === $options['action'] &&
			'theme' === $options['type']
		) {
			delete_transient( $this->transient_key );

			/* The answer held for this page load describes the theme that
			   was just replaced, so it is no longer an answer. */
			$this->remote = null;
		}
	}

	/* ------------------------------------------------------------------
	   Private helpers
	   ------------------------------------------------------------------ */

	/**
	 * Fetch and cache the remote version from GitHub.
	 *
	 * @return array|false  Array with 'version' key, or false on failure.
	 */
	private function get_remote_version() {
		global $pagenow;

		/*
		 * One page load asks GitHub once, whatever happens afterwards.
		 *
		 * This sits behind site_transient_update_themes, which WordPress
		 * reads several times while an admin screen is drawn — and on the
		 * Themes and Updates screens the cache below is deliberately
		 * skipped. Without this line each of those reads opened its own
		 * connection, every one of them free to wait ten seconds, so a
		 * slow GitHub could hold Appearance → Themes for the better part
		 * of a minute before a single theme appeared.
		 *
		 * A failure is remembered along with a success, because repeating
		 * a failure is precisely what costs the ten seconds.
		 */
		if ( null !== $this->remote ) {
			return $this->remote;
		}

		/* When on the Updates or Themes screen, or when user clicks 'Check Again', bypass cache */
		$force = isset( $_GET['force-check'] ) || ( is_admin() && in_array( $pagenow, array( 'update-core.php', 'themes.php' ), true ) );

		if ( ! $force ) {
			$cached = get_transient( $this->transient_key );
			if ( false !== $cached ) {
				$this->remote = $cached;
				return $cached;
			}
		}

		/* Fetch raw style.css from main branch with cache-busting timestamp */
		$raw_url = sprintf(
			'https://raw.githubusercontent.com/%s/main/style.css?_=%d',
			$this->repo,
			time()
		);

		$response = wp_remote_get( $raw_url, array(
			'timeout' => 10,
			'headers' => array( 'Accept' => 'text/plain' ),
		) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$this->remote = false;
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( preg_match( '/^[ \t\/*#@]*Version:\s*(.+)$/mi', $body, $matches ) ) {
			$data = array(
				'version' => trim( $matches[1] ),
				/* Which commit that version was read out of. See get_zip_url(). */
				'sha'     => $this->get_head_sha(),
			);

			/* Cache for 2 hours in normal circumstances */
			set_transient( $this->transient_key, $data, 2 * HOUR_IN_SECONDS );

			$this->remote = $data;
			return $data;
		}

		$this->remote = false;
		return false;
	}

	/**
	 * Which commit the main branch is currently pointing at.
	 *
	 * Asked for as a bare string rather than as the commit's full JSON —
	 * that Accept header makes GitHub answer with forty characters instead
	 * of several kilobytes describing a commit nobody here needs to read.
	 *
	 * Unauthenticated calls to this API are limited to sixty an hour per
	 * address. The answer is kept in the same transient as the version, and
	 * the memo above holds it for the rest of the page load, so an ordinary
	 * day costs a handful.
	 *
	 * @return string Forty hex characters, or '' if it could not be had.
	 */
	private function get_head_sha() {
		$response = wp_remote_get(
			sprintf( 'https://api.github.com/repos/%s/commits/main', $this->repo ),
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept'     => 'application/vnd.github.sha',
					'User-Agent' => 'RaisulSohanSite-Updater',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}

		$sha = trim( wp_remote_retrieve_body( $response ) );

		/* Checked rather than trusted: this string is about to become part
		   of a download address, and an error page would otherwise be built
		   into one. */
		return preg_match( '/^[0-9a-f]{40}$/', $sha ) ? $sha : '';
	}

	/**
	 * Build the URL for downloading the repo as a zip.
	 *
	 * Pinned to a commit wherever one is known, because a branch's archive
	 * is a moving target: GitHub serves it from a cache that can still be
	 * holding the previous snapshot for a while after a push. WordPress
	 * would then install that older copy over the current one and report
	 * success, having genuinely done what it was asked — the update simply
	 * arrives as a copy of what was already there.
	 *
	 * A commit's archive cannot go stale. The address changes every time
	 * the content does, so a cached copy of it is always the right one.
	 *
	 * Falls back to the branch when the commit could not be looked up,
	 * since an update that might be stale still beats no update at all.
	 *
	 * @param string $sha Commit to pin to, or '' for the branch.
	 * @return string
	 */
	private function get_zip_url( $sha = '' ) {
		if ( $sha ) {
			return sprintf(
				'https://github.com/%s/archive/%s.zip',
				$this->repo,
				$sha
			);
		}

		return sprintf(
			'https://github.com/%s/archive/refs/heads/main.zip',
			$this->repo
		);
	}
}

/* Fire it up after WordPress has fully loaded the theme. */
add_action( 'after_setup_theme', function() {
	new RS_GitHub_Updater();
} );
