<?php
/**
 * Meta Box — Product Edit Screen
 *
 * Renders a DetIt SEO panel inside the WooCommerce product edit screen.
 * Always shows DetIt's fields. When a SEO plugin is detected, a notice
 * informs the user that the values will also be written to that plugin.
 *
 * @package DetIt
 * @since   1.0.0
 */

namespace DetIt\Meta;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Meta_Box {

    /**
     * Register hooks.
     *
     * @return void
     */
    public static function register(): void {
        add_action( 'add_meta_boxes', [ self::class, 'add_meta_box' ] );
        add_action( 'save_post_product', [ self::class, 'save' ], 10, 2 );
    }

    /**
     * Register the meta box on the product post type.
     *
     * @return void
     */
    public static function add_meta_box(): void {
        add_meta_box(
            'detit-seo',
            __( 'DetIt SEO', 'detit' ),
            [ self::class, 'render' ],
            'product',
            'normal',
            'high'
        );
    }

    /**
     * Render the meta box HTML.
     *
     * @param  \WP_Post $post
     * @return void
     */
    public static function render( \WP_Post $post ): void {
        $product_id = $post->ID;
        $plugin     = SEO_Detector::detect();

        $desc    = Meta_Handler::get_meta_description( $product_id );
        $title   = Meta_Handler::get_meta_title( $product_id );
        $keyword = Meta_Handler::get_focus_keyword( $product_id );
        $score   = Meta_Handler::get_seo_score( $product_id );
        $scanned = Meta_Handler::get_last_scan( $product_id );

        wp_nonce_field( 'detit_save_meta_' . $product_id, 'detit_meta_nonce' );

        ?>
        <div class="detit-meta-box">

            <?php if ( SEO_Detector::has_seo_plugin() ) : ?>
                <div class="detit-notice detit-notice--info">
                    <?php
                    $plugin_label = self::plugin_label( $plugin );
                    printf(
                        /* translators: %s: SEO plugin name */
                        esc_html__( '%s detected. DetIt will write to that plugin\'s meta fields automatically.', 'detit' ),
                        '<strong>' . esc_html( $plugin_label ) . '</strong>'
                    );
                    ?>
                </div>
            <?php endif; ?>

            <div class="detit-field">
                <label for="detit_meta_title">
                    <?php esc_html_e( 'Meta Title', 'detit' ); ?>
                </label>
                <input
                    type="text"
                    id="detit_meta_title"
                    name="detit_meta_title"
                    value="<?php echo esc_attr( $title ); ?>"
                    maxlength="70"
                    class="widefat"
                />
                <p class="detit-hint">
                    <?php esc_html_e( 'Recommended: 50–60 characters. Leave blank to use the product name.', 'detit' ); ?>
                    <span class="detit-char-count" data-target="detit_meta_title" data-max="60">
                        <?php echo esc_html( strlen( $title ) ); ?>/60
                    </span>
                </p>
            </div>

            <div class="detit-field">
                <label for="detit_meta_description">
                    <?php esc_html_e( 'Meta Description', 'detit' ); ?>
                </label>
                <textarea
                    id="detit_meta_description"
                    name="detit_meta_description"
                    rows="3"
                    maxlength="320"
                    class="widefat"
                ><?php echo esc_textarea( $desc ); ?></textarea>
                <p class="detit-hint">
                    <?php esc_html_e( 'Recommended: 120–158 characters.', 'detit' ); ?>
                    <span class="detit-char-count" data-target="detit_meta_description" data-max="158">
                        <?php echo esc_html( strlen( $desc ) ); ?>/158
                    </span>
                </p>
            </div>

            <div class="detit-field">
                <label for="detit_focus_keyword">
                    <?php esc_html_e( 'Focus Keyword', 'detit' ); ?>
                </label>
                <input
                    type="text"
                    id="detit_focus_keyword"
                    name="detit_focus_keyword"
                    value="<?php echo esc_attr( $keyword ); ?>"
                    class="widefat"
                />
                <p class="detit-hint">
                    <?php esc_html_e( 'The primary keyword this product page should rank for.', 'detit' ); ?>
                </p>
            </div>

            <?php if ( $score > 0 ) : ?>
                <div class="detit-score-bar">
                    <span class="detit-score-label"><?php esc_html_e( 'SEO Score', 'detit' ); ?></span>
                    <span class="detit-score-value detit-score-<?php echo esc_attr( self::score_class( $score ) ); ?>">
                        <?php echo esc_html( $score ); ?>/100
                    </span>
                    <?php if ( $scanned ) : ?>
                        <span class="detit-score-date">
                            <?php
                            printf(
                                /* translators: %s: relative time */
                                esc_html__( 'Last scanned %s', 'detit' ),
                                esc_html( human_time_diff( $scanned ) . ' ago' )
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
        <?php
    }

    /**
     * Save meta box values on product save.
     *
     * @param  int       $product_id
     * @param  \WP_Post  $post
     * @return void
     */
    public static function save( int $product_id, \WP_Post $post ): void {
        // Verify nonce.
        if (
            ! isset( $_POST['detit_meta_nonce'] ) ||
            ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['detit_meta_nonce'] ) ), 'detit_save_meta_' . $product_id )
        ) {
            return;
        }

        // Skip auto-saves and revisions.
        if ( wp_is_post_autosave( $product_id ) || wp_is_post_revision( $product_id ) ) {
            return;
        }

        // Capability check.
        if ( ! current_user_can( 'edit_post', $product_id ) ) {
            return;
        }

        if ( isset( $_POST['detit_meta_title'] ) ) {
            Meta_Handler::save_meta_title( $product_id, wp_unslash( $_POST['detit_meta_title'] ) );
        }

        if ( isset( $_POST['detit_meta_description'] ) ) {
            Meta_Handler::save_meta_description( $product_id, wp_unslash( $_POST['detit_meta_description'] ) );
        }

        if ( isset( $_POST['detit_focus_keyword'] ) ) {
            Meta_Handler::save_focus_keyword( $product_id, wp_unslash( $_POST['detit_focus_keyword'] ) );
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Human-readable label for a detected SEO plugin.
     *
     * @param  string $plugin
     * @return string
     */
    private static function plugin_label( string $plugin ): string {
        return match ( $plugin ) {
            SEO_Detector::PLUGIN_YOAST    => 'Yoast SEO',
            SEO_Detector::PLUGIN_RANKMATH => 'Rank Math',
            SEO_Detector::PLUGIN_AIOSEO   => 'All in One SEO',
            default                        => 'Unknown SEO Plugin',
        };
    }

    /**
     * CSS class for the score colour indicator.
     *
     * @param  int $score 0–100.
     * @return string     'good' | 'ok' | 'poor'
     */
    private static function score_class( int $score ): string {
        if ( $score >= 70 ) return 'good';
        if ( $score >= 40 ) return 'ok';
        return 'poor';
    }
}
