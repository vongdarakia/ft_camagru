<?php 
/**
 * Post page. User post their images.
 *
 * Resources:
 *      Flipping image: Googled "getmedia html5 mirror"
 *      https://www.christianheilmann.com/2013/07/19/
 *      flipping-the-image-when-accessing-the-laptop-camera-with-getusermedia/
 *
 * PHP version 5.5.38
 *
 * @category  Page
 * @package   Camagru
 * @author    Akia Vongdara <vongdarakia@gmail.com>
 * @copyright 2017 Akia Vongdara
 * @license   No License
 * @link      localhost:8080
 */

session_start();
require_once '../config/paths.php';
require_once '../config/connect.php';
require_once '../includes/lib/auth.php';
require_once '../includes/models/User.php';


// Maybe use this.
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     // …
// }
checkUserAuthentication();
$email = $_SESSION["user_email"];
$user = new User($dbh);
$query = "select
    u.first 'author_fn',
    u.last 'author_ln',
    u.username 'author_login',
    u.email 'author_email',
    p.title 'title',
    p.img_file 'img_file',
    p.creation_date 'post_creation_date'
from `user` u inner join `post` p on p.author_id = u.id
where u.email = '".$email."'
order by p.creation_date desc";
$info = $user->getDataByPage(1, 10, $query);
$relative_path = "../"; // Path to root;


require_once TEMPLATES_PATH . "/header.php";
?>

<div class="container" id="post-container">
    <?php  
    if (isset($_SESSION["err_msg"]) && $_SESSION["err_msg"] != "") {
        echo "Error: " . $_SESSION["err_msg"];
        $_SESSION["err_msg"] = "";
    }
    ?>
    <div class="main">
        <div class="wrapper">
            <input
                type="hidden" 
                value="<?php echo IMG_DIR ?>"
                id="img-dir"
            >
            <input
                type="hidden" 
                value="<?php echo POSTS_DIR ?>"
                id="post-dir"
            >

            <form id="form-sticker">
            <?php 
            $stickers = ["patrick-gasp.png", "doge.png", "mustache-glasses.png"];
            $stickerNames = ["Patrick Gasp", "Doge", "Mustache Glasses"];
            for ($i=0; $i < 3; $i++) {
                echo '<label class="radio-inline">
                    <input type="radio" name="opt-sticker" onclick="changeSticker(this)" value="'.$i.'">
                    <div class="sticker-box" title="'. $stickerNames[$i] .'">
                        <img src="'. IMG_DIR . $stickers[$i] .'">
                    </div>
                </label>';
            }
            ?>
            </form>
            <div id="modes">
                <input class="mode-radio" type="radio" name="mode" id="camera-mode" value="camera" onchange="changeMode(this)" checked>
                <label for="camera-mode">Camera Mode</label>

                <input class="mode-radio" type="radio" name="mode" id="upload-mode" value="upload" onchange="changeMode(this)">
                <label for="upload-mode">Upload Mode</label>
            </div>

            <div id="file-wrapper">
                <input class="file-input" type="file" name="file" id="file" onchange="fileChange(this)">
                <label for="file" id="file-label"></label>
            </div>
                
            <div id="booth">
                <div id="video-wrapper">
                    <img src="" id="sticker-img">
                    <img src="" id="camera-img">
                    <video id="camera" width="400" height="300"></video>
                </div>

                <a href="#" id="btn-capture">Capture</a>

                <div id="captured-wrapper" class="" title="Captured Picture">
                    <img src="" id="captured-cam-img">
                    <img src="" id="captured-sticker-img">
                </div>

                <!-- These are used for converting images to base64 -->
                <canvas id="camera-canvas" width="400" height="300"></canvas>
                <canvas id="sticker-canvas" width="400" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="side">
        <div id="side-photos" class="wrapper">
            <div id="photos-header">
                <h3>Recent Photos</h3>
                <hr class="style14">
            </div>
            <div id="photos">
                <?php 
                foreach ($info->rows as $row) {
                    include '../templates/user_upload_box.php';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo JS_DIR . "main.js" ?>"></script>
<script src="<?php echo JS_DIR . "post.js" ?>">
    
</script>

<?php require_once TEMPLATES_PATH . "/footer.php"; ?>