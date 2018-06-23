<?php
/**
 * Powerful utility trait for adding custom filters in the WP Admin
 *
 * @copyright 2018 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace Conifer\Post;

use Timber\Timber;

/**
 * Declaratively add custom admin filter options.
 */
trait HasCustomAdminFilters {
  /**
   * Add a custom filter to the admin for the given post type, with custom
   * query behavior provided through a callback.
   *
   * @param string $name the form input name for the filter
   * @param array $options the options to display in the filter dropdown
   * @param callable $queryModifier a callback to mutate the WP_Query object
   * at query time
   * a given post. Takes a post ID as its sole parameter.
   */
  public static function add_admin_filter(
    string $name,
    array $options,
    string $postType,
    callable $queryModifier
  ) {
    add_action('restrict_manage_posts', function() use ($name, $options, $postType) {

      // we only want to render the filter menu if we're on the
      // edit screen for the given post type
      if ( static::allow_custom_filtering() ) {

        // default to blank string, which should mean "any"
        // NOTE: WordPress already verifies the nonce in this context;
        // no need to do so again
        // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
        $value = $_GET[$name] ?? '';

        static::render_custom_filter_select([
          'name'           => $name,
          'options'        => $options,
          'filtered_value' => $value,
        ]);
      }
    });

    add_action('pre_get_posts', function(\WP_Query $query) use ($name, $postType, $queryModifier) {
      if ( static::querying_by_custom_filter($name, $postType, $query) ) {
        // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
        $queryModifier($query, $_GET[$name]);
      }
    });
  }

  /**
   * Render the <select> element for an arbitrary custom admin filter.
   * Override this to customize the dropdown further.
   *
   * @param array $data the view data
   */
  protected static function render_custom_filter_select( array $data ) {
    Timber::render( 'admin/custom-filter-select.twig', $data );
  }

  /**
   * Whether to show the custom filter on the edit screen for the given post type.
   */
  protected static function allow_custom_filtering() : bool {
    return ($GLOBALS['post_type'] ?? null) === static::_post_type()
      && ($GLOBALS['pagenow'] ?? null) === 'edit.php';
  }

  /**
   * Whether the user is currently trying to query by the custom filter, according to GET params
   * and the current post type; determines whether the current WP_Query needs to be modified.
   *
   * @param string $name the filter name, i.e. the <input> element's "name" attribute
   * @param string $postType the post type to which the custom filter applies
   * @param WP_Query $query the current WP_Query object
   */
  protected static function querying_by_custom_filter( $name, $postType, \WP_Query $query ) {
    return static::allow_custom_filtering( $postType )
      && $query->query_vars['post_type'] === $postType
      // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
      && !empty( $_GET[$name] );
  }
}
