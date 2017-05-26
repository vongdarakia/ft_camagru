<?php 
/**
 * A templated for the user uploads on the home page.
 * This require the object $post and $relative_path to be present.
 *
 * $post Fields
 *      author_fn
 *      author_ln
 *      author_login
 *      author_email
 *      post_id
 *      like_id
 *      title
 *      img_file
 *      post_creation_date
 *
 * PHP version 5.5.38
 *
 * @category  Template
 * @package   Camagru
 * @author    Akia Vongdara <vongdarakia@gmail.com>
 * @copyright 2017 Akia Vongdara
 * @license   No License
 * @link      localhost:8080
 */

$img_path = $relative_path . POSTS_DIR_NAME . "/" . $post["img_file"];
if (!file_exists($img_path)) {
    return 0;
}

$size = getimagesize($img_path);
$width = $size[0];
$height = $size[1];
$boxSize = 150;

// Change for html img to work.
$img_path = SITE_DIR . "/" . POSTS_DIR_NAME . "/" . $post["img_file"];

// Makes sure to fill up the box when image sizes are not
// a 1:1 ratio.
if ($height < $width) {
    $class = "short-height";
    $offset = -($width * $boxSize / $height - $boxSize) / 2;
    $style = "left: " . $offset . "px;";
} else if ($width < $height) {
    $class = "short-width";
    $offset =  -($height * $boxSize / $width - $boxSize) / 2;
    $style = "top: " . $offset . "px;";
} else {
    $class = "perfect-box";
}
?>
<i class="btn-liked icon-heart-empty"></i>
<div class="user-post-box">
    <div class="crop">
        <img
            src="<?php echo $img_path; ?>"
            class="<?php echo $class; ?>"
            style="<?php echo $style; ?>"
        >
    </div>

    <?php 

    if (isset($_SESSION["user_email"]) && $_SESSION["user_email"] != "") {
        if ($post['like_id'] != null) {
            echo '<i class="btn-liked fa fa-heart" onclick="like(this)" id="' .$post['post_id'] . '"></i>';
        } else {
            echo '<i class="btn-like fa fa-heart-o" onclick="like(this)" id="' .$post['post_id'] . '"></i>';
        }
    }
    ?>
</div>
