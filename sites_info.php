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

			$dm_domain = $args[0];

			if( ( $nowww = preg_replace( '|^www\.|', '', $dm_domain ) ) != $dm_domain )
				$where = $this->db->prepare( 'domain IN (%s,%s)', $dm_domain, $nowww );
			else
				$where = $this->db->prepare( 'domain = %s', $dm_domain );

			$site_id = $this->db->get_var( "SELECT blog_id FROM {$dmtable} WHERE {$where} ORDER BY CHAR_LENGTH(domain) DESC LIMIT 1" );

			$options = array(
			  'return'     => true
			);
			$theme = WP_CLI::runcommand( 'option get current_theme --url=' . $args[0], $options );

			$url = WP_CLI::runcommand( 'site list --site__in=' . $site_id . ' --field=url', $options );

			$parsed_url = wp_parse_url( $url );
			echo "$site_id, {$args[0]}, {$parsed_url['host']}, $theme\n";
		}
	}

	WP_CLI::add_command( 'sites_info', 'Sites_Info' );
}
