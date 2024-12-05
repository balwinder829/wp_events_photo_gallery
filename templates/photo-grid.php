<div class="epa-photo-title">
    <h1>
    <?php 
    echo get_the_title($album_id).' ('.$total_photos.')';
    ?></h1>
</div>
<div class="epa-photo-content">
    <div class="epa-search-container">
        <div class="epa-search-box">
            <input type="text" class="epa-search-input" placeholder="Search by file name" />
            <button class="epa-search-button" data-album-id="<?php echo $album_id;?>">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <div class="epa-view-toggle">
            <button class="epa-list-view-button" data-view="list">
                <i class="fas fa-list"></i>
                <span class="epa-list-view-text">list view</span>
            </button>
        </div>
    </div>
</div>
<div class="epa-photo-container epa-photo-grid-container">
    <?php foreach ( $photos as $photo ) :
        $user_id = get_current_user_id(); 
        $wishlist = get_user_meta( $user_id, '_epa_wishlist', true );
        $is_in_wishlist = false;
        if (is_array($wishlist)) {
            foreach ($wishlist as $item) {
                if (isset($item->photo_id) && $item->photo_id == $photo->ID) {
                    $is_in_wishlist = true;
                    break;
                }
            }
        }
        $heart_icon = $is_in_wishlist ? 'fas' : 'far'; 
        $save_collection = $is_in_wishlist ? '' : 'Save to collection'; 
        ?>
        <div class="epa-photo-item epa-photo-grid">
            <?php
            $thumbnail_url = wp_get_attachment_image_url( $photo->ID, 'medium' );
            $watermark_img = get_post_meta($photo->ID,'watermark_img', true);
            $file_name = basename( get_attached_file( $photo->ID ) ); // Λήψη ονόματος αρχείου
            if (empty($watermark_img)) {
                $watermark_img = $thumbnail_url;
            }
            ?>
            <img src="<?php echo esc_url( $watermark_img ); ?>" data-full-url="<?php echo esc_url( $watermark_img ); ?>" alt="Photo">
            <p class="epa-photo-filename"><?php echo esc_html( pathinfo( $file_name, PATHINFO_FILENAME ) ); ?></p> <!-- Εμφάνιση ονόματος αρχείου -->
            <span class="epa-wishlist-icon" data-photo-id="<?php echo esc_attr( $photo->ID ); ?>">
                <i class="<?php echo $heart_icon;?> fa-heart"></i>
                <p class="save-collection"><?php echo $save_collection;?></p>
            </span>

        </div>
    <?php endforeach; ?>
</div>
<?php 
if($no_more_images == 1){ ?>
<div class="epa-photo-load-more">
    <button class="epa-load-more" data-album-id="<?php echo esc_attr( $album_id ); ?>">Load More</button>
</div>
<?php } ?>

<?php 
global $wpdb;
$table_name = $wpdb->prefix . 'user_collections';
$user_id = get_current_user_id();
$all_collections = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d",
        $user_id
    )
);
?>
<div class="epa-popup-container" id="epapopupContainer">
    <div class="epa-popup">
        <button class="epa-close-popup-btn" id="epaClosePopupBtn">×</button>
        <div class="epa-popup-left">
            <h2>Choose where to save:</h2>
            <img src="<?php echo EPA_PLUGIN_URL.'assets/collection-icon.png';?>" alt="Image" class="epa-popup-image">
            <p class="epa-image-name">IMG_16563</p>
        </div>
        <div class="epa-popup-right">
            <h2>Create new collection</h2>
            <div class="epa-new-collection">
                <input type="text" placeholder="Collection name" class="epa-collection-name">
                <button class="epa-add-collection-btn" data-thumbnail-id="0" data-user-id="<?php echo get_current_user_id();?>">+</button>
            </div>
            <h2>Add to existing collection</h2>

            <div class="epa-existing-collection">
                <div class="epa-search-bar">
                    <input type="text" placeholder="Search through your collections" class="epa-search-collection-input">
                    <i class="fas fa-search epa-search-collection-button"></i>
                </div>
                <div class="epa-collection-list">
                </div>
            </div>
            <button class="epa-save-btn" data-id="<?php echo $album_id;?>" disabled>SAVE</button>
        </div>
     </div>
</div>