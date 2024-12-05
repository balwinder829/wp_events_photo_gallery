<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Shortcodes {

    public function __construct() {
        add_shortcode( 'user_albums', array( $this, 'display_user_albums' ) );
        add_shortcode( 'user_collections', array( $this, 'display_user_collections' ) );
        add_shortcode( 'user_wishlist_photos', array( $this, 'display_user_wishlist_photos' ) ); // Νέο shortcode
    }

    public function display_user_wishlist_photos( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>Please log in to view your wishlist.</p>';
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_collections';
        $collection_id = isset( $_GET['collection_id'] ) ? intval( $_GET['collection_id'] ) : 0;
        $user_id = get_current_user_id();
        $all_collections = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d",
                $user_id
            )
        ); 

        $collection_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $collection_id
            ),
        ); 

        if ( $collection_id ) {

            $all_collection_photos = get_user_meta( $user_id, '_epa_wishlist', true );
            $page = 1;
            $posts_per_page =   get_option( 'epa_number_of_images', 10 )  ;
            $collection_name = '';
            $collection = [] ;
        
            if ( ! is_array( $all_collection_photos ) || empty( $all_collection_photos ) ) {
                return '<p>You have no photos in your collection.</p>';
            }
            
            foreach($all_collection_photos as $all_collection_photo){
                if (isset($all_collection_photo->collection_id) && $all_collection_photo->collection_id == $collection_id) {
                    $collection[] = $all_collection_photo->photo_id;
                }
            }

            if ( empty( $collection ) ) {
                return '<p>No photos found in your collection.</p>';
            }
            
            // Fetching the photos based on IDs in collection

            $args = array(
                'post_type'       => 'attachment',
                'paged'           => $page,
                'post__in'        => $collection,
                'posts_per_page'  => $posts_per_page,
                'orderby'         => 'post__in', // Maintain the collection order
            );
        
            $photos = get_posts( $args );
            
            $no_more_images = count($photos) < $posts_per_page ? 0 : 1;
            $total_collection_image = count($collection);
            $collection_name = isset($collection_data[0]->name) ? $collection_data[0]->name : '';

            ob_start();
            include EPA_PLUGIN_DIR . 'templates/wishlist-photos.php';
            return ob_get_clean();
        } else{

            if(empty($all_collections)){
                return '<p>You have no collection in your wishlist.</p>';
            }

            ob_start();
            echo '<div class="epa-collection-album-grid">';
            foreach ( $all_collections as $all_collection ) : 
                echo '<div class="epa-collection-album-item">';
                echo '<a href="' . esc_url( add_query_arg( 'collection_id', $all_collection->id ) ) . '">';
                echo '<img src="' . EPA_PLUGIN_URL . 'assets/folder-icon.png" alt="' . esc_attr( $all_collection->name ) . '">';
                echo '<h3>' . esc_html( $all_collection->name ) . '</h3>';
                echo '</a>';
                echo '<div class="my-photos-action"><span class="epa-edit-collection" data-collection-name="' .esc_attr( $all_collection->name ). '" data-collection-id="' .esc_attr( $all_collection->id ). '" data-collection-id="1">Edit</span>';
                echo '<span class="epa-remove-collection" data-collection-id="' .esc_attr( $all_collection->id ). '" data-collection-id="1">Delete</span></div>';
                echo '</div>';
            endforeach;
            echo '</div>';
    
            return ob_get_clean();
        }
        
    }
    


    public function display_album_photos( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>Please log in to view this album.</p>';
        }
    
        // Λήψη του album_id από το URL
        $album_id = isset( $_GET['album_id'] ) ? intval( $_GET['album_id'] ) : 0;
    
        if ( ! $album_id ) {
            return '<p>Album not found.</p>';
        }
    
        // Λήψη των φωτογραφιών του album
        $photo_manager = new Photo_Manager();
        $photos = $photo_manager->get_album_photos( $album_id );
    
        if ( empty( $photos ) ) {
            return '<p>No photos found in this album.</p>';
        }
    
        // Χρήση του template photo-grid.php
        ob_start();
        include EPA_PLUGIN_DIR . 'templates/photo-grid.php';
        return ob_get_clean();
    }
    


    public function display_user_albums( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>Please log in to view your albums.</p>';
        }
    
        $user_id = get_current_user_id();
        $album_manager = new Album_Manager();
        $album_id = isset( $_GET['album_id'] ) ? intval( $_GET['album_id'] ) : 0;
        $page = 1;
        $posts_per_page =   get_option( 'epa_number_of_images', 10 )  ;

        if ( $album_id ) {

            $args = array(
                'post_type'      => 'attachment',
                'posts_per_page' => -1,
                'post_status'    => 'inherit',
                'post_parent'    => $album_id,
            );
            
            $allphotos = get_posts( $args );
            $total_photos = count($allphotos);


            // Αν υπάρχει album_id, εμφανίζουμε τις φωτογραφίες του album
            $photo_manager = new Photo_Manager();
            $photos = $photo_manager->get_album_photos( $album_id, $page, $posts_per_page );
            if ( empty( $photos ) ) {
                return '<p>No photos found in this album.</p>';
            }
            $no_more_images = count($photos) < $posts_per_page ? 0 : 1;
    
            // Χρήση του template photo-grid.php
            ob_start();
            include EPA_PLUGIN_DIR . 'templates/photo-grid.php';
            return ob_get_clean();
        } else {
            // Εμφάνιση λίστας albums εάν δεν υπάρχει album_id
            $albums = $album_manager->get_user_albums( $user_id );
    
            if ( empty( $albums ) ) {
                return '<p>No albums assigned.</p>';
            }
    
            ob_start();
            echo '<div class="epa-album-grid">';
            foreach ( $albums as $album ) : 
                $thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id($album->ID), 'thumbnail');
                if(!empty($thumbnail_url)){
                    $thumbnail_url = $thumbnail_url[0];
                }else{
                    $thumbnail_url = EPA_PLUGIN_URL . 'assets/folder-icon.png';
                }
                echo '<div class="epa-album-item">';
                echo '<a href="' . esc_url( add_query_arg( 'album_id', $album->ID ) ) . '">';
                echo '<img src="' . $thumbnail_url . '" alt="' . esc_attr( $album->post_title ) . '">';
                echo '<h3>' . esc_html( $album->post_title ) . '</h3>';
                echo '</a>';
                echo '</div>';
            endforeach;
            echo '</div>';
    
            return ob_get_clean();
        }
    }
    
    
}
