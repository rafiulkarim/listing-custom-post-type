<?php
/*
Plugin Name: Listing
Description: Custom post listing
Author: Rafiul Karim
Author URI: https://automattic.com/wordpress-plugins/
Version: 1.0.1
 */

// Adding Custom Post Type for Listing
function Listing_setup_post_type() {
    $args = array(
        'public'    => true,
        'label'     => __( 'Listing', 'textdomain' ),
        'supports'           => array( 'title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-welcome-write-blog',
        'menu_position' => 6,
    );
    register_post_type( 'listing', $args );
}
add_action( 'init', 'Listing_setup_post_type' );


function op_register_menu_meta_box() {
    add_meta_box(
        'custom_post_parking_space',
        'Additional Information',
        'callback_custom_post_parking_space',
        'listing',
        'normal',
        'high'
    );
    add_meta_box(
        'custom_post_media_gallery',
        'Media Field',
        'callback_custom_post_media_gallery',
        'listing',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes_listing', 'op_register_menu_meta_box' );

function callback_custom_post_parking_space($post) {
    // Metabox content
    wp_nonce_field(basename(__FILE__), "address_and_parking_nonce");
    $data = get_post_meta($post->ID, "listing_info", true);
    ?>
    <style>
        .input-field{
            width: 100%;
        }
    </style>
    <p><b>Address</b></p>
    <input type="text" name="address" class="input-field" placeholder="Enter Listing Address" size="30" value="<?php echo $data[0] ?>">
    <p><b>Parking Space</b></p>
    <input type="text" name="parking_space" class="input-field" placeholder="Enter Parking Space" value="<?php echo $data[1] ?>">
    <?php
}



add_action('save_post', 'listing_post_data_save', 10, 2);

function listing_post_data_save($post_id, $post){
    if (!isset($_POST['address_and_parking_nonce']) || !wp_verify_nonce($_POST['address_and_parking_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    $post_slug = "listing";
    if ($post_slug != $post->post_type) {
        return;
    }

    $data = '';
    if ($_POST['address'] && $_POST['parking_space'] !== null) {
        $address = sanitize_text_field($_POST['address']);
        $parking_space = sanitize_text_field($_POST['parking_space']);
        $data = array($address, $parking_space);
    } else {
        $data = '';
    }
    update_post_meta($post_id, "listing_info", $data);
}

function callback_custom_post_media_gallery($post) {
    $banner_img = get_post_meta($post->ID,'post_banner_img',true);
    ?>
    <style type="text/css">
        .multi-upload-medias ul li .delete-img { position: absolute; right: 3px; top: 2px; background: aliceblue; border-radius: 50%; cursor: pointer; font-size: 14px; line-height: 20px; color: red; }
        .multi-upload-medias ul li { width: 120px; display: inline-block; vertical-align: middle; margin: 5px; position: relative; }
        .multi-upload-medias ul li img { width: 100%; }
    </style>

    <table cellspacing="10" cellpadding="10">
        <tr>
            <td>
                <?php echo multi_media_uploader_field( 'post_banner_img', $banner_img ); ?>
            </td>
        </tr>
    </table>

    <script type="text/javascript">
        jQuery(function($) {

            $('body').on('click', '.wc_multi_upload_image_button', function(e) {
                e.preventDefault();

                var button = $(this),
                    custom_uploader = wp.media({
                        title: 'Insert image',
                        button: { text: 'Use this image' },
                        multiple: true
                    }).on('select', function() {
                        var attech_ids = '';
                        attachments
                        var attachments = custom_uploader.state().get('selection'),
                            attachment_ids = new Array(),
                            i = 0;
                        attachments.each(function(attachment) {
                            attachment_ids[i] = attachment['id'];
                            attech_ids += ',' + attachment['id'];
                            if (attachment.attributes.type == 'image') {
                                $(button).siblings('ul').append('<li data-attechment-id="' + attachment['id'] + '"><a href="' + attachment.attributes.url + '" target="_blank"><img class="true_pre_image" src="' + attachment.attributes.url + '" /></a><i class=" dashicons dashicons-no delete-img"></i></li>');
                            } else {
                                $(button).siblings('ul').append('<li data-attechment-id="' + attachment['id'] + '"><a href="' + attachment.attributes.url + '" target="_blank"><img class="true_pre_image" src="' + attachment.attributes.icon + '" /></a><i class=" dashicons dashicons-no delete-img"></i></li>');
                            }

                            i++;
                        });

                        var ids = $(button).siblings('.attechments-ids').attr('value');
                        if (ids) {
                            var ids = ids + attech_ids;
                            $(button).siblings('.attechments-ids').attr('value', ids);
                        } else {
                            $(button).siblings('.attechments-ids').attr('value', attachment_ids);
                        }
                        $(button).siblings('.wc_multi_remove_image_button').show();
                    })
                        .open();
            });

            $('body').on('click', '.wc_multi_remove_image_button', function() {
                $(this).hide().prev().val('').prev().addClass('button').html('Add Media');
                $(this).parent().find('ul').empty();
                return false;
            });

        });

        jQuery(document).ready(function() {
            jQuery(document).on('click', '.multi-upload-medias ul li i.delete-img', function() {
                var ids = [];
                var this_c = jQuery(this);
                jQuery(this).parent().remove();
                jQuery('.multi-upload-medias ul li').each(function() {
                    ids.push(jQuery(this).attr('data-attechment-id'));
                });
                jQuery('.multi-upload-medias').find('input[type="hidden"]').attr('value', ids);
            });
        })
    </script>

    <?php
}


function multi_media_uploader_field($name, $value = '') {
    $image = '">Add Media';
    $image_str = '';
    $image_size = 'full';
    $display = 'none';
    $value = explode(',', $value);

    if (!empty($value)) {
        foreach ($value as $values) {
            if ($image_attributes = wp_get_attachment_image_src($values, $image_size)) {
                $image_str .= '<li data-attechment-id=' . $values . '><a href="' . $image_attributes[0] . '" target="_blank"><img src="' . $image_attributes[0] . '" /></a><i class="dashicons dashicons-no delete-img"></i></li>';
            }
        }

    }

    if($image_str){
        $display = 'inline-block';
    }

    return '<div class="multi-upload-medias"><ul>' . $image_str . '</ul><a href="#" class="wc_multi_upload_image_button button' . $image . '</a><input type="hidden" class="attechments-ids ' . $name . '" name="' . $name . '" id="' . $name . '" value="' . esc_attr(implode(',', $value)) . '" /><a href="#" class="wc_multi_remove_image_button button" style="display:inline-block;display:' . $display . '">Remove media</a></div>';
}

// Save Meta Box values.
add_action( 'save_post', 'wc_meta_box_save' );

function wc_meta_box_save( $post_id ) {
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if( !current_user_can( 'edit_post' ) ){
        return;
    }

    if( isset( $_POST['post_banner_img'] ) ){
        update_post_meta( $post_id, 'post_banner_img', $_POST['post_banner_img'] );
    }
}








