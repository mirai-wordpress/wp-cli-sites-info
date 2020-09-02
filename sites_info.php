<?php

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class Sites_Info extends WP_CLI_Command {

		protected $db;

		public function __construct() {
			$this->db = $GLOBALS['wpdb'];
		}

		public function __invoke( $args ) {

			if ( ! is_multisite() ) {
				WP_CLI::error( 'This is not a multisite installation. This command is for multisite only.' );
			}

			if ( count( $args ) < 1 ) {
				WP_CLI::error( 'No argument found. Usage: wp sites_info www.hotel-moderno.com' );
			}

			$dmtable = $this->db->base_prefix . 'domain_mapping';

			foreach ($args as $dm_domain ) {

				// Add www. if not present.
				if( substr( $dm_domain, 0, 4 ) !== 'www.' ) {
					$dm_domain = 'www.' . $dm_domain;
				}

				$where   = $this->db->prepare( 'domain = %s', $dm_domain );
				$site_id = $this->db->get_var( "SELECT blog_id FROM {$dmtable} WHERE {$where} AND active = 1 ORDER BY CHAR_LENGTH(domain) DESC LIMIT 1" );

				$options = array(
				  'return' => true,
				);
				$theme = WP_CLI::runcommand( 'option get current_theme --url=' . $dm_domain, $options );
				$url = WP_CLI::runcommand( 'site list --site__in=' . $site_id . ' --field=url', $options );

				$parsed_url = wp_parse_url( $url );
				echo "$site_id, {$dm_domain}, {$parsed_url['host']}, $theme\n";
			}
		}
	}

	WP_CLI::add_command( 'sites_info', 'Sites_Info' );
}
