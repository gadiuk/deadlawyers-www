<?php
/**
 * Plugin Name: DLS Company EDRPOU Admin
 * Description: Adds an EDRPOU / ЄДРПОУ field to Company admin screens for OpenDataBot data.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'dls_mu_normalize_company_edrpou' ) ) {
    function dls_mu_normalize_company_edrpou( $value ) {
        $digits = preg_replace( '/\D+/', '', (string) $value );

        if ( ! is_string( $digits ) || strlen( $digits ) < 8 || strlen( $digits ) > 10 ) {
            return '';
        }

        return $digits;
    }
}

if ( ! function_exists( 'dls_mu_get_company_edrpou' ) ) {
    function dls_mu_get_company_edrpou( $company_id ) {
        foreach ( array( '_company_edrpou', 'company_edrpou', '_edrpou', 'edrpou', '_company_code', 'company_code', 'code' ) as $key ) {
            $value = dls_mu_normalize_company_edrpou( get_post_meta( $company_id, $key, true ) );

            if ( '' !== $value ) {
                return $value;
            }
        }

        return '';
    }
}

if ( ! function_exists( 'dls_mu_register_company_edrpou_meta_box' ) ) {
    function dls_mu_register_company_edrpou_meta_box() {
        add_meta_box(
            'dls-company-edrpou',
            'EDRPOU / ЄДРПОУ',
            'dls_mu_render_company_edrpou_meta_box',
            'company',
            'normal',
            'high'
        );
    }
}
add_action( 'add_meta_boxes_company', 'dls_mu_register_company_edrpou_meta_box', 5 );

if ( ! function_exists( 'dls_mu_render_company_edrpou_meta_box' ) ) {
    function dls_mu_render_company_edrpou_meta_box( $post ) {
        $value = dls_mu_get_company_edrpou( $post->ID );

        wp_nonce_field( 'dls_company_edrpou_save', 'dls_company_edrpou_nonce' );
        ?>
        <p>
            <label for="dls-company-edrpou-field"><strong>EDRPOU / ЄДРПОУ company code</strong></label>
        </p>
        <input
            type="text"
            id="dls-company-edrpou-field"
            name="dls_company_edrpou"
            value="<?php echo esc_attr( $value ); ?>"
            class="widefat"
            inputmode="numeric"
            pattern="[0-9]*"
            maxlength="10"
        />
        <p class="description">Used for OpenDataBot financial data on job and company pages.</p>
        <?php
    }
}

if ( ! function_exists( 'dls_mu_save_company_edrpou_meta_box' ) ) {
    function dls_mu_save_company_edrpou_meta_box( $post_id ) {
        if ( empty( $_POST['dls_company_edrpou_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dls_company_edrpou_nonce'] ) ), 'dls_company_edrpou_save' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $value = isset( $_POST['dls_company_edrpou'] )
            ? dls_mu_normalize_company_edrpou( wp_unslash( $_POST['dls_company_edrpou'] ) )
            : '';

        if ( '' === $value ) {
            delete_post_meta( $post_id, '_company_edrpou' );
            return;
        }

        update_post_meta( $post_id, '_company_edrpou', $value );
        delete_transient( 'dls_odb_company_' . $value );
    }
}
add_action( 'save_post_company', 'dls_mu_save_company_edrpou_meta_box' );

if ( ! function_exists( 'dls_mu_add_company_edrpou_column' ) ) {
    function dls_mu_add_company_edrpou_column( $columns ) {
        if ( ! is_array( $columns ) || isset( $columns['dls_company_edrpou'] ) ) {
            return $columns;
        }

        $new = array();
        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;

            if ( 'title' === $key ) {
                $new['dls_company_edrpou'] = 'EDRPOU';
            }
        }

        if ( ! isset( $new['dls_company_edrpou'] ) ) {
            $new['dls_company_edrpou'] = 'EDRPOU';
        }

        return $new;
    }
}
add_filter( 'manage_edit-company_columns', 'dls_mu_add_company_edrpou_column', 50 );

if ( ! function_exists( 'dls_mu_render_company_edrpou_column' ) ) {
    function dls_mu_render_company_edrpou_column( $column, $post_id ) {
        if ( 'dls_company_edrpou' !== $column ) {
            return;
        }

        $edrpou = dls_mu_get_company_edrpou( $post_id );
        if ( $edrpou ) {
            echo esc_html( $edrpou );
            return;
        }

        echo '<span style="color:#999;">-</span>';
    }
}
add_action( 'manage_company_posts_custom_column', 'dls_mu_render_company_edrpou_column', 50, 2 );
